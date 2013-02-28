<?php
/**
 * API functions for shopping cart add-ons
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

if ( ! is_admin() ) {
	// Register shortcodes
	include( 'shortcodes.php' );

	// API function to return HTML elements related to the cart
	include( 'template-functions.php' );

	// Low Level API functions to retreive data about the current cart
	include( 'data-functions.php' );
}
