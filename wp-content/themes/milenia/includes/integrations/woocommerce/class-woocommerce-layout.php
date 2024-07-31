<?php

class Milenia_WooCommerce_Layout {

	public function __construct() {
		add_action( 'woocommerce_init', array( $this, 'woocommerce_init' ) );
	}

	public function woocommerce_init() {

		global $milenia_settings;

		remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
		remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

		remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open');
		remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail');
		remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title');
		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
		remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
		remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

		remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
		remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
		remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );

		/* Archive Hooks */
		add_action( 'woocommerce_archive_description', array( $this, 'woocommerce_ordering_products' ) );
		add_action( 'woocommerce_after_single_product', 'woocommerce_upsell_display', 15 );
		add_action( 'woocommerce_after_single_product', 'woocommerce_output_related_products', 20 );

		/* Content Product Hooks */
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'template_loop_product_thumbnail' ) );
		add_action( 'woocommerce_shop_loop_item_title', array( $this, 'template_loop_product_title' ) );
		add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'template_after_shop_loop_item_title' ) );

		if ( $milenia_settings['product-crosssell'] ) {
			add_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 9 );
		}

//		if ( defined( 'WOOCOMMERCE_VERSION' ) ) {
//			if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
//				add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
//			} else {
//				define( 'WOOCOMMERCE_USE_CSS', false );
//			}
//		}

//		add_filter( 'woocommerce_get_image_size_shop_single', function( $size ) {
//			return array(
//				'width' => 680,
//				'height' => 680,
//				'crop' => 1,
//			);
//		} );

		add_filter( 'loop_shop_columns', array( $this, 'loop_columns') );
		add_filter( 'loop_shop_per_page', array( $this, 'loop_per_page') );

		add_filter( 'woocommerce_show_page_title', function() { return false; } );

//		add_filter( 'woocommerce_pagination_args', array( $this, 'woocommerce_pagination_args') );
//		add_filter( 'woocommerce_general_settings', array( $this, 'woocommerce_general_settings_filter') );
//		add_filter( 'woocommerce_page_settings', array( $this, 'woocommerce_general_settings_filter') );
//		add_filter( 'woocommerce_catalog_settings', array( $this, 'woocommerce_general_settings_filter') );
//		add_filter( 'woocommerce_inventory_settings', array( $this, 'woocommerce_general_settings_filter') );
//		add_filter( 'woocommerce_shipping_settings', array( $this, 'woocommerce_general_settings_filter') );
//		add_filter( 'woocommerce_tax_settings', array( $this, 'woocommerce_general_settings_filter') );
//		add_filter( 'woocommerce_product_settings', array( $this, 'woocommerce_general_settings_filter') );

		add_filter( 'woocommerce_upsell_display_args', array( $this, 'upsell_display_args' ) );
		add_filter( 'woocommerce_cross_sells_total', array( $this, 'cross_sells_total' ) );
		add_filter( 'woocommerce_cross_sells_columns', array( $this, 'cross_sells_columns' ) );

	}

	public function loop_columns() {
		global $milenia_settings;
		return $milenia_settings['product-archive-columns'];
	}

	public function loop_per_page() {
		global $milenia_settings;
		return $milenia_settings['product-archive-per-page'];
	}

	public function woocommerce_ordering_products() {
		echo milenia_run()->get( 'woocommerce' )->ordering->output();
	}

	function woocommerce_general_settings_filter($options) {
		$delete = array( 'woocommerce_enable_lightbox' );

		foreach ( $options as $key => $option ) {
			if (isset($option['id']) && in_array($option['id'], $delete)) {
				unset($options[$key]);
			}
		}
		return $options;
	}

	public function template_loop_product_thumbnail() {
		$this->get_product_thumbnail();
	}

	public function get_product_thumbnail() { ?>

		<figure class="product-image">
			<a href="<?php echo esc_url(get_the_permalink()) ?>">
				<?php echo woocommerce_get_product_thumbnail( 'shop_catalog' ); ?>
			</a>
			<?php woocommerce_template_loop_add_to_cart(); ?>
		</figure>

		<?php
	}

	public function template_loop_product_title() {
		echo '<h5 class="product-name"><a href="'. esc_url(get_the_permalink()) .'">' . get_the_title() . '</a></h5>';
	}

	public function template_after_shop_loop_item_title() {
		echo '<div class="pricing-area">';
		woocommerce_template_loop_price();
		woocommerce_template_loop_rating();
		echo '</div>';
	}

	public function woocommerce_pagination_args($args) {

		$args['prev_text'] = esc_html__('Previous', 'milenia' );
		$args['next_text'] = esc_html__('Next', 'milenia' );

		return $args;
	}

	public function woocommerce_after_add_to_cart_button() {
		echo '</div>';
	}

	public function upsell_display_args($args) {
		global $milenia_settings;

		$args['posts_per_page'] = $milenia_settings['product-upsells-count'];

		return $args;
	}

	public function cross_sells_total($limit) {
		global $milenia_settings;

		$count_limit = $milenia_settings['product-crosssell-count'];

		if ( $count_limit > 0 )
			return $count_limit;

		return $limit;
	}


	public function cross_sells_columns($columns) {
		global $milenia_settings;

		$count_columns = $milenia_settings['product-crosssell-columns'];

		if ( $count_columns > 0 )
			return $count_columns;

		return $columns;
	}

}