<?php

if ( !class_exists( 'Milenia_Catalog_Ordering' ) ) {

	class Milenia_Catalog_Ordering {

		function __construct() {

		}

		public function woo_build_query_string ($params = array(), $key, $value) {
			$params[$key] = $value;
			return "?" . http_build_query($params);
		}

		public function woo_active_class($key1, $key2) {
			if ( $key1 == $key2 ) return " class='milenia-active'";
		}

		public function output() {

			global $milenia_config, $query_string;

			parse_str( $query_string, $params );

			$product_order = array();
			$product_order['default'] 	= esc_html__("Default", 'milenia' );
			$product_order['title'] 	= esc_html__("Name", 'milenia' );
			$product_order['price'] 	= esc_html__("Price", 'milenia' );
			$product_order['date'] 		= esc_html__("Date", 'milenia' );
			$product_order['popularity'] = esc_html__("Popularity", 'milenia' );

			$product_order_key = !empty($milenia_config['woocommerce']['product_order']) ? $milenia_config['woocommerce']['product_order'] : 'default';
			?>

			<div class="product-sort-section">

				<?php woocommerce_result_count() ?>

				<div class="milenia-custom-select">

					<div class="milenia-selected-option">
						<?php echo esc_html( $product_order[$product_order_key] ) ?>
					</div>

					<ul class="milenia-options-list">
						<li><a <?php echo sprintf('%s', $this->woo_active_class($product_order_key, 'default')); ?> href="<?php echo esc_url($this->woo_build_query_string($params, 'product_order', 'default')) ?>"><?php echo esc_html($product_order['default']) ?></a></li>
						<li><a <?php echo sprintf('%s', $this->woo_active_class($product_order_key, 'title')); ?> href="<?php echo esc_url($this->woo_build_query_string($params, 'product_order', 'title')) ?>"><?php echo esc_html($product_order['title']) ?></a></li>
						<li><a <?php echo sprintf('%s', $this->woo_active_class($product_order_key, 'price')); ?> href="<?php echo esc_url($this->woo_build_query_string($params, 'product_order', 'price')) ?>"><?php echo esc_html($product_order['price']) ?></a></li>
						<li><a <?php echo sprintf('%s', $this->woo_active_class($product_order_key, 'date')); ?> href="<?php echo esc_url($this->woo_build_query_string($params, 'product_order', 'date')) ?>"><?php echo esc_html($product_order['date']) ?></a></li>
						<li><a <?php echo sprintf('%s', $this->woo_active_class($product_order_key, 'popularity')); ?> href="<?php echo esc_url($this->woo_build_query_string($params, 'product_order', 'popularity')) ?>"><?php echo esc_html($product_order['popularity']) ?></a></li>
					</ul>

				</div>

			</div><!--/ .product-sort-section-->

			<?php
		}

	}
}

?>
