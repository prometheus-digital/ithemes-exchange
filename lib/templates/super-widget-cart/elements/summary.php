<?php
/**
 * This is the default template for the
 * super-widget-cart summary element.
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

<?php $count = it_exchange( 'cart', 'get-item-count' ); ?>
<?php do_action( 'it_exchange_super_widget_cart_before_summary_element' ); ?>
<div class="item-count">
	<?php do_action( 'it_exchange_super_widget_cart_begin_summary_element' ); ?>
	<?php if ( $count === 1 ) : ?>
		<?php printf( __( 'You have 1 item in your <a href="%s">%s</a>', 'LION' ), it_exchange_get_page_url( 'cart' ), strtolower( it_exchange_get_page_name( 'cart' ) ) ); ?>
	<?php else : ?>
		<?php printf( __( 'You have %s items in your <a href="%s">%s</a>', 'LION' ), $count, it_exchange_get_page_url( 'cart' ), strtolower( it_exchange_get_page_name( 'cart' ) ) ); ?>
	<?php endif; ?>
	<?php do_action( 'it_exchange_super_widget_cart_end_summary_element' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_cart_after_summary_element' ); ?>
