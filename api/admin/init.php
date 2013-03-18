<?php
/**
 * API Functions for settings pages / etc
 *
 * @since 0.3.4
*/

/**
 * Returns currency data
 *
 * @since 0.3.4
 * @todo Cache in a transient
 * @todo Provide param to break cache
 * @todo Better anticipate wp_error
*/
function it_cart_buddy_get_currency_options() {
	$currency_url = apply_filters( 'it_cart_buddy_currency_url', 'https://raw.github.com/glennansley/world-currencies/master/currencies.json' );
	$data = wp_remote_get( $currency_url );
	if ( ! is_wp_error( $data ) ) {
		$body = json_decode( wp_remote_retrieve_body( $data ) );
		if ( is_array( $body ) )
			return apply_filters( 'it_cart_buddy_get_currency_options', $body );
	}
	return false;
}
