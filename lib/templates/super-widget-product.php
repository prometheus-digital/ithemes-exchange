<?php
/**
 * The product template for the Super Widget.
 * 
 * @since 0.4.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 * 
 * Example: theme/exchange/super-widget-product.php
*/
?>

<?php it_exchange_get_template_part( 'messages' ); ?>

<?php if ( it_exchange( 'product', 'found' ) ) : ?>
	<div class="it-exchange-sw-product it-exchange-sw-processing-product">
		<?php if ( it_exchange_is_multi_item_cart_allowed() ) : ?>
			<?php it_exchange_get_template_part( 'super-widget', 'cart' ); ?>
		<?php endif; ?>
		
		<?php if ( it_exchange_is_page( 'product' ) && ( !it_exchange_is_multi_item_cart_allowed() || !it_exchange_is_current_product_in_cart() ) ) : ?>
			<div class="purchase-options">
				<?php it_exchange( 'product', 'purchase-options', array( 'add-to-cart-edit-quantity' => false, 'buy-now-edit-quantity' => false ) ); ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
