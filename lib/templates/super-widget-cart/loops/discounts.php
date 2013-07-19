<?php do_action( 'it_exchange_super_widget_cart_before_cart_discounts' ); ?>
<?php while( it_exchange( 'coupons', 'applied', array( 'type' => 'cart' ) ) ) : ?>
	<?php it_exchange( 'coupons', 'discount-label' ); ?> <?php _e( 'OFF', 'LION' ); ?> &#61; <?php it_exchange( 'cart', 'total' ); ?>
<?php endwhile; ?>
<?php do_action( 'it_exchange_super_widget_cart_after_cart_discounts' ); ?>
