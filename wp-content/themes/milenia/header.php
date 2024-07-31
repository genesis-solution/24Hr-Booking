<?php
/**
* The main template file that is responsible to display the site header.
*
* @package WordPress
* @subpackage Milenia
* @since Milenia 1.0
*/

// Prevent the direct loading of the file
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__('You cannot access this file directly', 'milenia') );
}

global $Milenia, $MileniaLayout;

$MileniaHelper = $Milenia->helper();

$milenia_header_type = $Milenia->getThemeOption('milenia-header-type', 'milenia-header-layout-v1', array(
	'overriden_by' => 'milenia-page-header-type',
	'depend_on' => array('key' => 'milenia-page-header-state', 'value' => '0')
));
$milenia_current_page_type = $Milenia->getThemeOption('milenia-page-type', 'milenia-default', array(
	'overriden_by' => 'milenia-page-type-individual',
	'depend_on' => array('key' => 'milenia-page-settings-inherit-individual', 'value' => '0')
));
$milenia_current_page_style = $Milenia->getThemeOption('milenia-page-type-portfolio-gallery-page-style', 'milenia-grid');

$milenia_container_classes = array('container');
$milenia_favicon = $Milenia->getThemeOption('milenia-favicon', null);
$milenia_apple_touch_icon = $Milenia->getThemeOption('milenia-apple-touch-icon', null);
$milenia_apple_touch_icon_57x57 = $Milenia->getThemeOption('milenia-apple-touch-icon-57x57', null);
$milenia_apple_touch_icon_72x72 = $Milenia->getThemeOption('milenia-apple-touch-icon-72x72', null);
$milenia_apple_touch_icon_76x76 = $Milenia->getThemeOption('milenia-apple-touch-icon-76x76', null);
$milenia_apple_touch_icon_114x114 = $Milenia->getThemeOption('milenia-apple-touch-icon-114x114', null);
$milenia_apple_touch_icon_120x120 = $Milenia->getThemeOption('milenia-apple-touch-icon-120x120', null);
$milenia_apple_touch_icon_144x144 = $Milenia->getThemeOption('milenia-apple-touch-icon-144x144', null);
$milenia_apple_touch_icon_152x152 = $Milenia->getThemeOption('milenia-apple-touch-icon-152x152', null);
$milenia_apple_touch_icon_180x180 = $Milenia->getThemeOption('milenia-apple-touch-icon-180x180', null);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>

	<!-- Basic Page Needs
    ==================================================== -->
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->

	<!-- Mobile Specific Metas
	==================================================== -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<link rel="profile" href="http://gmpg.org/xfn/11" />

	<?php if ( is_singular() && pings_open() ) : ?>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>


	<?php if(is_array($milenia_favicon) && isset($milenia_favicon['url']) && !empty($milenia_favicon['url'])) : ?>
	<!-- Favicon
	==================================================== -->
	<link rel="shortcut icon" href="<?php echo esc_url($milenia_favicon['url']); ?>">
	<?php endif; ?>

	<!-- Apple touch icons
	============================================ -->
	<?php if(is_array($milenia_apple_touch_icon) && isset($milenia_apple_touch_icon['url']) && !empty($milenia_apple_touch_icon['url'])) : ?>
	<link rel="apple-touch-icon" href="<?php echo esc_url($milenia_apple_touch_icon['url']); ?>">
	<?php endif; ?>
	<?php if(is_array($milenia_apple_touch_icon_57x57) && isset($milenia_apple_touch_icon_57x57['url']) && !empty($milenia_apple_touch_icon_57x57['url'])) : ?>
	<link rel="apple-touch-icon" sizes="57x57" href="<?php echo esc_url($milenia_apple_touch_icon_57x57['url']); ?>">
	<?php endif; ?>
	<?php if(is_array($milenia_apple_touch_icon_72x72) && isset($milenia_apple_touch_icon_72x72['url']) && !empty($milenia_apple_touch_icon_72x72['url'])) : ?>
	<link rel="apple-touch-icon" sizes="72x72" href="<?php echo esc_url($milenia_apple_touch_icon_72x72['url']); ?>">
	<?php endif; ?>
	<?php if(is_array($milenia_apple_touch_icon_76x76) && isset($milenia_apple_touch_icon_76x76['url']) && !empty($milenia_apple_touch_icon_76x76['url'])) : ?>
	<link rel="apple-touch-icon" sizes="76x76" href="<?php echo esc_url($milenia_apple_touch_icon_76x76['url']); ?>">
	<?php endif; ?>
	<?php if(is_array($milenia_apple_touch_icon_114x114) && isset($milenia_apple_touch_icon_114x114['url']) && !empty($milenia_apple_touch_icon_114x114['url'])) : ?>
	<link rel="apple-touch-icon" sizes="114x114" href="<?php echo esc_url($milenia_apple_touch_icon_114x114['url']); ?>">
	<?php endif; ?>
	<?php if(is_array($milenia_apple_touch_icon_120x120) && isset($milenia_apple_touch_icon_120x120['url']) && !empty($milenia_apple_touch_icon_120x120['url'])) : ?>
	<link rel="apple-touch-icon" sizes="120x120" href="<?php echo esc_url($milenia_apple_touch_icon_120x120['url']); ?>">
	<?php endif; ?>
	<?php if(is_array($milenia_apple_touch_icon_144x144) && isset($milenia_apple_touch_icon_144x144['url']) && !empty($milenia_apple_touch_icon_144x144['url'])) : ?>
	<link rel="apple-touch-icon" sizes="144x144" href="<?php echo esc_url($milenia_apple_touch_icon_144x144['url']); ?>">
	<?php endif; ?>
	<?php if(is_array($milenia_apple_touch_icon_152x152) && isset($milenia_apple_touch_icon_152x152['url']) && !empty($milenia_apple_touch_icon_152x152['url'])) : ?>
	<link rel="apple-touch-icon" sizes="152x152" href="<?php echo esc_url($milenia_apple_touch_icon_152x152['url']); ?>">
	<?php endif; ?>
	<?php if(is_array($milenia_apple_touch_icon_180x180) && isset($milenia_apple_touch_icon_180x180['url']) && !empty($milenia_apple_touch_icon_180x180['url'])) : ?>
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url($milenia_apple_touch_icon_180x180['url']); ?>">
	<?php endif; ?>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
/**
 * Hook for the prepend some content to the body.
 *
 * @hooked
 */
do_action( 'milenia_body_prepend' );
?>

	<!--================ Page Wrapper ================-->
	<div id="milenia-page-wrapper" class="milenia-page-wrapper">

		<?php get_template_part(sprintf('template-parts/header/%s', preg_replace('/milenia-/', '', $MileniaLayout->getHeaderType()))); ?>

		<?php get_template_part('template-parts/breadcrumb'); ?>

		<div class="milenia-content <?php echo esc_attr($MileniaLayout->getSidebarState()); ?>" style="<?php echo esc_attr(implode(' ', $MileniaLayout->verticalContentPadding())); ?>">
    		<div class="<?php echo esc_attr($MileniaHelper->getSanitizedHtmlClasses($milenia_container_classes)); ?>">
