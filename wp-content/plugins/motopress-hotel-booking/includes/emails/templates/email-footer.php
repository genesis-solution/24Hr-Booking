<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</div>
</td>
</tr>
</table>
<!-- End Content -->
</td>
</tr>
</table>
<!-- End Body -->
</td>
</tr>
<tr>
	<td align="center" valign="top">
		<!-- Footer -->
		<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
			<tr>
				<td valign="top">
					<table border="0" cellpadding="10" cellspacing="0" width="100%">
						<tr>
							<td colspan="2" valign="middle" id="credit">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo wpautop( wp_kses_post( wptexturize( $footerText ) ) );
								?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<!-- End Footer -->
	</td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</body>
</html>
