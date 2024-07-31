<?php

namespace MPHB\Admin\Fields;

/**
 * @since 3.8.1
 */
class InstallPluginField extends InputField {

	const TYPE = 'install-plugin';

	/**
	 * @var string The text under/before the "Install" button.
	 */
	protected $text = '';

	protected $buttonText    = '';
	protected $buttonClasses = 'button';

	protected $pluginSlug    = '';
	protected $pluginZipLink = '#';

	/**
	 * string|false Redirect URL of empty string to jusr reload the current page.
	 *     Use FALSE to do nothing.
	 */
	protected $redirect = '';

	public function __construct( $name, $args, $value = '' ) {
		parent::__construct( $name, $args, $value );

		$this->text          = isset( $args['text'] ) ? $args['text'] : $this->text;
		$this->buttonText    = isset( $args['button_text'] ) ? $args['button_text'] : __( 'Install & Activate', 'motopress-hotel-booking' );
		$this->buttonClasses = isset( $args['button_classes'] ) ? $args['button_classes'] : $this->buttonClasses;
		$this->pluginSlug    = isset( $args['plugin_slug'] ) ? $args['plugin_slug'] : $this->pluginSlug;
		$this->pluginZipLink = isset( $args['plugin_zip'] ) ? $args['plugin_zip'] : $this->pluginZipLink;
		$this->redirect      = isset( $args['redirect'] ) ? $args['redirect'] : $this->redirect;
	}

	protected function renderInput() {
		$output = '';

		if ( ! empty( $this->text ) ) {
			$output .= '<p>' . $this->text . '</p>';
		}

		if ( $this->redirect === false ) {
			$redirect = 'no';
		} else {
			$redirect = $this->redirect;
		}

		$output         .= '<p class="button-row">';
			$output     .= '<button class="' . esc_attr( $this->buttonClasses ) . '" data-plugin-slug="' . esc_attr( $this->pluginSlug ) . '" data-plugin-zip="' . esc_attr( $this->pluginZipLink ) . '" data-redirect="' . esc_attr( $redirect ) . '">';
				$output .= $this->buttonText;
			$output     .= '</button>';

			$output .= ' <span class="mphb-preloader mphb-hide"></span>';
			$output .= ' <span class="status-text mphb-hide"></span>';
		$output     .= '</p>';

		return $output;
	}
}
