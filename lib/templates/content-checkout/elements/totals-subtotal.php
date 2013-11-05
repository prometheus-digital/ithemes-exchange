<?php
/**
 * This is the default template for the Subtotal
 * element in the totals loop of the content-checkout
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

<?php do_action( 'it_exchange_content_checkout_before_subtotal_element' ); ?>
<div class="it-exchange-cart-totals-title it-exchange-table-column">
	<?php do_action( 'it_exchange_content_checkout_begin_subtotal_element_label' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php _e( 'Subtotal', 'LION' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_checkout_end_subtotal_element_label' ); ?>
</div>
<div class="it-exchange-cart-totals-amount it-exchange-table-column">
	<div class="it-exchange-table-column-inner">
		<?php it_exchange( 'cart', 'subtotal' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_checkout_end_subtotal_element_value' ); ?>
</div>
<?php do_action( 'it_exchange_content_checkout_totals_after_subtotal_element' ); ?>