<?php

namespace MPHB\Admin\MenuPages;

use \MPHB\Admin\Fields\FieldFactory;

class TaxesAndFeesMenuPage extends AbstractMenuPage {

	const TAXES_FEES_PAGE_NONCE_NAME = 'mphb_taxes_and_fees';

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
			echo '<div class="updated notice notice-success is-dismissible"><p>' . esc_html__( 'Taxes and fees saved.', 'motopress-hotel-booking' ) . '</p></div>';
		}
	}

	public function render() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Taxes & Fees', 'motopress-hotel-booking' ); ?></h1>

			<hr class="wp-header-end" />

			<form method="POST" action="" autocomplete="off">
				<?php

				wp_nonce_field( static::TAXES_FEES_PAGE_NONCE_NAME, static::TAXES_FEES_PAGE_NONCE_NAME );

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_fees']->render();
				?>
				<br/><hr/>

				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_accommodation_taxes']->render();
				?>
				<br/><hr/>

				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_service_taxes']->render();
				?>
				<br/><hr/>

				<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->fields['mphb_fee_taxes']->render();
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
			isset( $_POST[ static::TAXES_FEES_PAGE_NONCE_NAME ] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ static::TAXES_FEES_PAGE_NONCE_NAME ] ) ), static::TAXES_FEES_PAGE_NONCE_NAME )
		) {

			foreach ( array_keys( $this->fields ) as $option ) {

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$value = isset( $_POST[ $option ] ) ? $_POST[ $option ] : array();
				$value = $this->sanitizeOption( $option, $value );
				$this->saveOption( $option, $value );
			}
		}
	}

	/**
	 * @param string $option
	 * @param mixed  $value
	 * @return mixed Sanitized value.
	 */
	private function sanitizeOption( $option, $value ) {

		$field = $this->fields[ $option ];

		$value = wp_unslash( $value );
		$value = $field->sanitize( $value );

		return $value;
	}

	private function saveOption( $option, $value ) {

		$this->fields[ $option ]->setValue( $value );
		update_option( $option, $value, 'no' );
	}

	private function createFields() {

		$mphb_fees = get_option( 'mphb_fees', array() );

		foreach ( $mphb_fees as $key => $setting ) {
			if ( ! isset( $setting['included'] ) ) {
				$setting['included'] = 0;
				$mphb_fees[ $key ]   = $setting;
			}
		}

		$this->fields['mphb_fees'] = FieldFactory::create(
			'mphb_fees',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Fees', 'motopress-hotel-booking' ),
				'empty_label' => __( 'No fees have been created yet.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add new', 'motopress-hotel-booking' ),
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'label',
						array(
							'type'    => 'text',
							'label'   => __( 'Label', 'motopress-hotel-booking' ),
							'default' => __( 'New fee', 'motopress-hotel-booking' ),
							'size'    => 'wide',
						)
					),
					FieldFactory::create(
						'type',
						array(
							'type'    => 'select',
							'label'   => __( 'Type', 'motopress-hotel-booking' ),
							'default' => 'per_guest_per_day',
							'list'    => array(
								'per_guest_per_day'   => __( 'Per guest / per day', 'motopress-hotel-booking' ),
								'per_room_per_day'    => __( 'Per accommodation / per day', 'motopress-hotel-booking' ),
								'per_room_percentage' => __( 'Per accommodation (%)', 'motopress-hotel-booking' ),
							),
						)
					),
					FieldFactory::create(
						'amount',
						array(
							'type'       => 'amount',
							'label'      => __( 'Amount', 'motopress-hotel-booking' ),
							'size'       => 'wide',
							'default'    => array( 0, 0 ),
							'dependency' => array(
								'input'              => 'type',
								'single_input_on'    => array( 'per_room_per_day', 'per_room_percentage' ),
								'multiple_inputs_on' => array( 'per_guest_per_day' ),
							),
						)
					),
					FieldFactory::create(
						'limit',
						array(
							'type'        => 'number',
							'label'       => __( 'Limit', 'motopress-hotel-booking' ) . mphb_help_tip(
								__( 'How often this fee is charged. Set 0 to charge each day of the stay period. Set 1 to charge once.', 'motopress-hotel-booking' )
							),
							'inner_label' => __( 'days', 'motopress-hotel-booking' ),
							'min'         => 0,
							'dependency'  => array(
								'input'      => 'type',
								'disable_on' => array( 'per_room_percentage' ),
							),
						)
					),
					FieldFactory::create(
						'included',
						array(
							'type'        => 'single-checkbox',
							'label'       => __( 'Include', 'motopress-hotel-booking' ) . mphb_help_tip(
								__( 'Show accommodation rate with this charge included', 'motopress-hotel-booking' )
							),
							'inner_label' => __( 'Include', 'motopress-hotel-booking' ),
							'default'     => 0,
						)
					),
					FieldFactory::create(
						'rooms',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Accommodations', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => MPHB()->getRoomTypePersistence()->getIdTitleList( array(), array( 0 => __( 'All', 'motopress-hotel-booking' ) ) ),
						)
					),
				),
			),
			$mphb_fees
		);

		$mphb_accommodation_taxes = get_option( 'mphb_accommodation_taxes', array() );

		foreach ( $mphb_accommodation_taxes as $key => $setting ) {
			if ( ! isset( $setting['included'] ) ) {
				$setting['included']              = 0;
				$mphb_accommodation_taxes[ $key ] = $setting;
			}
		}

		$this->fields['mphb_accommodation_taxes'] = FieldFactory::create(
			'mphb_accommodation_taxes',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Accommodation Taxes', 'motopress-hotel-booking' ),
				'empty_label' => __( 'No taxes have been created yet.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add new', 'motopress-hotel-booking' ),
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'label',
						array(
							'type'    => 'text',
							'label'   => __( 'Label', 'motopress-hotel-booking' ),
							'default' => __( 'New tax', 'motopress-hotel-booking' ),
							'size'    => 'wide',
						)
					),
					FieldFactory::create(
						'type',
						array(
							'type'    => 'select',
							'label'   => __( 'Type', 'motopress-hotel-booking' ),
							'default' => 'per_guest_per_day',
							'list'    => array(
								'per_guest_per_day'   => __( 'Per guest / per day', 'motopress-hotel-booking' ),
								'per_room_per_day'    => __( 'Per accommodation / per day', 'motopress-hotel-booking' ),
								'per_room_percentage' => __( 'Per accommodation (%)', 'motopress-hotel-booking' ),
							),
						)
					),
					FieldFactory::create(
						'amount',
						array(
							'type'       => 'amount',
							'label'      => __( 'Amount', 'motopress-hotel-booking' ),
							'size'       => 'wide',
							'default'    => array( 0, 0 ),
							'dependency' => array(
								'input'              => 'type',
								'single_input_on'    => array( 'per_room_per_day', 'per_room_percentage' ),
								'multiple_inputs_on' => array( 'per_guest_per_day' ),
							),
						)
					),
					FieldFactory::create(
						'limit',
						array(
							'type'        => 'number',
							'label'       => __( 'Limit', 'motopress-hotel-booking' ) . mphb_help_tip(
								__( 'Limit of days the fee is charged. Set 0 to charge each day of stay period. Set 1 to charge once.', 'motopress-hotel-booking' )
							),
							'inner_label' => __( 'days', 'motopress-hotel-booking' ),
							'min'         => 0,
							'dependency'  => array(
								'input'      => 'type',
								'disable_on' => array( 'per_room_percentage' ),
							),
						)
					),
					FieldFactory::create(
						'included',
						array(
							'type'        => 'single-checkbox',
							'label'       => __( 'Include', 'motopress-hotel-booking' ) . mphb_help_tip(
								__( 'Show accommodation rate with this charge included', 'motopress-hotel-booking' )
							),
							'inner_label' => __( 'Include', 'motopress-hotel-booking' ),
							'default'     => 0,
						)
					),
					FieldFactory::create(
						'rooms',
						array(
							'type'      => 'multiple-checkbox',
							'label'     => __( 'Accommodations', 'motopress-hotel-booking' ),
							'all_value' => 0,
							'default'   => array( 0 ),
							'list'      => MPHB()->getRoomTypePersistence()->getIdTitleList( array(), array( 0 => __( 'All', 'motopress-hotel-booking' ) ) ),
						)
					),
				),
			),
			$mphb_accommodation_taxes
		);

		$this->fields['mphb_service_taxes'] = FieldFactory::create(
			'mphb_service_taxes',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Service Taxes', 'motopress-hotel-booking' ),
				'empty_label' => __( 'No taxes have been created yet.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add new', 'motopress-hotel-booking' ),
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'label',
						array(
							'type'    => 'text',
							'label'   => __( 'Label', 'motopress-hotel-booking' ),
							'default' => __( 'New tax', 'motopress-hotel-booking' ),
							'size'    => 'wide',
						)
					),
					FieldFactory::create(
						'type',
						array(
							'type'    => 'select',
							'label'   => __( 'Type', 'motopress-hotel-booking' ),
							'default' => 'percentage',
							'list'    => array(
								'percentage' => __( 'Percentage', 'motopress-hotel-booking' ),
							),
						)
					),
					FieldFactory::create(
						'amount',
						array(
							'type'                => 'amount',
							'label'               => __( 'Amount', 'motopress-hotel-booking' ),
							'size'                => 'wide',
							'default'             => 0,
							'default_render_type' => 'percent',
							'dependency'          => array(
								'input'              => 'type',
								'single_inputs_on'   => array( 'percentage' ),
								'multiple_inputs_on' => array(),
							),
						)
					),
					FieldFactory::create(
						'limit',
						array(
							'type'        => 'number',
							'label'       => __( 'Limit', 'motopress-hotel-booking' ) . mphb_help_tip(
								__( 'Limit of days the fee is charged. Set 0 to charge each day of stay period. Set 1 to charge once.', 'motopress-hotel-booking' )
							),
							'inner_label' => __( 'days', 'motopress-hotel-booking' ),
							'min'         => 0,
							'disabled'    => true,
							'classes'     => 'mphb-keep-disabled',
						)
					),
					FieldFactory::create(
						'rooms',
						array(
							'type'    => 'placeholder',
							'label'   => __( 'Accommodations', 'motopress-hotel-booking' ),
							'default' => '-',
						)
					),
				),
			),
			get_option( 'mphb_service_taxes', array() )
		);

		$this->fields['mphb_fee_taxes'] = FieldFactory::create(
			'mphb_fee_taxes',
			array(
				'type'        => 'rules-list',
				'label'       => __( 'Fee Taxes', 'motopress-hotel-booking' ),
				'empty_label' => __( 'No taxes have been created yet.', 'motopress-hotel-booking' ),
				'add_label'   => __( 'Add new', 'motopress-hotel-booking' ),
				'default'     => array(),
				'fields'      => array(
					FieldFactory::create(
						'label',
						array(
							'type'    => 'text',
							'label'   => __( 'Label', 'motopress-hotel-booking' ),
							'default' => __( 'New tax', 'motopress-hotel-booking' ),
							'size'    => 'wide',
						)
					),
					FieldFactory::create(
						'type',
						array(
							'type'    => 'select',
							'label'   => __( 'Type', 'motopress-hotel-booking' ),
							'default' => 'percentage',
							'list'    => array(
								'percentage' => __( 'Percentage', 'motopress-hotel-booking' ),
							),
						)
					),
					FieldFactory::create(
						'amount',
						array(
							'type'                => 'amount',
							'label'               => __( 'Amount', 'motopress-hotel-booking' ),
							'size'                => 'wide',
							'default'             => 0,
							'default_render_type' => 'percent',
							'dependency'          => array(
								'input'              => 'type',
								'single_inputs_on'   => array( 'percentage' ),
								'multiple_inputs_on' => array(),
							),
						)
					),
					FieldFactory::create(
						'limit',
						array(
							'type'        => 'number',
							'label'       => __( 'Limit', 'motopress-hotel-booking' ) . mphb_help_tip(
								__( 'Limit of days the fee is charged. Set 0 to charge each day of stay period. Set 1 to charge once.', 'motopress-hotel-booking' )
							),
							'inner_label' => __( 'days', 'motopress-hotel-booking' ),
							'min'         => 0,
							'disabled'    => true,
							'classes'     => 'mphb-keep-disabled',
						)
					),
					FieldFactory::create(
						'rooms',
						array(
							'type'    => 'placeholder',
							'label'   => __( 'Accommodations', 'motopress-hotel-booking' ),
							'default' => '-',
						)
					),
				),
			),
			get_option( 'mphb_fee_taxes', array() )
		);
	}

	protected function getMenuTitle() {
		return __( 'Taxes & Fees', 'motopress-hotel-booking' );
	}

	protected function getPageTitle() {
		return __( 'Taxes & Fees', 'motopress-hotel-booking' );
	}
}
