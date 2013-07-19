<?php do_action( 'it_exchange_super_widget_cart_quantity_before_wrapper' ); ?>
<div class="quantity-wrapper">
	<?php do_action( 'it_exchange_super_widget_cart_quantity_begin_wrapper' ); ?>
	<div class="quantity">
		<?php it_exchange( 'cart', 'update', 'class=it-exchange-update-quantity-button&label=' . __( 'Update Quantity', 'LION' ) ); ?>
	</div>

	<?php
	// Include the single-item-cart quantity actions template part
	it_exchange_get_template_part( 'super-widget-cart/elements/single-item-cart-cancel' );
	?>

	<?php do_action( 'it_exchange_super_widget_cart_quantity_end_wrapper' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_cart_quantity_after_wrapper' ); ?>
