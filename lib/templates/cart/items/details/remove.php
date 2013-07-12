<?php
/**
 * The main template file for the Remove Link in the cart-items loop for content-cart template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_cart_item_details_before_remove' ); ?>
<div class="it-exchange-cart-item-remove it-exchange-table-column cart-remove">
	<?php do_action( 'it_exchange_cart_item_details_begin_remove' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php it_exchange( 'cart-item', 'remove' ) ?>
	</div>
	<?php do_action( 'it_exchange_cart_item_details_end_remove' ); ?>
</div>
<?php do_action( 'it_exchange_cart_item_details_after_remove' ); ?>
