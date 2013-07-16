<?php do_action( 'it_exchange_super_widget_checkout_before_multi_item_cancel_action' ); ?>
<div class="cart-action view-cart">
	<?php it_exchange( 'checkout', 'cancel', array( 'label' => __( 'View Cart', 'LION' ) ) ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_checkout_after_multi_item_cancel_action' ); ?>
