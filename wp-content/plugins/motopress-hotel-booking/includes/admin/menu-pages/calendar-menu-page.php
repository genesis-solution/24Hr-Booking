<?php

namespace MPHB\Admin\MenuPages;

class CalendarMenuPage extends AbstractMenuPage {

	private $calendar;

	public function addActions() {
		parent::addActions();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ), 15 );
	}

	public function setupCalendar() {
		$this->calendar = new \MPHB\BookingsCalendar();
	}

	public function enqueueAdminScripts() {
		if ( $this->isCurrentPage() ) {
			MPHB()->getAdminScriptManager()->enqueue();
		}
	}

	public function render() {

		if ( current_user_can( 'edit_mphb_bookings' ) ) {
			$this->addTitleAction( __( 'New Booking', 'motopress-hotel-booking' ), add_query_arg( 'page', 'mphb_add_new_booking', admin_url( 'admin.php' ) ) );
		}


		$this->setupCalendar();
		?>
		<div class="wrap">
			<h1 class="mphb-booking-calendar-title wp-heading-inline"><?php esc_html_e( 'Booking Calendar', 'motopress-hotel-booking' ); ?></h1>
			<?php
			$this->calendar->render();
			?>
		</div>
		<?php
	}

	public function onLoad() {

		if ( ( ! isset( $_REQUEST['mphb_bookings_calendar']['period'] ) ||
				   empty( $_REQUEST['mphb_bookings_calendar']['period'] ) ) &&
						 ! isset( $_REQUEST['action_filter'] ) ) {

				$redirectToCustomPeriod = add_query_arg(
					array(
						'page'                   => $this->getName(),
						'mphb_bookings_calendar' => array(
							'period' => get_option( 'mphb_admin_default_calendar_period', \MPHB\BookingsCalendar::PERIOD_TYPE_CUSTOM ),
						),
					),
					admin_url( 'admin.php' )
				);

				wp_safe_redirect( $redirectToCustomPeriod );

		}
	}

	protected function getMenuTitle() {
		return __( 'Calendar', 'motopress-hotel-booking' );
	}

	protected function getPageTitle() {
		return __( 'Booking Calendar', 'motopress-hotel-booking' );
	}

}
