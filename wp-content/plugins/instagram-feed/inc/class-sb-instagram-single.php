<?php

if (! defined('ABSPATH')) {
	die('-1');
}

/**
 * Class SB_Instagram_Single
 *
 * Uses oEmbeds to get data about single Instagram posts
 *
 * @since 2.5.3/5.8.3
 *
 * @package Instagram Feed
 */
class SB_Instagram_Single // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
{
	/**
	 * The permalink for the post used to get oEmbed data.
	 *
	 * @var string
	 */
	private $permalink;

	/**
	 * The parsed ID found in the permalink URL.
	 *
	 * @var string
	 */
	private $permalink_id;

	/**
	 * Data related to the post.
	 *
	 * @var array
	 */
	private $post;

	/**
	 * Error data from retrieving the oEmbed.
	 *
	 * @var array
	 */
	private $error;

	/**
	 * Object that has of several encryption features.
	 *
	 * @var object|SB_Instagram_Data_Encryption
	 *
	 * @since 5.14.5
	 */
	protected $encryption;

	/**
	 * SB_Instagram_Single constructor.
	 *
	 * @param string $permalink_or_permalink_id Either a link to the post or the ID embedded in it.
	 */
	public function __construct($permalink_or_permalink_id)
	{
		if (strpos($permalink_or_permalink_id, 'http') !== false) {
			$this->permalink    = $permalink_or_permalink_id;
			$exploded_permalink = explode('/', $permalink_or_permalink_id);
			$permalink_id       = $exploded_permalink[4];

			$this->permalink_id = $permalink_id;
		} else {
			$this->permalink_id = $permalink_or_permalink_id;
			$this->permalink    = 'https://www.instagram.com/p/' . $this->permalink_id;
		}
		$this->error = false;

		$this->encryption = new SB_Instagram_Data_Encryption();
	}

	/**
	 * Sets post data from cache or fetches new data
	 * if it doesn't exist or hasn't been updated recently
	 *
	 * @since 2.5.3/5.8.3
	 */
	public function init()
	{
		$this->post = $this->maybe_saved_data();

		if (
			empty($this->post)
			 || ! $this->was_recently_updated()
		) {
			if (! $this->should_delay_oembed_request()) {
				$data = $this->fetch();
				if (! empty($data)) {
					$data       = $this->parse_and_restructure($data);
					$this->post = $data;
					$this->update_last_update_timestamp();
					$this->update_single_cache();
				} elseif ($data === false) {
					$this->add_oembed_request_delay();
				}
			}
		}
	}

	/**
	 * Get the data related to the Instagram post.
	 *
	 * @return array
	 *
	 * @since 2.5.3/5.8.3
	 */
	public function get_post()
	{
		return $this->post;
	}

	/**
	 * Get error that occurred when retrieving data
	 *
	 * @return array|false
	 */
	public function get_error()
	{
		return $this->error;
	}

	/**
	 * Image URLs expire so this will compare when the data
	 * was last updated from the API
	 *
	 * @return bool
	 *
	 * @since 2.5.3/5.8.3
	 */
	public function was_recently_updated()
	{
		if (! isset($this->post['last_update'])) {
			return false;
		}

		return ( time() - 14 * DAY_IN_SECONDS ) < $this->post['last_update'];
	}


	/**
	 * Makes an HTTP request for fresh data from the oembed
	 * endpoint. Returns false if no new data or there isn't
	 * a business access token found.
	 *
	 * @return bool|mixed|null
	 *
	 * @since 2.5.3/5.8.3
	 */
	public function fetch()
	{
		// need a connected business account for this to work.
		$access_token = SB_Instagram_Oembed::last_access_token();

		if (empty($access_token)) {
			$this->error = 'No access token';
			return false;
		}

		$url = SB_Instagram_Oembed::oembed_url();

		$fetch_url = add_query_arg(
			array(
				'url'          => $this->permalink,
				'access_token' => $access_token,
			),
			$url
		);

		$result = wp_remote_get(esc_url_raw($fetch_url));

		$data = false;
		if (! is_wp_error($result)) {
			$data = isset($result['body']) ? json_decode($result['body'], true) : false;

			if ($data && isset($data['error'])) {
				$this->add_oembed_request_delay();
				$error_beginning = __('API error %s:', 'instagram-feed');
				$this->error = sprintf($error_beginning, $data['error']['code']) . ' ' . $data['error']['message'];
				$data        = false;
			}
		} else {
			$error = '';
			foreach ($result->errors as $key => $item) {
				$error .= $key . ' - ' . $item[0] . ' ';
			}
			$this->error = $error;
		}

		return $data;
	}

	/**
	 * If there was a problem with the last oEmbed request, the plugin
	 * waits 5 minutes to try again to prevent burning out the access token
	 * or causing Instagram to throttle HTTP requests from the server
	 *
	 * @return bool
	 *
	 * @since 2.5.3/5.8.3
	 */
	public function should_delay_oembed_request()
	{
		return ( get_transient('sbi_delay_oembeds_' . $this->permalink_id) !== false );
	}

	/**
	 * If there's an error, API requests are delayed 5 minutes
	 * for the specific permalink/post
	 *
	 * @since 2.5.3/5.8.3
	 */
	public function add_oembed_request_delay()
	{
		set_transient('sbi_delay_oembeds_' . $this->permalink_id, true, 300);
	}

	/**
	 * Track last API request due to some data expiring and
	 * needing to be refreshed
	 *
	 * @since 2.5.3/5.8.3
	 */
	private function update_last_update_timestamp()
	{
		$this->post['last_update'] = time();
	}

	/**
	 * Data retrieved with this method has its own cache
	 *
	 * @since 2.5.3/5.8.3
	 */
	private function update_single_cache()
	{
		$stored_option = get_option('sbi_single_cache', array());
		if (! is_array($stored_option)) {
			$stored_option = json_decode($this->encryption->decrypt($stored_option), true);
		}
		$new           = array( $this->permalink_id => $this->post );
		$stored_option = array_merge($new, (array) $stored_option);
		// only latest 400 posts to prevent a crazy amount of these.
		$stored_option = array_slice($stored_option, 0, 400);

		update_option('sbi_single_cache', $this->encryption->encrypt(sbi_json_encode($stored_option)), false);
	}

	/**
	 * Data is restructured to look like regular API data
	 * for ease of use with other plugin features
	 *
	 * @param array $data Raw data from the oEmbed endpoint.
	 *
	 * @return array
	 *
	 * @since 2.5.3/5.8.3
	 */
	private function parse_and_restructure($data)
	{
		// TODO: parse all of the available data for this post, currently just thumbnail.

		$return = array(
			'thumbnail_url' => '',
			'id'            => $this->permalink_id,
			'media_type'    => 'OEMBED',
		);

		if (! empty($data['thumbnail_url'])) {
			$return['thumbnail_url'] = $data['thumbnail_url'];
		}

		apply_filters('sbi_single_parse_and_restructure', $return);

		return $return;
	}

	/**
	 * Returns whatever data exists or empty array
	 *
	 * @return array
	 *
	 * @since 2.5.3/5.8.3
	 */
	private function maybe_saved_data()
	{
		$stored_option = get_option('sbi_single_cache', array());
		if (! is_array($stored_option)) {
			$stored_option = json_decode($this->encryption->decrypt($stored_option), true);
		}
		$data = array();
		if (! empty($stored_option[ $this->permalink_id ])) {
			return $stored_option[ $this->permalink_id ];
		} else {
			$settings = get_option('sb_instagram_settings', array());
			$resize_disabled = false;
			if (isset($settings['sb_instagram_disable_resize']) && $settings['sb_instagram_disable_resize'] === 'on') {
				$resize_disabled = true;
			}

			if (! $resize_disabled) {
				global $wpdb;

				$posts_table_name = $wpdb->prefix . SBI_INSTAGRAM_POSTS_TYPE;
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$results = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT json_data FROM $posts_table_name
						WHERE instagram_id = %s
						LIMIT 1",
						$this->permalink_id
					)
				);
				// phpcs:enable
				if (isset($results[0])) {
					$data = json_decode($this->encryption->decrypt($results[0]), true);
				}
			}
		}

		return $data;
	}
}
