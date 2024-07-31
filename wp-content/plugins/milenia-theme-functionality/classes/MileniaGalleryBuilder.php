<?php
/**
 * The MileniaGalleryBuilder class.
 *
 * This class is responsible to create gallery builder metabox in specific post types.
 *
 * @package WordPress
 * @subpackage MileniaThemeFunctionality
 * @since MileniaThemeFunctionality 1.0.0
 */

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly.', 'milenia-app-textdomain') );
}


if (!class_exists('MileniaGalleryBuilder')) {

	class MileniaGalleryBuilder {

		/**
		 * Contains an action name.
		 *
		 * @access protected
		 * @var string
		 */
		protected $action_gallery_builder = 'milenia_generate_inserted_media_to_slider';

		/**
		 * Contains an arguments array.
		 *
		 * @access protected
		 * @var array
		 */
		protected $args = array(
			'builder_title' => 'Gallery Builder',
			'paths' => array(),
			'fields' => array(),
			'item_fields' => array(),
			'supports' => array('image', 'video')
		);

		/**
		 * Contain a post type name to which gallery builder will be added.
		 *
		 * @access protected
		 * @var string
		 */
		protected $post_type;

		/**
		 * Returns path to the file relative to the work directory.
		 *
		 * @param string $filename
		 * @access protected
		 * @return string
		 */
		protected function path($filename)
		{
			if(!isset($this->args['paths'])) {
				$this->args['paths'] = array();
				if(!isset($this->args['paths']['root'])) {
					$this->args['paths']['root'] = plugin_dir_path(__FILE__);
				}
			}
			return sprintf('%s/%s', $this->args['paths']['root'], $filename);
		}

		/**
		 * Returns path to the specified asset.
		 *
		 * @param string $filename
		 * @access protected
		 * @return string
		 */
		protected function assetUrl($filename) {
			if(!isset($this->args['paths'])) {
				$this->args['paths'] = array();
				if(!isset($this->args['paths']['url'])) {
					$this->args['paths']['url'] = plugin_dir_url(__FILE__);
				}
			}
			return preg_replace( '/\s/', '%20', sprintf('%s/%s', $this->args['paths']['url'], $filename));
		}

		/**
		 * The class constructor.
		 *
		 * @param string $post_type
		 * @param array $args
		 */
		function __construct($post_type, array $args) {

			$this->post_type = $post_type;
			$this->args = array_merge($this->args, $args);

			add_action( 'add_meta_boxes', array($this, 'register_metabox') );
			add_action( 'save_post', array($this, 'save_perm_metadata'), 1, 2);
			add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts_and_styles') );

			add_action('wp_ajax_' . $this->action_gallery_builder, array($this, 'generate_inserted_media_to_slider'));
		}

		/**
		 * Registers builder meta box in the WP ecosystem.
		 *
		 * @access public
		 */
		public function register_metabox() {
			add_meta_box(
				'milenia_gallery_builder',
				isset($this->args['builder_title']) ? esc_html($this->args['builder_title']) : esc_html__( 'Gallery Builder', 'milenia-app-textdomain' ),
				array($this, 'display_metabox'),
				$this->post_type,
				'normal',
				'default'
			);
		}

		/**
		 * Draws meta box content.
		 *
		 * @param WP_Post $post
		 * @access public
		 */
		public function display_metabox($post) {
			$data = $this->get_page_settings($post->ID);
			$data['args'] = $this->args;

			$this->view($this->path('views/milenia-gallery-builder-meta-boxes.php'), $data);
		}

		public function get_theme_gallery_builder($postid) {
			$milenia_gallery_builder = get_post_meta($postid, "milenia_gallery_builder", true);

			if (!is_array($milenia_gallery_builder)) {
				$milenia_gallery_builder = array();
			}

			return $milenia_gallery_builder;
		}

		public function enqueue_scripts_and_styles() {
			wp_enqueue_media();

			$css_file = $this->assetUrl('assets/css/gallery_builder.css');
			$css_file_form_styler = $this->assetUrl('assets/vendors/jQueryFormStyler/jquery.formstyler.css');
			$js_file_form_styler = $this->assetUrl('assets/vendors/jQueryFormStyler/jquery.formstyler.min.js');
			$js_file = $this->assetUrl('assets/js/gallery_builder.js');
			$js_file_modernizr = $this->assetUrl('assets/vendors/modernizr.js');

			wp_enqueue_style( 'milenia_gallery-builder', $css_file );
			wp_enqueue_style( 'milenia_form_styler', $css_file_form_styler );
			wp_enqueue_script( 'milenia_modernizr', $js_file_modernizr );
			wp_enqueue_script( array( 'jquery-ui-sortable' ) );
			wp_enqueue_script( 'milenia_form_styler', $js_file_form_styler, array( 'jquery' ), true);
			wp_enqueue_script( 'milenia_gallery-builder', $js_file, array( 'jquery', 'jquery-ui-sortable' ), true );

			wp_localize_script('milenia_gallery-builder', 'MileniaGalleryBuilderLocalizedData', array(
				'key' => esc_html__('Key', 'milenia-app-textdomain'),
				'value' => esc_html__('Value', 'milenia-app-textdomain')
			));
		}

		public function save_perm_metadata( $post_id, $post ) {

			if(function_exists('get_current_screen') && get_current_screen()->post_type != $this->post_type) return;

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

			if (!current_user_can('edit_post', $post_id)) return;

			if(!isset($_POST['milenia_gallery_builder'])) return;

			update_post_meta($post_id, 'milenia_gallery_builder', $_POST['milenia_gallery_builder']);

		}

		public function get_page_settings($post_id) {
			$milenia_gallery_builder = $this->get_theme_gallery_builder($post_id);

			$data = array();
			$data['milenia_gallery_builder'] = $milenia_gallery_builder;
			return $data;
		}

		public function view($pagepath, $data = array()) {
			@extract($data);
			return include $pagepath;
		}

		public static function get_post_builder($id) {
			$galleryString = get_post_meta( $id, 'milenia_gallery_builder', 1);
			return array_filter(explode(',', $galleryString));
		}

		function generate_inserted_media_to_slider() {

			if (function_exists('check_ajax_referer')) {
				check_ajax_referer($this->action_gallery_builder, 'milenia_gallery_builder_nonce');
			}

			if (is_admin()):

				$type = esc_attr($_POST['type']);


				ob_start();

				if ($type == 'image'):

					$itemsIDs = esc_attr($_POST['itemsIDs']);
					$items = explode(',', $itemsIDs);

					if (is_array($items)): ?>

						<?php foreach ($items as $id => $attach_id) : $lastid = $this->get_last_id(); ?>

							<li class='img-item add_animation'>

								<input type='hidden' name='milenia_gallery_builder[sliders][slides][<?php echo esc_attr($lastid) ?>][attach_id]' value='<?php echo esc_attr($attach_id) ?>'>
								<input type='hidden' name='milenia_gallery_builder[sliders][slides][<?php echo esc_attr($lastid) ?>][slide_type]' value='image'>

								<div class='img-preview'>
									<img alt='' src='<?php echo self::get_image(wp_get_attachment_url($attach_id), '155*105', true, true, true); ?>'>
									<div class='hover-container'>
										<div class="extra-content">
											<div class="inner-extra">
												<div class='remove-item'></div>
												<div class='drag-item'></div>

												<?php if(count($this->args['item_fields'])) : ?>
				                                    <div class='edit-item'></div>
				                                <?php endif; ?>
											</div>
										</div>
									</div>
								</div>

								<div class="popup-modal">
									<div class="popup-modal-outer">
										<div class="popup-modal-inner">
											<div class="popup-modal-inner-content">
												<header class="popup-modal-title">
						                            <h2><?php esc_html_e('Image Settings', 'milenia-app-textdomain'); ?></h2>
						                            <button type="button" class="popup-modal-close"></button>
						                        </header>

						                        <?php if(count($this->args['item_fields'])) : ?>
													<div class="popup-modal-content">
														<?php foreach($this->args['item_fields'] as $field) : ?>
															<?php
																if(!isset($field['name']) || !isset($field['type'])) continue;
																$milenia_item_field_data = array(
																	'field' => $field,
																	'slide' => $slide,
																	'slide_id' => $key
																);
															?>
						                                    <div class="popup-modal-content-col">
						                                        <?php if(isset($field['title'])) : ?>
						                                            <h4 class="popup-modal-content-col-title"><?php echo esc_html($field['title']); ?></h4>
						                                        <?php endif; ?>

																<?php $this->view($this->path(sprintf('views/gallery-builder-fields/item-fields/milenia-gallery-builder-field-%s.php', $field['type'])), $milenia_item_field_data); ?>

																<?php if(isset($field['description']) && !empty($field['description'])) : ?>
						                                        	<div class="popup-modal-content-col-desc"><?php echo esc_html($field['description']); ?></div>
																<?php endif; ?>
						                                    </div>
						                                <?php endforeach; ?>
													</div>
						                        <?php endif; ?>
											</div>
										</div>
									</div>
				                </div><!--/ .popup-modal-->

							</li><!--/ .img-item-->

						<?php endforeach; ?>

					<?php endif; ?>

				<?php elseif ($type == 'video'): $lastid = $this->get_last_id(); ?>

					<li class='img-item add_animation'>

						<input type='hidden' name='milenia_gallery_builder[sliders][slides][<?php echo esc_attr($lastid) ?>][src]' value=''>
						<input type='hidden' name='milenia_gallery_builder[sliders][slides][<?php echo esc_attr($lastid) ?>][slide_type]' value='video'>

						<div class='img-preview'>
							<img alt='' src='<?php echo esc_url($this->assetUrl('assets/images/video_item.png')); ?>'>
							<div class='hover-container'>
								<div class="extra-content">
									<div class="inner-extra">
										<div class='remove-item'></div>
										<div class='drag-item'></div>
										<div class='edit-item'></div>
									</div>
								</div>
							</div>
						</div>

						<div class='popup-modal'>

							<div class="modal-inner-content">

								<div class="modal-title">
									<h2><?php esc_html_e('Video Settings', 'milenia-app-textdomain'); ?></h2>
									<button class='popup_modal_close'></button>
								</div>

								<div class="meta-section">

									<div class="meta-container">
										<h4><?php esc_html_e('Video URL (YouTube, Vimeo or mp4)', 'milenia-app-textdomain'); ?></h4>
										<div class="meta-control">
											<input class="joker-text-option" type="text" value="" name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($lastid) ?>][src]">
										</div>
										<div class="meta-desc">
											<?php echo wp_kses(__('Examples: </br>
												Youtube - https://www.youtube.com/watch?v=RIQqVqQs9Xs </br>
												Vimeo - https://vimeo.com/7449107 </br>
												Defines the HTML5 Video Source file. Can be defined videomp4.', 'milenia-app-textdomain'), array('br' => array())) ?>
										</div>
									</div>

									<div class="meta-container">
										<h4><?php esc_html_e('Title', 'milenia-app-textdomain'); ?></h4>
										<div class="meta-control">
											<input class="joker-text-option" type="text" value="" name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($lastid) ?>][title][value]">
										</div>
										<div class="meta-desc">
										</div>
									</div>

									<div class="meta-container">
										<h4><?php esc_html_e('Description', 'milenia-app-textdomain'); ?></h4>
										<div class="meta-control">
											<textarea class="joker-text-option" name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($lastid) ?>][caption][value]" id="" cols="30" rows="10"></textarea>
										</div>
										<div class="meta-desc">
										</div>
									</div>

									<div class="meta-container">
										<h4><?php esc_html_e('Image Size ( setting for masonry )', 'milenia-app-textdomain'); ?></h4>
										<div class="meta-control">
											<select name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($lastid) ?>][image_size]">
												<option value="extra-small"><?php esc_html_e('Extra Small', 'milenia-app-textdomain'); ?></option>
												<option value="small"><?php esc_html_e('Small', 'milenia-app-textdomain'); ?></option>
												<option value="medium"><?php esc_html_e('Medium', 'milenia-app-textdomain'); ?></option>
												<option value="large"><?php esc_html_e('Large', 'milenia-app-textdomain'); ?></option>
												<option value="extra-large"><?php esc_html_e('Extra Large', 'milenia-app-textdomain'); ?></option>
											</select>
										</div>
										<div class="meta-desc"></div>
									</div>

								</div>

								<div class="meta-section">

									<div class="meta-container">
										<h4><?php esc_html_e('Cover image for Video', 'milenia-app-textdomain'); ?></h4>
										<div class="meta-control">
											<input type="hidden" name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($lastid) ?>][attach_id]" value="" class="select_img_attachid">
											<div class="select_img_preview"></div>
											<input type="button" class="button button-secondary button-large select_attach_id_from_media_library" value="<?php esc_attr_e('Select Image', 'milenia-app-textdomain') ?>">
										</div>
										<div class="meta-desc">
											<p><?php esc_html_e('Please select image to display as a featured one', 'milenia-app-textdomain') ?></p>
										</div>
									</div>

									<div class="meta-container">
										<h4><?php esc_html_e('Video aspect ration', 'milenia-app-textdomain'); ?></h4>
										<div class="meta-control">
											<select name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($lastid) ?>][el_aspect]">
												<option value="16:9">16:9</option>
												<option value="4:3">4:3</option>
											</select>
										</div>
										<div class="meta-desc">
											<p><?php esc_html_e('Select video aspect ratio.', 'milenia-app-textdomain') ?></p>
										</div>
									</div>

								</div>

							</div><!--/ .modal-inner-content-->

						</div><!--/ .popup-modal-->

						<div class="popup-modal-overlay"></div>

					</li><!--/ .img-item-->

				<?php endif; ?>

				<?php echo ob_get_clean(); ?>

			<?php endif;

			wp_die();
		}

		function get_option($name, $default_value = "") {

			$returnedValue = get_option("milenia_builder_" . $name, $default_value);

			if (gettype($returnedValue) == "string") {
				return stripslashes($returnedValue);
			} else {
				return $returnedValue;
			}
		}

		function get_last_id() {

			$lastid = $this->get_option("last_slide_id");

			if ($lastid < 3) {
				$lastid = 2;
			}
			$lastid ++;

			$this->update_option("last_slide_id", $lastid);

			return $lastid;
		}

		function update_option($name, $option_value) {
			if (update_option("milenia_builder_" . $name, $option_value)) {
				return true;
			}
		}

		public static function get_image($img_src, $dimensions, $crop = true, $single = true, $upscale = false) {
			if (empty($dimensions)) return $img_src;

			$sizes = explode('*', $dimensions);
			$img_src = aq_resize($img_src, $sizes[0], $sizes[1], $crop, $single, $upscale);

			if (!$img_src) { return ''; }

			return $img_src;
		}

		public static function which_video_service($video_url, $args) {
			$videos = array();
			$videoIdRegex = null;

			if (strpos($video_url, 'youtube.com/watch') !== false || strpos($video_url, 'youtu.be/') !== false) {
				preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $video_url, $matches);
				$video_id = $matches[0];

				if (!empty($video_id)) {
					$videos['ytid'] = trim($video_id);
					$videos['videoattributes'] = "version=3&amp;enablejsapi=1&amp;html5=1&amp;volume=100&amp;hd=1&amp;wmode=opaque&showinfo=0&ref=0;";
					$videos['aspectratio'] = $args['aspectratio'];
				}
			} elseif (strpos($video_url, 'vimeo.com') !== false) {

				if (strpos($video_url, 'player.vimeo.com') !== false) {
					$videoIdRegex = '/player.vimeo.com\/video\/([0-9]+)\??/i';
				} else { $videoIdRegex = '/vimeo.com\/([0-9]+)\??/i'; }

				if ($videoIdRegex !== null) {
					if (preg_match($videoIdRegex, $video_url, $results)) {
						$video_id = $results[1];
					}
					if (!empty($video_id)) {
						$videos['vimeoid'] = trim($video_id);
						$videos['videoattributes'] = "title=0&byline=0&portrait=0&api=1";
						$videos['aspectratio'] = $args['aspectratio'];
					}
				}

			} else {
				if (preg_match("/\.mp4$/", $video_url)) {
					$videos['videomp4'] = trim($video_url);
				} else if (preg_match("/\.ogv$/", $video_url)) {
					$videos['videoogv'] = trim($video_url);
				} else if (preg_match("/\.webm$/", $video_url)) {
					$videos['videowebm'] = trim($video_url);
				}
			}

			return self::create_video_data_string($videos);
		}

		public static function create_video_data_string($data = array()) {
			$data_string = "";

			if (empty($data)) return "";

			foreach ($data as $key => $value) {
				if (empty($value)) continue;
				if (is_array($value)) $value = implode(", ", $value);
				$data_string .= "data-{$key}='{$value}' \n";
			}
			return $data_string;
		}

		function parseVideos($videoString = null) {
			$videos = array();
			if (!empty($videoString)) {

				$videoString = stripslashes(trim($videoString));
				$videoString = explode("\n", $videoString);
				$videoString = array_filter($videoString, 'trim');

				foreach ($videoString as $video) {
					if (strpos($video, 'iframe') !== FALSE) {
						$anchorRegex = '/src="(.*)?"/isU';
						$results = array();
						if (preg_match($anchorRegex, $video, $results)) {
							$link = trim($results[1]);
						}
					} else {
						$link = $video;
					}
					if (!empty($link)) {
						$video_id = NULL;
						$videoIdRegex = NULL;
						$results = array();
						if (strpos($link, 'youtu') !== FALSE) {
							if(strpos($link, 'youtube.com/watch') !== FALSE){
								$videoIdRegex = '/http:\/\/(?:www\.)?youtube.*watch\?v=([a-zA-Z0-9\-_]+)/';
							} else if (strpos($link, 'youtube.com') !== FALSE) {
								$videoIdRegex = '/youtube.com\/(?:embed|v){1}\/([a-zA-Z0-9_]+)\??/i';
							} else if (strpos($link, 'youtu.be') !== FALSE) {
								$videoIdRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';
							}
							if ($videoIdRegex !== NULL) {
								if (preg_match($videoIdRegex, $link, $results)) {
									$video_str = 'http://www.youtube.com/v/%s?fs=1&amp;autoplay=1';
									$thumbnail_str = 'http://img.youtube.com/vi/%s/2.jpg';
									$fullsize_str = 'http://img.youtube.com/vi/%s/0.jpg';
									$video_id = $results[1];
								}
							}
						} else if (strpos($video, 'vimeo') !== FALSE) {
							if (strpos($video, 'player.vimeo.com') !== FALSE) {
								$videoIdRegex = '/player.vimeo.com\/video\/([0-9]+)\??/i';
							} else {
								$videoIdRegex = '/vimeo.com\/([0-9]+)\??/i';
							}
							if ($videoIdRegex !== NULL) {
								if (preg_match($videoIdRegex, $link, $results)) {
									$video_id = $results[1];
									try {
										$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$video_id.php"));
										if (!empty($hash) && is_array($hash)) {
											$video_str = 'http://vimeo.com/moogaloop.swf?clip_id=%s';
											$thumbnail_str = $hash[0]['thumbnail_small'];
											$fullsize_str = $hash[0]['thumbnail_large'];
										} else {
											unset($video_id);
										}
									} catch (Exception $e) {
										unset($video_id);
									}
								}
							}
						}
						if (!empty($video_id)) {
							$videos[] = array(
								'url' => sprintf($video_str, $video_id),
								'thumbnail' => sprintf($thumbnail_str, $video_id),
								'fullsize' => sprintf($fullsize_str, $video_id)
							);
						}
					}
				}
			}
			return $videos;
		}

	}

}
