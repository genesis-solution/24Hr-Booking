<?php
if (!defined('ABSPATH'))
    wp_die('No direct access allowed');

global $WPBE;
?>

<!------------------ filter sets profiles popup --------------------------->
<div id="wpbe_fprofile_popup" style="display: none;">
    <div class="wpbe-modal wpbe-modal2 wpbe-style">
        <div class="wpbe-modal-inner">
            <div class="wpbe-modal-inner-header">
                <h3 class="wpbe-modal-title"><?php esc_html_e('Filters profiles', 'bulk-editor') ?></h3>
                <a href="javascript:void(0)" class="wpbe-modal-close wpbe-modal-close-fprofile"></a>
            </div>
            <div class="wpbe-modal-inner-content">

                <div class="wpbe-form-element-container">
                    <div class="wpbe-name-description">
                        <strong><?php esc_html_e('Profiles', 'bulk-editor') ?></strong>
                        <span><?php esc_html_e('Here you can load previously saved filters profile. After pressing on the load button, Posts Editor data reloading will start immediately!', 'bulk-editor') ?></span>

                        <ul id="wpbe_loaded_fprofile_data_info"></ul>

                    </div>
                    <div class="wpbe-form-element">

                        <select id="wpbe_load_fprofile">
                            <option value="0"><?php esc_html_e('Select filter profile to load', 'bulk-editor') ?></option>
                            <?php if (!empty($fprofiles)): ?>
                                <?php foreach ($fprofiles as $pkey => $pvalue) : ?>
                                    <option value="<?php echo esc_html($pkey) ?>"><?php echo esc_html($pvalue['title']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>


                        <div style="display: none;" id="wpbe_load_fprofile_actions">
                            <a href="javascript:void(0)" class="button button-primary button" id="wpbe_load_fprofile_btn"><?php esc_html_e('load', 'bulk-editor') ?></a>&nbsp;
                            <a href="#" class="button button-primary button wpbe_delete_fprofile"><?php esc_html_e('remove', 'bulk-editor') ?></a>
                        </div>

                    </div>
                </div>



                <div class="wpbe-form-element-container wpbe-new-fprofile-inputs">
                    <div class="wpbe-name-description">
                        <strong><?php esc_html_e('New Filter Profile', 'bulk-editor') ?></strong>
                        <span><?php esc_html_e('Here you can type any title and save current filters set. Type here any title and then press Save button OR press Enter button on your keyboard!', 'bulk-editor') ?></span>
                    </div>
                    <div class="wpbe-form-element">
                        <div class="posts_search_container">
                            <input type="text" value="" id="wpbe_new_fprofile" />
                        </div>
                    </div>
                </div>


                <div class="wpbe-form-element-container wpbe-new-fprofile-attention">

                    <div class="notice notice-info">
                        <p>
                            <?php esc_html_e('You can save filter profile only when you applying filters to the posts.', 'bulk-editor') ?>    
                        </p>
                    </div>

                </div>

            </div>
            <div class="wpbe-modal-inner-footer">
                <a href="javascript:void(0)" class="button button-primary button-large button-large-1"  id="wpbe_new_fprofile_btn"><?php esc_html_e('Save', 'bulk-editor') ?></a>
                <a href="javascript:void(0)" class="wpbe-modal-close-fprofile button button-primary button-large button-large-2"><?php esc_html_e('Close', 'bulk-editor') ?></a>
            </div>
        </div>
    </div>

    <div class="wpbe-modal-backdrop"></div>

</div>
