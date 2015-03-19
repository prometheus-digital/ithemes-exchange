<?php
/**
 * The checkout template for the Super Widget.
 *
 * @since 0.4.0
 * @version 1.0.1
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
*/
?>

<?php do_action( 'it_exchange_super_widget_checkout_before_wrap' ); ?>
<div class="it-exchange-sw-processing it-exchange-sw-processing-checkout">
	<?php it_exchange_get_template_part( 'messages' ); ?>
	<?php do_action( 'it_exchange_super_widget_checkout_begin_wrap' ); ?>
	<?php
	// If we have cart Items
	if ( it_exchange( 'cart', 'has-cart-items' ) ) {

		// Default loops we want to include on this view
		$loops = array();
		// Add cart items loop to list of loops we want to include
		$loops[] = 'items';
		// Add Payment buttons if only one item in cart
		if ( ! it_exchange_is_multi_item_cart_allowed() || ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_cart_products_count() < 2 ) )
			$loops[] = 'transaction-methods';

		// Include template parts for each of the above loops
		foreach( it_exchange_get_template_part_loops( 'super-widget-checkout', 'has-cart-items', $loops ) as $loop ) :
			it_exchange_get_template_part( 'super-widget-checkout/loops/' . $loop );
		endforeach;

		// Add additional checkout actions loop
		if ( ( it_exchange( 'coupons', 'accepting', array( 'type' => 'cart' ) ) || it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ) || it_exchange_get_global( 'can_edit_purchase_quantity' ) ) {
			it_exchange_get_template_part( 'super-widget-checkout/loops/actions' );
		}

	} else {
		it_exchange_get_template_part( 'super-widget-cart/elements/empty-cart-notice' );
	}
	?>
	<?php do_action( 'it_exchange_super_widget_checkout_end_wrap' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_checkout_after_wrap' ); ?>
