<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="popup_val_in_tbl wpbe-button" onclick="wpbe_multi_select_cell(this)">
    <ul>
        <?php if (!empty($selected_terms_ids)): ?>
            <?php foreach ($selected_terms_ids as $k => $term_id): ?>
                <li class="wpbe_li_tag"><?php echo $terms[$term_id] ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="wpbe_li_tag"><?php esc_html_e('no items', 'bulk-editor') ?></li>
            <?php endif; ?>
    </ul>
</div>
