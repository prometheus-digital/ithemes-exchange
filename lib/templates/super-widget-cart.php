<?php
/**
 * This file outputs the cart summary for the superwidget
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
			<?php if ( it_exchange( 'cart', 'supports-multiple-items' ) ) : ?>
				<div class="cart-item-remove"><?php it_exchange( 'cart-item', 'remove' ); ?></div>
			<?php endif; ?>
		<?php endwhile; ?>
	</div>
	<hr />
	<div class="it-exchange-cart-subtotal"><?php _e( 'Sub-total:', 'LION' ); ?> <?php it_exchange( 'cart', 'subtotal' ); ?></div>
	<div class="it-exchange-checkout-link"><?php it_exchange( 'cart', 'checkout' ); ?></div>
	<div class="it-exchnange-cancel"><?php it_exchange( 'cart', 'empty', 'format=link&label=' . __( 'Cancel', 'LION' ) ); ?></div>
<?php endif; ?>
