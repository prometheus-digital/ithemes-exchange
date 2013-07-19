<?php do_action( 'it_exchange_super_widget_cart_actions_before_apply_coupon' ); ?>
<div class="coupon">
	<?php do_action( 'it_exchange_super_widget_cart_actions_begin_apply_coupon' ); ?>
	<?php it_exchange( 'coupons', 'apply', array( 'type' => 'cart' ) ); ?>
	<?php it_exchange( 'cart', 'update', array( 'class' => 'it-exchange-apply-coupon-button', 'label' => __( 'Apply', 'LION' ) ) ); ?>
	<?php do_action( 'it_exchange_super_widget_cart_actions_end_apply_coupon' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_cart_actions_after_apply_coupon' ); ?>
