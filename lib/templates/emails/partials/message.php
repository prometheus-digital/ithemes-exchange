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
 * Example: theme/exchange/emails/partial/message.php
 */
?>
<tr>
	<td align="center">
		<!--[if mso]>
		<center>
			<table>
				<tr>
					<td width="640">
		<![endif]-->
		<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 640px; background: <?php it_exchange( 'email', 'body-background-color' ); ?>;" class="wrapper body-bkg-color">
			<tr>
				<td valign="top" style="padding: 10px 25px;">
					<table width="100%">
						<tr>
							<td>
								<?php do_action( 'it_exchange_email_template_before_message' ); ?>
								<?php it_exchange( 'email', 'message' ); ?>
								<?php do_action( 'it_exchange_email_template_after_message' ); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<!--[if mso]>
		</td></tr></table>
		</center>
		<![endif]-->
	</td>
</tr>
