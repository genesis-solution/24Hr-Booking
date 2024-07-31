<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
		<title>
		<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo get_bloginfo( 'name', 'display' );
		?>
			</title>
		<style>
			<?php do_action( 'mphb_email_head_styles' ); ?>
			<?php do_action( "mphb_{$templateId}_email_head_styles" ); ?>
		</style>
	</head>
	<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<div id="wrapper">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
				<tr>
					<td align="center" valign="top">
						<?php if ( MPHB()->settings()->emails()->hasLogo() ) : ?>
							<div id="template_header_image">
								<p style="margin-top:0;">
									<?php
									echo '<img src="' . esc_url( MPHB()->settings()->emails()->getLogoUrl() ) . '" alt="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" />';
									?>
								</p>
							</div>
						<?php endif; ?>
						<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
							<tr>
								<td align="center" valign="top">
									<!-- Header -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header">
										<tr>
											<td id="header_wrapper">
												<h1>
												<?php
													// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
													echo $headerText;
												?></h1>
											</td>
										</tr>
									</table>
									<!-- End Header -->
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<!-- Body -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
										<tr>
											<td valign="top" id="body_content">
												<!-- Content -->
												<table border="0" cellpadding="20" cellspacing="0" width="100%">
													<tr>
														<td valign="top">
															<div id="body_content_inner">

