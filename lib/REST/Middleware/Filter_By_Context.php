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
 *
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

		$schema  = $route->get_schema();
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

			$v_schema = $schema['properties'][ $key ];

			if ( ! in_array( $context, $v_schema['context'] ) ) {
				unset( $item[ $key ] );

				continue;
			}

			// #/definitions/object_title
			if ( isset( $v_schema['$ref'] ) ) {
				$ref = $v_schema['$ref'];

				$exploded = explode( '/', $ref );

				if ( count( $exploded ) !== 3 ) {
					continue; // Throw an exception? a _doing_it_wrong?
				}

				$search = $exploded[1];
				$search = substr( $search, 1 ); // Only support definitions found from the root of the document for now
				$title  = $exploded[2];

				if ( ! isset( $schema[ $search ], $schema[ $search ][ $title ] ) ) {
					continue;
				}

				$v_schema = $schema[ $search ][ $title ];
			}

			if ( 'object' === $v_schema['type'] && ! empty( $v_schema['properties'] ) ) {
				$item[ $key ] = $this->filter_object( $value, $v_schema, $context );
			}
		}

		return $item;
	}

	/**
	 * Filter an object's properties according to a schema.
	 *
	 * @since 1.36.0
	 *
	 * @param array  $object
	 * @param array  $object_schema
	 * @param string $context
	 *
	 * @return array
	 */
	protected function filter_object( $object, $object_schema, $context ) {
		foreach ( $object_schema['properties'] as $attribute => $details ) {

			if ( $details['type'] === 'object' ) {
				$object[ $attribute ] = $this->filter_object( $object[ $attribute ], $details, $context );
			}

			if ( empty( $details['context'] ) ) {
				continue;
			}

			if ( ! in_array( $context, $details['context'] ) ) {
				unset( $object[ $attribute ] );
			}
		}

		return $object;
	}
}