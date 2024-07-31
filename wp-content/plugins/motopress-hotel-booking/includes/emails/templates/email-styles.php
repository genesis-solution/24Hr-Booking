<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bg        = MPHB()->settings()->emails()->getBGColor();
$body      = MPHB()->settings()->emails()->getBodyBGColor();
$base      = MPHB()->settings()->emails()->getBaseColor();
$base_text = \MPHB\Utils\ColorUtils::lightOrDark( $base, '#202020', '#ffffff' );
$text      = MPHB()->settings()->emails()->getBodyTextColor();

$bg_darker_10    = \MPHB\Utils\ColorUtils::hexDarker( $bg, 10 );
$body_darker_10  = \MPHB\Utils\ColorUtils::hexDarker( $body, 10 );
$base_lighter_20 = \MPHB\Utils\ColorUtils::hexLighter( $base, 20 );
$base_lighter_40 = \MPHB\Utils\ColorUtils::hexLighter( $base, 40 );
$text_lighter_20 = \MPHB\Utils\ColorUtils::hexLighter( $text, 20 );
?>
#wrapper {
background-color: <?php echo esc_attr( $bg ); ?>;
margin: 0;
padding: 70px 0 70px 0;
-webkit-text-size-adjust: none !important;
width: 100%;
}

#template_container {
box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important;
background-color: <?php echo esc_attr( $body ); ?>;
border: 1px solid <?php echo esc_attr( $bg_darker_10 ); ?>;
border-radius: 3px !important;
}

#template_header {
background-color: <?php echo esc_attr( $base ); ?>;
border-radius: 3px 3px 0 0 !important;
color: <?php echo esc_attr( $base_text ); ?>;
border-bottom: 0;
font-weight: bold;
line-height: 100%;
vertical-align: middle;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#template_header h1 {
color: <?php echo esc_attr( $base_text ); ?>;
}

#template_footer td {
padding: 0;
-webkit-border-radius: 6px;
}

#template_footer #credit {
border:0;
color: <?php echo esc_attr( $base_lighter_40 ); ?>;
font-family: Arial;
font-size:12px;
line-height:125%;
text-align:center;
padding: 0 48px 48px 48px;
}

#body_content {
background-color: <?php echo esc_attr( $body ); ?>;
}

#body_content table td {
padding: 48px;
}

#body_content table td td {
padding: 12px;
}

#body_content table td th {
padding: 12px;
}

#body_content p {
margin: 0 0 16px;
}

#body_content_inner {
color: <?php echo esc_attr( $text_lighter_20 ); ?>;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: 14px;
line-height: 150%;
}

.td {
color: <?php echo esc_attr( $text_lighter_20 ); ?>;
border: 1px solid <?php echo esc_attr( $body_darker_10 ); ?>;
}

.text {
color: <?php echo esc_attr( $text ); ?>;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

.link {
color: <?php echo esc_attr( $base ); ?>;
}

#header_wrapper {
padding: 36px 48px;
display: block;
}

h1 {
color: <?php echo esc_attr( $base ); ?>;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: 30px;
font-weight: 300;
line-height: 150%;
margin: 0;
text-shadow: 0 1px 0 <?php echo esc_attr( $base_lighter_20 ); ?>;
-webkit-font-smoothing: antialiased;
}

h2 {
color: <?php echo esc_attr( $base ); ?>;
display: block;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: 18px;
font-weight: bold;
line-height: 130%;
margin: 16px 0 8px;
}

h3 {
color: <?php echo esc_attr( $base ); ?>;
display: block;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: 16px;
font-weight: bold;
line-height: 130%;
margin: 16px 0 8px;
}

a {
color: <?php echo esc_attr( $base ); ?>;
font-weight: normal;
text-decoration: underline;
}

img {
border: none;
display: inline;
font-size: 14px;
font-weight: bold;
height: auto;
line-height: 100%;
outline: none;
text-decoration: none;
text-transform: capitalize;
}

.mphb-price-breakdown{
	border-collapse: collapse;
	width: 100%;
	color: inherit;
}
.mphb-price-breakdown .mphb-price-breakdown-booking{
	font-weight: bold;
}
.mphb-price-breakdown th{
	font-weight: normal;
}
.mphb-price-breakdown tfoot th{
	font-weight: bold;
}
.mphb-price-breakdown th,
.mphb-price-breakdown td{
	color: inherit;
	text-align: left;
	border: 1px solid;
	padding: 12px 20px !important;
}
<?php
