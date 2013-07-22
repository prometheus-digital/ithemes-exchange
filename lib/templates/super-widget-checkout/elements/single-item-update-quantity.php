<?php if ( it_exchange_get_global( 'can_edit_purchase_quantity' ) ) : ?>
	<?php do_action( 'it_exchange_super_widget_checkout_before_single_item_update_quantity_action' ); ?>
	<div class="cart-action update-quantity">
		<?php it_exchange( 'checkout', 'cancel', array( 'class' => 'sw-cart-focus-quantity', 'focus' => 'quantity', 'label' => __( 'Quantity', 'LION' ) ) ); ?>
	</div>
	<?php do_action( 'it_exchange_super_widget_checkout_after_single_item_update_quantity_action' ); ?>
<?php endif; ?>
