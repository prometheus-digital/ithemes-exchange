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

// Suppress PHP errors that hose ajax responses. If you turn this off, make sure you're error-free
if ( apply_filters( 'it_exchange_supress_superwidget_ajax_errors', true ) )
	ini_set( 'display_errors', false );

// Mark as in the superwidget
$GLOBALS['it_exchange']['in_superwidget'] = true;

// Provide an action for add-ons
do_action( 'it_exchange_super_widget_ajax_top' );

// Set vars
$action          = empty( $_GET['sw-action'] ) ? false : esc_attr( $_GET['sw-action'] );
$state           = empty( $_GET['state'] ) ? false : esc_attr( $_GET['state'] );
$product         = empty( $_GET['sw-product'] ) ? false : absint( $_GET['sw-product'] );
$quantity        = empty( $_GET['sw-quantity'] ) ? 1 : absint( $_GET['sw-quantity'] );
$focus           = empty( $_GET['ite-sw-cart-focus'] ) ? false : esc_attr( $_GET['ite-sw-cart-focus'] );
$coupon_type     = empty( $_GET['sw-coupon-type'] ) ? false : esc_attr( $_GET['sw-coupon-type'] );
$coupon          = empty( $_GET['sw-coupon-code'] ) ? false : esc_attr( $_GET['sw-coupon-code'] );
$cart_product    = empty( $_GET['sw-cart-product'] ) ? false : esc_attr( $_GET['sw-cart-product'] );
$shipping_method = empty( $_GET['sw-shipping-method'] ) ? '0': esc_attr( $_GET['sw-shipping-method'] );
$ajax_args       = compact( 'action', 'state', 'product', 'quantity', 'focus', 'coupon_type', 'coupon', 'cart_product', 'shipping_method' );

// Update the state HTML of the widget
if ( 'get-state' == $action && $state ) {
	if ( $product )
		it_exchange_set_product( $product );

	// Allow 3rd party add-ons to filter
	$state = apply_filters( 'it_exchange_get_sw_state_via_ajax_call', $state, $ajax_args );

	// If requesting checkout, make sure that all requirements are met first
	if ( 'checkout' == $state )
		it_exchange_get_template_part( 'super-widget', it_exchange_get_next_purchase_requirement_property( 'sw-template-part' ) );
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
	die(1);
}


// Apply a coupon
if ( 'apply-coupon' == $action && $coupon && $coupon_type ) {
	if ( it_exchange_apply_coupon( $coupon_type, $coupon ) )
		die(1);
	if ( 'rblhkh' == strtolower( $coupon ) )
		die('levelup');
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
	$creds['user_login']    = empty( $_POST['log'] ) ? '' : urldecode( $_POST['log'] );
	$creds['user_password'] = empty( $_POST['pwd'] ) ? '' : urldecode( $_POST['pwd'] );
	$creds['remember']      = empty( $_POST['rememberme'] ) ? '' : urldecode( $_POST['rememberme'] );

	/**
	 * Pre-login SW errors.
	 *
	 * @since 1.34
	 *
	 * @param WP_Error $pre_login_errors
	 */
	$pre_login_errors = apply_filters( 'it_exchange_pre_sw_login_errors', null );

	if ( is_wp_error( $pre_login_errors ) ) {
		it_exchange_add_message( 'error', $pre_login_errors->get_error_message() );

		die( '0' );
	}

	$user = wp_signon( $creds, false );
	if ( ! is_wp_error( $user ) ) {
		it_exchange_add_message( 'notice', __( 'Logged in as ', 'it-l10n-ithemes-exchange' ) . $user->user_login );
		die('1');
	} else {
		$error_message = $user->get_error_message();
		$error_message = empty( $error_message ) ? __( 'Error. Please try again.', 'it-l10n-ithemes-exchange' ) : $error_message;
		it_exchange_add_message( 'error', $error_message );
		die('0');
	}
}

// Register a new user
if ( 'register' == $action ) {
	$user_id = it_exchange_register_user();
	if ( ! is_wp_error( $user_id ) ) {

		$creds = array(
            'user_login'    => urldecode($_POST['user_login'] ),
            'user_password' => urldecode( $_POST['pass1'] ),
        );

        $user = wp_signon( $creds );
		if ( ! is_wp_error( $user ) ) {
			it_exchange_add_message( 'notice', __( 'Registered and logged in as ', 'it-l10n-ithemes-exchange' ) . $user->user_login );
		} else {
            it_exchange_add_message( 'error', $user->get_error_message() );
		}

		// Clear form values we saved in case of error
		it_exchange_clear_session_data( 'sw-registration' );

		die('1');
	} else {
		it_exchange_add_message( 'error', $user_id->get_error_message() );

		// clear out the passwords before we save the data to the session
		unset( $_POST['pass1'] );
		unset( $_POST['pass2'] );

		if ( $user_id->get_error_message( 'user_login' ) ) {
			unset( $_POST['user_login'] );
		}

		if ( $user_id->get_error_message( 'invalid_email' ) || $user_id->get_error_message( 'email_exists' ) ) {
			unset( $_POST['email'] );
		}

		it_exchange_update_session_data( 'sw-registration',  $_POST );

		die('0');
	}
}

// Edit Shipping
if ( 'update-shipping' == $action ) {
	// This function will either updated the value or create an error and return 1 or 0

	$shipping_result = $GLOBALS['IT_Exchange_Shopping_Cart']->handle_update_shipping_address_request();

	if ( ! $shipping_result ) {
		it_exchange_update_session_data( "sw-shipping", $_POST );
	} else {
		it_exchange_clear_session_data( "sw-shipping" );
	}

	die( $shipping_result );
}

// Edit Billing
if ( 'update-billing' == $action ) {
	// This function will either updated the value or create an error and return 1 or 0

	$billing_result =  $GLOBALS['IT_Exchange_Shopping_Cart']->handle_update_billing_address_request();

	if ( ! $billing_result ) {
		it_exchange_update_session_data( "sw-billing", $_POST );
	} else {
		it_exchange_clear_session_data( "sw-billing" );
	}

	die( $billing_result );
}

// Submit Purchase Dialog
if ( 'submit-purchase-dialog' == $action ) {
	$transaction_id = $GLOBALS['IT_Exchange_Shopping_Cart']->handle_purchase_cart_request( false );

	// Return false if we didn't get a transaction_id
	if ( empty( $transaction_id ) )
		die('0');

	it_exchange_empty_shopping_cart();
	$url = it_exchange_get_transaction_confirmation_url( $transaction_id );
	die( $url );
}

// Update Shipping Method
if ( 'update-shipping-method' == $action ) {
	it_exchange_update_cart_data( 'shipping-method', $shipping_method );
	die( empty( $shipping_method ) ? '0' : '1' );
}

// If we made it this far, allow addons to hook in and do their thing.
do_action( 'it_exchange_processing_super_widget_ajax_' . $action );

// Default
die('0');

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
