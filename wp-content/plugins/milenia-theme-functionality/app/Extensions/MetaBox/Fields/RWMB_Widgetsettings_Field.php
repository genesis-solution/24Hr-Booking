<?php
/**
* The RWMB_Breadcrumb_Field class.
*
* @package WordPress
* @subpackage MileniaThemeFunctionality
* @since MileniaThemeFunctionality 1.0.0
*/

class RWMB_Widgetsettings_Field extends RWMB_Field
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

        ob_start(); ?>

        <div class="redux-widgets-area-settings-container" data-counter="<?php echo esc_attr(!empty($meta) && is_array($meta) ? (max(array_keys($meta)) + 1) : 0); ?>">
            <script class="redux-widgets-area-settings-configuration-template" type="text/x-handlebars-template">
                <div class="redux-widgets-area-settings-configuration">
                    <fieldset>
                        <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][index]',$field['id'])); ?>"><?php esc_html_e('Widget index:', 'milenia-app-textdomain'); ?></label>
                        <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][index]',$field['id'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][index]',$field['id'])); ?>">
                            <?php for($i = 1; $i < 20; $i++) : ?>
                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                            <?php endfor; ?>
                        </select>

                        <legend><?php esc_html_e('Horizontal alignment', 'milenia-app-textdomain'); ?></legend>

                        <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][default]',$field['id'])); ?>"><?php esc_html_e('Default alignment:', 'milenia-app-textdomain'); ?></label>
                        <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][default]',$field['id'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][default]',$field['id'])); ?>">
                            <option value="left"><?php esc_html_e('Left', 'milenia-app-textdomain'); ?></option>
                            <option value="center"><?php esc_html_e('Center', 'milenia-app-textdomain'); ?></option>
                            <option value="right"><?php esc_html_e('Right', 'milenia-app-textdomain'); ?></option>
                        </select>

                        <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom]', $field['id'])); ?>"><?php esc_html_e('Custom alignment:', 'milenia-app-textdomain'); ?></label>
                        <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom]', $field['id'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom]', $field['id'])); ?>">
                            <option value="left"><?php esc_html_e('Left', 'milenia-app-textdomain'); ?></option>
                            <option value="center"><?php esc_html_e('Center', 'milenia-app-textdomain'); ?></option>
                            <option value="right"><?php esc_html_e('Right', 'milenia-app-textdomain'); ?></option>
                        </select>

                        <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom-breakpoint]', $field['id'])); ?>"><?php esc_html_e('Custom alignment breakpoint:', 'milenia-app-textdomain'); ?></label>
                        <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom-breakpoint]', $field['id'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom-breakpoint]', $field['id'])); ?>">
                            <option value="xl"><?php esc_html_e('xl', 'milenia-app-textdomain'); ?></option>
                            <option value="lg"><?php esc_html_e('lg', 'milenia-app-textdomain'); ?></option>
                            <option value="md"><?php esc_html_e('md', 'milenia-app-textdomain'); ?></option>
                            <option value="sm"><?php esc_html_e('sm', 'milenia-app-textdomain'); ?></option>
                        </select>
                    </fieldset>

                    <hr>

                    <fieldset>
                        <legend><?php esc_html_e('Vertical alignment', 'milenia-app-textdomain'); ?></legend>

                        <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][default]', $field['id'])); ?>"><?php esc_html_e('Default alignment:', 'milenia-app-textdomain'); ?></label>
                        <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][default]', $field['id'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][default]', $field['id'])); ?>">
                            <option value="middle"><?php esc_html_e('Middle', 'milenia-app-textdomain'); ?></option>
                            <option value="top"><?php esc_html_e('Top', 'milenia-app-textdomain'); ?></option>
                            <option value="bottom"><?php esc_html_e('Bottom', 'milenia-app-textdomain'); ?></option>
                        </select>

                        <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom]', $field['id'])); ?>"><?php esc_html_e('Custom alignment:', 'milenia-app-textdomain'); ?></label>
                        <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom]', $field['id'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom]', $field['id'])); ?>">
                            <option value="middle"><?php esc_html_e('Middle', 'milenia-app-textdomain'); ?></option>
                            <option value="top"><?php esc_html_e('Top', 'milenia-app-textdomain'); ?></option>
                            <option value="bottom"><?php esc_html_e('Bottom', 'milenia-app-textdomain'); ?></option>
                        </select>

                        <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom-breakpoint]', $field['id'])); ?>"><?php esc_html_e('Custom alignment breakpoint:', 'milenia-app-textdomain'); ?></label>
                        <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom-breakpoint]', $field['id'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom-breakpoint]', $field['id'])); ?>">
                            <option value="xl"><?php esc_html_e('xl', 'milenia-app-textdomain'); ?></option>
                            <option value="lg"><?php esc_html_e('lg', 'milenia-app-textdomain'); ?></option>
                            <option value="md"><?php esc_html_e('md', 'milenia-app-textdomain'); ?></option>
                            <option value="sm"><?php esc_html_e('sm', 'milenia-app-textdomain'); ?></option>
                        </select>
                    </fieldset>

                    <hr>

                    <fieldset>
                        <legend><?php esc_html_e('Lists direction', 'milenia-app-textdomain'); ?></legend>

                        <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][lists-direction]',$field['id'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][lists-direction]',$field['id'])); ?>">
                            <option value="vertical"><?php esc_html_e('Vertical', 'milenia-app-textdomain'); ?></option>
                            <option value="horizontal"><?php esc_html_e('Horizontal', 'milenia-app-textdomain'); ?></option>
                        </select>
                    </fieldset>

                    <hr>

                    <button type="button" class="button button-primary redux-widgets-area-settings-remove-btn"><?php esc_html_e('Remove the configuration', 'milenia-app-textdomain'); ?></button>
                </div>
            </script>

            <div class="redux-widgets-area-settings-configurations"><?php if(!empty($meta) && is_array($meta)) : ?>
                    <?php foreach($meta as $index => $configuration) : ?>
                        <div class="redux-widgets-area-settings-configuration">
                            <fieldset>
                                <label for="<?php echo esc_attr(sprintf('%s[%d][index]',$field['id'], $index)); ?>"><?php esc_html_e('Widget index:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[%d][index]',$field['id'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][index]',$field['id'], $index)); ?>">
                                    <?php for($i = 1; $i < 20; $i++) : ?>
                                        <option <?php selected( $configuration['index'], $i); ?> value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                    <?php endfor; ?>
                                </select>

                                <legend><?php esc_html_e('Horizontal alignment', 'milenia-app-textdomain'); ?></legend>

                                <label for="<?php echo esc_attr(sprintf('%s[%d][horizontal][default]',$field['id'], $index)); ?>"><?php esc_html_e('Default alignment:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[%d][horizontal][default]',$field['id'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][horizontal][default]',$field['id'], $index)); ?>">
                                    <option value="left" <?php selected( $configuration['horizontal']['default'], 'left'); ?>><?php esc_html_e('Left', 'milenia-app-textdomain'); ?></option>
                                    <option value="center" <?php selected( $configuration['horizontal']['default'], 'center'); ?>><?php esc_html_e('Center', 'milenia-app-textdomain'); ?></option>
                                    <option value="right" <?php selected( $configuration['horizontal']['default'], 'right'); ?>><?php esc_html_e('Right', 'milenia-app-textdomain'); ?></option>
                                </select>

                                <label for="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom]', $field['id'], $index)); ?>"><?php esc_html_e('Custom alignment:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom]', $field['id'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom]', $field['id'], $index)); ?>">
                                    <option value="left" <?php selected( $configuration['horizontal']['custom'], 'left'); ?>><?php esc_html_e('Left', 'milenia-app-textdomain'); ?></option>
                                    <option value="center" <?php selected( $configuration['horizontal']['custom'], 'center'); ?>><?php esc_html_e('Center', 'milenia-app-textdomain'); ?></option>
                                    <option value="right" <?php selected( $configuration['horizontal']['custom'], 'right'); ?>><?php esc_html_e('Right', 'milenia-app-textdomain'); ?></option>
                                </select>

                                <label for="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom-breakpoint]', $field['id'], $index)); ?>"><?php esc_html_e('Custom alignment breakpoint:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom-breakpoint]', $field['id'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom-breakpoint]', $field['id'], $index)); ?>">
                                    <option value="xl" <?php selected( $configuration['horizontal']['custom-breakpoint'], 'xl'); ?>><?php esc_html_e('xl', 'milenia-app-textdomain'); ?></option>
                                    <option value="lg" <?php selected( $configuration['horizontal']['custom-breakpoint'], 'lg'); ?>><?php esc_html_e('lg', 'milenia-app-textdomain'); ?></option>
                                    <option value="md" <?php selected( $configuration['horizontal']['custom-breakpoint'], 'md'); ?>><?php esc_html_e('md', 'milenia-app-textdomain'); ?></option>
                                    <option value="sm" <?php selected( $configuration['horizontal']['custom-breakpoint'], 'sm'); ?>><?php esc_html_e('sm', 'milenia-app-textdomain'); ?></option>
                                </select>
                            </fieldset>

                            <hr>

                            <fieldset>
                                <legend><?php esc_html_e('Vertical alignment', 'milenia-app-textdomain'); ?></legend>

                                <label for="<?php echo esc_attr(sprintf('%s[%d][vertical][default]', $field['id'], $index)); ?>"><?php esc_html_e('Default alignment:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[%d][vertical][default]', $field['id'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][vertical][default]', $field['id'], $index)); ?>">
                                    <option value="middle" <?php selected( $configuration['vertical']['default'], 'middle'); ?>><?php esc_html_e('Middle', 'milenia-app-textdomain'); ?></option>
                                    <option value="top" <?php selected( $configuration['vertical']['default'], 'top'); ?>><?php esc_html_e('Top', 'milenia-app-textdomain'); ?></option>
                                    <option value="bottom" <?php selected( $configuration['vertical']['default'], 'bottom'); ?>><?php esc_html_e('Bottom', 'milenia-app-textdomain'); ?></option>
                                </select>

                                <label for="<?php echo esc_attr(sprintf('%s[%d][vertical][custom]', $field['id'], $index)); ?>"><?php esc_html_e('Custom alignment:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[%d][vertical][custom]', $field['id'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][vertical][custom]', $field['id'], $index)); ?>">
                                    <option value="middle" <?php selected( $configuration['vertical']['custom'], 'middle'); ?>><?php esc_html_e('Middle', 'milenia-app-textdomain'); ?></option>
                                    <option value="top" <?php selected( $configuration['vertical']['custom'], 'top'); ?>><?php esc_html_e('Top', 'milenia-app-textdomain'); ?></option>
                                    <option value="bottom" <?php selected( $configuration['vertical']['custom'], 'bottom'); ?>><?php esc_html_e('Bottom', 'milenia-app-textdomain'); ?></option>
                                </select>

                                <label for="<?php echo esc_attr(sprintf('%s[%d][vertical][custom-breakpoint]', $field['id'], $index)); ?>"><?php esc_html_e('Custom alignment breakpoint:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[%d][vertical][custom-breakpoint]', $field['id'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][vertical][custom-breakpoint]', $field['id'], $index)); ?>">
                                    <option value="xl" <?php selected( $configuration['vertical']['custom-breakpoint'], 'xl'); ?>><?php esc_html_e('xl', 'milenia-app-textdomain'); ?></option>
                                    <option value="lg" <?php selected( $configuration['vertical']['custom-breakpoint'], 'lg'); ?>><?php esc_html_e('lg', 'milenia-app-textdomain'); ?></option>
                                    <option value="md" <?php selected( $configuration['vertical']['custom-breakpoint'], 'md'); ?>><?php esc_html_e('md', 'milenia-app-textdomain'); ?></option>
                                    <option value="sm" <?php selected( $configuration['vertical']['custom-breakpoint'], 'sm'); ?>><?php esc_html_e('sm', 'milenia-app-textdomain'); ?></option>
                                </select>
                            </fieldset>

                            <hr>

                            <fieldset>
                                <legend><?php esc_html_e('Lists direction', 'milenia-app-textdomain'); ?></legend>

                                <select id="<?php echo esc_attr(sprintf('%s[%d][lists-direction]',$field['id'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][lists-direction]',$field['id'], $index)); ?>">
                                    <option value="vertical" <?php selected( $configuration['lists-direction'], 'vertical'); ?>><?php esc_html_e('Vertical', 'milenia-app-textdomain'); ?></option>
                                    <option value="horizontal" <?php selected( $configuration['lists-direction'], 'horizontal'); ?>><?php esc_html_e('Horizontal', 'milenia-app-textdomain'); ?></option>
                                </select>
                            </fieldset>

                            <hr>

                            <button type="button" class="button button-primary redux-widgets-area-settings-remove-btn"><?php esc_html_e('Remove the configuration', 'milenia-app-textdomain'); ?></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?></div>

            <button type="button" class="button button-primary redux-widgets-area-settings-add-btn"><?php esc_html_e('+ Add configuration', 'milenia-app-textdomain'); ?></button>
        </div>

        <?php return ob_get_clean();
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
        wp_enqueue_script(
            'handlebars',
            MILENIA_FUNCTIONALITY_URL . 'extensions/WidgetsAreaSettingsField/widgets_area_settings/assets/js/handlebars-v4.0.12.js',
            array('jquery'),
            time(),
            true
        );

        wp_enqueue_script(
            'redux-field-widgets-area-settings',
            MILENIA_FUNCTIONALITY_URL . 'extensions/WidgetsAreaSettingsField/widgets_area_settings/assets/js/redux-field-widgets-area-settings.js',
            array( 'jquery' ),
            time(),
            true
        );

        wp_enqueue_style(
            'redux-field-widgets-area-settings',
            MILENIA_FUNCTIONALITY_URL . 'extensions/WidgetsAreaSettingsField/widgets_area_settings/assets/css/redux-field-widgets-area-settings.css',
            time(),
            true
        );
    }
}
?>
