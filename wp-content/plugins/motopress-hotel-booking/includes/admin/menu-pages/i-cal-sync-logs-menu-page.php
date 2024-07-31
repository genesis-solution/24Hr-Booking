<?php

namespace MPHB\Admin\MenuPages;

use \MPHB\Admin\SyncLogsListTable;
use \MPHB\Admin\SyncRoomsListTable;
use \MPHB\iCal\BackgroundProcesses\QueuedSynchronizer;
use \MPHB\iCal\Queue;

class iCalSyncLogsMenuPage extends AbstractMenuPage {

	private $queue = null;

	/**
	 * @var SyncRoomsListTable|SyncLogsListTable The list of rooms or logs.
	 */
	private $listTable = null;

	public function __construct( $name, $atts = array() ) {
		parent::__construct( $name, $atts );

		if ( isset( $_GET['queue'] ) ) {
			$this->queue = sanitize_text_field( wp_unslash( $_GET['queue'] ) );
		}

		add_action( 'admin_bar_menu', array( $this, 'showPageLink' ), 100 );
	}

	public function addActions() {
		parent::addActions();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAssets' ) );
	}

	public function enqueueAssets() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		wp_enqueue_style( 'mphb-admin-ical-css', MPHB()->getPluginUrl( 'assets/css/admin-ical.min.css' ), array(), MPHB()->getVersion() );

		if ( is_null( $this->queue ) ) {
			wp_enqueue_script( 'mphb-admin-ical', MPHB()->getPluginUrl( 'assets/js/admin/admin-ical.min.js' ), array( 'jquery', 'mphb-canjs' ), MPHB()->getVersion(), true );

			wp_localize_script(
				'mphb-admin-ical',
				'MPHB_iCal',
				array(
					'ajaxUrl'    => MPHB()->getAjaxUrl(),
					'actions'    => array(
						'sync' => array(
							'progress'    => 'mphb_ical_sync_get_progress',
							'abort'       => 'mphb_ical_sync_abort',
							'remove_item' => 'mphb_ical_sync_remove_item',
							'clear_all'   => 'mphb_ical_sync_clear_all',
						),
					),
					'nonces'     => MPHB()->getAjax()->getAdminNonces(),
					'i18n'       => array(
						'abort'          => __( 'Abort Process', 'motopress-hotel-booking' ),
						'aborting'       => __( 'Aborting...', 'motopress-hotel-booking' ),
						'clear'          => __( 'Delete All Logs', 'motopress-hotel-booking' ),
						'clearing'       => __( 'Deleting...', 'motopress-hotel-booking' ),
						'items_singular' => __( '%d item', 'motopress-hotel-booking' ),
						'items_plural'   => __( '%d items', 'motopress-hotel-booking' ),
					),
					'inProgress' => MPHB()->getQueuedSynchronizer()->isInProgress(),
				)
			);
		}
	}

	public function onLoad() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		if ( is_null( $this->queue ) ) {
			$this->listTable = new SyncRoomsListTable();
		} else {
			$this->listTable = new SyncLogsListTable();
		}

		$this->listTable->prepare_items();
	}

	public function render() {
		?>
		<div class="wrap mphb-sync-details-wrapper">

			<h1 class="wp-heading-inline"><?php esc_html_e( 'Calendars Synchronization Status', 'motopress-hotel-booking' ); ?></h1>
			<p><?php esc_html_e( 'Here you can see synchronization status of your external calendars.', 'motopress-hotel-booking' ); ?></p>

			<?php // SYNC ROOMS ?>
			<?php
			if ( is_null( $this->queue ) ) {
				$syncAllUrl   = admin_url( 'admin.php?page=mphb_ical_import&action=sync&accommodation_ids=all' );
				$synchronizer = MPHB()->getQueuedSynchronizer();
				?>
				<p>
					<a href="<?php echo esc_url( $syncAllUrl ); ?>" class="button"><?php esc_html_e( 'Sync All External Calendars', 'motopress-hotel-booking' ); ?></a>
					<button class="button mphb-abort-process" <?php disabled( ! $synchronizer->isInProgress() ); ?>><?php esc_html_e( 'Abort Process', 'motopress-hotel-booking' ); ?></button>
					<button class="button mphb-clear-all"><?php esc_html_e( 'Delete All Logs', 'motopress-hotel-booking' ); ?></button>
				</p>

				<?php // SYNC LOGS ?>
				<?php
			} else {
				$roomId = ! is_null( $this->queue ) ? mphb_parse_queue_room_id( $this->queue ) : 0;
				$room   = ( $roomId != 0 ) ? MPHB()->getRoomRepository()->findById( $roomId ) : null;
				?>
				<h2><?php echo ! is_null( $room ) ? esc_html( $room->getTitle() ) : ''; ?></h2>
			<?php } ?>

			<form id="<?php echo sanitize_key( $this->listTable->get_plural() . '-filter' ); ?>" method="POST" action="">
				<?php
				$pageFromRequest = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
				// We need to ensure that the form posts back to current page
				?>
				<input type="hidden" name="page" value="<?php echo esc_attr( $pageFromRequest ); ?>" />

				<?php $this->listTable->display(); ?>
			</form>

		</div>
		<?php
	}

	protected function getMenuTitle() {
		return '';
	}

	protected function getPageTitle() {
		return __( 'Calendars Sync Status', 'motopress-hotel-booking' );
	}

	/**
	 * @param \WP_Admin_Bar $adminBar
	 */
	public function showPageLink( $adminBar ) {
		if ( Queue::countItems() == 0 ) {
			return;
		}

		$adminBar->add_node(
			array(
				'id'    => 'mphb_ical_show_sync_progress',
				'title' => __( 'Calendars Sync Status', 'motopress-hotel-booking' ),
				'href'  => $this->getUrl(),
				'meta'  => array(
					'title' => __( 'Display calendars synchronization status.', 'motopress-hotel-booking' ),
				),
			)
		);
	}
}
