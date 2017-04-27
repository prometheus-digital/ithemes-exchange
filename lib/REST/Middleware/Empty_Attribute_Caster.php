<?php
/**
 * Cast empty attributes to the correct type.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;

use iThemes\Exchange\REST\Request;

/**
 * Class Empty_Attribute_Caster
 *
 * @package iThemes\Exchange\REST\Middleware
 */
class Empty_Attribute_Caster implements Middleware {

	/**
	 * @inheritDoc
	 */
	public function handle( Request $request, Delegate $next ) {

		$r = $next->next( $request );

		if ( $r instanceof \WP_Error ) {
			return $r;
		}

		$schema = $request->get_matched_route_controller()->get_schema();

		if ( ! $schema || empty( $schema['properties'] ) ) {
			return $r;
		}

		$data = $r->get_data();

		if ( \ITUtility::is_associative_array( $data ) ) {
			$data = $this->cast( $data, $schema );
		} elseif ( is_array( $data ) ) {
			foreach ( $data as $i => $datum ) {
				$data[ $i ] = $this->cast( $datum, $schema );
			}
		}

		$r->set_data( $data );

		return $r;
	}

	/**
	 * Cast the empty attributes to the correct type.
	 *
	 * todo handle nested attributes
	 *
	 * @since 2.0.0
	 *
	 * @param array $data
	 * @param array $schema
	 *
	 * @return array
	 */
	public function cast( array $data, array $schema ) {
		$properties = $schema['properties'];

		foreach ( $data as $key => $value ) {
			if ( empty( $value ) && isset( $properties[ $key ], $properties[ $key ]['type'] ) ) {
				if ( $properties[ $key ]['type'] === 'object' ) {
					$data[ $key ] = new \stdClass();
				} elseif ( $properties[ $key ]['type'] === 'array' ) {
					$data[ $key ] = array();
				}
			}
		}

		return $data;
	}
}
