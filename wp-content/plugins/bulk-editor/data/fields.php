<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function wpbe_get_fields() {
    static $users = array();

    if ($users === array()) {
        $users = WPBE_HELPER::get_users();
    }

    //***

    $post_mime_types = [];
    foreach (get_allowed_mime_types() as $value) {
        $post_mime_types[$value] = $value;
    }

    global $WPBE;

    //***

    return apply_filters('wpbe_extend_fields', array(
        '__checker' => array(
            'show' => 1, //this is special checkbox only for functionality
            'title' => WPBE_HELPER::draw_checkbox(array('class' => 'all_posts_checker')),
            'desc' => esc_html__('Checkboxes for the posts selection. Use SHIFT button on your keyboard to select multiple rows.', 'bulk-editor'),
            'field_type' => 'none',
            'type' => 'number',
            'editable' => FALSE,
            'edit_view' => 'checkbox',
            'order' => FALSE,
            'move' => FALSE,
            'direct' => TRUE,
            'site_editor_visibility' => 1
        ),
        'ID' => array(
            'show' => 1, //1 - enabled here by default
            'title' => 'ID',
            'field_type' => 'field',
            'type' => 'number',
            'editable' => FALSE,
            'edit_view' => 'textinput',
            'order' => TRUE,
            'move' => FALSE,
            'direct' => TRUE,
            'site_editor_visibility' => 1
        ),
        '_thumbnail_id' => array(
            'show' => 1, //by default
            'title' => esc_html__('Thumbnail', 'bulk-editor'),
            'field_type' => 'meta',
            'type' => 'number',
            'editable' => true,
            'edit_view' => 'thumbnail',
            'order' => FALSE,
            'direct' => TRUE,
            'site_editor_visibility' => 1,
            'prohibit_post_types' => array('attachment'),
            'css_classes' => '',
        ),
        'post_title' => array(
            'show' => 1,
            'title' => esc_html__('Title', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'textinput',
            'order' => TRUE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_content' => array(
            'show' => 1,
            'title' => esc_html__('Content', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'popupeditor',
            'order' => FALSE,
            'direct' => TRUE,
            'site_editor_visibility' => 1
        ),
        'post_excerpt' => array(
            'show' => 1,
            'title' => esc_html__('Excerpt', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'popupeditor',
            'order' => FALSE,
            'direct' => !$WPBE->show_notes,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_name' => array(
            'show' => 0,
            'title' => esc_html__('Slug', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'textinput',
            'sanitize' => 'urldecode',
            'order' => FALSE,
            'direct' => !$WPBE->show_notes,
            //'prohibit_post_types' => array(),
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_status' => array(
            'show' => 1,
            'title' => esc_html__('Status', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'select',
            'select_options' => WPBE_HELPER::get_post_statuses(),
            'order' => FALSE,
            'direct' => TRUE,
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1
        ),
        'comment_status' => array(
            'show' => 0,
            'title' => esc_html__('Comment status', 'bulk-editor'),
            'desc' => esc_html__('Can users comment post or not.', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'switcher',
            'select_options' => array(
                'open' => esc_html__('Open', 'bulk-editor'), //true
                'closed' => esc_html__('Closed', 'bulk-editor'), //false
            ),
            'order' => FALSE,
            'direct' => !$WPBE->show_notes,
            //'allow_post_types' => array(),
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1,
            'css_classes' => '',
        ),
        'ping_status' => array(
            'show' => 0,
            'title' => esc_html__('Ping status', 'bulk-editor'),
            'desc' => esc_html__('A ping is a [this site has new content] notification that invites search engine bots to visit your blog.', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'switcher',
            'select_options' => array(
                'open' => esc_html__('Open', 'bulk-editor'), //true
                'closed' => esc_html__('Closed', 'bulk-editor'), //false
            ),
            'order' => FALSE,
            'direct' => !$WPBE->show_notes,
            //'allow_post_types' => array(),
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1,
            'css_classes' => '',
        ),
        'to_ping' => array(
            'show' => 0,
            'title' => esc_html__('Send trackbacks to', 'bulk-editor'),
            'desc' => esc_html__('Separate multiple URLs with spaces. Ping status should be open! Remove 2 links separated by space, save. After opening you will not see links as they had been pinged! No sense for roll back, its sending is instant. Trackbacks are a way to notify legacy blog systems that you’ve linked to them. If you link other WordPress sites, they’ll be notified automatically using pingbacks, no other action necessary. Do not use these fields for something else. They are parsed many times in core code (69 matches for to_ping); their format is fixed.', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'popupeditor',
            'order' => FALSE,
            'direct' => !$WPBE->show_notes,
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1
        ),
        /*
          'pinged' => array(
          'show' => 0,
          'title' => esc_html__('Trackbacks sent', 'bulk-editor'),
          'desc' => esc_html__('Where to its pigned already', 'bulk-editor'),
          'field_type' => 'field',
          'type' => 'string',
          'editable' => TRUE,
          'edit_view' => 'popupeditor',
          'order' => FALSE,
          'direct' => TRUE,
          'prohibit_post_types' => array('attachment'),
          'site_editor_visibility' => 1
          ),
         */
        'post_password' => array(
            'show' => 0,
            'title' => esc_html__('Post password', 'bulk-editor'),
            'desc' => esc_html__('Password for private posts', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'textinput',
            'order' => TRUE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1
        ),
        'menu_order' => array(
            'show' => 0,
            'title' => esc_html__('Menu order', 'bulk-editor'),
            'desc' => esc_html__('Custom ordering position.', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'number',
            'sanitize' => 'intval',
            'editable' => TRUE,
            'edit_view' => 'textinput',
            'order' => TRUE,
            'direct' => TRUE,
            'site_editor_visibility' => 1
        ),
        'post_author' => array(
            'show' => 1,
            'title' => esc_html__('Author', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'select',
            'select_options' => $users,
            'order' => FALSE,
            'direct' => !$WPBE->show_notes,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_date' => array(
            'show' => 1,
            'title' => esc_html__('Date Published', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'timestamp', //timestamp, unix
            'set_day_end' => FALSE, //false: 00:00:00, true: 23:59:59
            'editable' => TRUE,
            'edit_view' => 'calendar',
            'order' => TRUE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_date_gmt' => array(
            'show' => 0,
            'title' => esc_html__('Date Published GMT', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'timestamp', //timestamp, unix
            'set_day_end' => FALSE, //false: 00:00:00, true: 23:59:59
            'editable' => TRUE,
            'edit_view' => 'calendar',
            'order' => TRUE,
            'direct' => !$WPBE->show_notes,
            //'prohibit_post_types' => array(),
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_modified' => array(
            'show' => 1,
            'title' => esc_html__('Date modified', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'timestamp', //timestamp, unix
            'set_day_end' => FALSE, //false: 00:00:00, true: 23:59:59
            'editable' => TRUE,
            'edit_view' => 'calendar',
            'order' => TRUE,
            'direct' => !$WPBE->show_notes,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_modified_gmt' => array(
            'show' => 0,
            'title' => esc_html__('Date modified GMT', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'timestamp', //timestamp, unix
            'set_day_end' => FALSE, //false: 00:00:00, true: 23:59:59
            'editable' => TRUE,
            'edit_view' => 'calendar',
            'order' => TRUE,
            'direct' => TRUE,
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'post_parent' => array(
            'show' => 0,
            'title' => esc_html__('Post parent', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'number',
            'editable' => TRUE,
            'edit_view' => 'textinput',
            'sanitize' => 'intval',
            'order' => FALSE,
            'direct' => !$WPBE->show_notes,
            //'allow_post_types' => array(),
            //'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1
        ),
        'post_type' => array(
            'show' => 1,
            'title' => esc_html__('Post type', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'select',
            'select_options' => WPBE_HELPER::filter_post_types(),
            'order' => FALSE,
            'direct' => !$WPBE->show_notes,
            //'prohibit_post_types' => array(),
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1
        ),
        'post_mime_type' => array(
            'disabled' => TRUE,
            'show' => 0,
            'title' => esc_html__('Post mime type', 'bulk-editor'),
            'desc' => esc_html__('Not in bulk edit as there is no built-uploader here, so no sense change mime type. Filtering only! The function is designed specifically for attached records.', 'bulk-editor'),
            'field_type' => 'field',
            'type' => 'string',
            'editable' => TRUE,
            'edit_view' => 'select',
            'select_options' => $post_mime_types,
            'order' => FALSE,
            'direct' => TRUE,
            'allow_post_types' => array('attachment'),
            'css_classes' => 'not-for-variations',
            'site_editor_visibility' => 1
        ),
        'sticky_posts' => array(
            'show' => 0,
            'title' => esc_html__('Sticky', 'bulk-editor'),
            'desc' => esc_html__('Stick this post to the front page', 'bulk-editor'),
            'field_type' => 'meta',
            'type' => 'intval',
            'editable' => TRUE,
            'edit_view' => 'switcher',
            'select_options' => array(
                1 => esc_html__('Yes', 'bulk-editor'), //true
                0 => esc_html__('No', 'bulk-editor'), //false
            ),
            'order' => FALSE,
            'direct' => TRUE,
            'allow_post_types' => array('post'),
            'prohibit_post_types' => array('attachment'),
            'site_editor_visibility' => 1,
            'css_classes' => '',
        ),
    ));
}
