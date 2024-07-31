<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (empty($text)) {
    $text = esc_html__('Not allowed!', 'bulk-editor');
}
?>

<a class="info_helper info_restricked" data-balloon-length="medium" data-balloon-pos="<?php esc_html_e($direction) ?>" data-balloon="<?php esc_html_e($text) ?>"><img src="<?php echo WPBE_ASSETS_LINK . 'images/restricted.png' ?>" width="25" alt="" /></a>

