<?php
/**
 * This file outputs the cart summary for the Cart Summary add-on / widget
 * @since 0.4.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) :  ?>  
	<div class="it-exchange-cart-items">
		<?php while( it_exchange( 'cart', 'cart-items' ) ) : ?>
			<div class="cart-item-title"><?php it_exchange( 'cart-item', 'title' ) ?></div>
			<div class="cart-item-quantity"><?php it_exchange( 'cart-item', 'quantity', 'format=var_value' ) ?></div>
			<div class="cart-item-subtotal"><?php it_exchange( 'cart-item', 'subtotal' ); ?></div>
		<?php endwhile; ?>
	</div>
	<div class="it-exchange-cart-total"><?php _e( 'Total:', 'LION' ); ?> <?php it_exchange( 'cart', 'total' ); ?></div>
	<?php if ( ! get_query_var( 'cart' ) && it_exchange_is_multi_item_cart_allowed() ) : ?>
		<div class="it-exchange-cart-link"><?php it_exchange( 'checkout', 'cancel', 'label=' . __( 'View cart', 'LION' ) ); ?></div>
	<?php endif; ?>
	<?php if ( ! get_query_var( 'checkout' ) && it_exchange_is_multi_item_cart_allowed() ) : ?>
		<div class="it-exchange-checkout-link"><?php it_exchange( 'cart', 'checkout', 'format=link' ); ?></div>
	<?php endif; ?>
<?php else : ?>
	<div class="it-exchange-no-items"><?php _e( 'Cart is empty', 'LION' ); ?></div>
<?php endif; ?>
