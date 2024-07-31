<?php
/**
 * WooCommerce
 *
 * @category Integration
 */
class Milenia_WooCommerce extends Milenia_Integration {

	public function __construct() {

		$this->includes = array(
			'functions.php',
			'class-woocommerce-ordering.php',
			'class-woocommerce-layout.php'
		);

		parent::__construct( dirname( __FILE__ ) );
	}

	public function init() {

		$this->ordering = new Milenia_Catalog_Ordering();
		$this->layout = new Milenia_WooCommerce_Layout();

	}

	public function after_setup() {
		add_action( 'after_setup_theme', array( $this, 'setup' ) );
	}

	public function setup_actions() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}


	public function setup() {

		add_theme_support( 'woocommerce', apply_filters( 'milenia_woocommerce_args', array(
			'thumbnail_image_width' => 450,
			'gallery_thumbnail_image_width' => 206,
			'single_image_width' => 680,
			'product_grid'          => array(
				'default_columns' => 4,
				'default_rows'    => 4,
				'min_columns'     => 2,
				'max_columns'     => 4,
				'min_rows'        => 1
			)
		) ) );

		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

	}

	/**
	 * Enqueue script on favorite form.
	 *
	 * @since 3.6.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'milenia-woocommerce-mod', $this->get_url() . 'js/woocommerce' . ( WP_DEBUG ? '' : '.min' ) . '.js', array( 'jquery' ), 1, true );

	}

}
