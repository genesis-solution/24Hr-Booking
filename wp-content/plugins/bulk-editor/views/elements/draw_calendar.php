<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<input type="text" onmouseover="wpbe_init_calendar(this)" data-title="<?php esc_html_e(str_replace('"', '', strip_tags($post_title))) ?>" data-val-id="calendar_<?php echo $field_key ?>_<?php echo $post_id ?>" value="<?php if ($val) echo date('d/m/Y H:i', $val) ?>" class="wpbe_calendar" placeholder="<?php echo ($print_placeholder ? $post_title : '') ?>" />
<input type="hidden" data-key="<?php echo $field_key ?>" data-post-id="<?php esc_html_e($post_id) ?>" id="calendar_<?php echo $field_key ?>_<?php echo $post_id ?>" value="<?php echo $val ?>" name="<?php echo $name ?>" />
<a href="javascript: void(0);" class="wpbe_calendar_cell_clear" title="<?php esc_html_e('Will never reset published date, and for modified date field  will set current time!', 'bulk-editor') ?>"><?php esc_html_e('clear', 'bulk-editor') ?></a>
