<?php
/**
 * This is the default template part for the
 * apply_coupon action in the content-cart template
 * part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-cart/actions/ directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_cart_actions_before_apply_coupon' ); ?>
<?php if ( it_exchange( 'coupons', 'supported', array( 'type' => 'cart' ) ) && it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) ) : ?>
	<?php do_action( 'it_exchange_content_cart_actions_begin_apply_coupon' ); ?>
	<div class="it-exchange-cart-apply-coupons">
		<?php it_exchange( 'coupons', 'apply', array( 'type' => 'cart' ) ); ?>
		<?php it_exchange( 'cart', 'update', array( 'label' => __( 'Apply Coupon', 'LION' ) ) ); ?>
	</div>
	<?php do_action( 'it_exchange_content_cart_actions_end_apply_coupon' ); ?>
<?php endif; ?>
<?php do_action( 'it_exchange_content_cart_actions_after_apply_coupon' ); ?>