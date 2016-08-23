<?php
/**
 * This file contains the markup for the receipt email cart details.
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
								<?php _e( 'Price', 'it-l10n-ithemes-exchange' ); ?>
							</th>
						</tr>
						<?php do_action( 'it_exchange_email_template_receipt_cart-details_after_header_row' ); ?>

						<?php do_action( 'it_exchange_email_template_receipt_cart-details_begin_products' ); ?>
						<?php while ( it_exchange( 'transaction', 'line-items' ) ): ?>
							<tr>
								<td align="left" style="border-bottom: 1px <?php echo it_exchange( 'line-item', 'has-children' ) ? 'dashed' : 'solid'; ?> <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding: 10px 0;" class="border-highlight-color">
									<table>
										<tr>
											<?php if ( it_exchange( 'transaction', 'has-featured-image' ) ): ?>
												<td>
													<img src="<?php it_exchange( 'transaction', 'featured-image', 'format=url&size=thumbnail' ); ?>" width="80" style="margin-right: 20px;" />
												</td>
											<?php endif; ?>
											<td>
												<?php do_action( 'it_exchange_email_template_receipt_cart-details_before_product_name' ); ?>
												<strong><?php it_exchange( 'line-item', 'name' ); ?></strong><br>
												<?php do_action( 'it_exchange_email_template_receipt_cart-details_after_product_name' ); ?>
												<?php it_exchange( 'transaction', 'variants' ); ?>

												<?php if ( it_exchange( 'line-item', 'has-description' ) ): ?>
													<p style="border-left: 4px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding-left: 10px; max-width: 300px; font-size: .9em" class="border-highlight-color">
														<?php it_exchange( 'line-item', 'description' ); ?>
													</p>
												<?php endif; ?>

												<?php if ( it_exchange( 'transaction', 'has-purchase-message' ) ): ?>
													<p style="border-left: 4px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding-left: 10px; max-width: 300px; font-size: .9em" class="border-highlight-color">
														<?php it_exchange( 'transaction', 'purchase-message' ); ?>
													</p>
												<?php endif; ?>
											</td>
										</tr>
									</table>
								</td>
								<td align="center" style="border-bottom: 1px <?php echo it_exchange( 'line-item', 'has-children' ) ? 'dashed' : 'solid'; ?> <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding: 10px 0;" class="border-highlight-color">
									<?php if ( it_exchange( 'line-item', 'supports-quantity' ) ) : ?>
										<?php it_exchange( 'line-item', 'quantity', 'format=var_value' ); ?>
									<?php else: ?>
										&nbsp;
									<?php endif; ?>
								</td>
								<td align="right" style="border-bottom: 1px <?php echo it_exchange( 'line-item', 'has-children' ) ? 'dashed' : 'solid'; ?> <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding: 10px 0;" class="border-highlight-color">
									<?php it_exchange( 'line-item', 'total' ); ?>
								</td>
							</tr>
							<?php if ( it_exchange( 'line-item', 'has-children' ) ) : ?>
								<?php while ( it_exchange( 'line-item', 'children' ) ) : ?>
									<tr>
										<td align="left" style="border-bottom: 1px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding: 10px 0;" class="border-highlight-color">
											<table>
												<tr>
													<td style="font-size: 80%;">
														<?php do_action( 'it_exchange_email_template_receipt_cart-details_before_product_name' ); ?>
														&ndash; <?php it_exchange( 'line-item', 'name' ); ?><br>
														<?php do_action( 'it_exchange_email_template_receipt_cart-details_after_product_name' ); ?>

														<?php if ( it_exchange( 'line-item', 'has-description' ) ): ?>
															<p style="border-left: 4px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding-left: 10px; max-width: 300px; font-size: .9em" class="border-highlight-color">
																<?php it_exchange( 'line-item', 'description' ); ?>
															</p>
														<?php endif; ?>

														<?php if ( it_exchange( 'transaction', 'has-purchase-message' ) ): ?>
															<p style="border-left: 4px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding-left: 10px; max-width: 300px; font-size: .9em" class="border-highlight-color">
																<?php it_exchange( 'transaction', 'purchase-message' ); ?>
															</p>
														<?php endif; ?>
													</td>
												</tr>
											</table>
										</td>
										<td align="center" style="border-bottom: 1px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding: 10px 0; font-size: 80%;" class="border-highlight-color">
											<?php if ( it_exchange( 'line-item', 'supports-quantity' ) ) : ?>
												<?php it_exchange( 'line-item', 'quantity', 'format=var_value' ); ?>
											<?php else: ?>
												&nbsp;
											<?php endif; ?>
										</td>
										<td align="right" style="border-bottom: 1px solid <?php it_exchange( 'email', 'body-highlight-color' ); ?>; padding: 10px 0; font-size: 80%;" class="border-highlight-color">
											(<?php it_exchange( 'line-item', 'total' ); ?>)
										</td>
									</tr>
								<?php endwhile; ?>
							<?php endif; ?>
						<?php endwhile; ?>
						<?php do_action( 'it_exchange_email_template_receipt_cart-details_end_products' ); ?>
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
