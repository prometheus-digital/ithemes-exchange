<?php
/**
 * This is the default template for the Quantity
 * cart item element in the content-checkout.php
 * template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-checkout/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_checkout_before_item_quantity_element' ); ?>
<div class="it-exchange-cart-item-quantity it-exchange-table-column">
	<?php do_action( 'it_exchange_content_checkout_begin_item_quantity_element_value' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php it_exchange( 'cart-item', 'quantity', array( 'format' => 'var_value' ) ) ?>
	</div>
	<?php do_action( 'it_exchange_content_checkout_end_item_quantity_element_value' ); ?>
</div>
<?php do_action( 'it_exchange_content_checkout_after_item_quantity_element' ); ?>