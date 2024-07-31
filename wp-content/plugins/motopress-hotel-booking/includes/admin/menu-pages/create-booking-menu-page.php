<?php

namespace MPHB\Admin\MenuPages;

class CreateBookingMenuPage extends AbstractMenuPage {

	/**
	 * 1 - nothing happened, just show the search form
	 * 2 - on search, show search results
	 * 3 - step checkout
	 * 4 - step booking
	 *
	 * @var int
	 */
	private $step = 1;

	/**
	 * @var \MPHB\Admin\MenuPages\CreateBooking\SearchStep
	 */
	private $search = null;

	/**
	 * @var \MPHB\Admin\MenuPages\CreateBooking\ResultsStep
	 */
	private $results = null;

	/**
	 * @var \MPHB\Admin\MenuPages\CreateBooking\CheckoutStep
	 */
	private $checkout = null;

	/**
	 * @var \MPHB\Admin\MenuPages\CreateBooking\BookingStep
	 */
	private $booking = null;

	public function __construct( $name, $atts = array() ) {
		parent::__construct( $name, $atts );

		if ( isset( $_REQUEST['step'] ) ) {
			$this->step = absint( $_REQUEST['step'] );
		}
	}

	public function addActions() {
		parent::addActions();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
		add_action( 'mphb_cb_search_form_before_end', array( $this, 'bookingRulesDisabledNotification' ) );
	}

	/**
	 * @since 3.7.2 added new action - "mphb_enqueue_checkout_scripts".
	 */
	public function enqueueScripts() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		MPHB()->getPublicScriptManager()->register();

		if ( $this->step == 3 && $this->checkout->isValidStep() ) {
			$booking = $this->checkout->getBooking();

			$checkoutData = array(
				'min_adults'   => MPHB()->settings()->main()->getMinAdults(),
				'min_children' => MPHB()->settings()->main()->getMinChildren(),
			);

			if ( MPHB()->settings()->main()->getConfirmationMode() === 'payment' ) {
				$checkoutData['deposit_amount'] = $booking->calcDepositAmount();
			}

			$checkoutData['total'] = $booking->calcPrice();

			MPHB()->getPublicScriptManager()->addCheckoutData( $checkoutData );

			foreach ( MPHB()->gatewayManager()->getListActive() as $gateway ) {
				MPHB()->getPublicScriptManager()->addGatewayData( $gateway->getId(), $gateway->getCheckoutData( $booking ) );
			}

			wp_enqueue_script( 'mphb-jquery-serialize-json' );

			do_action( 'mphb_enqueue_checkout_scripts' );
		}

		MPHB()->getPublicScriptManager()->enqueue();

		// [MB-738] Enqueue admin CSS to apply proper styles on page "Add New Booking"
		wp_register_style( 'mphb-admin-css', MPHB()->getPublicScriptManager()->scriptUrl( 'assets/css/admin.min.css' ), array(), MPHB()->getVersion() );
		wp_enqueue_style( 'mphb-admin-css' );

		add_action( 'admin_print_footer_scripts', array( MPHB()->getPublicScriptManager(), 'localize' ), 0 );
	}

	public function onLoad() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		switch ( $this->step ) {
			case 2:
				$this->results = new CreateBooking\ResultsStep();
				$this->results->setup();
				$this->results->setNextUrl( $this->getUrl( array( 'step' => 3 ) ) );
				// No break - render results and search form

			case 1:
				$this->search = new CreateBooking\SearchStep();
				$this->search->setup();
				$this->search->setNextUrl( $this->getUrl( array( 'step' => 2 ) ) );
				break;

			case 3:
				$this->checkout = new CreateBooking\CheckoutStep();
				$this->checkout->setup();
				$this->checkout->setNextUrl( $this->getUrl( array( 'step' => 4 ) ) );
				break;

			case 4:
				$this->booking = new CreateBooking\BookingStep();
				$this->booking->setup();
				break;
		}
	}

	public function render() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Add New Booking', 'motopress-hotel-booking' ); ?></h1>
			<a href="<?php echo esc_url( $this->getUrl() ); ?>" class="page-title-action"><?php esc_html_e( 'Clear Search Results', 'motopress-hotel-booking' ); ?></a>

			<hr class="wp-header-end" />

			<?php
			switch ( $this->step ) {
				case 1:
					$this->search->render();
					break;

				case 2:
					$this->search->render();
					$this->results->render();
					break;

				case 3:
					$this->checkout->render();
					break;

				case 4:
					$this->booking->render();
					break;
			}
			?>
		</div>
		<?php
	}

	protected function getMenuTitle() {
		return '';
	}

	protected function getPageTitle() {
		return __( 'Add New Booking', 'motopress-hotel-booking' );
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

}
