<?php

namespace MPHB\Utils;

use Plugin_Upgrader;

class ThirdPartyPluginsUtils {

	/**
	 * Check is plugin active.
	 *
	 * @param string $pluginSubDirSlashFile
	 * @return bool
	 */
	public static function isPluginActive( $pluginSubDirSlashFile ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			/**
			 * Detect plugin. For use on Front End only.
			 */
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( $pluginSubDirSlashFile );
	}

	/**
	 * Check is active WooCommerce
	 *
	 * @return bool
	 */
	public static function isActiveWoocommerce() {
		return self::isPluginActive( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Check is active Hotel Booking WooCommerce Payments
	 *
	 * @return bool
	 */
	public static function isActiveMphbWoocommercePayments() {
		return self::isPluginActive( 'mphb-woocommerce/mphb-woocommerce.php' );
	}

	/**
	 * Check is active Hotel Booking Payment Request
	 *
	 * @return bool
	 */
	public static function isActiveMphbPaymentRequest() {
		return self::isPluginActive( 'mphb-request-payment/mphb-request-payment.php' );
	}

	/**
	 * Check is active Hotel Booking Reviews
	 *
	 * @return bool
	 */
	public static function isActiveMphbReviews() {
		return self::isPluginActive( 'mphb-reviews/mphb-reviews.php' );
	}

	/**
	 * Check is active Hotel Booking & MailChimp Integration
	 *
	 * @return bool
	 *
	 * @since 3.7.2
	 */
	public static function isActiveMphbMailchimp() {
		return self::isPluginActive( 'mphb-mailchimp/mphb-mailchimp.php' );
	}

	/**
	 * Check is active Hotel Booking Notifier
	 *
	 * @return bool
	 *
	 * @since 3.7.2
	 */
	public static function isActiveMphbNotifier() {
		return self::isPluginActive( 'mphb-notifier/mphb-notifier.php' );
	}

	/**
	 * Check is active Easy Digital Downloads
	 *
	 * @return bool
	 */
	public static function isActiveEDD() {
		return self::isPluginActive( 'easy-digital-downloads/easy-digital-downloads.php' );
	}

	/**
	 * Check is active Hotel Booking
	 *
	 * @return bool
	 */
	public static function isActiveMphb() {
		return self::isPluginActive( 'motopress-hotel-booking/motopress-hotel-booking.php' );
	}

	/**
	 * Check is active Hotel Booking Lite
	 *
	 * @return bool
	 */
	public static function isActiveMphbLite() {
		return self::isPluginActive( 'motopress-hotel-booking-lite/motopress-hotel-booking.php' );
	}

	/**
	 * @param string $slug Plugin slug, like: <pre>wordpress-importer/wordpress-imported.php</pre>.
	 * @return bool
	 *
	 * @since 3.8.1
	 */
	public static function isPluginInstalled( $slug ) {
		return file_exists( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $slug );
	}

	/**
	 * @param string $pluginZip Plugin ZIP download link, like:
	 *     <pre>https://downloads.wordpress.org/plugin/wordpress-importer.latest-stable.zip</pre>.
	 * @return bool
	 *
	 * @since 3.8.1
	 */
	public static function installPlugin( $pluginZip ) {
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		// "... Just be on the safe side"
		wp_cache_flush();

		$upgrader = new Plugin_Upgrader();

		/**
		 * @var bool|\WP_Error TRUE on success, FALSE or WP_Error on failure.
		 */
		$installed = $upgrader->install( $pluginZip );

		return $installed === true;
	}

	/**
	 * @param string $slug Plugin slug, like: <pre>wordpress-importer/wordpress-imported.php</pre>.
	 * @return bool
	 *
	 * @since 3.8.1
	 */
	public static function activatePlugin( $slug ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		/**
		 * @var null|\WP_Error WP_Error on invalid file or NULL on success.
		 */
		$activated = activate_plugin( $slug );

		return ! is_wp_error( $activated );
	}

}
