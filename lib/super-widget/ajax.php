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
$quantity     = empty( $_GET['sw-quantity'] ) ? 1 : absint( $_GET['sw-quantity'] );
$focus        = empty( $_GET['ite-sw-cart-focus'] ) ? false : esc_attr( $_GET['ite-sw-cart-focus'] );
$coupon_type  = empty( $_GET['sw-coupon-type'] ) ? false : esc_attr( $_GET['sw-coupon-type'] );
$coupon       = empty( $_GET['sw-coupon-code'] ) ? false : esc_attr( $_GET['sw-coupon-code'] );
$cart_product = empty( $_GET['sw-cart-product'] ) ? false : esc_attr( $_GET['sw-cart-product'] );
$un           = empty( $_GET['sw-un'] ) ? false : esc_attr( $_GET['sw-un'] );
$pw           = empty( $_GET['sw-p1'] ) ? false : esc_attr( $_GET['sw-p1'] );
$remember     = empty( $_GET['sw-remember'] ) ? false : esc_attr( $_GET['sw-remember'] );
$fn           = empty( $_GET['sw-fn'] ) ? false : esc_attr( $_GET['sw-fn'] );
$ln           = empty( $_GET['sw-ln'] ) ? false : esc_attr( $_GET['sw-ln'] );
$em           = empty( $_GET['sw-em'] ) ? false : esc_attr( $_GET['sw-em'] );
$p2           = empty( $_GET['sw-p2'] ) ? false : esc_attr( $_GET['sw-p2'] );

// Update the state HTML of the widget
if ( 'get-state' == $action && $state ) {
	if ( $product )
		$GLOBALS['it_exchange']['product'] = it_exchange_get_product( $product );

	// Force Log-in if asking for checkout and user isn't logged in.
	if ( ! is_user_logged_in() && 'checkout' == $state )
		it_exchange_get_template_part( 'super-widget', 'registration' );
	else
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

// Remove item from cart
if ( 'remove-from-cart' == $action && ! empty( $cart_product ) ) {
	it_exchange_delete_cart_product( $cart_product );
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

// Login
if ( 'login' == $action ) {
	$creds['user_login'] = $un;
	$creds['user_password']  = $pw;
	$creds['remember']   = $remember;

	$user = wp_signon( $creds, false );
	if ( ! is_wp_error( $user ) ) {
		it_exchange_add_message( 'notice', __( 'Logged in as ', 'LION' ) . $user->user_login );
		die('1');
	} else {
		it_exchange_add_message( 'error', $user->get_error_message() );
		die('0');
	}
}

// Register a new user
if ( 'register' == $action ) {
	$user_data['user_login'] = $un;
	$user_data['first_name'] = $fn;
	$user_data['last_name']  = $ln;
	$user_data['email']      = $em;
	$user_data['pass1']      = $pw;
	$user_data['pass2']      = $p2;

	$user = it_exchange_register_user( $user_data );
	if ( ! is_wp_error( $user ) ) {
		$creds['user_login']    = $un;
		$creds['user_password'] = $pw;
		$creds['remember']      = false;
		if ( $user = wp_signon( $creds, false ) )
			it_exchange_add_message( 'notice', __( 'Registered and logged in as ', 'LION' ) . $user->user_login );
		die('1');
	} else {
		it_exchange_add_message( 'error', $user->get_error_message() );
		die('0');
	}
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
