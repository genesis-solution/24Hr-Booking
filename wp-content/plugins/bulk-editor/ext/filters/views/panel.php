<?php
if (!defined('ABSPATH'))
    wp_die('No direct access allowed');
?>

<form method="post" id="wpbe_filter_form">
    <div class="col-lg-4">
        <ul class="wpbe_filter_form_texts">
            <li><?php wpbe_filter_draw_text(); ?></li>            
        </ul>
    </div>

    <div class="col-lg-4">
        <ul class="wpbe_filter_form_texts">            
            <li><?php wpbe_filter_draw_numbers(); ?></li>
            <li><?php wpbe_filter_draw_other(); ?></li>
        </ul>
    </div>

    <div class="col-lg-4">
        <ul>
            <li><?php wpbe_filter_draw_taxonomies(); ?></li>
        </ul>
    </div>
    <div class="clear"></div>
</form>

<div class="clear"></div>
<br />
<hr />
<a href="#" class="button button-primary button-large" id="wpbe_filter_posts_btn"><?php esc_html_e('Filter', 'bulk-editor') ?></a>
<a href="#" class="button button-primary button-large wpbe_filter_reset_btn1" style="display: none;"><?php esc_html_e('Reset', 'bulk-editor') ?></a>

<div class="clear"></div>
<br />
<a href="https://bulk-editor.pro/document/filters/" target="_blank" class="button button-primary wpbe-info-btn"><span class="icon-book"></span>&nbsp;<?php esc_html_e('Documentation', 'bulk-editor') ?></a>
<br />



<!-------------------------------------------------------------->

<?php

function wpbe_filter_draw_taxonomies() {
    //get all posts taxonomies
    global $WPBE;
    $taxonomy_objects = get_object_taxonomies($WPBE->settings->current_post_type, 'objects');
    //unset($taxonomy_objects['post_type']);
    //***

    if (!empty($taxonomy_objects)) {
        foreach ($taxonomy_objects as $t) {

            $terms_by_parents = array();
			
			if (!apply_filters('wpbe_show_taxonomy_filter', true, $t)) {
				continue;
			}
			
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
            <div class='filter-unit-wrap wpbe-overflow-vis'>

                <table class="wpbe-full-width">
                    <tr>
                        <td class="wpbe-full-width">
                            <select class="chosen-select wpbe_filter_select wpbe-full-width" multiple="" id="wpbe_filter_taxonomies_<?php echo $t->name ?>" name="wpbe_filter[taxonomies][<?php echo $t->name ?>][]" data-placeholder="<?php esc_html_e($t->label) ?>">
                                <?php if (!empty($terms)): ?>
                                    <?php foreach ($terms as $tt) : ?>
                                        <option value="<?php echo $tt->term_id ?>"><?php echo $tt->name ?></option>
                                        <?php draw_child_filter_terms($tt->term_id, $terms_by_parents, 1) ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </td>
                        <td>
                            <div class='select-wrap' style="display: inline-block">
                                <select name="wpbe_filter[taxonomies_operators][<?php echo $t->name ?>]">
                                    <option value="IN">OR</option>
                                    <option value="AND">AND</option>
                                    <option value="NOT IN">NOT IN</option>
                                    <?php if (apply_filters('wpbe_filter_taxonomies_exists_show', false)): ?>
                                        <option value="NOT EXISTS">NOT EXISTS</option>
                                        <option value="EXISTS">EXISTS</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="clear"></div>
            <?php
        }
    }
}

//service
function draw_child_filter_terms($term_id, $terms_by_parents, $level) {
    ?>
    <?php if (isset($terms_by_parents[$term_id]) AND!empty($terms_by_parents[$term_id])): ?>
        <?php
        foreach ($terms_by_parents[$term_id] as $tt) :
            ?>
            <option style="padding-left: <?php echo 5 * $level ?>px" value="<?php echo $tt->term_id ?>"><?php echo $tt->name ?></option>
            <?php draw_child_filter_terms($tt->term_id, $terms_by_parents, $level + 1); ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php
}

//*****************************


function wpbe_filter_draw_text() {

    global $WPBE;

    $behavior_options = array(
        'like' => esc_html__('LIKE', 'bulk-editor'),
        'exact' => esc_html__('EXACT', 'bulk-editor'),
        'not' => esc_html__('NOT', 'bulk-editor'),
        'begin' => esc_html__('BEGIN', 'bulk-editor'),
        'end' => esc_html__('END', 'bulk-editor'),
        'empty' => esc_html__('Empty', 'bulk-editor'),
    );

    $filter_keys = array(
        'post__in' => array(
            'placeholder' => esc_html__('ID(s). Use comma or/and minus for range', 'bulk-editor'),
            'behavior_options' => array('exact' => esc_html__('EXACT', 'bulk-editor'))
        ),
        'post_title' => array(
            'placeholder' => sprintf(esc_html__('[%s] title ...', 'bulk-editor'), $WPBE->settings->current_post_type),
            'behavior_options' => $behavior_options
        ),
        'post_content' => array(
            'placeholder' => sprintf(esc_html__('[%s] content ...', 'bulk-editor'), $WPBE->settings->current_post_type),
            'behavior_options' => $behavior_options
        ),
        'post_excerpt' => array(
            'placeholder' => sprintf(esc_html__('[%s] excerpt ...', 'bulk-editor'), $WPBE->settings->current_post_type),
            'behavior_options' => $behavior_options
        ),
        'post_name' => array(
            'placeholder' => sprintf(esc_html__('[%s] slug ...', 'bulk-editor'), $WPBE->settings->current_post_type),
            'behavior_options' => $behavior_options
        ),
        'post_password' => array(
            'placeholder' => sprintf(esc_html__('[%s] password ...', 'bulk-editor'), $WPBE->settings->current_post_type),
            'behavior_options' => $behavior_options
        ),
    );

    $filter_keys = apply_filters('wpbe_filter_text', $filter_keys);
    ?>

    <?php foreach ($filter_keys as $key => $item) : ?>
        <div class='filter-unit-wrap'>
            <div class="col-lg-10">
                <div class="wpbe-pr2px">
                    <input type="text" placeholder="<?php echo $item['placeholder'] ?>" name="wpbe_filter[<?php echo $key ?>][value]" value="" />
                </div>
            </div>
            <div class="col-lg-2">

                <select name="wpbe_filter[<?php echo $key ?>][behavior]">
                    <?php foreach ($item['behavior_options'] as $key => $title) : ?>
                        <option value="<?php echo $key ?>"><?php echo $title ?></option>
                    <?php endforeach; ?>
                </select>

            </div>
            <div class="clear"></div>
        </div>

    <?php endforeach; ?>

    <?php
}

function wpbe_filter_draw_numbers() {
    $fields = wpbe_get_fields();
    $filter_keys = apply_filters('wpbe_filter_numbers', []);

    $tmp = [];
    foreach ($fields as $key => $value) {
        if ($value['edit_view'] === 'calendar') {
            $tmp[] = $key;
        }
    }

    if (!empty($filter_keys)) {
        foreach ($filter_keys as $k => $kk) {
            if (in_array($k, $tmp)) {
                unset($filter_keys[$k]);
            }
        }
    }

    if (!empty($filter_keys)) {
        ?>
        <div class='filter-unit-wrap filter-unit-wrap-numbers'>
            <?php foreach ($filter_keys as $key => $item) : ?>

                <div class="col-lg-6 prmb2">
                    <input type="number" name="wpbe_filter[<?php echo $key ?>][from]" min="0" placeholder="<?php echo $item['placeholder_from'] ?>" value="" /><br />
                </div>
                <div class="col-lg-6 prmb2">
                    <input type="number" name="wpbe_filter[<?php echo $key ?>][to]" min="0" placeholder="<?php echo $item['placeholder_to'] ?>" value="" />
                </div>


                <div class="wpbe_height_4 clear"></div>
            <?php endforeach; ?>

            <div class="clear"></div>
        </div>

        <?php
    }
}

function wpbe_filter_draw_other() {
    $fields = wpbe_get_fields();
    global $WPBE;
    ?>
    <div class='filter-unit-wrap wpbe_filter_draw_other'>


        <div class="col-lg-6 prmb2">
            <div class="pl1">
                <input type="number" name="wpbe_filter[menu_order_from]" min="0" placeholder="<?php esc_html_e('Menu order from', 'bulk-editor') ?>" value="" /><br />
            </div>
        </div>
        <div class="col-lg-6 prmb2">
            <div class="pr1">
                <input type="number" name="wpbe_filter[menu_order_to]" min="0" placeholder="<?php esc_html_e('Menu order to', 'bulk-editor') ?>" value="" />
            </div>
        </div>




        <div class="col-lg-6 prmb2">
            <div class="pl1">
                <input type="number" name="wpbe_filter[post_parent_from]" min="0" placeholder="<?php esc_html_e('Post parent from', 'bulk-editor') ?>" value="" /><br />
            </div>
        </div>
        <div class="col-lg-6 prmb2">
            <div class="pr1">
                <input type="number" name="wpbe_filter[post_parent_to]" min="0" placeholder="<?php esc_html_e('Post parent to', 'bulk-editor') ?>" value="" />
            </div>
        </div>


        <div class="col-lg-6 prmb2">
            <div class="pl1">
                <?php
                echo WPBE_HELPER::draw_select(array(
                    'options' => array(-1 => sprintf(esc_html__('[%s] status', 'bulk-editor'), $WPBE->settings->current_post_type)) + $fields['post_status']['select_options'],
                    'field' => 'post_status',
                    'post_id' => 0,
                    'class' => 'wpbe_filter_select',
                    'name' => 'wpbe_filter[post_status]'
                ));
                ?>
            </div>
        </div>


        <div class="col-lg-6 prmb2">
            <div class="pr1">
                <?php
                echo WPBE_HELPER::draw_select(array(
                    'options' => array(-1 => esc_html__('Comment status', 'bulk-editor')) + $fields['comment_status']['select_options'],
                    'field' => 'comment_status',
                    'post_id' => 0,
                    'class' => 'wpbe_filter_select',
                    'name' => 'wpbe_filter[comment_status]'
                ));
                ?>
            </div>
        </div>



        <div class="col-lg-6 prmb2">
            <div class="pl1">
                <?php
                echo WPBE_HELPER::draw_select(array(
                    'options' => array(-1 => esc_html__('Ping status', 'bulk-editor')) + $fields['ping_status']['select_options'],
                    'field' => 'ping_status',
                    'post_id' => 0,
                    'class' => 'wpbe_filter_select',
                    'name' => 'wpbe_filter[ping_status]'
                ));
                ?>
            </div>
        </div>

        <?php if (in_array($WPBE->settings->current_post_type, $fields['post_mime_type']['allow_post_types'])): ?>

            <div class="col-lg-6 prmb2">
                <div class="pr1">
                    <?php
                    echo WPBE_HELPER::draw_select(array(
                        'options' => array(-1 => esc_html__('Post mime type', 'bulk-editor')) + $fields['post_mime_type']['select_options'],
                        'field' => 'post_mime_type',
                        'post_id' => 0,
                        'class' => 'wpbe_filter_select',
                        'name' => 'wpbe_filter[post_mime_type]'
                    ));
                    ?>
                </div>
            </div>

        <?php endif; ?>

        <?php if (in_array($WPBE->settings->current_post_type, $fields['sticky_posts']['allow_post_types'])): ?>
            <div class="col-lg-6 prmb2">
                <div class="pl1">
                    <?php
                    echo WPBE_HELPER::draw_select(array(
                        'options' => array(-1 => esc_html__('Sticky posts', 'bulk-editor')) + $fields['sticky_posts']['select_options'],
                        'field' => 'sticky_posts',
                        'post_id' => 0,
                        'class' => 'wpbe_filter_select',
                        'name' => 'wpbe_filter[sticky_posts]'
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>


        <div class="col-lg-6 prmb2">
            <div class="pr1">
                <?php
                $users = array();
                if ($users === array()) {
                    $uu = get_users();
                    $users = array();
                    if (!empty($uu)) {
                        foreach ($uu as $u) {
                            $users[$u->data->ID] = $u->data->display_name;
                        }
                    }
                }
                echo WPBE_HELPER::draw_select(array(
                    'options' => array('-1' => esc_html__('By author', 'bulk-editor')) + $users, //+ $fields['author']['select_options'],
                    'field' => 'post_author',
                    'post_id' => 0,
                    'class' => 'wpbe_filter_select',
                    'name' => 'wpbe_filter[post_author]'
                ));
                ?>
            </div>
        </div>

        <div class="col-lg-6 prmb2">
            <div class="pl1">
                <?php
                echo WPBE_HELPER::draw_select(array(
                    'options' => array(-1 => esc_html__('Thumbnail', 'bulk-editor'), 'empty' => esc_html__('Empty', 'bulk-editor'), 'not_empty' => esc_html__('Not empty', 'bulk-editor')),
                    'field' => '_thumbnail_id',
                    'post_id' => 0,
                    'class' => 'wpbe_filter_select',
                    'name' => 'wpbe_filter[_thumbnail_id]'
                ));
                ?>
            </div>
        </div>		

        <?php
        $calendar_fields = [];

        foreach ($fields as $key => $value) {
            if ($value['edit_view'] === 'calendar') {
                $calendar_fields[] = $key;
            }
        }
        ?>

        <div style="clear: both;"></div>
        <?php foreach ($calendar_fields as $ff) : ?>

            <?php if ($fields[$ff]['field_type'] === 'meta'): ?>

                <div class="col-lg-6 prmb2">
                    <div class="pl1">
                        <?php
                        echo WPBE_HELPER::draw_calendar("wpbe_filter_{$ff}_from", sprintf(esc_html__('%s from', 'bulk-editor'), $fields[$ff]['title']), $ff . '_from', '', "wpbe_filter[{$ff}][from]", true);
                        ?>
                    </div>
                </div>
                <div class="col-lg-6 prmb2">
                    <div class="pr1">
                        <?php
                        echo WPBE_HELPER::draw_calendar("wpbe_filter_{$ff}_to", sprintf(esc_html__('%s to', 'bulk-editor'), $fields[$ff]['title']), $ff . '_to', '', "wpbe_filter[{$ff}][to]", true);
                        ?>
                    </div>
                </div>


            <?php else: ?>

                <div class="col-lg-6 prmb2">
                    <div class="pl1">
                        <?php
                        echo WPBE_HELPER::draw_calendar("wpbe_filter_{$ff}_from", sprintf(esc_html__('%s from', 'bulk-editor'), $fields[$ff]['title']), $ff . '_from', '', "wpbe_filter[{$ff}_from]", true);
                        ?>
                    </div>
                </div>
                <div class="col-lg-6 prmb2">
                    <div class="pr1">
                        <?php
                        echo WPBE_HELPER::draw_calendar("wpbe_filter_{$ff}_to", sprintf(esc_html__('%s to', 'bulk-editor'), $fields[$ff]['title']), $ff . '_to', '', "wpbe_filter[{$ff}_to]", true);
                        ?>
                    </div>
                </div>

            <?php endif; ?>



        <?php endforeach; ?>


        <div style="clear: both;"></div>
        <?php
        $filter_keys = apply_filters('wpbe_filter_other', array());
        if (!empty($filter_keys)) {
            $padding = 'right';
            foreach ($filter_keys as $key => $item) {
                ?>
                <div class="col-lg-6 prmb2">
                    <div class="wpbe_filter_other_item" style="padding-<?php echo $padding ?>: 1px !important;">
                        <?php
                        echo WPBE_HELPER::draw_select(array(
                            'options' => array(
                                '' => $item['title'],
                                '1' => esc_html__('Yes', 'bulk-editor'), //true
                                'zero' => esc_html__('No', 'bulk-editor'), //false
                            ),
                            'field' => $key,
                            'post_id' => 0,
                            'class' => 'wpbe_filter_select',
                            'name' => 'wpbe_filter[' . $key . ']'
                        ));
                        ?>
                    </div>
                </div>
                <?php
                if ($padding == 'right') {
                    $padding = 'left';
                } else {
                    $padding = 'right';
                }
            }
        }
        ?>
        <div class="clear"></div>
    </div>

    <?php
}
