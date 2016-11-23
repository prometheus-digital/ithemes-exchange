<?php
/**
 * This file contains the markup for the receipt email cart details.
 *
 * @since   2.0.0
 * @link    http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 *
 * Example: theme/exchange/emails/receipt/cart-details.php
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
		<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 640px; background: <?php it_exchange( 'email', 'body-background-color' ); ?>; margin: 0 auto;" class="wrapper body-bkg-color">
			<tr>
				<td valign="top" style="padding: 20px 25px; ">
					<table width="100%" style="line-height: 1.2;">
						<?php do_action( 'it_exchange_email_template_receipt_cart-details_before_header_row' ); ?>
						<tr>
							<th align="left" style="border-bottom: 1px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding: 0 0 10px 0;" class="border-highlight-color">
								<?php _e( 'Description', 'it-l10n-ithemes-exchange' ); ?>
							</th>
							<th align="center" style="border-bottom: 1px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding: 0 0 10px 0;" class="border-highlight-color">
								<?php _ex( 'Qty', 'Line Item Quantity', 'it-l10n-ithemes-exchange' ); ?>
							</th>
							<th align="right" style="border-bottom: 1px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding: 0 0 10px 0;" class="border-highlight-color">
								<?php _e( 'Amount', 'it-l10n-ithemes-exchange' ); ?>
							</th>
						</tr>
						<?php do_action( 'it_exchange_email_template_receipt_cart-details_after_header_row' ); ?>

						<?php do_action( 'it_exchange_email_template_receipt_cart-details_begin_line_items' ); ?>
						<?php while ( it_exchange( 'transaction', 'line-items' ) ): ?>
							<?php it_exchange_get_template_part( 'emails/receipt/line-item' ); ?>
							<?php if ( it_exchange( 'line-item', 'has-children' ) ) : ?>
								<?php while ( it_exchange( 'line-item', 'children' ) ) : ?>
									<?php it_exchange_get_template_part( 'emails/receipt/line-item-child' ); ?>
								<?php endwhile; ?>
							<?php endif; ?>
						<?php endwhile; ?>
						<?php do_action( 'it_exchange_email_template_receipt_cart-details_end_line_items' ); ?>
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
