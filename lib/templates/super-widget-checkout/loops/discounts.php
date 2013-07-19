<?php do_action( 'it_exchange_super_widget_checkout_before_cart_discounts' ); ?>
<div class="cart-discount">
	<?php do_action( 'it_exchange_super_widget_checkout_begin_cart_discounts' ); ?>
	<?php while( it_exchange( 'coupons', 'applied', array( 'type' => 'cart' ) ) ) : ?>
		<?php it_exchange( 'coupons', 'discount-label' ); ?> <?php _e( 'OFF', 'LION' ); ?> &#61; <?php it_exchange( 'cart', 'total' ); ?>
	<?php endwhile; ?>
	<?php do_action( 'it_exchange_super_widget_checkout_end_cart_discounts' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_checkout_after_cart_discounts' ); ?>
