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

$layout      = it_exchange( 'email', 'get-layout' );
$row_bkg     = $layout == 'boxed' ? 'none' : it_exchange( 'email', 'get-footer-background' );
$table_bkg   = $layout == 'full' ? 'none' : it_exchange( 'email', 'get-footer-background' );
$row_class   = $layout == 'boxed' ? '' : 'footer-bkg';
$table_class = $layout == 'full' ? '' : 'footer-bkg';
?>
<tr><td style="height: 40px;"></td></tr>
<!-- begin footer -->
<tr style="background: <?php echo $row_bkg; ?>;" class="footer-row <?php echo $row_class; ?>">
	<td align="center">
		<table id="footer" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 640px; padding-top: 20px; background: <?php echo $table_bkg; ?>;" class="<?php echo $table_class; ?>">
			<tr>
				<td valign="top" align="center" style="padding: 10px 25px 100px 25px; ">
					<table width="100%">
						<?php do_action( 'it_exchange_email_template_before_footer_text_row' ); ?>
						<tr style="text-align: center;">
							<td style="color: <?php it_exchange( 'email', 'footer-text-color' ); ?>;" class="footer-text-container">
								<?php if ( it_exchange( 'email', 'has-footer-text' ) ): ?>
									<?php do_action( 'it_exchange_email_template_before_footer_text' ); ?>
									<?php it_exchange( 'email', 'footer-text' ); ?>
									<?php do_action( 'it_exchange_email_template_after_footer_text' ); ?>
								<?php endif; ?>
							</td>
						</tr>
						<?php do_action( 'it_exchange_email_template_after_footer_text_row' ); ?>

						<?php do_action( 'it_exchange_email_template_before_footer_logo_row' ); ?>
						<tr class="footer-logo-container" style="text-align: center">
							<?php do_action( 'it_exchange_email_template_before_footer_logo_container' ); ?>
							<td>
								<?php if ( it_exchange( 'email', 'has-footer-logo' ) ): ?>
									<?php do_action( 'it_exchange_email_template_before_footer_logo' ); ?>
									<img src="<?php it_exchange( 'email', 'footer-logo' ); ?>" width="<?php it_exchange( 'email', 'footer-logo-size' ); ?>" style="margin-top: 40px;" />
									<?php do_action( 'it_exchange_email_template_after_footer_logo' ); ?>
								<?php endif; ?>
							</td>
							<?php do_action( 'it_exchange_email_template_after_footer_logo_container' ); ?>
						</tr>
						<?php do_action( 'it_exchange_email_template_after_footer_logo_row' ); ?>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>
<!-- end footer -->