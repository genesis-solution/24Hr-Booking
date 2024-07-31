<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function wpbe_get_total_settings($data) {
    return array(
        'per_page' => array(
            'title' => esc_html__('Default posts count per page', 'bulk-editor'),
            'desc' => esc_html__('How many rows shows per page in tab Posts Editor. Max possible value is 100!', 'bulk-editor'),
            'value' => '',
            'type' => 'number'
        ),
        'default_sort_by' => array(
            'title' => esc_html__('Default sort by', 'bulk-editor'),
            'desc' => esc_html__('Select column by which posts sorting is going after plugin page loaded', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => $data['default_sort_by']
        ),
        'default_sort' => array(
            'title' => esc_html__('Default sort', 'bulk-editor'),
            'desc' => esc_html__('Select sort direction for Default sort', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => array(
                'desc' => array('title' => 'DESC'),
                'asc' => array('title' => 'ASC')
            )
        ),
        'show_admin_bar_menu_btn' => array(
            'title' => esc_html__('Show button in admin bar', 'bulk-editor'),
            'desc' => esc_html__('Show Bulk Editor button in admin bar for quick access to the Posts Editor', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => array(
                1 => array('title' => esc_html__('Yes', 'bulk-editor')),
                0 => array('title' => esc_html__('No', 'bulk-editor')),
            )
        ),
        'show_thumbnail_preview' => array(
            'title' => esc_html__('Show thumbnail preview', 'bulk-editor'),
            'desc' => esc_html__('Show bigger thumbnail preview on mouse over', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => array(
                1 => array('title' => esc_html__('Yes', 'bulk-editor')),
                0 => array('title' => esc_html__('No', 'bulk-editor')),
            )
        ),
        'load_switchers' => array(
            'title' => esc_html__('Load beauty switchers', 'bulk-editor'),
            'desc' => esc_html__('Load beauty switchers instead of checkboxes in the Posts Editor.', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => array(
                1 => array('title' => esc_html__('Yes', 'bulk-editor')),
                0 => array('title' => esc_html__('No', 'bulk-editor')),
            )
        ),
        'use_wp_editor' => array(
            'title' => esc_html__('Use gutenberg', 'bulk-editor'),
            'desc' => esc_html__('Allows to edit content with the gutenberg editor if its enabled for the selected post type', 'bulk-editor'),
            'value' => '',
            'type' => 'select',
            'select_options' => array(
                1 => array('title' => esc_html__('Yes', 'bulk-editor')),
                0 => array('title' => esc_html__('No', 'bulk-editor')),
            )
        ),
        'quick_search_fieds' => array(
            'title' => __('Add fields to the quick search', 'bulk-editor'),
            'desc' => __('Adds more fields to quick search fields drop-down on the tools panel. Works only for text fields. Syntax: post_name:Post slug,post_content:Content', 'bulk-editor'),
            'value' => '',
            'type' => 'text'
        ),
        'override_switcher_fieds' => array(
            'title' => esc_html__('Override meta checkbox', 'bulk-editor'),
            'desc' => esc_html__('By default, the checkbox works with 1 and 0. If you need to redefine these values for example to "yes" or "true". Syntax: meta_key1:yes,meta_key2:true', 'bulk-editor'),
            'value' => '',
            'type' => 'text'
        ),
    );
}
