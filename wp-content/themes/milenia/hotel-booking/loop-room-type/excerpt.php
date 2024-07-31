<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php do_action( 'mphb_render_loop_room_type_before_excerpt' ); ?>

<?php

$excerpt = get_the_excerpt();
$charlength = apply_filters('milenia_rooty_excerpt_length', 200);
$output = '';
$html = '';

if ( mb_strlen( $excerpt ) > $charlength ) {
	$subex = mb_substr( $excerpt, 0, $charlength - 5 );
	$exwords = explode( ' ', $subex );
	$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) ) - 1;
	if ( $excut < 0 ) {
		$output .= mb_substr( $subex, 0, $excut );
	} else {
		$output .= $subex;
	}
	$output .= '...';
} else {
	$output .= $excerpt;
}

$html .= '<div class="milenia-get-excerpt">' . $output . '</div>';
echo wp_kses_post($html);

?>

<?php do_action( 'mphb_render_loop_room_type_after_excerpt' ); ?>
