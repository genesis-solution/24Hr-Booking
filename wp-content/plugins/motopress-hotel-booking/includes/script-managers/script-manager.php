<?php

namespace MPHB\ScriptManagers;

abstract class ScriptManager {

	/**
	 *
	 * @var array
	 */
	protected $styleDependencies = array();

	/**
	 *
	 * @var string[]
	 */
	protected $scriptDependencies = array( 'jquery' );

	/**
	 *
	 * @var bool
	 */
	protected $isScriptDebug = false;

	public function __construct() {
		$this->isScriptDebug = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}

	public function addDependency( $dependency ) {
		$this->scriptDependencies[] = $dependency;
	}

	public function addStyleDependency( $dependency ) {
		$this->styleDependencies[] = $dependency;
	}

	/**
	 *
	 * @param string $locale Optional.
	 * @return string
	 */
	protected function getDatepickerLocale( $locale = null ) {
		$availableLocales = include 'datepick-locales.php';
		if ( is_null( $locale ) ) {
			$locale = get_locale();
		}
		if ( $locale == 'nb_NO' || $locale == 'nn_NO' ) {
			$locale = 'no';
		}
		if ( ! in_array( $locale, $availableLocales ) ) {
			$locale = substr( $locale, 0, 2 );
			if ( ! in_array( $locale, $availableLocales ) ) {
				$locale = 'en_US';
			}
		}
		return $locale;
	}

	/**
	 * @param string $theme
	 *
	 * @return string|false
	 */
	protected function locateDatepickFile( $theme ) {
		if (
			$theme === '' // Default theme selected
			|| ! array_key_exists( $theme, MPHB()->settings()->main()->getDatepickerThemesList() ) && ! array_key_exists( $theme, MPHB()->settings()->main()->getAdminDatepickerThemesList() )
		) {
			return false;
		} elseif ( $theme == 'admin' ) {
			return $datepickerThemeFile = $this->scriptUrl( 'assets/css/admin-datepick-themes/mphb-datepicker-admin.css' );
		}

		$datepickerThemeFile = $this->scriptUrl( "assets/css/datepick-themes/mphb-datepicker-{$theme}.css" );

		return $datepickerThemeFile;
	}

	protected function registerDatepickerLocalization() {

		$locale = $this->getDatepickerLocale();

		if ( $locale === 'en_US' ) {
			// en_US is default locale for datepicker and not needs localization
			return;
		}

		$datepickerLocale = str_replace( '_', '-', $locale );

		$datepickerLocaleFile = $this->scriptUrl( "vendors/kbwood/datepick/jquery.datepick-{$datepickerLocale}.js" );

		wp_register_script( 'mphb-kbwood-datepick-localization', $datepickerLocaleFile, array( 'mphb-kbwood-datepick' ), MPHB()->getVersion(), true );

		$this->addDependency( 'mphb-kbwood-datepick-localization' );
	}

	public function register() {

		wp_register_script(
			'mphb-canjs',
			$this->scriptUrl( 'vendors/canjs/can.custom.min.js' ),
			array( 'jquery' ),
			MPHB()->getVersion(),
			true
		);
		$this->addDependency( 'mphb-canjs' );

		wp_register_script(
			'mphb-kbwood-plugin',
			$this->scriptUrl( 'vendors/kbwood/datepick/jquery.plugin.min.js' ),
			array( 'jquery' ),
			MPHB()->getVersion(),
			true
		);
		wp_register_script(
			'mphb-kbwood-datepick',
			$this->scriptUrl( 'vendors/kbwood/datepick/jquery.datepick.min.js' ),
			array( 'jquery', 'mphb-kbwood-plugin' ),
			MPHB()->getVersion(),
			true
		);
		$this->addDependency( 'mphb-kbwood-datepick' );

		$this->registerDatepickerLocalization();

		$this->registerStyles();
	}

	protected function registerStyles() {

		wp_register_style( 'mphb-kbwood-datepick-css', $this->scriptUrl( 'vendors/kbwood/datepick/jquery.datepick.css' ), null, MPHB()->getVersion() );
		$this->addStyleDependency( 'mphb-kbwood-datepick-css' );
	}

	public function scriptUrl( $relativePath ) {
		if ( $this->isScriptDebug ) {
			// Flexslider has suffix "-min.js"; Spectrum has "-min.js" version,
			// but does not have ".js" (not minified) version
			$relativePath = str_replace( array( '.min.js', '.min.css' ), array( '.js', '.css' ), $relativePath );
		}
		$scriptUrl = MPHB()->getPluginUrl( $relativePath );
		return $scriptUrl;
	}

	abstract public function enqueue();
}
