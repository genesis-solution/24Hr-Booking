<?php

namespace InstagramFeed\Helpers;

/**
 * @since 6.1.2
 */
class Util {
	/**
	 * Returns the enabled debugging flag state.
	 *
	 * @return bool
	 */
	public static function isDebugging() {
		return ( defined( 'SBI_DEBUG' ) && SBI_DEBUG === true ) || isset( $_GET['sbi_debug'] ) || isset( $_GET['sb_debug'] );
	}

	public static function isIFPage() {
		return get_current_screen() !== null && ! empty( $_GET['page'] ) && strpos( $_GET['page'], 'sbi-' ) !== false;
	}

	/**
	 * Returns the script debug state.
	 *
	 * @return bool
	 */
	public static function is_script_debug() {
		return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG === true );
	}

	
	/**
	 * Get other active plugins of Smash Balloon
	 * 
	 * @since 6.2.0
	 */
	public static function get_sb_active_plugins_info() {
		// get the WordPress's core list of installed plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed_plugins = get_plugins();

		$is_facebook_installed = false;
		$facebook_plugin = 'custom-facebook-feed/custom-facebook-feed.php';
		if ( isset( $installed_plugins['custom-facebook-feed-pro/custom-facebook-feed.php'] ) ) {
			$is_facebook_installed = true;
			$facebook_plugin = 'custom-facebook-feed-pro/custom-facebook-feed.php';
		} else if ( isset( $installed_plugins['custom-facebook-feed/custom-facebook-feed.php'] ) ) {
			$is_facebook_installed = true;
		}

		$is_instagram_installed = false;
		$instagram_plugin = 'instagram-feed/instagram-feed.php';
		if ( isset( $installed_plugins['instagram-feed-pro/instagram-feed.php'] ) ) {
			$is_instagram_installed = true;
			$instagram_plugin = 'instagram-feed-pro/instagram-feed.php';
		} else if ( isset( $installed_plugins['instagram-feed/instagram-feed.php'] ) ) {
			$is_instagram_installed = true;
		}

		$is_twitter_installed = false;
		$twitter_plugin = 'custom-twitter-feeds/custom-twitter-feed.php';
		if ( isset( $installed_plugins['custom-twitter-feeds-pro/custom-twitter-feed.php'] ) ) {
			$is_twitter_installed = true;
			$twitter_plugin = 'custom-twitter-feeds-pro/custom-twitter-feed.php';
		} else if ( isset( $installed_plugins['custom-twitter-feeds/custom-twitter-feed.php'] ) ) {
			$is_twitter_installed = true;
		}

		$is_youtube_installed = false;
		$youtube_plugin       = 'feeds-for-youtube/youtube-feed.php';
		if ( isset( $installed_plugins['youtube-feed-pro/youtube-feed-pro.php'] ) ) {
			$is_youtube_installed = true;
			$youtube_plugin       = 'youtube-feed-pro/youtube-feed-pro.php';
		} elseif ( isset( $installed_plugins['feeds-for-youtube/youtube-feed.php'] ) ) {
			$is_youtube_installed = true;
		}

		$is_social_wall_installed = isset( $installed_plugins['social-wall/social-wall.php'] ) ? true : false;
		$social_wall_plugin = 'social-wall/social-wall.php';

		// Uncanny Automator plugin
		$is_uncanny_automator_installed = isset( $installed_plugins['uncanny-automator/uncanny-automator.php'] ) ? true : false;
		$uncanny_automator_plugin = 'uncanny-automator/uncanny-automator.php';
		$uncanny_automator_download_plugin = 'https://downloads.wordpress.org/plugin/uncanny-automator.zip';

		return array(
			'is_facebook_installed' => $is_facebook_installed,
			'is_instagram_installed' => $is_instagram_installed,
			'is_twitter_installed' => $is_twitter_installed,
			'is_youtube_installed' => $is_youtube_installed,
			'is_social_wall_installed' => $is_social_wall_installed,
			'is_uncanny_automator_installed' => $is_uncanny_automator_installed,
			'facebook_plugin' => $facebook_plugin,
			'instagram_plugin' => $instagram_plugin,
			'twitter_plugin' => $twitter_plugin,
			'youtube_plugin' => $youtube_plugin,
			'social_wall_plugin' => $social_wall_plugin,
			'uncanny_automator_plugin' => $uncanny_automator_plugin,
			'uncanny_automator_download_plugin' => $uncanny_automator_download_plugin,
			'installed_plugins' => $installed_plugins,
		);
	}

	/**
	 * Get a valid timestamp to avoid Year 2038 problem.
	 * 
	 * @param mixed $timestamp
	 * @return int
	 */
	public static function get_valid_timestamp( $timestamp ) {
		// check if timestamp is negative and set to maximum value
		if ( $timestamp < 0 ) {
			$timestamp = 2147483647;
		}

		if( is_numeric( $timestamp ) ) {
			$timestamp = (int) $timestamp;
			return $timestamp;
		} 

		$new_timestamp = new \DateTime( $timestamp );
		$year = $new_timestamp->format( 'Y' );
		if ( (int) $year >= 2038 ) {
			$new_timestamp->setDate( 2037, 12, 30 )->setTime( 00, 00, 00 );
			$timestamp = $new_timestamp->getTimestamp();
		} else {
			$timestamp = strtotime( $timestamp );
		}

		return $timestamp;
	}

	/**
	 * Checks if the user has custom templates, CSS or JS added
	 * 
	 * @return bool
	 * @since 6.3
	 */
	public static function sbi_has_custom_templates() {
		// Check if the user has sbi custom templates in their theme
		$templates = array(
			'feed.php',
			'footer.php',
			'header.php',
			'item.php',
		);
		foreach ( $templates as $template ) {
			if ( locate_template( 'sbi/' . $template ) ) {
				return true;
			}
		}

		// Check if the user has custom CSS and or JS added in the settings
		$settings = get_option( 'sb_instagram_settings', array() );
		if ( isset( $settings['sb_instagram_custom_css'] ) && ! empty( $settings['sb_instagram_custom_css'] ) ) {
			return true;
		}
		if ( isset( $settings['sb_instagram_custom_js'] ) && ! empty( $settings['sb_instagram_custom_js'] ) ) {
			return true;
		}

		return false;
	}

	/** 
	 * Checks if the user has custom templates, CSS or JS added and if they have dismissed the notice
	 * 
	 * @return bool
	 * @since 6.3
	 */
	public static function sbi_show_legacy_css_settings() {
		$show_legacy_css_settings = false;
		$sbi_statuses = get_option( 'sbi_statuses', array() );
		
		if ( ( isset( $sbi_statuses['custom_templates_notice'] ) 
			&& self::sbi_has_custom_templates() )
			|| self::sbi_legacy_css_enabled() ) {
			$show_legacy_css_settings = true;
		}

		$show_legacy_css_settings = apply_filters( 'sbi_show_legacy_css_settings', $show_legacy_css_settings );

		return $show_legacy_css_settings;
	}

	/**
	 * Checks if the user has legacy CSS enabled 
	 * 
	 * @return bool
	 * @since 6.3
	 */
	public static function sbi_legacy_css_enabled() {
		$legacy_css_enabled = false;
		$settings = get_option( 'sb_instagram_settings', array() );
		if ( isset( $settings['enqueue_legacy_css'] ) 
			&& $settings['enqueue_legacy_css'] ) {
			$legacy_css_enabled = true;
		}

		$legacy_css_enabled = apply_filters( 'sbi_legacy_css_enabled', $legacy_css_enabled );

		return $legacy_css_enabled;
	}

	/**
	 * Checks if sb_instagram_posts_manager errors exists.
	 * 
	 * @return bool
	 */
	public static function sbi_has_admin_errors() {
		global $sb_instagram_posts_manager;
		$are_critical_errors = $sb_instagram_posts_manager->are_critical_errors();

		if ( $are_critical_errors ) {
			return true;
		}
		
		$errors = $sb_instagram_posts_manager->get_errors();
		if( ! empty( $errors ) ) {
			foreach ( $errors as $type => $error ) {
				if ( in_array( $type, array( 'database_create', 'upload_dir', 'unused_feed', 'platform_data_deleted' ) ) 
					&& ! empty( $error ) ) {
					return true;
				}
			}
		}

		return false;
	}
}