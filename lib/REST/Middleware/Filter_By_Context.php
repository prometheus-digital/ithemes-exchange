<?php
/**
 * Filter the response by context.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;

use iThemes\Exchange\REST\Request;

/**
 * Class Filter_By_Context
 * @package iThemes\Exchange\REST\Middleware
 */
class Filter_By_Context implements Middleware {

	/**
	 * @inheritDoc
	 */
	public function handle( Request $request, Delegate $next ) {

		$response = $next->next( $request );

		if ( is_wp_error( $response ) || ! $response->get_data() ) {
			return $response;
		}

		$data  = $response->get_data();
		$route = $request->get_matched_route_controller();

		$schema = $route->get_schema();
		$context = $request['context'] ?: 'view';

		if ( is_array( $data ) && \ITUtility::is_associative_array( $data ) ) {

			$data = $this->filter_item_by_context( $data, $context, $schema );

			$response->set_data( $data );
		} elseif ( is_array( $data ) ) {

			$filtered = array();

			foreach ( $data as $i => $item ) {
				$filtered[ $i ] = $this->filter_item_by_context( $item, $context, $schema );
			}

			$response->set_data( $filtered );
		}

		return $response;
	}

	/**
	 * Filter an item by context according to the route's schema.
	 *
	 * @since 1.36.0
	 *
	 * @param array  $item
	 * @param string $context
	 * @param array  $schema
	 *
	 * @return array
	 */
	protected function filter_item_by_context( $item, $context, $schema ) {

		foreach ( $item as $key => $value ) {

			if ( empty( $schema['properties'][ $key ] ) || empty( $schema['properties'][ $key ]['context'] ) ) {
				continue;
			}

			if ( ! in_array( $context, $schema['properties'][ $key ]['context'] ) ) {
				unset( $item[ $key ] );
			}

			if ( 'object' === $schema['properties'][ $key ]['type'] && ! empty( $schema['properties'][ $key ]['properties'] ) ) {
				foreach ( $schema['properties'][ $key ]['properties'] as $attribute => $details ) {

					if ( empty( $details['context'] ) ) {
						continue;
					}

					if ( ! in_array( $context, $details['context'] ) ) {
						if ( isset( $item[ $key ][ $attribute ] ) ) {
							unset( $item[ $key ][ $attribute ] );
						}
					}
				}
			}
		}

		return $item;
	}
}