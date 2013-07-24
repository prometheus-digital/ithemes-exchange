<?php
/**
 * This is the default template part for the
 * coupon discount element in the coupons loop of
 * the content-checkout template part.
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

<?php do_action( 'it_exchange_content_checkout_coupon_before_discount_element' ); ?>
<div class="it-exchange-cart-coupon-discount it-exchange-table-column">
	<?php do_action( 'it_exchange_content_checkout_coupon_begin_discount_element' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php it_exchange( 'coupons', 'discount' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_checkout_coupon_end_discount_element' ); ?>
</div>
<?php do_action( 'it_exchange_content_checkout_coupon_after_discount_element' ); ?>