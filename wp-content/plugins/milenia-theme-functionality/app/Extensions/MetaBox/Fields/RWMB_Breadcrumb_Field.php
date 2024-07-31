<?php
/**
* The RWMB_Breadcrumb_Field class.
*
* @package WordPress
* @subpackage MileniaThemeFunctionality
* @since MileniaThemeFunctionality 1.0.0
*/

class RWMB_Breadcrumb_Field extends RWMB_Field
{
    /**
     * Returns html markup of the field.
     *
     * @param array $meta
     * @param array $field
     * @access public
     * @static
     * @return string
     */
    public static function html($meta, $field)
    {
        global $milenia_settings;
        global $post;

        $meta = array_merge(array(
            'page-title' => esc_html__('Page Title', 'milenia-app-textdomain'),
            'breadcrumb-path' => esc_html__('Home, Pages, Page Title', 'milenia-app-textdomain'),
            'breadcrumb-path-delimiter' => '/',
            'content-alignment' => 'text-center',
            'background-color' => '#f1f1f1',
            'title-color' => '#1c1c1c',
            'page-title-bottom-offset' => 20,
            'content-color' => '#858585',
            'links-color' => '#1c1c1c',
            'background-image' => 'none',
            'background-image-url' => 'none',
            'background-image-opacity' => 1,
            'padding-top' => 40,
            'padding-right' => 15,
            'padding-bottom' => 40,
            'padding-left' => 15
        ), is_array($meta) ? $meta : array());

        ob_start();
        require(MILENIA_FUNCTIONALITY_ROOT . 'extensions/BreadcrumbSectionField/breadcrumb_section/field_breadcrumb_section.tpl.php');
        ?>
            <div id="breadcrumb-root">
                <breadcrumb-section
                    field-name="<?php echo esc_attr($field['id']); ?>"
                    breadcrumb-path-state="<?php echo esc_attr(isset($meta['breadcrumb-path-state'])) ?>"
                    breadcrumb-path="<?php echo esc_attr($meta['breadcrumb-path']) ?>"
                    breadcrumb-path-delimiter="<?php echo esc_attr($meta['breadcrumb-path-delimiter']) ?>"
                    background-image="<?php echo esc_attr(isset($meta['background-image']) ? $meta['background-image'] : null); ?>"
                    background-image-url="<?php echo esc_attr(isset($meta['background-image']) ? wp_get_attachment_image_url($meta['background-image'], 'full') : null) ?>"
                    background-image-opacity="<?php echo esc_attr($meta['background-image-opacity']) ?>"
                    content-alignment="<?php echo esc_attr($meta['content-alignment']) ?>"
                    background-color="<?php echo esc_attr($meta['background-color']) ?>"
                    content-color="<?php echo esc_attr($meta['content-color']) ?>"
                    links-color="<?php echo esc_attr($meta['links-color']) ?>"
                    page-title-state="<?php echo esc_attr(isset($meta['page-title-state'])); ?>"
                    page-title="<?php echo esc_attr($meta['page-title']); ?>"
                    page-title-bottom-offset="<?php echo esc_attr($meta['page-title-bottom-offset']) ?>"
                    title-color="<?php echo esc_attr($meta['title-color']) ?>"
                    title-font="<?php echo esc_attr($milenia_settings['h1-font']['font-family']); ?>"
                    title-font-size="<?php echo esc_attr($milenia_settings['h1-font']['font-size']); ?>"
                    title-line-height="<?php echo esc_attr($milenia_settings['h1-font']['line-height']); ?>"
                    title-font-weight="<?php echo esc_attr($milenia_settings['h1-font']['font-weight']); ?>"
                    padding-top="<?php echo esc_attr($meta['padding-top']) ?>"
                    padding-right="<?php echo esc_attr($meta['padding-right']) ?>"
                    padding-bottom="<?php echo esc_attr($meta['padding-bottom']) ?>"
                    padding-left="<?php echo esc_attr($meta['padding-left']) ?>"
                    parallax="<?php echo esc_attr(isset($meta['parallax'])) ?>"

                    demo-title-text="<?php esc_attr_e('Interactive demo', 'milenia-app-textdomain'); ?>"
                    page-title-state-text="<?php esc_attr_e('Show page title', 'milenia-app-textdomain'); ?>"
                    page-title-text="<?php esc_attr_e('[Demo only] Page title', 'milenia-app-textdomain'); ?>"
                    page-title-bottom-offset-text="<?php esc_attr_e('Title\'s bottom offset (px)', 'milenia-app-textdomain'); ?>"
                    breadcrumb-path-state-text="<?php esc_attr_e('Show breadcrumbs', 'milenia-app-textdomain'); ?>"
                    breadcrumb-path-state-text="<?php esc_attr_e('Show breadcrumbs', 'milenia-app-textdomain'); ?>"
                    breadcrumb-path-text="<?php esc_attr_e('[Demo only] Page path (comma-separated)', 'milenia-app-textdomain'); ?>"
                    breadcrumb-path-delimiter-text="<?php esc_attr_e('Breadcrumbs path delimiter', 'milenia-app-textdomain'); ?>"
                    content-alignment-text="<?php esc_attr_e('Content alignment', 'milenia-app-textdomain'); ?>"
                    content-alignment-left-text="<?php esc_attr_e('Left', 'milenia-app-textdomain'); ?>"
                    content-alignment-center-text="<?php esc_attr_e('Center', 'milenia-app-textdomain'); ?>"
                    content-alignment-right-text="<?php esc_attr_e('Right', 'milenia-app-textdomain'); ?>"
                    background-color-text="<?php esc_attr_e('Background color', 'milenia-app-textdomain'); ?>"
                    content-color-text="<?php esc_attr_e('Content color', 'milenia-app-textdomain'); ?>"
                    links-color-text="<?php esc_attr_e('Links color', 'milenia-app-textdomain'); ?>"
                    title-color-text="<?php esc_attr_e('Title color', 'milenia-app-textdomain'); ?>"
                    background-image-text="<?php esc_attr_e('Select background image', 'milenia-app-textdomain'); ?>"
                    remove-background-image-text="<?php esc_attr_e('Remove background image', 'milenia-app-textdomain'); ?>"
                    background-image-opacity-text="<?php esc_attr_e('Background image transparency', 'milenia-app-textdomain'); ?>"
                    padding-top-text="<?php esc_attr_e('Padding top (px)', 'milenia-app-textdomain'); ?>"
                    padding-right-text="<?php esc_attr_e('Padding right (px)', 'milenia-app-textdomain'); ?>"
                    padding-bottom-text="<?php esc_attr_e('Padding bottom (px)', 'milenia-app-textdomain'); ?>"
                    padding-left-text="<?php esc_attr_e('Padding left (px)', 'milenia-app-textdomain'); ?>"
                    parallax-text="<?php esc_attr_e('Enable parallax', 'milenia-app-textdomain'); ?>"></breadcrumb-section>
            </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Appends necessary assets.
     *
     * @access public
     * @static
     * @return void
     */
    public static function admin_enqueue_scripts()
    {
        global $milenia_settings;

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_media();

        wp_enqueue_script(
            'redux-field-breadcrumb-section-vue-lib',
            MILENIA_FUNCTIONALITY_URL . 'extensions/BreadcrumbSectionField/breadcrumb_section/assets/vendors/vue.min.js',
            null,
            time(),
            true
        );

        wp_enqueue_script(
            'redux-field-breadcrumb-section-parallax-lib',
            MILENIA_FUNCTIONALITY_URL . 'extensions/BreadcrumbSectionField/breadcrumb_section/assets/vendors/jquery.parallax-1.1.3.min.js',
            array('jquery'),
            time(),
            true
        );

        wp_enqueue_script(
            'jqueryui',
            MILENIA_FUNCTIONALITY_URL . 'extensions/BreadcrumbSectionField/breadcrumb_section/assets/vendors/jqueryui/jquery-ui.min.js',
            array('jquery'),
            time(),
            true
        );

        wp_enqueue_script(
            'redux-field-breadcrumb-section-vue-component-js',
            MILENIA_FUNCTIONALITY_URL . 'extensions/BreadcrumbSectionField/breadcrumb_section/assets/js/breadcrumb-section.vue.js',
            array( 'redux-field-breadcrumb-section-vue-lib' ),
            time(),
            true
        );

        wp_enqueue_style(
            'redux-field-breadcrumb-section-jqu-css',
            MILENIA_FUNCTIONALITY_URL . 'extensions/BreadcrumbSectionField/breadcrumb_section/assets/css/breadcrumb-section.vue.css',
            time(),
            true
        );

        wp_enqueue_style(
            'jqueryui',
            MILENIA_FUNCTIONALITY_URL . 'extensions/BreadcrumbSectionField/breadcrumb_section/assets/vendors/jqueryui/jquery-ui.min.css',
            time(),
            true
        );

        if(isset($milenia_settings) && function_exists('milenia_google_fonts_url'))
        {
            $fonts_charsets = isset($milenia_settings['milenia-google-charsets']) && !empty($milenia_settings['milenia-google-charsets']) ? $milenia_settings['milenia-google-charsets'] : array('latin');

            wp_enqueue_style('milenia-google-fonts', milenia_google_fonts_url(array(
                $milenia_settings['h1-font']['font-family'] => array($milenia_settings['h1-font']['font-weight'])
            ), $fonts_charsets), null, null);
        }
    }
}
?>
