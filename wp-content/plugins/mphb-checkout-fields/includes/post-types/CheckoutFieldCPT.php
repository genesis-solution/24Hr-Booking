<?php

namespace MPHB\CheckoutFields\PostTypes;

use MPHB\Admin\Fields\FieldFactory;
use MPHB\Admin\Groups\MetaBoxGroup;
use MPHB\CheckoutFields\CPTPages\EditCheckoutFieldsPage;
use MPHB\CheckoutFields\CPTPages\ManageCheckoutFieldsPage;
use MPHB\PostTypes\EditableCPT;
use MPHB\CheckoutFields\CheckoutFieldsHelper;

/**
 * @since 1.0
 */
class CheckoutFieldCPT extends EditableCPT {

	protected $postType = 'mphb_checkout_field';

	public function addActions() {

		parent::addActions();

		add_action( 'admin_menu', array( $this, 'moveSubmenu' ), 1001 ); // Notifier uses priority 1000
	}

	public function createManagePage() {

		return new ManageCheckoutFieldsPage( $this->postType );
	}

	protected function createEditPage() {

		return new EditCheckoutFieldsPage( $this->postType, $this->getFieldGroups() );
	}

	public function register() {

		$labels = array(
			'name'                  => esc_html__( 'Checkout Fields', 'mphb-checkout-fields' ),
			'singular_name'         => esc_html__( 'Checkout Field', 'mphb-checkout-fields' ),
			'add_new'               => esc_html_x( 'Add New', 'Add new checkout field', 'mphb-checkout-fields' ),
			'add_new_item'          => esc_html__( 'Add New Checkout Field', 'mphb-checkout-fields' ),
			'edit_item'             => esc_html__( 'Edit Checkout Field', 'mphb-checkout-fields' ),
			'new_item'              => esc_html__( 'New Checkout Field', 'mphb-checkout-fields' ),
			'view_item'             => esc_html__( 'View Checkout Field', 'mphb-checkout-fields' ),
			'search_items'          => esc_html__( 'Search Checkout Field', 'mphb-checkout-fields' ),
			'not_found'             => esc_html__( 'No checkout fields found', 'mphb-checkout-fields' ),
			'not_found_in_trash'    => esc_html__( 'No checkout fields found in Trash', 'mphb-checkout-fields' ),
			'all_items'             => esc_html__( 'Checkout Fields', 'mphb-checkout-fields' ),
			'insert_into_item'      => esc_html__( 'Insert into checkout field description', 'mphb-checkout-fields' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this checkout field', 'mphb-checkout-fields' ),
		);

		$args = array(
			'labels'               => $labels,
			'public'               => false,
			'show_ui'              => true,
			'show_in_menu'         => mphb()->menus()->getMainMenuSlug(),
			'supports'             => array( 'title' ),
			'register_meta_box_cb' => array( $this, 'registerMetaBoxes' ),
			'rewrite'              => false,
			'show_in_rest'         => true,
			'map_meta_cap'         => true,
			'capability_type'      => array( 'mphb_checkout_field', 'mphb_checkout_fields' ),
		);

		register_post_type( $this->postType, $args );
	}

	public function getFieldGroups() {

		$settingsGroup = new MetaBoxGroup( 'mphb_checkout_field_settings', esc_html__( 'Parameters', 'mphb-checkout-fields' ), $this->postType );

		// Add fields to "Settings" metabox
		$settingsFields = array(
			'name'         => FieldFactory::create(
				'mphb_cf_name',
				array(
					'type'        => 'text',
					'label'       => esc_html__( 'System Name', 'mphb-checkout-fields' ),
					'description' => esc_html__( 'Important! The plugin identifies this field thanks to the customer info. Only Latin lowercase letters, numbers, hyphens and underscores are allowed. The name must be unique.', 'mphb-checkout-fields' ),
					'pattern'     => '[a-z0-9_\-]+',
					'required'    => true,
				)
			),
			'type'         => FieldFactory::create(
				'mphb_cf_type',
				array(
					'type'    => 'select',
					'label'   => esc_html__( 'Type', 'mphb-checkout-fields' ),
					'list'    => array(
						'checkbox'      => esc_html__( 'Checkbox', 'mphb-checkout-fields' ),
						'country'       => esc_html__( 'Country', 'mphb-checkout-fields' ),
						'date_of_birth' => esc_html__( 'Date of Birth', 'mphb-checkout-fields' ),
						'email'         => esc_html__( 'Email', 'mphb-checkout-fields' ),
						'heading'       => esc_html__( 'Heading', 'mphb-checkout-fields' ),
						'paragraph'     => esc_html__( 'Paragraph', 'mphb-checkout-fields' ),
						'phone'         => esc_html__( 'Phone', 'mphb-checkout-fields' ),
						'select'        => esc_html__( 'Select', 'mphb-checkout-fields' ),
						'text'          => esc_html__( 'Text', 'mphb-checkout-fields' ),
						'textarea'      => esc_html__( 'Textarea', 'mphb-checkout-fields' ),
						'file_upload'   => esc_html__( 'File Upload', 'mphb-checkout-fields' ),
					),
					'default' => 'text',
				)
			),
			'inner_label'  => FieldFactory::create(
				'mphb_cf_inner_label',
				array(
					'type'         => 'text',
					'label'        => esc_html__( 'Checkbox Text', 'mphb-checkout-fields' ),
					'translatable' => true,
					'classes'      => 'mphb-inner-label-ctrl',
				)
			),
			'text_content' => FieldFactory::create(
				'mphb_cf_text_content',
				array(
					'type'         => 'rich-editor',
					'label'        => esc_html__( 'Text Content', 'mphb-checkout-fields' ),
					'rows'         => 4,
					'translatable' => true,
					'classes'      => 'mphb-text-content-ctrl',
					'isMediaButtonsOn' => false,
					'tinymceSettings'  => array(
						'force_br_newlines' => true,
						'force_p_newlines' => false,
						'forced_root_block' => false,
						'toolbar1' => 'bold,italic,link'
					),
					'quicktagsSettings' => array(
						'buttons' => 'strong,em,link'
					)
				)
			),
			'placeholder'  => FieldFactory::create(
				'mphb_cf_placeholder',
				array(
					'type'         => 'text',
					'label'        => esc_html__( 'Placeholder', 'mphb-checkout-fields' ),
					'description'  => esc_html__( 'Short hint that describes the expected value of this field.', 'mphb-checkout-fields' ),
					'translatable' => true,
					'classes'      => 'mphb-placeholder-ctrl',
				)
			),
			'pattern'      => FieldFactory::create(
				'mphb_cf_pattern',
				array(
					'type'         => 'text',
					'label'        => esc_html__( 'Pattern', 'mphb-checkout-fields' ),
					'description'  => sprintf( esc_html__( 'A regular expression that the input value must match to be valid. If using the pattern attribute, inform the user about the expected format. For example %s is a US phone number in the format of: 123-456-7890.', 'mphb-checkout-fields' ), '<code>\d{3}[\-]\d{3}[\-]\d{4}</code>' ),
					'translatable' => true,
					'classes'      => 'mphb-pattern-ctrl',
				)
			),
			'description'  => FieldFactory::create(
				'mphb_cf_description',
				array(
					'type'         => 'rich-editor',
					'label'        => esc_html__( 'Description', 'mphb-checkout-fields' ),
					'description'  => esc_html__( 'Any hint about this field you want to show to your customers.', 'mphb-checkout-fields' ),
					'rows'         => 4,
					'translatable' => true,
					'classes'      => 'mphb-description-ctrl',
					'isMediaButtonsOn' => false,
					'tinymceSettings'  => array(
						'force_br_newlines' => true,
						'force_p_newlines' => false,
						'forced_root_block' => false,
						'toolbar1' => 'bold,italic,link'
					),
					'quicktagsSettings' => array(
						'buttons' => 'strong,em,link'
					)
				)
			),
			'css_class'    => FieldFactory::create(
				'mphb_cf_css_class',
				array(
					'type'        => 'text',
					'label'       => esc_html__( 'Additional CSS Class(es)', 'mphb-checkout-fields' ),
					'description' => esc_html__( 'Separate multiple classes with spaces.', 'mphb-checkout-fields' ),
				)
			),
			'options'      => FieldFactory::create(
				'mphb_cf_options',
				array(
					'type'         => 'complex',
					'label'        => esc_html__( 'Options', 'mphb-checkout-fields' ),
					'description'  => esc_html__( 'You can use the first option as a placeholder to provide guidance about what you expect: just leave the value of the option empty. Option Value is a system name for Visible Label. Visible Label is visible to users and you can edit it anytime.', 'mphb-checkout-fields' ),
					'fields'       => array(
						FieldFactory::create(
							'value',
							array(
								'type'  => 'text',
								'label' => esc_html__( 'Option Value', 'mphb-checkout-fields' ),
							)
						),
						FieldFactory::create(
							'label',
							array(
								'type'  => 'text',
								'label' => esc_html__( 'Visible Label', 'mphb-checkout-fields' ),
							)
						),
					),
					'default'      => array(),
					'add_label'    => esc_html__( 'Add Option', 'mphb-checkout-fields' ),
					'sortable'     => true,
					'translatable' => true,
					'classes'      => 'mphb-options-ctrl',
				)
			),
			'file_types'   => FieldFactory::create(
				'mphb_cf_file_types',
				array(
					'type'        => 'text',
					'label'       => esc_html__( 'File Types', 'mphb-checkout-fields' ),
					'default'     => '',
					'classes'     => 'mphb-file-types-ctrl',
					'description' => esc_html__( 'Allowed file extensions, separated by comma. For example: png, pdf, jpeg', 'mphb-checkout-fields' ),
				)
			),
			'upload_size'  => FieldFactory::create(
				'mphb_cf_upload_size',
				array(
					'type'        => 'text',
					'label'       => esc_html__( 'Upload Size', 'mphb-checkout-fields' ),
					'default'     => '',
					'classes'     => 'mphb-upload-size-ctrl',
					'description' => esc_html__( 'Max upload file size in bytes. For example: 4000000', 'mphb-checkout-fields' ),
				)
			),
			'checked'      => FieldFactory::create(
				'mphb_cf_checked',
				array(
					'type'        => 'checkbox',
					'inner_label' => esc_html__( 'Checked by default', 'mphb-checkout-fields' ),
					'default'     => false,
					'classes'     => 'mphb-checked-ctrl',
				)
			),
			'required'     => FieldFactory::create(
				'mphb_cf_required',
				array(
					'type'        => 'checkbox',
					'inner_label' => esc_html__( 'Required', 'mphb-checkout-fields' ),
					'default'     => true,
					'description' => esc_html__( 'This field must be filled out before submitting the form.', 'mphb-checkout-fields' ),
					'classes'     => 'mphb-required-ctrl'
				)
			),
			'enabled'      => FieldFactory::create(
				'mphb_cf_enabled',
				array(
					'type'        => 'checkbox',
					'inner_label' => esc_html__( 'Enabled', 'mphb-checkout-fields' ),
					'default'     => true,
					'description' => esc_html__( 'Display this field in the form.', 'mphb-checkout-fields' ),
				)
			),
		);

		$postId = mphb_get_editing_post_id();

		if ( CheckoutFieldsHelper::isDefaultCheckoutFieldPost( $postId ) ) {
			$settingsFields['name']->setDisabled( true );
			$settingsFields['name']->setDescription( esc_html__( 'You cannot change the name of this system field.', 'mphb-checkout-fields' ) );

			$settingsFields['type']->setDisabled( true );
			$settingsFields['type']->setDescription( esc_html__( 'You cannot change the type of this system field.', 'mphb-checkout-fields' ) );

			$fieldName = get_post_meta( $postId, 'mphb_cf_name', true );

			if ( $fieldName == 'email' ) {
				$settingsFields['required']->setDisabled( true );
				$settingsFields['required']->setDescription( esc_html__( "Email field is required. You can't make it optional.", 'mphb-checkout-fields' ) );

				$settingsFields['enabled']->setDisabled( true );
				$settingsFields['enabled']->setDescription( esc_html__( "Email field is required. You can't disable it.", 'mphb-checkout-fields' ) );
			}
		}

		$settingsGroup->addFields( $settingsFields );

		return array(
			'settings' => $settingsGroup,
		);
	}

	/**
	 * Callback for action "admin_menu".
	 *
	 * @global array $submenu
	 */
	public function moveSubmenu() {
        
		global $submenu;

		if ( ! isset( $submenu['mphb_booking_menu'] ) ) {
			return;
		}

		$bookingMenu = &$submenu['mphb_booking_menu'];

		$cptIndex  = false;
		$syncIndex = false;

		$currentScreen = 'edit.php?post_type=' . $this->postType;

		foreach ( $bookingMenu as $index => $bookingSubmenu ) {
			if ( ! isset( $bookingSubmenu[2] ) ) {
				continue;
			}

			$screen = $bookingSubmenu[2];

			if ( $screen === $currentScreen ) {
				$cptIndex = $index;
			} elseif ( $screen === 'mphb_ical' ) {
				$syncIndex = $index;
			}
		}

		if ( $cptIndex !== false && $syncIndex !== false ) {
			$cptSubmenu = array_splice( $bookingMenu, $cptIndex, 1 );
			if ( $cptIndex < $syncIndex ) {
				$syncIndex--;
			}
			array_splice( $bookingMenu, $syncIndex, 0, $cptSubmenu );
		}

		unset( $bookingMenu );
	}
}
