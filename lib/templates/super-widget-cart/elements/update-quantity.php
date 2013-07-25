<?php
/**
 * This is the default template for the 
 * super-widget-cart update-quantity element.
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

<?php do_action( 'it_exchange_super_widget_cart_before_update_quantity_element' ); ?>
<?php do_action( 'it_exchange_super_widget_cart_before_quantity_wrapper' ); ?>
<div class="quantity-wrapper">
	<?php do_action( 'it_exchange_super_widget_cart_begin_quantity_wrapper' ); ?>
	<div class="quantity">
		<?php it_exchange( 'cart', 'update', 'class=it-exchange-update-quantity-button&label=' . __( 'Update Quantity', 'LION' ) ); ?>
	</div>

	<?php
	// Include the single-item-cart quantity actions template part
	it_exchange_get_template_part( 'super-widget-cart/elements/single-item-cart-cancel' );
	?>

	<?php do_action( 'it_exchange_super_widget_cart_end_quantity_wrapper' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_cart_afterquantity__wrapper' ); ?>
<?php do_action( 'it_exchange_super_widget_cart_after_update_quantity_element' ); ?>
