<?php

namespace MPHB\Admin\MenuPages;

use MPHB\Admin\MenuPages\EditBooking;
use MPHB\Entities\Booking;
use RuntimeException as Error;

/**
 * @since 3.8
 */
class EditBookingMenuPage extends AbstractMenuPage {

	/**
	 * @var string
	 */
	protected $currentStep = '';

	/**
	 * @var string
	 */
	protected $nextStep = '';

	/**
	 * @var EditBooking\StepControl|null
	 */
	protected $stepControl = null;

	/**
	 * @var Booking|null
	 */
	protected $editBooking = null;

	/**
	 * @var string[]
	 */
	protected $errors = array();

	public function onLoad() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		try {
			$this->editBooking = $this->findBooking();
			$this->stepControl = $this->detectStep();

			$this->stepControl->setup();

		} catch ( \Exception $e ) {
			$this->errors = explode( PHP_EOL, $e->getMessage() );
		}

		add_action( 'mphb_booking_edit_dates_form_before_end', array( $this, 'bookingRulesDisabledNotification' ) );
	}

	/**
	 * @since 3.9.9
	 */
	public function bookingRulesDisabledNotification() {

		if ( MPHB()->settings()->main()->isBookingRulesForAdminDisabled() &&
			(bool) is_admin() &&
			isset( $_REQUEST['page'] ) &&
			in_array( $_REQUEST['page'], array( 'mphb_add_new_booking', 'mphb_edit_booking' ) )
		) {
			echo sprintf(
				'<p class="description">%s</p>',
				esc_html__( 'Note: booking rules are disabled in the plugin settings and are not taken into account.', 'motopress-hotel-booking' )
			);
		}
	}

	/**
	 * @return Booking
	 * @throws Error If the booking is not set or not found.
	 */
	protected function findBooking() {
		if ( ! isset( $_GET['booking_id'] ) ) {
			throw new Error( __( 'The booking is not set.', 'motopress-hotel-booking' ) );
		}

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$bookingId = mphb_posint( wp_unslash( $_GET['booking_id'] ) );
		$booking   = mphb_get_booking( $bookingId );

		if ( is_null( $booking ) ) {
			throw new Error( __( 'The booking not found.', 'motopress-hotel-booking' ) );
		}

		return $booking;
	}

	/**
	 * @return EditBooking\StepControl
	 */
	protected function detectStep() {
		$stepsSequence = array(
			// Current step => next step
			'edit'     => 'summary',
			'summary'  => 'checkout',
			'checkout' => 'booking',
			'booking'  => '', // No matter
		);

		$currentStep  = 'edit';
		$stepFromPOST = isset( $_POST['step'] ) ? sanitize_text_field( wp_unslash( $_POST['step'] ) ) : null;

		if ( ! empty( $stepFromPOST ) && in_array( $stepFromPOST, array_keys( $stepsSequence ) ) ) {
			$currentStep = $stepFromPOST;
		}

		$this->currentStep = $currentStep;
		$this->nextStep    = $stepsSequence[ $currentStep ];

		switch ( $currentStep ) {
			case 'edit':
				return new EditBooking\EditControl( $this->editBooking );
			break;
			case 'summary':
				return new EditBooking\SummaryControl( $this->editBooking );
			break;
			case 'checkout':
				return new EditBooking\CheckoutControl( $this->editBooking );
			break;
			case 'booking':
				return new EditBooking\BookingControl( $this->editBooking );
			break;
			default:
				return new EditBooking\StepControl( $this->editBooking );
			break;
		}
	}

	public function render() {
		$backUrl = $this->getBackUrl();

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html( sprintf( __( 'Edit Booking #%d', 'motopress-hotel-booking' ), $this->editBooking->getId() ) ); ?></h1>

			<?php if ( ! empty( $backUrl ) ) { ?>
				<a href="<?php echo esc_url( $backUrl ); ?>" class="page-title-action"><?php $this->currentStep == 'edit' ? esc_html_e( 'Cancel', 'motopress-hotel-booking' ) : esc_html_e( 'Back', 'motopress-hotel-booking' ); ?></a>
			<?php } ?>

			<hr class="wp-header-end">

			<div class="mphb-edit-booking <?php echo esc_attr( $this->currentStep ); ?>">
				<?php
				if ( empty( $this->errors ) ) {
					$this->renderValid();
				} else {
					$this->renderInvalid();
				}
				?>
			</div>
		</div>
		<?php
	}

	protected function renderValid() {
		do_action( 'mphb_edit_booking_before_valid_step', $this->editBooking, $this->currentStep );

		// See MPHB\Admin\MenuPages\EditBooking\*Control
		do_action(
			'mphb_edit_booking_form',
			$this->editBooking,
			array(
				'current_step' => $this->currentStep,
				'next_step'    => $this->nextStep,
				'action_url'   => $this->getUrl(),
			)
		);

		do_action( 'mphb_edit_booking_after_valid_step', $this->editBooking, $this->currentStep );
	}

	protected function renderInvalid() {
		do_action( 'mphb_edit_booking_before_invalid_step', $this->errors, $this->currentStep );

		mphb_get_template_part( 'edit-booking/errors', array( 'errors' => $this->errors ) );

		do_action( 'mphb_edit_booking_after_invalid_step', $this->errors, $this->currentStep );
	}

	/**
	 * @return string Back URL or empty string "".
	 */
	protected function getBackUrl() {
		if ( is_null( $this->editBooking ) ) {
			return '';
		}

		switch ( $this->currentStep ) {
			case 'edit':
				return get_edit_post_link( $this->editBooking->getId() );
				break;

			case 'summary':
			case 'checkout':
			case 'booking':
				return $this->getUrl();
			break;

			default:
				return '';
			break;
		}
	}

	public function getUrl( $moreArgs = array() ) {
		if ( ! is_null( $this->editBooking ) ) {
			$moreArgs['booking_id'] = $this->editBooking->getId();
		}

		if ( isset( $_GET['lang'] ) ) {
			$moreArgs['lang'] = sanitize_text_field( wp_unslash( $_GET['lang'] ) );
		}

		return parent::getUrl( $moreArgs );
	}

	/**
	 * @return string
	 */
	protected function getPageTitle() {
		return __( 'Edit Booking', 'motopress-hotel-booking' );
	}

	/**
	 * @return string
	 */
	protected function getMenuTitle() {
		return __( 'Edit Booking', 'motopress-hotel-booking' );
	}
}
