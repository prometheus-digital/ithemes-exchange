<?php
/**
 * This is the default template part for the coupon details loop in the content-cart template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_cart_coupons_before_loop' ); ?>
<?php while ( it_exchange( 'coupons', 'applied', array( 'type' => 'cart' ) ) ) : ?>
	<?php do_action( 'it_exchange_content_cart_coupons_begin_loop' ); ?>
	<div class="it-exchange-table-row">
		<?php foreach ( it_exchange_get_content_cart_coupon_details() as $detail ) : ?>
			<?php
            /** 
             * Theme and add-on devs should add code to this loop by 
             * hooking into it_exchange_get_content_cart_coupon_details filter
             * and adding the appropriate template file to their theme or add-on
             */
			it_exchange_get_template_part( 'content-cart/coupons/details/' . $detail ); ?>
		<?php endforeach; ?>
	</div>
	<?php do_action( 'it_exchange_content_cart_coupons_end_loop' ); ?>
<?php endwhile; ?>
<?php do_action( 'it_exchange_content_cart_coupons_after_loop' ); ?>
