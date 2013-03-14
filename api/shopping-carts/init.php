<?php
/**
 * API functions for shopping cart add-ons
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

if ( ! is_admin() ) {
	// API function to return HTML elements related to the cart
	include( 'cart-template-functions.php' );

	// API function to return HTML elements related to the checkout page 
	include( 'checkout-template-functions.php' );

	// Low Level API functions to retreive data about the current cart
	include( 'data-functions.php' );

	// Lib
	include( 'lib.php' );

	// Register shortcodes
	include( 'shortcodes.php' );
}
