<?php
if (!defined('ABSPATH'))
    wp_die('No direct access allowed');

global $WPBE;
?>


<div class="notice notice-warning">
    <p>
        <?php printf(esc_html__('Bulk editing will be applied to: %s', 'bulk-editor'), '<span class="wpbe_action_will_be_applied_to">' . sprintf(esc_html__('all the [%s] on the site', 'bulk-editor'), $WPBE->settings->current_post_type) . '</span>') ?>
    </p>
</div>

<form method="post" id="wpbe_bulk_form">

    <div class="wpbe-tabs wpbe-tabs-style-shape">
        <section id="wpbe-bulk-basic" class="content-current">

            <div class="col-lg-12">
                <ul class="wpbe_filter_form_texts">
                    <li>
                        <small><i><?php esc_html_e('text', 'bulk-editor') ?></i></small><br />
                        <?php wpbe_bulk_draw_text(apply_filters('wpbe_bulk_text', $text_keys)); ?>
                    </li>
                </ul>
            </div>



            <br />
            <div class="clear"></div>
            <hr />

            <?php if (count(apply_filters('wpbe_bulk_number', $num_keys))): ?>

                <div class="col-lg-12">
                    <ul class="wpbe_filter_form_texts">
                        <li>
                            <small><i><?php esc_html_e('numeric', 'bulk-editor') ?></i></small><br />
                            <?php wpbe_bulk_draw_nums(apply_filters('wpbe_bulk_number', $num_keys)); ?>
                        </li>
                    </ul>
                </div>

                <br />
                <div class="clear"></div>
                <hr />

            <?php endif; ?>

            <div class="col-lg-12">
                <ul class="wpbe_filter_form_texts">
                    <li>
                        <small><i><?php esc_html_e('statuses and types', 'bulk-editor') ?></i></small><br />
                        <?php wpbe_bulk_draw_other(apply_filters('wpbe_bulk_other', $other_keys)); ?>
                    </li>
                </ul>
            </div>

            <br />
            <div class="clear"></div>
            <hr />

            <div class="col-lg-12">
                <ul class="wpbe_filter_form_texts">
                    <li>
                        <?php wpbe_bulk_draw_taxonomies(); ?>
                    </li>
                </ul>
            </div>

            <div class="clear"></div>

            <hr />

            <div class="col-lg-12">
                <ul class="wpbe_filter_form_texts">
                    <li><?php wpbe_bulk_draw_add1($settings_fields); ?></li>
                </ul>
            </div>

            <br />
            <div class="clear"></div>
            <hr />
        </section>
    </div>



</form>

<div class="clear"></div>
<br />
<div class="clear"></div>
<div class="wpbe_progress" style="display: none;">
    <div class="wpbe_progress_in" id="wpbe_bulk_progress">0%</div>
</div>
<br />


<div class="wpbe-float-left">
    <a href="#" class="button button-primary button-large" id="wpbe_bulk_posts_btn" style="display: none;"><?php esc_html_e('Do Bulk Edit', 'bulk-editor') ?></a>
    <a href="#" class="button button-primary wpbe_bulk_terminate" title="<?php esc_html_e('terminate bulk operation', 'bulk-editor') ?>" style="display: none;"><?php esc_html_e('terminate bulk operation', 'bulk-editor') ?></a>
</div>


<div class="wpbe_delete_wraper wpbe-float-right">


        <input type="checkbox" id="wpbe_bulk_delete_posts_btn_fuse" value='1'>
        <label for="wpbe_bulk_delete_posts_btn_fuse"><?php esc_html_e('Bulk deleting', 'bulk-editor') ?></label><br />


    <div style="height: 3px;"></div>
    <a href="#" disabled='disabled' class="button button-primary button-large" id="wpbe_bulk_delete_posts_btn" ><?php printf(esc_html__('Delete [%s]!', 'bulk-editor'), $WPBE->settings->current_post_type) ?></a>


</div>

<div class="clear"></div>

<hr />
<h4><?php esc_html_e('Notes', 'bulk-editor') ?>:</h4>
<ul>
    <?php if ($WPBE->show_notes) : ?>
        <li class="wpbe_set_attention">* <?php esc_html_e('In red containers wrapped fields which are not possible modify in bulk in FREE version of the plugin!', 'bulk-editor') ?><br /></li>
    <?php endif; ?>

    <li>* <?php esc_html_e('In the case of an aborted bulk-operation you can roll back changes in the tab History', 'bulk-editor') ?><br /></li>
    <li>* <?php
        printf(esc_html__('Time by time (one time per week for example) - make the backup of your site database. For example by %s', 'bulk-editor'), WPBE_HELPER::draw_link(
                        array(
                            'href' => 'https://wordpress.org/plugins/wp-migrate-db/',
                            'title' => esc_html__('this plugin', 'bulk-editor'),
                            'target' => '_blank'
                        )
                ))
        ?><br /></li>
    <li>

        <a href="https://bulk-editor.pro/document/wordpress-posts-bulk-edit/" target="_blank" class="button button-primary wpbe-info-btn"><span class="icon-book"></span>&nbsp;<?php esc_html_e('Documentation', 'bulk-editor') ?></a>

    </li>
</ul><br />
<br />

<!-------------------------------------------------------------------->
<?php

function wpbe_bulk_draw_text($bulk_fields) {
    global $WPBE;
    $fields = $WPBE->settings->get_fields();
    ?>
    <?php foreach ($bulk_fields as $field_key => $field) : ?>

        <?php
        if (!in_array($WPBE->settings->current_user_role, apply_filters('wpbe_permit_special_roles', ['administrator']))) {
            if (intval($WPBE->settings->get_site_editor_visibility()[$field_key]) === 0) {
                continue;
            }
        }
        ?>
        <?php if (isset($fields[$field_key])): ?>

            <?php
            if ($fields[$field_key]['edit_view'] == 'gallery_popup_editor') {
                continue;
            }
            ?>

            <?php if ($fields[$field_key]['edit_view'] == 'meta_popup_editor'): ?>
                <div class="col-lg-4">
                    <div class='filter-unit-wrap <?php echo $fields[$field_key]['css_classes'] ?> <?php if (!$fields[$field_key]['direct']): ?>wpbe-direct-field<?php endif; ?>'>
                        <div class="col-lg-1">
                            <div class="wpbe_height_4"></div>
                            <?php if ($fields[$field_key]['direct']): ?>
                                <input type="checkbox" title='<?php esc_html_e('select it to use', 'bulk-editor') ?>' name="wpbe_bulk[is][<?php echo $field_key ?>]" class="bulk_checker" data-field-key="<?php esc_html_e($field_key) ?>" data-title="<?php esc_html_e($fields[$field_key]['title']) ?>" value="1" /><br />
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-11">

                            <div>
                                <?php echo WPBE_HELPER::draw_meta_popup_editor_btn($field_key, 0, $fields[$field_key]['title']); ?>
                            </div>

                            <input type="hidden" name="wpbe_bulk[<?php echo $field_key ?>][value]" value="" />
                            <input type="hidden" name="wpbe_bulk[<?php echo $field_key ?>][behavior]" value="new" />
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-lg-4">
                    <div class='filter-unit-wrap <?php echo $field['css_classes'] ?> <?php if (!$fields[$field_key]['direct']): ?>wpbe-direct-field<?php endif; ?>'>
                        <div class="col-lg-1">
                            <div class="wpbe_height_4"></div>

                            <?php if ($fields[$field_key]['direct']): ?>
                                <input type="checkbox" title='<?php esc_html_e('select it to use', 'bulk-editor') ?>' name="wpbe_bulk[is][<?php echo $field_key ?>]" class="bulk_checker" data-field-key="<?php esc_html_e($field_key) ?>" data-title="<?php esc_html_e($field['title']) ?>" value="1" /><br />
                            <?php endif; ?>

                        </div>
                        <div class="col-lg-7">
                            <input type="text" class="wpbe_bulk_value" disabled="" placeholder="<?php echo $field['title'] ?>" name="wpbe_bulk[<?php echo $field_key ?>][value]" value="" />
                        </div>
                        <div class="col-lg-2">
                            <select class="wpbe_bulk_add_special_key" disabled="">
                                <option value="-1"><?php esc_html_e('variable', 'bulk-editor') ?></option>
                                <option value="{TITLE}"><?php esc_html_e('TITLE', 'bulk-editor') ?></option>
                                <option value="{ID}">ID</option>
                                <option value="{MENU_ORDER}"><?php esc_html_e('MENU ORDER', 'bulk-editor') ?></option>

                            </select>
                        </div>
                        <div class="col-lg-2">
                            <select name="wpbe_bulk[<?php echo $field_key ?>][behavior]" disabled="" class="wpbe_bulk_value_signs" data-key="<?php esc_html_e($field_key) ?>">
                                <option value="append"><?php esc_html_e('append', 'bulk-editor') ?></option>
                                <option value="prepend"><?php esc_html_e('prepend', 'bulk-editor') ?></option>
                                <option value="new"><?php esc_html_e('new', 'bulk-editor') ?></option>
                                <option value="replace"><?php esc_html_e('replace', 'bulk-editor') ?></option>
                            </select>
                        </div>

                        <div class="clear"></div>

                        <div class='filter-unit-wrap wpbe_bulk_replace_to_<?php echo $field_key ?>' style="display: none;">

                            <div class="col-lg-2">
                                <select name="wpbe_bulk[<?php echo $field_key ?>][case]" disabled="">
                                    <option value="same"><?php esc_html_e('same case', 'bulk-editor') ?></option>
                                    <option value="ignore"><?php esc_html_e('ignore case', 'bulk-editor') ?></option>
                                </select>
                            </div>

                            <div class="col-lg-8">
                                <input type="text" class="wpbe_bulk_value" disabled="" placeholder="<?php esc_html_e('replace to text', 'bulk-editor') ?>" name="wpbe_bulk[<?php echo $field_key ?>][replace_to]" value="" />
                            </div>


                            <div class="col-lg-2">
                                <select class="wpbe_bulk_add_special_key" disabled="">
                                    <option value="-1"><?php esc_html_e('variable', 'bulk-editor') ?></option>
                                    <option value="{TITLE}"><?php esc_html_e('TITLE', 'bulk-editor') ?></option>
                                    <option value="{ID}">ID</option>
                                    <option value="{MENU_ORDER}"><?php esc_html_e('MENU ORDER', 'bulk-editor') ?></option>

                                </select>
                            </div>



                            <div class="clear"></div>
                        </div>

                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>


    <?php endforeach; ?>
    <div class="clear"></div>
    <?php
}

function wpbe_bulk_draw_nums($filter_keys) {
    global $WPBE;
    $fields = wpbe_get_fields();
    ?>
    <?php foreach ($filter_keys as $field_key => $field) : ?>

        <?php
        if (!in_array($WPBE->settings->current_user_role, apply_filters('wpbe_permit_special_roles', ['administrator']))) {
            if (intval($WPBE->settings->get_site_editor_visibility()[$field_key]) === 0) {
                continue;
            }
        }

        //+++

        if ($fields[$field_key]['edit_view'] === 'calendar') {
            continue;
        }
        ?>

        <div class="col-lg-4">
            <div class='filter-unit-wrap <?php echo $field['css_classes'] ?> <?php if (!$field['direct']): ?>wpbe-direct-field<?php endif; ?>'>
                <div class="col-lg-1">
                    <div class="wpbe_height_4"></div>
                    <?php if ($field['direct']): ?>
                        <input type="checkbox" title='<?php esc_html_e('select it to use', 'bulk-editor') ?>' name="wpbe_bulk[is][<?php echo $field_key ?>]" class="bulk_checker" data-field-key="<?php esc_html_e($field_key) ?>" data-title="<?php esc_html_e($field['title']) ?>" value="1" /><br />
                    <?php endif; ?>
                </div>
                <div class="col-lg-7">
                    <input type="number" class="wpbe_bulk_value" disabled="" placeholder="<?php echo $field['title'] ?>" name="wpbe_bulk[<?php echo $field_key ?>][value]" value="" />
                </div>
                <div class="col-lg-4">
                    <select name="wpbe_bulk[<?php echo $field_key ?>][behavior]" disabled="">
                        <?php foreach ($field['options'] as $key => $title) : ?>
                            <option value="<?php echo $key ?>"><?php echo $title ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="clear"></div>
    <br />
    <div class="col-lg-4">
        <?php WPBE_HELPER::draw_rounding_drop_down() ?>
    </div>

    <div class="col-lg-1">
        <?php
        WPBE_HELPER::draw_tooltip(sprintf(esc_html__('Select how to round float values fractions in the numeric fields. Works for numeric meta fields. Read more %s.', 'bulk-editor'), WPBE_HELPER::draw_link(array(
                            'title' => esc_html__('here', 'bulk-editor'),
                            'href' => 'https://bulk-editor.pro/document/rounding/',
                            'target' => '_blank'
                        ))))
        ?>
    </div>
    <div class="col-lg-6 wpbe_formula">
        <div class="col-lg-3">
            <p><?php esc_html_e('Selected num fields', 'bulk-editor'); ?></p>
        </div>
        <div class="col-lg-1">
            <select class="wpbe_formula_action">
                <option value="+">+</option>
                <option value="-">-</option>
                <option value="*">*</option>
                <option value="/">/</option>
            </select>
        </div>

        <div class="col-lg-7">
            <select class="wpbe_formula_value">
                <option value=-1></option>
                <?php foreach ($filter_keys as $field_key => $field) : ?>
                    <?php
                    if (!isset($fields[$field_key]['meta_key'])) {

                        if ($field_key == 'download_expiry' || $field_key == 'download_limit') {
                            $meta_key = '_' . $field_key;
                        } else {
                            continue;
                        }
                    } else {
                        $meta_key = $fields[$field_key]['meta_key'];
                    }
                    ?>

                    <option value="<?php echo $meta_key ?>"><?php echo $field['title']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-1">
            <?php
            WPBE_HELPER::draw_tooltip(sprintf(esc_html__('You can select numeric field then make math operation with another numeric field using [+ - * /] value', 'bulk-editor')))
            ?>
        </div>
    </div>
    <div class="col-lg-12 wpbe_random">
        <div class="col-lg-1">
            <label for="wpbe_random_action"><?php esc_html_e('Random number', 'bulk-editor'); ?></label>
        </div>
        <div class="col-lg-1">
            <select id="wpbe_random_action">
                <option value="+">+</option>
                <option value="-">-</option>
                <option value="*">*</option>
                <option value="/">/</option>
            </select>
        </div>
        <div class="col-lg-1">
            <label for="wpbe_random_decimal"><?php esc_html_e('Decimal', 'bulk-editor'); ?></label>
        </div>
        <div class="col-lg-1">
            <select id="wpbe_random_decimal">
                <option value="1">0</option>
                <option value="10">1</option>
                <option value="100">2</option>
                <option value="1000">3</option>
                <option value="10000">4</option>
            </select>
        </div>
        <div class="col-lg-1">
            <label for="wpbe_random_from" ><?php esc_html_e('From:', 'bulk-editor'); ?></label>
        </div>		
        <div class="col-lg-2">			
            <input id="wpbe_random_from" type="number" >		
        </div>	
        <div class="col-lg-1">
            <label for="wpbe_random_to" ><?php esc_html_e('To:', 'bulk-editor'); ?></label>
        </div>		
        <div class="col-lg-2">
            <input id="wpbe_random_to" type="number" >			
        </div>			
        <div class="col-lg-1">
            <?php
            WPBE_HELPER::draw_tooltip(sprintf(esc_html__('You can create random numbers for test filling fields or for SEO tasks', 'bulk-editor')))
            ?>
        </div>
    </div>
    <div class="clear"></div>
    <?php
}

function wpbe_bulk_draw_taxonomies() {
    global $WPBE;
    //get all posts taxonomies
    $taxonomy_objects = get_object_taxonomies($WPBE->settings->current_post_type, 'objects');

    //***

    if (!empty($taxonomy_objects)) {
        ?>
        <small><i><?php esc_html_e('taxonomies', 'bulk-editor') ?></i></small><br />
        <?php
        foreach ($taxonomy_objects as $t) {

            if (!in_array($WPBE->settings->current_user_role, apply_filters('wpbe_permit_special_roles', ['administrator']))) {
                if (isset($WPBE->settings->get_site_editor_visibility()[$t->name]) AND intval($WPBE->settings->get_site_editor_visibility()[$t->name]) === 0) {
                    continue;
                }
            }

            //***

            $terms_by_parents = array();

            $terms = get_terms(array(
                'taxonomy' => $t->name,
                'hide_empty' => false
            ));

            if (!empty($terms)) {
                foreach ($terms as $k => $term) {
                    if ($term->parent > 0) {
                        $terms_by_parents[$term->parent][] = $term;
                        unset($terms[$k]);
                    }
                }
            }
            ?>
            <div class="col-lg-4">
                <div class='filter-unit-wrap not-for-variations wpbe-overflow-vis <?php if (!$WPBE->settings->get_fields()[$t->name]['direct']): ?>wpbe-direct-field<?php endif; ?>'>

                    <div class="col-lg-1">
                        <div class="wpbe_height_4"></div>
                        <?php if ($WPBE->settings->get_fields()[$t->name]['direct']): ?>
                            <input type="checkbox" title='<?php esc_html_e('select it to use', 'bulk-editor') ?>' name="wpbe_bulk[is][<?php echo $t->name ?>]" class="bulk_checker" data-field-key="<?php esc_html_e($t->name) ?>" data-title="<?php esc_html_e($t->label) ?>" value="1" /><br />
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-9">
                        <select class="chosen-select wpbe_filter_select" disabled="" multiple="" id="wpbe_bulk_taxonomies_<?php echo $t->name ?>" name="wpbe_bulk[<?php echo $t->name ?>][value][]" data-placeholder="<?php esc_html_e($t->label) ?>">
                            <?php if (!empty($terms)): ?>
                                <?php foreach ($terms as $tt) : ?>
                                    <option value="<?php echo $tt->term_id ?>"><?php echo $tt->name ?></option>
                                    <?php draw_child_filter_terms($tt->term_id, $terms_by_parents, 1) ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <div class='select-wrap' style="display: inline-block">
                            <select name="wpbe_bulk[<?php echo $t->name ?>][behavior]" disabled="">
                                <option value="append"><?php esc_html_e('append', 'bulk-editor') ?></option>
                                <option value="replace"><?php esc_html_e('replace', 'bulk-editor') ?></option>
                                <option value="remove"><?php esc_html_e('remove', 'bulk-editor') ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="clear"></div>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="clear"></div>
        <?php
    }
}

function wpbe_bulk_draw_other($filter_keys) {
    global $WPBE;
    ?>
    <?php foreach ($filter_keys as $field_key => $field) : ?>

        <?php
        if (!in_array($WPBE->settings->current_user_role, apply_filters('wpbe_permit_special_roles', ['administrator']))) {
            if (!isset($WPBE->settings->get_site_editor_visibility()[$field_key]) OR intval($WPBE->settings->get_site_editor_visibility()[$field_key]) === 0) {
                continue;
            }
        }
        ?>

        <div class="col-lg-4">
            <div class='filter-unit-wrap <?php echo $field['css_classes'] ?> <?php if (!$field['direct']): ?>wpbe-direct-field<?php endif; ?>'>
                <div class="col-lg-1">
                    <div class="wpbe_height_4"></div>
                    <?php if ($field['direct']): ?>
                        <input type="checkbox" title='<?php esc_html_e('select it to use', 'bulk-editor') ?>' name="wpbe_bulk[is][<?php echo $field_key ?>]" class="bulk_checker" data-field-key="<?php esc_html_e($field_key) ?>" data-title="<?php esc_html_e($field['title']) ?>" value="1" /><br />
                    <?php endif; ?>
                </div>

                <div class="col-lg-11">
                    <?php
                    $opt = array(-1 => sprintf(esc_html__('Set: %s', 'bulk-editor'), $field['title']));
                    $opt = array_merge($opt, $field['options']);

                    echo WPBE_HELPER::draw_select(array(
                        'options' => $opt,
                        'field' => '',
                        'post_id' => 0,
                        'class' => 'wpbe_filter_select',
                        'name' => 'wpbe_bulk[' . $field_key . '][value]',
                        'disabled' => TRUE
                    ));
                    ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="clear"></div>
    <?php
}

/* * ****************************************** */

function wpbe_bulk_draw_add1($fields) {
    global $WPBE;

    //***

    $options1 = array(
        'new' => esc_html__('set new', 'bulk-editor'),
        'invalue' => esc_html__('increase by value', 'bulk-editor'),
        'devalue' => esc_html__('decrease by value', 'bulk-editor')
    );

    //***

    $current_field_key = '_thumbnail_id';
    $show_field = true;
    if (!in_array($WPBE->settings->current_user_role, apply_filters('wpbe_permit_special_roles', ['administrator']))) {
        if (intval($WPBE->settings->get_site_editor_visibility()[$current_field_key]) === 0) {
            $show_field = false;
        }
    }
    ?>

    <?php if ($show_field): ?>

        <small><i><?php esc_html_e('other', 'bulk-editor') ?></i></small><br />

        <div class="col-lg-3">
            <div class='filter-unit-wrap <?php echo $fields[$current_field_key]['css_classes'] ?> <?php if (!$fields[$current_field_key]['direct']): ?>wpbe-direct-field<?php endif; ?>'>
                <div class="col-lg-1">
                    <div class="wpbe_height_4"></div>
                    <?php if ($fields[$current_field_key]['direct']): ?>
                        <input type="checkbox" title='<?php esc_html_e('select it to use', 'bulk-editor') ?>' name="wpbe_bulk[is][<?php echo $current_field_key ?>]" class="bulk_checker" data-field-key="<?php esc_html_e($current_field_key) ?>" data-title="<?php esc_html_e($fields[$current_field_key]['title']) ?>" value="1" /><br />
                    <?php endif; ?>
                </div>
                <div class="col-lg-4">
                    <img src="" alt="" width="30" id="wpbe_bulk_select_thumb" />
                    <input type="hidden" class="wpbe_bulk_value" name="wpbe_bulk[<?php echo $current_field_key ?>][value]" value="" />
                    <input type="hidden" name="wpbe_bulk[<?php echo $current_field_key ?>][behavior]" value="new" />
                </div>
                <div class="col-lg-7">
                    <a href="#" id="wpbe_bulk_select_thumb_btn" class="button button-primary button"><?php esc_html_e('select thumbnail', 'bulk-editor') ?></a>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    <?php endif; ?>


    <?php
    $calendar_fields = [];

    foreach ($fields as $key => $value) {
        if ($value['edit_view'] === 'calendar') {
            $calendar_fields[] = $key;
        }
    }

    if (!empty($calendar_fields)):
        foreach ($calendar_fields as $current_field_key) :
            $show_field = true;
            if (!in_array($WPBE->settings->current_user_role, apply_filters('wpbe_permit_special_roles', ['administrator']))) {
                if (intval($WPBE->settings->get_site_editor_visibility()[$current_field_key]) === 0) {
                    $show_field = false;
                }
            }
            ?>

            <?php if ($show_field): ?>
                <div class="col-lg-3">
                    <div class='filter-unit-wrap <?php echo $fields[$current_field_key]['css_classes'] ?> <?php if (!$fields[$current_field_key]['direct']): ?>wpbe-direct-field<?php endif; ?>'>
                        <div class="col-lg-1">
                            <div class="wpbe_height_4"></div>
                            <?php if ($fields[$current_field_key]['direct']): ?>
                                <input type="checkbox" title='<?php esc_html_e('select it to use', 'bulk-editor') ?>' name="wpbe_bulk[is][<?php echo $current_field_key ?>]" class="bulk_checker" data-field-key="<?php esc_html_e($current_field_key) ?>" data-title="<?php esc_html_e($fields[$current_field_key]['title']) ?>" value="1" /><br />
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-11">
                            <div class="content-wrap">
                                <?php
                                echo WPBE_HELPER::draw_calendar($current_field_key, $fields[$current_field_key]['title'], $current_field_key, '', 'wpbe_bulk[' . $current_field_key . '][value]', true);
                                ?>
                                <input type="hidden" name="wpbe_bulk[<?php echo $current_field_key ?>][behavior]" value="new" />
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php
        endforeach;
    endif;
    ?>

    <div class="clear"></div>
    <br />

    <?php
    $current_field_key = 'post_author';
    $show_field = true;
    if (!in_array($WPBE->settings->current_user_role, apply_filters('wpbe_permit_special_roles', ['administrator']))) {
        if (intval($WPBE->settings->get_site_editor_visibility()[$current_field_key]) === 0) {
            $show_field = false;
        }
    }
    $opt_auth = array();
    $opt_auth = WPBE_HELPER::get_users();
    ?>
    <?php if ($show_field): ?>
        <div class="col-lg-3">
            <div class='filter-unit-wrap wpbe_post_author_edit <?php echo $fields[$current_field_key]['css_classes'] ?> <?php if (!$WPBE->settings->get_fields()[$current_field_key]['direct']): ?>wpbe-direct-field<?php endif; ?>'>
                <div class="col-lg-1">
                    <div class="wpbe_height_4"></div>
                    <?php if ($WPBE->settings->get_fields()[$current_field_key]['direct']): ?>
                        <input type="checkbox" title='<?php esc_html_e('select it to use', 'bulk-editor') ?>' name="wpbe_bulk[is][<?php echo $current_field_key ?>]" class="bulk_checker" data-field-key="<?php esc_html_e($current_field_key) ?>" data-title="<?php esc_html_e($fields[$current_field_key]['title']) ?>" value="1" /><br />
                    <?php endif; ?>
                </div>
                <div class="col-lg-11 ">
                    <?php
                    echo WPBE_HELPER::draw_select(array(
                        'disabled' => 1,
                        'options' => $opt_auth,
                        'field' => '',
                        'post_id' => 0,
                        'class' => 'wpbe_filter_select chosen-select',
                        'name' => 'wpbe_bulk[' . $current_field_key . '][value]'
                    ));
                    ?>
                    <i><?php echo $fields[$current_field_key]['title'] ?></i>
                    <input type="hidden" name="wpbe_bulk[<?php echo $current_field_key ?>][behavior]" value="new" />
                </div>
                <div class="clear"></div>
            </div>
        </div>
    <?php endif; ?>


    <div class="clear"></div>
    <?php
}
