<?php
/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     ReduxFramework
 * @author      Dovy Paukstys
 * @version     3.6.11
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Don't duplicate me!
if( !class_exists( 'ReduxFramework_widgets_area_settings' ) ) {
    /**
     * Main ReduxFramework_widgets_area_settings class
     *
     * @since       1.0.0
     */
    class ReduxFramework_widgets_area_settings {

        /**
         * Contains amount of grid rows in the admin panel.
         *
         * @access protected
         * @var int
         */
        protected $rows_count = 5;

        /**
         * Field Constructor.
         *
         * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        function __construct( $field = array(), $value ='', $parent ) {
            $this->parent = $parent;
            $this->field = $field;
            $this->value = $value;
            if ( empty( $this->extension_dir ) ) {
                $this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
                $this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
            }
            // Set default args for this field to avoid bad indexes. Change this to anything you use.
            $defaults = array(
                'default' => array(),
                'stylesheet'        => '',
                'output'            => true,
                'enqueue'           => true,
                'enqueue_frontend'  => true,
                'columns'           => 4
            );
            $this->field = wp_parse_args( $this->field, $defaults );

            if(empty($this->value) && !empty($this->field['default'])) {
                $this->value = $this->field['default'];
            }

        }
        /**
         * Field Render Function.
         *
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        public function render() {

            global $milenia_settings;

            ?>
                <div class="redux-widgets-area-settings-container" data-counter="<?php echo esc_attr(!empty($this->value) ? (max(array_keys($this->value)) + 1) : 0); ?>">
                    <script class="redux-widgets-area-settings-configuration-template" type="text/x-handlebars-template">
                        <div class="redux-widgets-area-settings-configuration">
                            <fieldset>
                                <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][index]',$this->field['name'])); ?>"><?php esc_html_e('Widget index:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][index]',$this->field['name'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][index]',$this->field['name'])); ?>">
                                    <?php for($i = 1; $i < 20; $i++) : ?>
                                        <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                    <?php endfor; ?>
                                </select>

                                <legend><?php esc_html_e('Horizontal alignment', 'milenia-app-textdomain'); ?></legend>

                                <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][default]',$this->field['name'])); ?>"><?php esc_html_e('Default alignment:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][default]',$this->field['name'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][default]',$this->field['name'])); ?>">
                                    <option value="left"><?php esc_html_e('Left', 'milenia-app-textdomain'); ?></option>
                                    <option value="center"><?php esc_html_e('Center', 'milenia-app-textdomain'); ?></option>
                                    <option value="right"><?php esc_html_e('Right', 'milenia-app-textdomain'); ?></option>
                                </select>

                                <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom]', $this->field['name'])); ?>"><?php esc_html_e('Custom alignment:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom]', $this->field['name'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom]', $this->field['name'])); ?>">
                                    <option value="left"><?php esc_html_e('Left', 'milenia-app-textdomain'); ?></option>
                                    <option value="center"><?php esc_html_e('Center', 'milenia-app-textdomain'); ?></option>
                                    <option value="right"><?php esc_html_e('Right', 'milenia-app-textdomain'); ?></option>
                                </select>

                                <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom-breakpoint]', $this->field['name'])); ?>"><?php esc_html_e('Custom alignment breakpoint:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom-breakpoint]', $this->field['name'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][horizontal][custom-breakpoint]', $this->field['name'])); ?>">
                                    <option value="xl"><?php esc_html_e('xl', 'milenia-app-textdomain'); ?></option>
                                    <option value="lg"><?php esc_html_e('lg', 'milenia-app-textdomain'); ?></option>
                                    <option value="md"><?php esc_html_e('md', 'milenia-app-textdomain'); ?></option>
                                    <option value="sm"><?php esc_html_e('sm', 'milenia-app-textdomain'); ?></option>
                                </select>
                            </fieldset>

                            <hr>

                            <fieldset>
                                <legend><?php esc_html_e('Vertical alignment', 'milenia-app-textdomain'); ?></legend>

                                <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][default]', $this->field['name'])); ?>"><?php esc_html_e('Default alignment:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][default]', $this->field['name'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][default]', $this->field['name'])); ?>">
                                    <option value="middle"><?php esc_html_e('Middle', 'milenia-app-textdomain'); ?></option>
                                    <option value="top"><?php esc_html_e('Top', 'milenia-app-textdomain'); ?></option>
                                    <option value="bottom"><?php esc_html_e('Bottom', 'milenia-app-textdomain'); ?></option>
                                </select>

                                <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom]', $this->field['name'])); ?>"><?php esc_html_e('Custom alignment:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom]', $this->field['name'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom]', $this->field['name'])); ?>">
                                    <option value="middle"><?php esc_html_e('Middle', 'milenia-app-textdomain'); ?></option>
                                    <option value="top"><?php esc_html_e('Top', 'milenia-app-textdomain'); ?></option>
                                    <option value="bottom"><?php esc_html_e('Bottom', 'milenia-app-textdomain'); ?></option>
                                </select>

                                <label for="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom-breakpoint]', $this->field['name'])); ?>"><?php esc_html_e('Custom alignment breakpoint:', 'milenia-app-textdomain'); ?></label>
                                <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom-breakpoint]', $this->field['name'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][vertical][custom-breakpoint]', $this->field['name'])); ?>">
                                    <option value="xl"><?php esc_html_e('xl', 'milenia-app-textdomain'); ?></option>
                                    <option value="lg"><?php esc_html_e('lg', 'milenia-app-textdomain'); ?></option>
                                    <option value="md"><?php esc_html_e('md', 'milenia-app-textdomain'); ?></option>
                                    <option value="sm"><?php esc_html_e('sm', 'milenia-app-textdomain'); ?></option>
                                </select>
                            </fieldset>

                            <hr>

                            <fieldset>
                                <legend><?php esc_html_e('Lists direction', 'milenia-app-textdomain'); ?></legend>

                                <select id="<?php echo esc_attr(sprintf('%s[{{counter}}][lists-direction]',$this->field['name'])); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[{{counter}}][lists-direction]',$this->field['name'])); ?>">
                                    <option value="vertical"><?php esc_html_e('Vertical', 'milenia-app-textdomain'); ?></option>
                                    <option value="horizontal"><?php esc_html_e('Horizontal', 'milenia-app-textdomain'); ?></option>
                                </select>
                            </fieldset>

                            <hr>

                            <button type="button" class="button button-primary redux-widgets-area-settings-remove-btn"><?php esc_html_e('Remove the configuration', 'milenia-app-textdomain'); ?></button>
                        </div>
                    </script>

                    <div class="redux-widgets-area-settings-configurations"><?php if(!empty($this->value) && is_array($this->value)) : ?>
                            <?php foreach($this->value as $index => $configuration) : ?>
                                <div class="redux-widgets-area-settings-configuration">
                                    <fieldset>
                                        <label for="<?php echo esc_attr(sprintf('%s[%d][index]',$this->field['name'], $index)); ?>"><?php esc_html_e('Widget index:', 'milenia-app-textdomain'); ?></label>
                                        <select id="<?php echo esc_attr(sprintf('%s[%d][index]',$this->field['name'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][index]',$this->field['name'], $index)); ?>">
                                            <?php for($i = 1; $i < 20; $i++) : ?>
                                                <option <?php selected( $configuration['index'], $i); ?> value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                            <?php endfor; ?>
                                        </select>

                                        <legend><?php esc_html_e('Horizontal alignment', 'milenia-app-textdomain'); ?></legend>

                                        <label for="<?php echo esc_attr(sprintf('%s[%d][horizontal][default]',$this->field['name'], $index)); ?>"><?php esc_html_e('Default alignment:', 'milenia-app-textdomain'); ?></label>
                                        <select id="<?php echo esc_attr(sprintf('%s[%d][horizontal][default]',$this->field['name'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][horizontal][default]',$this->field['name'], $index)); ?>">
                                            <option value="left" <?php selected( $configuration['horizontal']['default'], 'left'); ?>><?php esc_html_e('Left', 'milenia-app-textdomain'); ?></option>
                                            <option value="center" <?php selected( $configuration['horizontal']['default'], 'center'); ?>><?php esc_html_e('Center', 'milenia-app-textdomain'); ?></option>
                                            <option value="right" <?php selected( $configuration['horizontal']['default'], 'right'); ?>><?php esc_html_e('Right', 'milenia-app-textdomain'); ?></option>
                                        </select>

                                        <label for="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom]', $this->field['name'], $index)); ?>"><?php esc_html_e('Custom alignment:', 'milenia-app-textdomain'); ?></label>
                                        <select id="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom]', $this->field['name'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom]', $this->field['name'], $index)); ?>">
                                            <option value="left" <?php selected( $configuration['horizontal']['custom'], 'left'); ?>><?php esc_html_e('Left', 'milenia-app-textdomain'); ?></option>
                                            <option value="center" <?php selected( $configuration['horizontal']['custom'], 'center'); ?>><?php esc_html_e('Center', 'milenia-app-textdomain'); ?></option>
                                            <option value="right" <?php selected( $configuration['horizontal']['custom'], 'right'); ?>><?php esc_html_e('Right', 'milenia-app-textdomain'); ?></option>
                                        </select>

                                        <label for="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom-breakpoint]', $this->field['name'], $index)); ?>"><?php esc_html_e('Custom alignment breakpoint:', 'milenia-app-textdomain'); ?></label>
                                        <select id="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom-breakpoint]', $this->field['name'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][horizontal][custom-breakpoint]', $this->field['name'], $index)); ?>">
                                            <option value="xl" <?php selected( $configuration['horizontal']['custom-breakpoint'], 'xl'); ?>><?php esc_html_e('xl', 'milenia-app-textdomain'); ?></option>
                                            <option value="lg" <?php selected( $configuration['horizontal']['custom-breakpoint'], 'lg'); ?>><?php esc_html_e('lg', 'milenia-app-textdomain'); ?></option>
                                            <option value="md" <?php selected( $configuration['horizontal']['custom-breakpoint'], 'md'); ?>><?php esc_html_e('md', 'milenia-app-textdomain'); ?></option>
                                            <option value="sm" <?php selected( $configuration['horizontal']['custom-breakpoint'], 'sm'); ?>><?php esc_html_e('sm', 'milenia-app-textdomain'); ?></option>
                                        </select>
                                    </fieldset>

                                    <hr>

                                    <fieldset>
                                        <legend><?php esc_html_e('Vertical alignment', 'milenia-app-textdomain'); ?></legend>

                                        <label for="<?php echo esc_attr(sprintf('%s[%d][vertical][default]', $this->field['name'], $index)); ?>"><?php esc_html_e('Default alignment:', 'milenia-app-textdomain'); ?></label>
                                        <select id="<?php echo esc_attr(sprintf('%s[%d][vertical][default]', $this->field['name'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][vertical][default]', $this->field['name'], $index)); ?>">
                                            <option value="middle" <?php selected( $configuration['vertical']['default'], 'middle'); ?>><?php esc_html_e('Middle', 'milenia-app-textdomain'); ?></option>
                                            <option value="top" <?php selected( $configuration['vertical']['default'], 'top'); ?>><?php esc_html_e('Top', 'milenia-app-textdomain'); ?></option>
                                            <option value="bottom" <?php selected( $configuration['vertical']['default'], 'bottom'); ?>><?php esc_html_e('Bottom', 'milenia-app-textdomain'); ?></option>
                                        </select>

                                        <label for="<?php echo esc_attr(sprintf('%s[%d][vertical][custom]', $this->field['name'], $index)); ?>"><?php esc_html_e('Custom alignment:', 'milenia-app-textdomain'); ?></label>
                                        <select id="<?php echo esc_attr(sprintf('%s[%d][vertical][custom]', $this->field['name'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][vertical][custom]', $this->field['name'], $index)); ?>">
                                            <option value="middle" <?php selected( $configuration['vertical']['custom'], 'middle'); ?>><?php esc_html_e('Middle', 'milenia-app-textdomain'); ?></option>
                                            <option value="top" <?php selected( $configuration['vertical']['custom'], 'top'); ?>><?php esc_html_e('Top', 'milenia-app-textdomain'); ?></option>
                                            <option value="bottom" <?php selected( $configuration['vertical']['custom'], 'bottom'); ?>><?php esc_html_e('Bottom', 'milenia-app-textdomain'); ?></option>
                                        </select>

                                        <label for="<?php echo esc_attr(sprintf('%s[%d][vertical][custom-breakpoint]', $this->field['name'], $index)); ?>"><?php esc_html_e('Custom alignment breakpoint:', 'milenia-app-textdomain'); ?></label>
                                        <select id="<?php echo esc_attr(sprintf('%s[%d][vertical][custom-breakpoint]', $this->field['name'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][vertical][custom-breakpoint]', $this->field['name'], $index)); ?>">
                                            <option value="xl" <?php selected( $configuration['vertical']['custom-breakpoint'], 'xl'); ?>><?php esc_html_e('xl', 'milenia-app-textdomain'); ?></option>
                                            <option value="lg" <?php selected( $configuration['vertical']['custom-breakpoint'], 'lg'); ?>><?php esc_html_e('lg', 'milenia-app-textdomain'); ?></option>
                                            <option value="md" <?php selected( $configuration['vertical']['custom-breakpoint'], 'md'); ?>><?php esc_html_e('md', 'milenia-app-textdomain'); ?></option>
                                            <option value="sm" <?php selected( $configuration['vertical']['custom-breakpoint'], 'sm'); ?>><?php esc_html_e('sm', 'milenia-app-textdomain'); ?></option>
                                        </select>
                                    </fieldset>

                                    <hr>

                                    <fieldset>
                                        <legend><?php esc_html_e('Lists direction', 'milenia-app-textdomain'); ?></legend>

                                        <select id="<?php echo esc_attr(sprintf('%s[%d][lists-direction]',$this->field['name'], $index)); ?>" class="milenia-styled-select" name="<?php echo esc_attr(sprintf('%s[%d][lists-direction]',$this->field['name'], $index)); ?>">
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
            <?php
        }

        /**
         * Enqueue Function.
         *
         * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        public function enqueue() {
            wp_enqueue_script(
                'handlebars',
                $this->extension_url . 'assets/js/handlebars-v4.0.12.js',
                array('jquery'),
                time(),
                true
            );

            wp_enqueue_script(
                'redux-field-widgets-area-settings',
                $this->extension_url . 'assets/js/redux-field-widgets-area-settings.js',
                array( 'jquery' ),
                time(),
                true
            );

            wp_enqueue_style(
                'redux-field-widgets-area-settings',
                $this->extension_url . 'assets/css/redux-field-widgets-area-settings.css',
                time(),
                true
            );

        }

        /**
         * Output Function.
         *
         * Used to enqueue to the front-end
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        public function output() {
            if ( $this->field['enqueue_frontend'] ) {
            }
        }

    }
}
