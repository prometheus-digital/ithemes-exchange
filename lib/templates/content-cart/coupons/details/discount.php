<?php
/**
 * This is the default template part for the coupon discount detail in the coupons loop of the content-cart template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_cart_coupon_details_before_discount' ); ?>
<div class="it-exchange-cart-coupon-discount it-exchange-table-column">
	<?php do_action( 'it_exchange_content_cart_coupon_details_begin_discount' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php it_exchange( 'coupons', 'discount' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_cart_coupon_details_end_discount' ); ?>
</div>
<?php do_action( 'it_exchange_content_cart_coupon_details_after_discount' ); ?>
