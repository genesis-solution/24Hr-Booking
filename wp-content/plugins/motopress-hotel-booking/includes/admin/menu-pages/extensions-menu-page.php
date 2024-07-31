<?php

namespace MPHB\Admin\MenuPages;

class ExtensionsMenuPage extends AbstractMenuPage {

	/**
	 * @var array ["slug", "title", "excerpt", "thumbnail", "link"]
	 */
	protected $products = array(); // See onLoad()

	public function addActions() {
		parent::addActions();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function createMenu() {
		if ( MPHB()->settings()->main()->showExtensionLinks() ) {
			parent::createMenu();
		}
	}

	public function enqueueScripts() {
		if ( $this->isCurrentPage() ) {
			MPHB()->getAdminScriptManager()->enqueue();
		}
	}

	public function onLoad() {
		if ( ! $this->isCurrentPage() ) {
			return;
		}

		$products = $this->loadProducts();

		if ( $products !== false ) {
			$this->products = $products;
		}
	}

	/**
	 * @return array|false
	 */
	protected function loadProducts() {
		$products = get_transient( 'mphb_extensions' );

		if ( $products !== false ) {
			return $products;
		}

		// Request products
		$apiProducts = $this->requestProducts();
		$products    = $this->parseProducts( $apiProducts );

		// Load from reserve option
		if ( $products === false ) {
			$products = get_option( 'mphb_last_known_extensions', false );
		} else {
			update_option( 'mphb_last_known_extensions', $products, 'no' );
		}

		if ( $products !== false ) {
			set_transient( 'mphb_extensions', $products, DAY_IN_SECONDS );
		}

		return $products;
	}

	/**
	 * @return \stdClass[]|false
	 */
	protected function requestProducts() {
		$requestUrl  = 'https://motopress.com/edd-api/v2/products/?category=hotel-booking-addons';
		$requestArgs = array(
			'timeout' => 15,
		);

		$request = wp_remote_get( $requestUrl, $requestArgs );

		if ( is_wp_error( $request ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $request );

		/**
		 * @var \stdClass[]|null
		 */
		$json = json_decode( $body );

		if ( empty( $json ) ) {
			return false;
		}

		return $json->products;
	}

	/**
	 * @param \stdClass[]|false $apiProducts
	 * @return array|false
	 */
	protected function parseProducts( $apiProducts ) {
		if ( $apiProducts === false ) {
			return false;
		}

		$products = array();

		$pluginProductId = MPHB()->settings()->license()->getProductId();

		foreach ( $apiProducts as $product ) {

			/**
			 * @var \stdClass
			 */
			$info = $product->info;

			if ( $info->status != 'publish' || $info->id == $pluginProductId ) {
				continue;
			}

			$products[] = array(
				'slug'      => $info->slug,
				'title'     => $info->title,
				'excerpt'   => $info->excerpt,
				'thumbnail' => $info->thumbnail,
				'link'      => $info->link,
			);
		}

		return $products;
	}

	public function render() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Extensions', 'motopress-hotel-booking' ); ?></h1>

			<?php if ( ! empty( $this->products ) ) { ?>
				<p><?php esc_html_e( 'Extend the functionality of Hotel Booking plugin with the number of helpful addons for your custom purposes.', 'motopress-hotel-booking' ); ?></p>
				<div class="mphb-extensions">
					<?php foreach ( $this->products as $product ) { ?>
						<?php
						$utmLink = add_query_arg(
							array(
								'utm_source' => 'customer_website_dashboard',
								'utm_medium' => $product['slug'],
							),
							$product['link']
						);
						?>
						<div class="mphb-extension">
							<a href="<?php echo esc_url( $utmLink ); ?>" target="_blank">
								<img src="<?php echo esc_url( $product['thumbnail'] ); ?>" class="mphb-extension-thumbnail" />
							</a>
							<div class="mphb-extension-content">
								<h3 class="mphb-extension-title">
									<a href="<?php echo esc_url( $utmLink ); ?>" target="_blank">
										<?php echo esc_html( $product['title'] ); ?>
									</a>
								</h3>
								<p class="mphb-extension-excerpt">
								<?php
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo wp_trim_words( esc_html( $product['excerpt'] ), 25 );
								?>
									</p>
								<a href="<?php echo esc_url( $utmLink ); ?>" class="mphb-extension-link button" target="_blank">
													<?php
													esc_html_e( 'Get this Extension', 'motopress-hotel-booking' );
													?>
									</a>
							</div>
						</div>
					<?php } ?>
				</div>
			<?php } else { ?>
				<p><?php esc_html_e( 'No extensions found.', 'motopress-hotel-booking' ); ?></p>
			<?php } ?>
		</div>
		<?php
	}

	protected function getPageTitle() {
		return __( 'Extensions', 'motopress-hotel-booking' );
	}

	protected function getMenuTitle() {
		return '<span class="dashicons dashicons-admin-plugins" style="font-size:17px;"></span> ' .
			__( 'Extensions', 'motopress-hotel-booking' );
	}
}
