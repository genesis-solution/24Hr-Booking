<?php

namespace MPHB\Admin\MenuPages;

use \MPHB\Admin\Fields\FieldFactory;

class BookingRulesMenuPage extends AbstractMenuPage {

	const BOOKING_RULES_PAGE_NONCE_NAME = 'mphb_booking_rules';

	private $fields = array();

	public function addActions() {

		parent::addActions();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ) );
		add_action( 'admin_notices', array( $this, 'showNotices' ) );
	}

	public function enqueueAdminScripts() {

		if ( $this->isCurrentPage() ) {

			MPHB()->getAdminScriptManager()->enqueue();
			wp_enqueue_script( 'mphb-jquery-serialize-json' );
		}
	}

	public function showNotices() {

		if ( $this->isCurrentPage() && isset( $_POST['save'] ) ) {

			echo '<div class="updated notice notice-success is-dismissible"><p>' . esc_html__( 'Booking rules saved.', 'motopress-hotel-booking' ) . '</p></div>';
		}
	}

	public function render() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Booking Rules', 'motopress-hotel-booking' ); ?></h1>

			<hr class="wp-header-end" />

			<form method="POST" action="" autocomplete="off">
				<?php

				wp_nonce_field( static::BOOKING_RULES_PAGE_NONCE_NAME, static::BOOKING_RULES_PAGE_NONCE_NAME );

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_check_in_days']->render();
				?>
				<br/><hr/>

				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_check_out_days']->render();
				?>
				<br/><hr/>

				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_min_stay_length']->render();
				?>
				<br/><hr/>

				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_max_stay_length']->render();
				?>
				<br/><hr/>

				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_booking_rules_custom']->render();
				?>
				<br/><hr/>

				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_min_advance_reservation']->render();
				?>
				<br/><hr/>

				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_max_advance_reservation']->render();
				?>
				<br/><hr/>

                <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_buffer_days']->render();
				?>

				<p class="submit">
					<input name="save" type="submit" class="button button-primary" id="publish" value="<?php esc_attr_e( 'Save Changes', 'motopress-hotel-booking' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}

	public function onLoad() {

		if ( ! $this->isCurrentPage() ) {
			return;
		}

		$this->createFields();

		if ( isset( $_POST['save'] ) &&
			isset( $_POST[ static::BOOKING_RULES_PAGE_NONCE_NAME ] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ static::BOOKING_RULES_PAGE_NONCE_NAME ] ) ), static::BOOKING_RULES_PAGE_NONCE_NAME )
		) {
			$this->saveCustomRules();
			$this->processReservationRules();
		}
	}

	private function saveCustomRules() {

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$customRules = ! empty( $_POST['mphb_booking_rules_custom'] ) ? $_POST['mphb_booking_rules_custom'] : array();
		$customRules = $this->sanitize( 'mphb_booking_rules_custom', $customRules );
		$this->save( 'mphb_booking_rules_custom', $customRules );
	}

	/**
	 * Build reservation rules and prepare season priorities.
	 */
	private function processReservationRules() {

		$postFields = array(
			'mphb_check_in_days',
			'mphb_check_out_days',
			'mphb_min_stay_length',
			'mphb_max_stay_length',
			'mphb_min_advance_reservation',
			'mphb_max_advance_reservation',
			'mphb_buffer_days',
		);

		foreach ( $postFields as $postField ) {
			// Use array_values() to reset numeric indexes
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$postValues = ! empty( $_POST[ $postField ] ) ? array_values( $_POST[ $postField ] ) : array();
			$postValues = $this->sanitize( $postField, $postValues );

			// All values are numbers, so convert all strings in the array into numbers
			array_walk_recursive(
				$postValues,
				function ( &$value, $key ) {
					$value = (int) $value;
				}
			);

			$this->save( $postField, $postValues );
		}
	}

	/**
	 * @param string $option
	 * @param mixed  $value
	 * @return mixed Sanitized value.
	 */
	private function sanitize( $option, $value ) {

		$field = $this->fields[ $option ];

		$value = wp_unslash( $value );
		$value = $field->sanitize( $value );

		return $value;
	}

	private function save( $option, $value ) {

		$this->fields[ $option ]->setValue( $value );
		update_option( $option, $value, 'no' );
	}

	private function createFields() {

		// Load room types only on default language
		MPHB()->translation()->setupDefaultLanguage();
		$roomTypes = MPHB()->getRoomTypePersistence()->getIdTitleList( array(), array( 0 => __( 'All', 'motopress-hotel-booking' ) ) );
		MPHB()->translation()->restoreLanguage();

		$seasons    = MPHB()->getSeasonPersistence()->getIdTitleList( array(), array( 0 => __( 'All', 'motopress-hotel-booking' ) ) );
		$daysOfWeek = \MPHB\Utils\DateUtils::getDaysList();

		// Consider first day settings: move first day to the top of the list
		$startDay = MPHB()->settings()->dateTime()->getFirstDay();

		if ( $startDay > 0 ) {
			$startPart  = array_slice( $daysOfWeek, $startDay, 7 - $startDay, true );
			$endPart    = array_slice( $daysOfWeek, 0, $startDay, true );
			$daysOfWeek = array_replace( $startPart, $endPart );
		}

		$this->fields['mphb_check_in_days'] = FieldFactory::create(
			'mphb_check_in_days',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Check-in days', 'motopress-hotel-booking' ),
				'empty_label' => __( 'Guests can check in any day.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add rule', 'motopress-hotel-booking' ),
				'sortable'    => true,
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'check_in_days',
						array(
							'type'    => 'multiple-checkbox',
							'label'   => __( 'Days', 'motopress-hotel-booking' ),
							'default' => range( 0, 6 ),
							'list'    => $daysOfWeek,
						)
					),
					FieldFactory::create(
						'room_type_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Accommodations', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $roomTypes,
						)
					),
					FieldFactory::create(
						'season_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Seasons', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $seasons,
						)
					),
				),
			),
			get_option( 'mphb_check_in_days', array() )
		);

		$this->fields['mphb_check_out_days'] = FieldFactory::create(
			'mphb_check_out_days',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Check-out days', 'motopress-hotel-booking' ),
				'empty_label' => __( 'Guests can check out any day.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add rule', 'motopress-hotel-booking' ),
				'sortable'    => true,
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'check_out_days',
						array(
							'type'    => 'multiple-checkbox',
							'label'   => __( 'Days', 'motopress-hotel-booking' ),
							'default' => range( 0, 6 ),
							'list'    => $daysOfWeek,
						)
					),
					FieldFactory::create(
						'room_type_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Accommodations', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $roomTypes,
						)
					),
					FieldFactory::create(
						'season_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Seasons', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $seasons,
						)
					),
				),
			),
			get_option( 'mphb_check_out_days', array() )
		);

		$this->fields['mphb_min_stay_length'] = FieldFactory::create(
			'mphb_min_stay_length',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Minimum stay', 'motopress-hotel-booking' ),
				'empty_label' => __( 'There are no minimum stay rules.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add rule', 'motopress-hotel-booking' ),
				'sortable'    => true,
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'min_stay_length',
						array(
							'type'        => 'number',
							'label'       => __( 'Minimum stay', 'motopress-hotel-booking' ),
							'inner_label' => __( 'nights', 'motopress-hotel-booking' ),
							'default'     => 1,
							'min'         => 1,
						)
					),
					FieldFactory::create(
						'room_type_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Accommodations', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $roomTypes,
						)
					),
					FieldFactory::create(
						'season_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Seasons', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $seasons,
						)
					),
				),
			),
			get_option( 'mphb_min_stay_length', array() )
		);

		$this->fields['mphb_max_stay_length'] = FieldFactory::create(
			'mphb_max_stay_length',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Maximum stay', 'motopress-hotel-booking' ),
				'empty_label' => __( 'There are no maximum stay rules.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add rule', 'motopress-hotel-booking' ),
				'sortable'    => true,
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'max_stay_length',
						array(
							'type'        => 'number',
							'label'       => __( 'Maximum stay', 'motopress-hotel-booking' ),
							'inner_label' => __( 'nights', 'motopress-hotel-booking' ),
							'default'     => 15,
							'min'         => 1,
						)
					),
					FieldFactory::create(
						'room_type_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Accommodations', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $roomTypes,
						)
					),
					FieldFactory::create(
						'season_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Seasons', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $seasons,
						)
					),
				),
			),
			get_option( 'mphb_max_stay_length', array() )
		);

		$this->fields['mphb_booking_rules_custom'] = FieldFactory::create(
			'mphb_booking_rules_custom',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Block accommodation', 'motopress-hotel-booking' ),
				'empty_label' => __( 'There are no blocking accommodation rules.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add rule', 'motopress-hotel-booking' ),
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'room_type_id',
						array(
							'type'    => 'select',
							'label'   => __( 'Accommodation Type', 'motopress-hotel-booking' ),
							'default' => 0,
							'list'    => $roomTypes,
						)
					),
					FieldFactory::create(
						'room_id',
						array(
							'type'             => 'dynamic-select',
							'label'            => __( 'Accommodation', 'motopress-hotel-booking' ),
							'dependency_input' => 'room_type_id',
							'ajax_action'      => 'mphb_get_accommodations_list',
							'list_callback'    => 'mphb_get_rooms_select_list',
							'default'          => 0,
							'list'             => array( 0 => __( 'All', 'motopress-hotel-booking' ) ),
						)
					),
					FieldFactory::create(
						'date_from',
						array(
							'type'     => 'datepicker',
							'label'    => __( 'From', 'motopress-hotel-booking' ),
							'size'     => 'wide',
							'required' => true,
							'readonly' => false,
						)
					),
					FieldFactory::create(
						'date_to',
						array(
							'type'     => 'datepicker',
							'label'    => __( 'Till', 'motopress-hotel-booking' ),
							'size'     => 'wide',
							'required' => true,
							'readonly' => false,
						)
					),
					FieldFactory::create(
						'restrictions',
						array(
							'type'    => 'multiple-checkbox',
							'label'   => __( 'Restriction', 'motopress-hotel-booking' ) .
								mphb_help_tip(
									'<p>' . __( 'Not check-in rule marks the date as unavailable for check-in.', 'motopress-hotel-booking' ) . '</p>' .
										'<p>' . __( 'Not check-out rule marks the date as unavailable for check-out.', 'motopress-hotel-booking' ) . '</p>' .
										'<p>' . __( 'Not stay-in rule displays the date as blocked. This date is unavailable for check-out and check-in on the next date.', 'motopress-hotel-booking' ) . '</p>' .
										'<p>' . __( 'Not stay-in with Not check-out rules completely block the selected date, additionally displaying the previous date as unavailable for check-in.', 'motopress-hotel-booking' ) . '</p>',
									true
								),
							'default' => array(),
							'list'    => array(
								'check-in'  => __( 'Not check-in', 'motopress-hotel-booking' ),
								'check-out' => __( 'Not check-out', 'motopress-hotel-booking' ),
								'stay-in'   => __( 'Not stay-in', 'motopress-hotel-booking' ),
							),
						)
					),
					FieldFactory::create(
						'comment',
						array(
							'type'  => 'textarea',
							'label' => __( 'Comment', 'motopress-hotel-booking' ),
						)
					),
				),
			),
			get_option( 'mphb_booking_rules_custom', array() )
		);

		$this->fields['mphb_min_advance_reservation'] = FieldFactory::create(
			'mphb_min_advance_reservation',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Minimum advance reservation', 'motopress-hotel-booking' ),
				'empty_label' => __( 'There are no minimum advance reservation rules.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add rule', 'motopress-hotel-booking' ),
				'sortable'    => true,
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'min_advance_reservation',
						array(
							'type'        => 'number',
							'label'       => __( 'Minimum advance reservation', 'motopress-hotel-booking' ),
							'inner_label' => __( 'nights', 'motopress-hotel-booking' ),
							'default'     => 0,
							'min'         => 0,
						)
					),
					FieldFactory::create(
						'room_type_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Accommodations', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $roomTypes,
						)
					),
					FieldFactory::create(
						'season_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Seasons', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $seasons,
						)
					),
				),
			),
			get_option( 'mphb_min_advance_reservation', array() )
		);

		$this->fields['mphb_max_advance_reservation'] = FieldFactory::create(
			'mphb_max_advance_reservation',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Maximum advance reservation', 'motopress-hotel-booking' ),
				'empty_label' => __( 'There are no maximum advance reservation rules.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add rule', 'motopress-hotel-booking' ),
				'sortable'    => true,
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'max_advance_reservation',
						array(
							'type'        => 'number',
							'label'       => __( 'Maximum advance reservation', 'motopress-hotel-booking' ),
							'inner_label' => __( 'nights', 'motopress-hotel-booking' ),
							'default'     => 0,
							'min'         => 0,
						)
					),
					FieldFactory::create(
						'room_type_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Accommodations', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $roomTypes,
						)
					),
					FieldFactory::create(
						'season_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Seasons', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $seasons,
						)
					),
				),
			),
			get_option( 'mphb_max_advance_reservation', array() )
		);

		$this->fields['mphb_buffer_days'] = FieldFactory::create(
			'mphb_buffer_days',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Booking buffer', 'motopress-hotel-booking' ),
				'empty_label' => __( 'There are no booking buffer rules.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add rule', 'motopress-hotel-booking' ),
				'sortable'    => true,
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'buffer_days',
						array(
							'type'        => 'number',
							'label'       => __( 'Booking buffer', 'motopress-hotel-booking' ),
							'inner_label' => __( 'nights', 'motopress-hotel-booking' ),
							'default'     => 0,
							'min'         => 0,
						)
					),
					FieldFactory::create(
						'room_type_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Accommodations', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $roomTypes,
						)
					),
					FieldFactory::create(
						'season_ids',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Seasons', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => $seasons,
						)
					),
				),
			),
			get_option( 'mphb_buffer_days', array() )
		);
	}

	protected function getMenuTitle() {
		return __( 'Booking Rules', 'motopress-hotel-booking' );
	}

	protected function getPageTitle() {
		return __( 'Booking Rules', 'motopress-hotel-booking' );
	}
}
