<?php do_action( 'it_exchange_super_widget_product_before_purchase_options' ); ?>
<div class="purchase-options">
	<?php do_action( 'it_exchange_super_widget_product_begin_purchase_options' ); ?>
	<?php it_exchange( 'product', 'purchase-options', array( 'add-to-cart-edit-quantity' => false, 'buy-now-edit-quantity' => false ) ); ?>
	<?php do_action( 'it_exchange_super_widget_product_end_purchase_options' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_product_after_purchase_options' ); ?>
