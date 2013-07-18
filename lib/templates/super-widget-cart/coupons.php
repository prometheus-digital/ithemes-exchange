<?php do_action( 'it_exchange_super_widget_cart_coupons_before_wrapper' ); ?>
<div class="coupons-wrapper">
	<?php
	do_action( 'it_exchange_super_widget_cart_coupons_begin_wrapper' );

	// Include applied coupons loop if any exist
	if ( it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) )
		it_exchange_get_template_part( 'super-widget-cart/applied-coupons/loop' );

	// If accepting coupons, include template part
	if ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) )
		it_exchange_get_template_part( 'super-widget-cart/actions/apply-coupon' );

	// Include the single-item-cart actions template part
	it_exchange_get_template_part( 'super-widget-cart/actions/single-item-cart-cancel' );

	do_action( 'it_exchange_super_widget_cart_coupons_end_wrapper' );
	?>
</div>
<?php do_action( 'it_exchange_super_widget_cart_coupons_after_wrapper' ); ?>
