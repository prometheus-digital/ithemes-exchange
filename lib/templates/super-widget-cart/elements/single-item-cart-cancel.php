<?php do_action( 'it_exchange_super_widget_cart_actions_before_single_item_cancel_wrapper' ); ?>
<div class="cart-actions-wrapper">
	<?php do_action( 'it_exchange_super_widget_cart_actions_begin_single_item_cancel_wrapper' ); ?>
	<div class="cart-action cancel-update">
	<?php it_exchange( 'cart', 'checkout', array( 'class' => 'sw-cart-focus-checkout', 'focus' => 'checkout', 'label' =>  __( 'Cancel', 'LION' ) ) ); ?>
	</div>
	<?php do_action( 'it_exchange_super_widget_cart_actions_end_single_item_cancel_wrapper' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_cart_actions_after_single_item_cancel_wrapper' ); ?>
