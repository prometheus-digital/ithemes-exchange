<?php
/**
 * Cart Feedback middleware.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;

use iThemes\Exchange\REST\Request;

/**
 * Class Cart_Feedback
 *
 * @package iThemes\Exchange\REST\Middleware
 */
class Cart_Feedback implements Middleware {

	/**
	 * @inheritDoc
	 */
	public function handle( Request $request, Delegate $next ) {

		$response = $next->next( $request );

		if ( $request->get_method() === 'GET' || ( $response instanceof \WP_REST_Response && ! $response->get_data() ) ) {
			return $response;
		}

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );

		if ( ! $cart ) {
			return $response;
		}

		$feedback = array(
			'notices' => array(),
			'errors'  => array(),
		);

		foreach ( $cart->get_feedback()->notices() as $notice ) {
			$feedback['notices'][] = array(
				'text' => (string) $notice,
				'item' => $notice->get_item() ? array(
					'type' => $notice->get_item()->get_type(),
					'id'   => $notice->get_item()->get_id()
				) : array()
			);
		}

		foreach ( $cart->get_feedback()->errors() as $error ) {
			$feedback['errors'][] = array(
				'text' => (string) $error,
				'item' => $error->get_item() ? array(
					'type' => $error->get_item()->get_type(),
					'id'   => $error->get_item()->get_id()
				) : array()
			);
		}

		if ( $response instanceof \WP_REST_Response ) {
			$data             = $response->get_data();
			$data['feedback'] = $feedback;
			$response->set_data( $data );
		} else {
			$response->add_data( $feedback );
		}

		return $response;
	}
}
