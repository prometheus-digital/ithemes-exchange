<?php
/**
 * Cart Decorator middleware.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;

use iThemes\Exchange\REST\Request;

/**
 * Class Cart_Decorator
 *
 * This middleware will automatically set the cart object on the request.
 *
 * This is used so that feedback, which is stored in-memory, can be kept throughout
 * the request.
 *
 * @package iThemes\Exchange\REST\Middleware
 */
class Cart_Decorator implements Middleware {

	/**
	 * @inheritDoc
	 */
	public function handle( Request $request, Delegate $next ) {

		if ( $cart_id = $request->get_param( 'cart_id', 'URL' ) ) {
			$cart = it_exchange_get_cart( $cart_id );

			if ( $cart ) {
				$request->set_cart( $cart );
			}
		}

		return $next->next( $request );
	}
}