<?php
/**
 * This is the default template for the Total detail in the totals loop of the content-cart.php template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_cart_totals_details_before_total' ); ?>
<div class="it-exchange-cart-totals-title it-exchange-cart-total it-exchange-table-column">
	<div class="it-exchange-table-column-inner">
		<?php _e( 'Total', 'LION' ); ?>
	</div>
</div>
<div class="it-exchange-cart-totals-title it-exchange-cart-total it-exchange-table-column">
	<div class="it-exchange-table-column-inner">
		<?php it_exchange( 'cart', 'total' ); ?>
	</div>
</div>
<?php do_action( 'it_exchange_content_cart_totals_details_after_total' ); ?>
