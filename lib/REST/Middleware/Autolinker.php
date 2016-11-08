<?php
/**
 * Autolinker middleware.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;

/**
 * Class Autolinker
 * @package iThemes\Exchange\REST\Middleware
 */
class Autolinker implements Middleware {

	/**
	 * @inheritDoc
	 */
	public function handle( Request $request, Delegate $next ) {

		$response = $next->next( $request );

		if ( is_wp_error( $response ) || ! $response->get_data() ) {
			return $response;
		}

		$route  = $request->get_matched_route_controller();
		$method = $request->get_method();

		try {

			$current = $request->get_route() ? trailingslashit( $request->get_route() ) : '';

			if ( $route->has_parent() ) {
				$up = \iThemes\Exchange\REST\get_rest_url( $route->get_parent(), $request->get_url_params() );
			} else {
				$up = '';
			}

			$data = $response->get_data();

			if ( is_array( $data ) && ! \ITUtility::is_associative_array( $data ) ) {

				$linked = array();

				foreach ( $data as $i => $item ) {
					if ( ! isset( $item['_links'] ) ) {
						$item['_links'] = array();
					}

					if ( $up && $route->get_parent() instanceof Getable ) {
						$item['_links']['up'][] = array(
							'href' => $up
						);
					}

					if ( $current && isset( $item['id'] ) ) {
						$item['_links']['self'][] = array(
							'href' => rest_url( $current . $item['id'] . '/' ),
						);
					}

					$linked[ $i ] = $item;
				}

				$response->set_data( $linked );
			} elseif ( $data ) {
				if ( $up && $route->get_parent() instanceof Getable ) {
					$response->add_link( 'up', $up );
				}

				if ( $method === 'POST' && $current && isset( $data['id'] ) ) {
					$response->add_link( 'self', $current . $data['id'] . '/' );
				} else {
					$response->add_link( 'self', rest_url( $request->get_route() ) );
				}
			}
		}
		catch ( \UnexpectedValueException $e ) {

		}

		return $response;
	}
}