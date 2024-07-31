<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia-app-textdomain') );
}

$milenia_item_fields = isset($item_fields) ? $item_fields : array();
?>

<?php if (is_array($slides) && !empty($slides)) : ?>

    <?php foreach ($slides as $key => $slide) : ?>

        <?php if ($slide['slide_type'] == 'image'): ?>

            <li class='img-item'>

                <input type="hidden" name='milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][attach_id]' value='<?php echo esc_attr($slide['attach_id']) ?>'>
                <input type="hidden" name='milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][slide_type]' value='image'>
				<input type="hidden" name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][milenia-item-likes-count]" value="<?php echo esc_attr(isset($slide['milenia-item-likes-count']) ? $slide['milenia-item-likes-count'] : 0); ?>">

                <div class='img-preview'>
                    <?php if ($slide['attach_id'] > 0) : ?>
                        <img src='<?php echo self::get_image(wp_get_attachment_url( $slide['attach_id'] ), '155*105', true, true, false); ?> ' alt='' />
                    <?php endif; ?>

                    <div class='hover-container'>
                        <div class="extra-content">
                            <div class="inner-extra">
                                <div class='remove-item'></div>
                                <div class='drag-item'></div>

                                <?php if(count($milenia_item_fields)) : ?>
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

		                        <?php if(count($milenia_item_fields)) : ?>
									<div class="popup-modal-content">
										<?php foreach($milenia_item_fields as $field) : ?>
											<?php
												if(!isset($field['name']) || !isset($field['type'])) continue;
												$milenia_item_field_data = array(
													'field' => $field,
													'slide' => $slide,
													'slide_id' => $key
												);
											?>
		                                    <div class="popup-modal-content-col <?php if(isset($field['full-width-column']) && boolval($field['full-width-column'])) echo 'popup-modal-content-col-full-width'; ?>">
		                                        <?php if(isset($field['title'])) : ?>
		                                            <label class="popup-modal-content-col-title"><?php echo esc_html($field['title']); ?></label>
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

        <?php elseif ($slide['slide_type'] == 'video'): ?>

            <?php
                $attach_id = isset($slide['attach_id']) ? esc_attr($slide['attach_id']) : '';
                $src = isset($slide['src']) ? esc_attr($slide['src']) : '';
                $title = isset($slide['title']['value']) ? esc_attr($slide['title']['value']) : '';
                $image_size = isset($slide['image_size']) ? esc_attr($slide['image_size']) : 'small';
                $caption = isset($slide['caption']['value']) ? esc_attr($slide['caption']['value']) : '';
                $el_aspect = isset($slide['el_aspect']) ? esc_attr($slide['el_aspect']) : '';
            ?>

            <li class='img-item'>

                <input type='hidden' name='milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][src]' value='<?php echo esc_attr($slide['src']) ?>'>
                <input type='hidden' name='milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][slide_type]' value='video'>

                <div class='img-preview'>
                    <img alt='' src='<?php echo $this->assetUrl('img/video_item.png'); ?>'>
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
                                    <input class="milenia-text-option" type="text" value="<?php echo $src; ?>" name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][src]">
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
                                    <input class="milenia-text-option" type="text" value="<?php echo esc_attr($title); ?>" name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][title][value]">
                                </div>
                                <div class="meta-desc">
                                </div>
                            </div><!--/ .meta-container-->

                            <div class="meta-container">
                                <h4><?php esc_html_e('Description', 'milenia-app-textdomain'); ?></h4>
                                <div class="meta-control">
                                    <textarea class="milenia-text-option" name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][caption][value]" id="" cols="30" rows="10"><?php echo $caption ?></textarea>
                                </div>
                                <div class="meta-desc"></div>
                            </div>

                            <div class="meta-container">
                                <h4><?php esc_html_e('Image Size ( setting for masonry )', 'milenia-app-textdomain'); ?></h4>
                                <div class="meta-control">
                                    <select name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][image_size]">
                                        <option <?php echo (isset($image_size) && $image_size == 'extra-small') ? 'selected="selected"' : '' ?> value="extra-small"><?php esc_html_e('Extra Small', 'milenia-app-textdomain'); ?></option>
                                        <option <?php echo (isset($image_size) && $image_size == 'small') ? 'selected="selected"' : '' ?> value="small"><?php esc_html_e('Small', 'milenia-app-textdomain'); ?></option>
                                        <option <?php echo (isset($image_size) && $image_size == 'medium') ? 'selected="selected"' : '' ?> value="medium"><?php esc_html_e('Medium', 'milenia-app-textdomain'); ?></option>
                                        <option <?php echo (isset($image_size) && $image_size == 'large') ? 'selected="selected"' : '' ?> value="large"><?php esc_html_e('Large', 'milenia-app-textdomain'); ?></option>
                                        <option <?php echo (isset($image_size) && $image_size == 'extra-large') ? 'selected="selected"' : '' ?> value="extra-large"><?php esc_html_e('Extra Large', 'milenia-app-textdomain'); ?></option>
                                    </select>
                                </div>
                                <div class="meta-desc"></div>
                            </div>

                        </div>

                        <div class="meta-section">

                            <div class="meta-container">
                                <h4><?php esc_html_e('Cover image for Video', 'milenia-app-textdomain'); ?></h4>
                                <div class="meta-control">
                                    <input type="hidden" name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][attach_id]" value="<?php echo $attach_id ?>" class="select_img_attachid">
                                    <div class="select_img_preview">
                                        <?php if ($attach_id > 0): ?>
                                            <img src="<?php echo self::get_image(wp_get_attachment_url($attach_id), '295*165', true, true, true); ?>" alt="" />
                                        <?php endif; ?>
                                    </div>
                                    <input type="button" class="button button-secondary button-large select_attach_id_from_media_library" value="<?php esc_attr_e('Select Image', 'milenia-app-textdomain') ?>">
                                </div>
                                <div class="meta-desc">
                                    <p><?php esc_html_e('Please select image to display as a featured one', 'milenia-app-textdomain') ?></p>
                                </div>
                            </div>

                            <div class="meta-container">
                                <h4><?php esc_html_e('Video aspect ration', 'milenia-app-textdomain'); ?></h4>
                                <div class="meta-control">
                                    <select name="milenia_gallery_builder[sliders][slides][<?php echo esc_attr($key) ?>][el_aspect]">
                                        <option <?php if ($el_aspect == '16:9'): ?>selected<?php endif; ?> value="16:9">16:9</option>
                                        <option <?php if ($el_aspect == '4:3'): ?>selected<?php endif; ?> value="4:3">4:3</option>
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

    <?php endforeach; ?>

<?php endif; ?>
