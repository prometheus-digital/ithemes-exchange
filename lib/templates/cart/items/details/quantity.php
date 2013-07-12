<?php
/**
 * This is the default template for the Quantity cart item details in the content-cart.php template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_cart_item_details_before_quantity' ); ?>
<div class="it-exchange-cart-item-quantity it-exchange-table-column">
	<?php do_action( 'it_exchange_cart_item_details_begin_quantity' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php it_exchange( 'cart-item', 'quantity' ) ?>
	</div>
	<?php do_action( 'it_exchange_cart_item_details_end_quantity' ); ?>
</div>
<?php do_action( 'it_exchange_cart_item_details_after_quantity' ); ?>
