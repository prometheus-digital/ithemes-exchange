<?php
/**
 * This is the default template for the 
 * super-widget-product purchase elements.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-product/elements directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_super_widget_product_before_purchase_options_element' ); ?>
<div class="purchase-options">
	<?php do_action( 'it_exchange_super_widget_product_begin_purchase_options_element' ); ?>
	<?php it_exchange( 'product', 'purchase-options', array( 'add-to-cart-edit-quantity' => false, 'buy-now-edit-quantity' => false ) ); ?>
	<?php do_action( 'it_exchange_super_widget_product_end_purchase_options_element' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_product_after_purchase_options_element' ); ?>
