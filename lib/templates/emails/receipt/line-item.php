<?php
/**
 * This file contains the markup for the receipt email line item.
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
