<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class Vc_Navbar_Undoredo
 */
class Vc_Navbar_Undoredo {
	public function __construct() {
		// Backend
		add_filter( 'vc_nav_controls', array(
			$this,
			'addControls',
		) );

		// Frontend
		add_filter( 'vc_nav_front_controls', array(
			$this,
			'addControls',
		) );
	}

	/**
	 * @param $controls
	 * @return array
	 */
	public function addControls( $controls ) {
		$controls[] = array(
			'undo',
			'<li><a id="vc_navbar-undo" href="javascript:;" class="vc_icon-btn vc_undo-redo" disabled title="' . esc_attr__( 'Undo', 'js_composer' ) . '"><i class="vc-composer-icon vc-c-icon-undo"></i></a></li>',
		);
		$controls[] = array(
			'redo',
			'<li><a id="vc_navbar-redo" href="javascript:;" class="vc_icon-btn vc_undo-redo" disabled title="' . esc_attr__( 'Redo', 'js_composer' ) . '"><i class="vc-composer-icon vc-c-icon-redo"></i></a></li>',
		);

		return $controls;
	}
}
