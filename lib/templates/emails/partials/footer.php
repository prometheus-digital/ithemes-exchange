<?php
/**
 * This file contains the markup for the email template footer.
 *
 * @since   1.36
 * @link    http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 *
 * Example: theme/exchange/emails/partial/footer.php
 */
?>
<!-- begin footer -->
<tr>
	<td align="center">
		<table id="footer" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 640px; padding-top: 20px;" class="wrapper">
			<tr>
				<td valign="top" align="center" style="padding: 10px 25px 100px 25px; ">
					<table width="100%">
						<tr style="text-align: center;">
							<td style="color: <?php it_exchange( 'email', 'footer-text-color' ); ?>;" class="footer-text-container">
								<?php it_exchange( 'email', 'footer-text' ); ?>
							</td>
						</tr>
						<tr class="footer-logo-container" style="text-align: center">
							<td>
								<?php if ( it_exchange( 'email', 'has-footer-logo' ) ): ?>
									<img src="<?php it_exchange( 'email', 'footer-logo' ); ?>" width="<?php it_exchange( 'email', 'footer-logo-size' ); ?>" style="margin-top: 40px;" />
								<?php endif; ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>
<!-- end footer -->