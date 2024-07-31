<?php

namespace MPHB\Admin\MenuPages;

use \MPHB\iCal\LogsHandler;
use \MPHB\iCal\Stats;

/**
 * Process "upload" and "sync" calls. But render only "upload". All "sync"
 * requests redirected to iCalSyncLogsMenuPage.
 */
class iCalImportMenuPage extends AbstractMenuPage {

	/**
	 * "upload" or "sync"
	 *
	 * @var string
	 */
	private $action = '';

	/**
	 *
	 * @var int
	 */
	private $roomId = 0;

	/**
	 * File uploaded and ready to import.
	 *
	 * @var bool
	 */
	private $fileUploaded = false;

	/**
	 * Background import is in progress.
	 *
	 * @var bool
	 */
	private $isUploading = false;

	public function __construct( $name, $atts = array() ) {
		parent::__construct( $name, $atts );

		$this->action       = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$this->fileUploaded = $this->action == 'upload' && isset( $_FILES['import'] );
	}

	public function addActions() {
		parent::addActions();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
	}

	/**
	 *
	 * @global \WP_Scripts $wp_scripts
	 */
	public function enqueueAdminScripts() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		wp_enqueue_style( 'mphb-admin-ical-css', MPHB()->getPluginUrl( 'assets/css/admin-ical.min.css' ), array(), MPHB()->getVersion() );

		if ( $this->isUploading ) {
			wp_enqueue_script( 'mphb-admin-ical', MPHB()->getPluginUrl( 'assets/js/admin/admin-ical.min.js' ), array( 'jquery', 'mphb-canjs' ), MPHB()->getVersion(), true );

			wp_localize_script(
				'mphb-admin-ical',
				'MPHB_iCal',
				array(
					'ajaxUrl'    => MPHB()->getAjaxUrl(),
					'actions'    => array(
						'upload' => array(
							'progress' => 'mphb_ical_upload_get_progress',
							'abort'    => 'mphb_ical_upload_abort',
						),
					),
					'nonces'     => MPHB()->getAjax()->getAdminNonces(),
					'i18n'       => array(
						'abort'    => __( 'Abort Process', 'motopress-hotel-booking' ),
						'aborting' => __( 'Aborting...', 'motopress-hotel-booking' ),
					),
					'inProgress' => $this->isUploading,
				)
			);
		}
	}

	/**
	 * Process both uploads and syncs. But all syncs then will be redirected to
	 * page mphb_sync_logs.
	 */
	public function onLoad() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		// TODO Simplify IDs detection?
		$id = ( isset( $_GET['accommodation_id'] ) ? intval( $_GET['accommodation_id'] ) : 0 );
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$ids = ( isset( $_GET['accommodation_ids'] ) ? $_GET['accommodation_ids'] : null );

		if ( $id ) {
			$ids = array( $id );
		} elseif ( is_null( $ids ) ) {
			$ids = array();
		} elseif ( $ids == 'all' ) {
			$ids = MPHB()->getRoomPersistence()->getPosts(
				array(
					'orderby' => 'ID',
					'order'   => 'ASC',
				)
			);
		} elseif ( strpos( $ids, ',' ) !== false ) {
			$ids = \MPHB\Utils\ValidateUtils::validateCommaSeparatedIds( $ids );
		} else {
			$ids = array();
		}

		$this->roomId = $id;

		switch ( $this->action ) {

			case 'upload':
				$uploader = MPHB()->getICalUploader();
				if ( $this->fileUploaded ) {
					$uploader->reset();
					// do not unslash $_FILES['import']['tmp_name'] otherwise there is a bug in file path on windows
					// phpcs:ignore
					$calendarURI = isset( $_FILES['import']['tmp_name'] ) ? sanitize_text_field( $_FILES['import']['tmp_name'] ) : '';
					$uploader->parseCalendar( $id, $calendarURI );
					$this->isUploading = true;
				} else {
					$this->isUploading = $uploader->isInProgress();
					$uploader->touch();
				}
				break;

			case 'sync':
				MPHB()->getQueuedSynchronizer()->sync( $ids );
				$args = array(
					'page' => 'mphb_sync_logs',
				);
				wp_safe_redirect( $this->getUrl( $args ) );
				break;

		} // switch ( $this->action )
	}

	/**
	 * Render upload page only. All syncs will be redirected to page mphb_sync_logs.
	 */
	public function render() {
		$room        = MPHB()->getRoomRepository()->findById( $this->roomId );
		$roomType    = $room ? MPHB()->getRoomTypeRepository()->findById( $room->getRoomTypeId() ) : null;
		$uploader    = MPHB()->getICalUploader();
		$logsHandler = new LogsHandler();

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Import Calendar', 'motopress-hotel-booking' ); ?></h1>
			<a class="page-title-action wp-exclude-emoji" href="<?php echo esc_url( MPHB()->getICalMenuPage()->getUrl() ); ?>"><?php esc_html_e( 'Back', 'motopress-hotel-booking' ); ?> &#10548;&#xFE0E;</a>
			<hr class="wp-header-end" />
			<ul>
				<?php if ( $room ) { ?>
					<li><?php printf( esc_html__( 'Accommodation: %s', 'motopress-hotel-booking' ), '<strong>' . wp_kses_post( $room->getTitle() ) . '</strong>' ); ?></li>
				<?php } ?>
				<?php if ( $roomType ) { ?>
					<li><?php printf( esc_html__( 'Accommodation Type: %s', 'motopress-hotel-booking' ), '<a target="_blank" href="' . esc_url( $roomType->getLink() ) . '">' . esc_html( $roomType->getTitle() ) . '</a>' ); ?></li>
				<?php } ?>
			</ul>

			<div class="mphb-upload-import-details-wrapper">
				<?php
				if ( $this->fileUploaded ) {
					echo '<p>', esc_html__( 'Please be patient while the calendars are imported. You will be notified via this page when the process is completed.', 'motopress-hotel-booking' ), '</p>';
				}
				if ( $this->isUploading ) {
					$logsHandler->displayProgress();
				}
				?>

				<hr class="wp-header-end" />

				<?php
				if ( ! $this->fileUploaded && ! $this->isUploading ) {
					wp_import_upload_form( 'admin.php?page=mphb_ical_import&action=upload&accommodation_id=' . $this->roomId );
				}

				// Show process information (only if uploading current file)
				if ( $this->isUploading ) {
					// Load logs and counts via AJAX
					$processDetails = array(
						'logs'  => array(),
						'stats' => Stats::emptyStats(),
					);
					$logsHandler->display( $processDetails );
				}

				// Show "Back" button
				if ( $this->fileUploaded || $this->isUploading ) {
					$backUrl = $this->getUrl(
						array(
							'action'           => 'upload',
							'accommodation_id' => $this->roomId,
						)
					);
					?>
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=mphb_ical' ) ); ?>"><?php esc_html_e( 'Back', 'motopress-hotel-booking' ); ?></a>
					<a class="button button-secondary" href="<?php echo esc_url( $backUrl ); ?>"><?php esc_html_e( 'Import Calendar', 'motopress-hotel-booking' ); ?></a>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}

	protected function getMenuTitle() {
		return '';
	}

	protected function getPageTitle() {
		return __( 'Import Calendar', 'motopress-hotel-booking' );
	}

}
