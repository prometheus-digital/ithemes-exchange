<?php
/**
 * This is the default template for the
 * super-widget-cart single-item-cart-cancel element.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-cart/elements directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_super_widget_cart_before_single_item_cancel_element' ); ?>
<?php do_action( 'it_exchange_super_widget_cart_before_single_item_cancel_wrap' ); ?>
<div class="cart-actions-wrapper">
	<?php do_action( 'it_exchange_super_widget_cart_begin_single_item_cancel_wrap' ); ?>
	<div class="cart-action cancel-update">
	<?php it_exchange( 'cart', 'checkout', array( 'class' => 'sw-cart-focus-checkout', 'focus' => 'checkout', 'label' =>  __( 'Cancel', 'LION' ) ) ); ?>
	</div>
	<?php do_action( 'it_exchange_super_widget_cart_end_single_item_cancel_wrap' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_cart_after_single_item_cancel_wrap' ); ?>
<?php do_action( 'it_exchange_super_widget_cart_after_single_item_cancel_element' ); ?>
