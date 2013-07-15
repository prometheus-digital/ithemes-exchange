<?php
/**
 * This is the default template part for the apply_coupon action in the content-cart template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_cart_actions_before_apply_coupon' ); ?>
<?php if ( it_exchange( 'coupons', 'supported', 'type=cart' ) && it_exchange( 'coupons', 'accepting', 'type=cart' ) ) : ?>
	<?php do_action( 'it_exchange_content_cart_actions_begin_apply_coupon' ); ?>
	<div class="cart-apply-coupons">
		<?php it_exchange( 'coupons', 'apply', 'type=cart' ); ?>
		<?php it_exchange( 'cart', 'update', 'label=' . __( 'Apply Coupon', 'LION' ) ); ?>
	</div>
	<?php do_action( 'it_exchange_content_cart_actions_end_apply_coupon' ); ?>
<?php endif; ?>
<?php do_action( 'it_exchange_content_cart_actions_after_apply_coupon' ); ?>
