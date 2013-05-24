<div class="it-exchange-order-total">
	<?php _e( 'Order Total:', 'LION' ); ?> <?php it_exchange( 'cart', 'total' ); ?>
	<br /><?php it_exchange( 'cart', 'empty', 'format=link&label=' . __( 'Cancel Purchase', 'LION' ) ); ?>
	<?php if ( it_exchange( 'coupons', 'accepting', 'type=cart' ) || it_exchange( 'coupons', 'has-applied', 'type=cart' ) ) : ?>
		<?php $label = (boolean) it_exchange( 'coupons', 'has-applied', 'type=cart' ) ? __( 'View Coupons', 'LION' ) : __( 'Add Coupon', 'LION' ); ?>
		<br /><?php it_exchange( 'checkout', 'cancel', 'class=sw-cart-focus-coupons&focus=coupon&label=' . $label ); ?>
	<?php endif; ?>
	<?php $label = it_exchange_is_multi_item_cart_allowed() ? __( 'View cart', 'LION' ) : __( 'Update Quantity', 'LION' ); ?>
	<br /><?php it_exchange( 'checkout', 'cancel', 'class=sw-cart-focus-quantity&focus=quantity&label=' . $label ); ?>
</div>
<div class="it-exchange-payment-methods">
    <?php if ( ! it_exchange( 'checkout', 'has-transaction-methods' ) ) : ?>
        <p><?php _e( 'No Payment add-ons enabled.', 'LION' ); ?></p>
    <?php else : ?>
        <?php while( it_exchange( 'checkout', 'transaction-methods' ) ) : ?>
            <?php it_exchange( 'transaction-method', 'make-payment' ); ?>
        <?php endwhile; ?>
    <?php endif; ?>
</div>
