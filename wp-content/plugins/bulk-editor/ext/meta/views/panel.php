<?php
if (!defined('ABSPATH'))
    wp_die('No direct access allowed');

global $WPBE;
?>

<h4 class="wpbe-documentation"><a href="https://bulk-editor.pro/document/wordpress-posts-meta-fields/" target="_blank" class="button button-primary"><span class="icon-book"></span></a>&nbsp;<?php esc_html_e('Meta Fields', 'bulk-editor') ?></h4>


<?php if ($WPBE->show_notes) : ?>
    <div class="notice notice-warning">
        <p class="wpbe_set_attention"><?php esc_html_e('In FREE version of the plugin it is possible manipulate with 2 meta fields.', 'bulk-editor') ?></p>
    </div>
<?php endif; ?>

<div class="col-lg-6">
    <h5><?php esc_html_e('Add Custom key by hands', 'bulk-editor') ?>:</h5>
    <input type="text" value="" class="wpbe_meta_key_input" />&nbsp;
    <a href="#" id="wpbe_meta_add_new_btn" class="button button-primary button-large"><?php esc_html_e('Add', 'bulk-editor') ?></a> 

</div>

<div class="col-lg-6">
    <h5><?php esc_html_e('Get meta keys from any post by its ID', 'bulk-editor') ?>:</h5>
    <input type="number" min="1" class="wpbe_meta_keys_get_input" value="" placeholder="<?php esc_html_e('enter post ID', 'bulk-editor') ?>" />&nbsp;
    <a href="#" id="wpbe_meta_get_btn" class="button button-primary button-large"><?php esc_html_e('Get', 'bulk-editor') ?></a>

</div>
<div class="col-lg-12">
    <br>

    <input type="text" value="" class="wpbe-full-width" placeholder="<?php esc_html_e('Meta quick search ...', 'bulk-editor') ?>" id="wpbe_meta_finder" /><br />

</div>
<div class="clear"></div>



<form id="metaform" method="post" action="">
    <input type="hidden" name="wpbe_meta_fields[]" value="" />
	<input type="hidden"  id="wpbe_meta_nonce" value="<?php echo  wp_create_nonce( 'wpbe_meta_nonce' ); ?>"  />  
    <ul class="wpbe_fields" id="wpbe_meta_list">

        <?php
        if (!empty($metas)) {
            foreach ($metas as $m) {
                wpbe_meta_print_li($m);
            }
        }
        ?>

    </ul>


    <br />

    <input type="submit" class="button button-primary button-primary" value="<?php esc_html_e('Save meta fields', 'bulk-editor') ?>" />

</form>

<div style="display: none;" id="wpbe_meta_li_tpl">
    <?php
    wpbe_meta_print_li(array(
        'meta_key' => '__META_KEY__',
        'title' => '__TITLE__',
        'edit_view' => '',
        'type' => ''
    ));
    ?>
</div>

<?php

function wpbe_meta_print_li($m) {
    ?>
    <li class="wpbe_options_li">
        <a href="#" class="help_tip wpbe_drag_and_drope" title="<?php esc_html_e('drag and drop', 'bulk-editor') ?>"><img src="<?php echo WPBE_ASSETS_LINK ?>images/move.png" alt="<?php esc_html_e('move', 'bulk-editor') ?>" /></a>

        <div class="col-lg-4">
            <input type="text" name="wpbe_meta_fields[<?php echo $m['meta_key'] ?>][meta_key]" value="<?php echo $m['meta_key'] ?>" readonly="" class="wpbe_column_li_option wpbe_column_li_option1" />&nbsp;

        </div>
        <div class="col-lg-4">
            <input type="text" name="wpbe_meta_fields[<?php echo $m['meta_key'] ?>][title]" placeholder="<?php esc_html_e('enter title', 'bulk-editor') ?>" value="<?php echo $m['title'] ?>" class="wpbe_column_li_option wpbe_column_li_option2" />&nbsp;

        </div>
        <div class="col-lg-2">
            <div class="select-wrap">
                <select name="wpbe_meta_fields[<?php echo $m['meta_key'] ?>][edit_view]" class="wpbe_meta_view_selector">
                    <option <?php selected($m['edit_view'], 'textinput') ?> value="textinput"><?php esc_html_e('textinput', 'bulk-editor') ?></option>
                    <option <?php selected($m['edit_view'], 'popupeditor') ?> value="popupeditor"><?php esc_html_e('textarea', 'bulk-editor') ?></option>
                    <option <?php selected($m['edit_view'], 'switcher') ?> value="switcher"><?php esc_html_e('checkbox', 'bulk-editor') ?></option>
                    <option <?php selected($m['edit_view'], 'calendar') ?> value="calendar"><?php esc_html_e('calendar', 'bulk-editor') ?></option>
                    <option <?php selected($m['edit_view'], 'meta_popup_editor') ?> value="meta_popup_editor"><?php esc_html_e('array', 'bulk-editor') ?></option>
                    <option <?php selected($m['edit_view'], 'gallery_popup_editor') ?> value="gallery_popup_editor"><?php esc_html_e('gallery', 'bulk-editor') ?></option>
                </select>
            </div>
        </div>
        <div class="col-lg-1">
            <div class="select-wrap" <?php if (in_array($m['edit_view'], array('popupeditor', 'switcher', 'meta_popup_editor', 'gallery_popup_editor', 'calendar'))): ?>style="display: none;"<?php endif; ?>>
                <select name="wpbe_meta_fields[<?php echo $m['meta_key'] ?>][type]" class="wpbe_meta_type_selector">
                    <option <?php selected($m['type'], 'string') ?> value="string"><?php esc_html_e('string', 'bulk-editor') ?></option>
                    <option <?php selected($m['type'], 'number') ?> value="number"><?php esc_html_e('number', 'bulk-editor') ?></option>
                </select>
            </div>
        </div>
        <div class="col-lg-1">
            &nbsp;<a href="#" class="button button-primary wpbe_meta_delete" title="<?php esc_html_e('delete', 'bulk-editor') ?>"></a>
        </div>

        <div class="clear"></div>

    </li>
    <?php
}
