<?php

namespace MPHB\Admin\Groups;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsGroup extends InputGroup {

	protected $name;
	protected $page;
	protected $description;


	/**
	 * @note that name of group must
	 * @param string $name
	 * @param string $label Optional.
	 * @param string $page
	 * @param string $description Optional.
	 */
	public function __construct( $name, $label, $page, $description = '' ) {

		parent::__construct( $name, $label );

		$this->description = $description;
		$this->page        = $page;
	}


	public function addField( \MPHB\Admin\Fields\InputField $field ) {

		// TODO temporary solution. move this code
		switch ( $field->getName() ) {

			case 'mphb_template_mode':
				$value = MPHB()->settings()->main()->getTemplateMode();
				break;

			case 'mphb_email_base_color':
				$value = MPHB()->settings()->emails()->getBaseColor();
				break;

			case 'mphb_email_bg_color':
				$value = MPHB()->settings()->emails()->getBGColor();
				break;

			case 'mphb_email_body_bg_color':
				$value = MPHB()->settings()->emails()->getBodyBGColor();
				break;

			case 'mphb_email_body_text_color':
				$value = MPHB()->settings()->emails()->getBodyTextColor();
				break;

			default:
				$value = get_option( $field->getName(), $field->getDefault() );
				break;
		}

		$field->setValue( $value );

		parent::addField( $field );
	}

	public function register() {

		$label = $this->getLabel();

		if ( ! empty( $label ) ) {

			$link = sprintf(
				'%1$s#%2$s',
				add_query_arg( array() ),
				$this->getName()
			);

			$label = sprintf(
				'%3$s <a class="mphb-link-anchor" id="%1$s" href="%2$s">#</a>',
				esc_attr( $this->getName() ),
				esc_url( $link ),
				$this->getLabel()
			);
		}

		add_settings_section(
			$this->getName(),
			$label,
			array( $this, 'render' ),
			$this->getPage(),
			array(
				'before_section' => '<div data-group-name="' . esc_attr( $this->getName() ) . '">',
				'after_section'  => '</div>',
			)
		);

		foreach ( $this->fields as $field ) {

			register_setting( $this->getName(), $field->getName() );
			add_settings_field( $field->getName(), $field->getLabel(), array( $field, 'output' ), $this->getPage(), $this->getName() );
		}
	}

	public function getPage() {

		return $this->page;
	}

	public function render() {

		if ( ! empty( $this->description ) ) {

			echo '<p>' . wp_kses_post( $this->description ) . '</p>';
		}
	}

	public function save() {

		foreach ( $this->fields as $field ) {

			if ( isset( $_POST[ $field->getName() ] ) && ! $field->isDisabled() ) {

				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$value = wp_unslash( $_POST[ $field->getName() ] );
				$value = $field->sanitize( $value );

				update_option( $field->getName(), $value );

				if ( $field->isTranslatable() ) {

					MPHB()->translation()->registerWPMLString( $field->getName(), $value );
				}
			}
		}
	}
}
