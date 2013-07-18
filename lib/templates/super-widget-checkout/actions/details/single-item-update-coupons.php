<?php if ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) || it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) : ?>
	<?php do_action( 'it_exchange_super_widget_checkout_before_single_item_update_coupons_action' ); ?>
	<div class="cart-action add-coupon">
		<?php it_exchange( 'checkout', 'cancel', array( 'class' => 'sw-cart-focus-coupon', 'focus' => 'coupon', 'label' => __( 'Coupons', 'LION' ) ) ); ?>
	</div>
	<?php do_action( 'it_exchange_super_widget_checkout_after_single_item_update_coupons_action' ); ?>
<?php endif; ?>
