<?php
/**
 * Plugin integrations.
 */

abstract class Milenia_Integration {

	public $includes = array();
	public $directory;

	/**
	 * Add customizer support.
	 *
	 * @since 3.5.0
	 * @access public
	 * @var $has_customizer
	 */
	public function __construct( $directory ) {
		$this->directory = $directory;
		$this->includes();
		$this->init();
		$this->after_setup();
		$this->setup_actions();
		$this->internal_actions();
	}

	private function includes() {
		if ( empty( $this->includes ) ) {
			return;
		}

		foreach ( $this->includes as $file ) {
			require_once( trailingslashit( $this->directory ) . $file );
		}
	}

	public function init() {}

	public function after_setup() {}

	public function setup_actions() {}

	private function internal_actions() {
		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	public function body_class( $classes ) {
		$classes[] = $this->get_slug();

		return $classes;
	}

	public function get_url() {
		return trailingslashit( get_theme_file_uri( 'includes/integrations/' . $this->get_slug() ) );
	}

	public function get_dir() {
		return trailingslashit( $this->directory );
	}

	private function get_slug() {
		$slug = basename( $this->get_dir() );

		return $slug;
	}

}
