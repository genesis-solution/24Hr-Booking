<?php
if (!defined('ABSPATH'))
    wp_die('No direct access allowed');
?>

<?php if (!empty($history)): ?>
    <ul class="wpbe_fields" id="wpbe_history_list">
        <?php foreach ($history as $operation) : ?>
            <?php $is_solo = isset($operation['field_key']) ? true : false; ?>

            <li class="wpbe_options_li <?php echo($is_solo ? 'solo_li' : 'bulk_li') ?> wpbe_history_item wpbe_history_li_show" id="wpbe_history_<?php echo($is_solo ? $operation['id'] : $operation['bulk_key']) ?>">
                <?php
                $filds = "";
                if ($is_solo) {
                    $filds = $operation['field_key'];
                } else {
                    if (!empty($operation['set_of_keys'])) {
                        $keys = json_decode($operation['set_of_keys']);
                        if ($keys) {
                            $filds = implode(",", $keys);
                        }
                    }
                }
                ?>
                <div class="wpbe_history_data wpbe_history_hidden" data-types="<?php echo($is_solo ? 1 : 2) ?>" data-author="<?php esc_html_e($operation['user_id']) ?>"  data-date="<?php echo esc_html_e(($is_solo) ? $operation["mod_date"] : $operation['started']); ?>" data-fields="<?php esc_html_e($filds) ?>">
                </div>   
                <div class="col-lg-4">
                    <?php if ($is_solo): ?>
                        <h5><?php echo '#' . $operation['post_id'] . '. ' . get_the_title($operation['post_id']) ?></h5>
                        <h6>[<?php echo $operation['field_key'] ?>]</h6>
                    <?php else: ?>
                        <h5><?php esc_html_e('Bulk operation', 'bulk-editor') ?></h5>
                        [<span style="color: <?php echo($operation['state'] == 'completed' ? 'green' : 'red') ?>;"><small><?php printf(esc_html__('state: %s', 'bulk-editor'), $operation['state']) ?></small></span>]
                    <?php endif; ?>
                </div>

                <div class="col-lg-3">
                    <small>
                        <?php
                        if ($is_solo) {
                            echo date(get_option('date_format') . ' ' . get_option('time_format'), $operation['mod_date']);
                        } else {
                            echo date(get_option('date_format') . ' ' . get_option('time_format'), $operation['started']);
                            if ($operation['state'] !== 'terminated') {
                                echo ' - ' . date(get_option('date_format') . ' ' . get_option('time_format'), $operation['finished']);
                            }
                        }
                        ?>
                    </small>
                </div>


                <div class="col-lg-3">
                    <small>
                        <?php
                        if ($is_solo AND isset($settings_fields[$operation['field_key']]['edit_view'])) {
                            $show_val = in_array($settings_fields[$operation['field_key']]['edit_view'], array('textinput'));
                            $sanitize = '';
                            if (isset($settings_fields[$operation['field_key']]['sanitize'])) {
                                $sanitize = $settings_fields[$operation['field_key']]['sanitize'];
                            }
                            if ($show_val) {
                                printf(esc_html__('value been: %s', 'bulk-editor'), '<i>' . substr($posts_obj->__sanitize_answer_value($operation['field_key'], $sanitize, $operation['prev_val']), 0, 120) . '</i>');
                            } else {
                                echo '&nbsp;';
                            }
                        } else {

                            if (isset($operation['posts_count'])) {
                                printf(esc_html__('posts bulked: %s', 'bulk-editor'), '<b>' . intval($operation['posts_count']) . '</b>');
                            }

                            if (!empty($operation['set_of_keys'])) {
                                $set_of_keys = json_decode($operation['set_of_keys']);
                                $names = array();
                                foreach ($set_of_keys as $kk) {
                                    if (isset($settings_fields_full[$kk]['title'])) {
                                        $names[] = $settings_fields_full[$kk]['title'];
                                    }
                                }
                                $names = implode(', ', $names);
                                echo '<br />';
                                printf(esc_html__('columns: %s', 'bulk-editor'), '<b>' . $names . '</b>');
                            }
                        }
                        ?>
                    </small>
                </div>


                <div class="col-lg-2 wpbe-text-align-right">
                    <?php if ($is_solo): ?>
                        <a href="javascript: wpbe_history_revert_solo(<?php echo $operation['id'] ?>, <?php echo $operation['post_id'] ?>);void(0);" class="button button-primary wpbe_history_btn wpbe_history_revert" title="<?php esc_html_e('revert', 'bulk-editor') ?>"></a>
                        <a href="javascript: wpbe_history_delete_solo(<?php echo $operation['id'] ?>);void(0);" class="button button-primary wpbe_history_btn wpbe_history_delete" title="<?php esc_html_e('delete', 'bulk-editor') ?>"></a><br />
                    <?php else: ?>

                        <a href="javascript: wpbe_history_revert_bulk('<?php echo $operation['bulk_key'] ?>', <?php echo $operation['id'] ?>);void(0);" class="button button-primary wpbe_history_btn wpbe_history_revert" title="<?php esc_html_e('revert', 'bulk-editor') ?>"></a>
                        <a href="javascript: wpbe_history_delete_bulk('<?php echo $operation['bulk_key'] ?>');void(0);" class="button button-primary wpbe_history_btn wpbe_history_delete" title="<?php esc_html_e('delete', 'bulk-editor') ?>"></a><br />

                        <div class="wpbe_progress" style="display: none;">
                            <div class="wpbe_progress_in" id="wpbe_bulk_progress_<?php echo $operation['id'] ?>">0%</div>
                        </div>

                    <?php endif; ?>
                </div>

                <div class="clear"></div>

            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>

    <h5><?php esc_html_e('History is empty!', 'bulk-editor') ?></h5>

<?php endif; ?>
