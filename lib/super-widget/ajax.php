<?php
/**
 * This file processes AJAX call from the super widget
 * @package IT_Exchange
 * @since 0.4.0
*/

// Die if called directly
if ( ! function_exists( 'add_action' ) ) {
	turtles_all_the_way_down();
	die();
}

// Mark as in the superwidget
$GLOBALS['it_exchange']['in_superwidget'] = true;

// Set vars
$action       = empty( $_GET['sw-action'] ) ? false : esc_attr( $_GET['sw-action'] );
$state        = empty( $_GET['state'] ) ? false : esc_attr( $_GET['state'] );
$product      = empty( $_GET['sw-product'] ) ? false : absint( $_GET['sw-product'] );
$quantity     = empty( $_GET['sw-quantity'] ) ? false : absint( $_GET['sw-quantity'] );
$focus        = empty( $_GET['ite-sw-cart-focus'] ) ? false : esc_attr( $_GET['ite-sw-cart-focus'] );
$coupon_type  = empty( $_GET['sw-coupon-type'] ) ? false : esc_attr( $_GET['sw-coupon-type'] );
$coupon       = empty( $_GET['sw-coupon-code'] ) ? false : esc_attr( $_GET['sw-coupon-code'] );
$cart_product = empty( $_GET['sw-cart-product'] ) ? false : esc_attr( $_GET['sw-cart-product'] );

// Update the state HTML of the widget
if ( 'get-state' == $action && $state ) {
	if ( $product )
		$GLOBALS['it_exchange']['product'] = it_exchange_get_product( $product );
	it_exchange_get_template_part( 'super-widget', $state );
	die();
}

// Buy Now action
if ( ( 'add-to-cart' == $action || 'buy-now' == $action ) && $product && $quantity ) {
	if ( it_exchange_add_product_to_shopping_cart( $product, $quantity ) )
		die(1);
	die(0);
}

// Empty Cart
if ( 'empty-cart' == $action ) {
	it_exchange_empty_shopping_cart();
	die(1);
}

// Apply a coupon
if ( 'apply-coupon' == $action && $coupon && $coupon_type ) {
	if ( it_exchange_apply_coupon( $coupon_type, $coupon ) )
		die(1);
	die(0);
}

// Remove a coupon
if ( 'remove-coupon' == $action && $coupon && $coupon_type ) {
	if ( it_exchange_remove_coupon( $coupon_type, $coupon ) )
		die(1);
	die(0);
}

// Update Quantity
if ( 'update-quantity' == $action && $quantity && $cart_product ) {
	if ( it_exchange_update_cart_product_quantity( $cart_product, $quantity, false ) )
		die(1);
	die(0);
}

die('bad state');






/**
 * Just for fun
 *
 * @since 0.4.0
*/
function turtles_all_the_way_down() {
?>
<pre>
         .-""""-.\
         |"   (a \
         \--'    |
          ;,___.;.
       _ / `"""`\#'.
      | `\"==    \##\
      \   )     /`;##;
       ;-'   .-'  |##|
       |"== (  _.'|##|
       |     ``   /##/
        \"==     .##'
         ',__.--;#;`
         /  /   |\(
         \  \   (
         /  /    \
        (__(____.'
<br />
George says "You can't do that!"
</pre>
<?php
}
