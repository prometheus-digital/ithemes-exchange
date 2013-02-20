<?php
/**
 * API functions for shopping cart add-ons
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

if ( ! is_admin() ) {
	// Register shortcodes
	add_shortcode( 'cart_buddy_shopping_cart', 'it_cart_buddy_get_shopping_cart_html' );
}

/**
 * This function returns the HTML for the shopping cart
 *
 * @since 0.3.7
 * @return string html for the shopping cart
*/
function it_cart_buddy_get_shopping_cart_html( $add_on=false ) {
	$add_on = it_cart_buddy_get_active_shopping_cart( $add_on );
	if ( is_wp_error( $add_on ) ) {
		foreach( $add_on->get_error_messages() as $message ) {
			$error .= '<p>' . $message . '</p>';
		}
		return $error;
	}

	return apply_filters( 'it_cart_buddy_get_shopping_cart_html-' . $add_on['slug'], '' );
}
/**
 * This function will call the function that prints the shopping cart HTML
 *
 * @since 0.3.7
 * @return void
*/
function it_cart_buddy_print_shopping_cart( $add_on=false ) {
	$add_on = it_cart_buddy_get_active_shopping_cart( $add_on );
	if ( is_wp_error( $add_on ) ) {
		foreach( $add_on->get_error_messages() as $message ) {
			echo '<p>' . $message . '</p>';
		}
	}

	do_action( 'it_cart_buddy_print_shopping_cart-' . $add_on['slug'] );
}

/**
 * This returns the active shopping cart plugin
 *
 * It returns the specific addon if a slug is passed an that add_on is enabled
 * - If multiple add-ons are enabled and a specific add-on is not requested, it will return the first enabled add-on it finds.
 * - It will return false if none of the above conditions are met
 *
 * @since 0.3.7
 * @param string $add_on_var the var registered for the add-on.
 * @return mixed add_on array or WP_Error
*/
function it_cart_buddy_get_active_shopping_cart( $add_on_var ) {
	$enabled_shopping_cart_add_ons = it_cart_buddy_get_enabled_add_ons( array( 'category' => 'shopping-carts' ) );
	if ( empty( $enabled_shopping_cart_add_ons ) )
		return new WP_Error( 'no-add-on-carts-enabled', __( 'Oops! You have no shopping cart add-ons enabled.', 'LION' ) );

	if ( $add_on_var && ! isset( $enabled_shopping_cart_add_ons[$add_on_var] ) )
		return new WP_Error( 'add-on-not-found', __( 'Oops! The requested shopping cart add-on was not found: ', 'LION' ) . $add_on_var );

	$add_on = empty( $add_on_var ) ? reset( $enabled_shopping_cart_add_ons ) : it_cart_buddy_get_add_on( $add_on_var );
	return $add_on;
}
