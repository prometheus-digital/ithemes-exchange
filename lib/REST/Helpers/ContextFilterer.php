<?php
/**
 * Filter the response by context.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Helpers;

use JsonSchema\Constraints\Factory;
use JsonSchema\Validator;

/**
 * Class ContextFilterer
 *
 * @package iThemes\Exchange\REST\Middleware
 */
class ContextFilterer {

	/**
	 * Filter an item by context according to the route's schema.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $item    The data being filtered.
	 * @param string $context The context being adhered to.
	 * @param array  $schema  The entire document schema.
	 *
	 * @return array
	 */
	public function filter( $item, $context, $schema ) {

		foreach ( $item as $key => $value ) {

			if ( empty( $schema['properties'][ $key ] ) || empty( $schema['properties'][ $key ]['context'] ) ) {
				continue;
			}

			$v_schema = $schema['properties'][ $key ];

			if ( ! in_array( $context, $v_schema['context'] ) ) {
				unset( $item[ $key ] );

				continue;
			}

			$v_schema = $this->get_complex_v_schema( $v_schema, $schema, $value );

			if ( isset( $v_schema['context'] ) && ! in_array( $context, $v_schema['context'] ) ) {
				unset( $item[ $key ] );

				continue;
			}

			if ( empty( $v_schema['type'] ) ) {
				continue;
			}

			if ( 'object' === $v_schema['type'] && ! empty( $v_schema['properties'] ) ) {
				$item[ $key ] = $this->filter_object( $value, $v_schema, $context, $schema );
			}
		}

		return $item;
	}

	/**
	 * Filter an object's properties according to a schema.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $object
	 * @param array  $object_schema
	 * @param string $context
	 * @param array  $schema
	 *
	 * @return array
	 */
	protected function filter_object( $object, $object_schema, $context, $schema ) {
		foreach ( $object_schema['properties'] as $attribute => $v_schema ) {

			if ( ! isset( $object[ $attribute ] ) ) {
				continue;
			}

			$v_schema = $this->get_complex_v_schema( $v_schema, $schema, $object );

			if ( $v_schema['type'] === 'object' ) {
				$object[ $attribute ] = $this->filter_object( $object[ $attribute ], $v_schema, $context, $schema );
			}

			if ( empty( $v_schema ) || empty( $v_schema['context'] ) ) {
				continue;
			}

			if ( ! in_array( $context, $v_schema['context'] ) ) {
				unset( $object[ $attribute ] );
			}
		}

		return $object;
	}

	/**
	 * Get a value schema for a complex entity.
	 *
	 * @since 2.0.0
	 *
	 * @param array $property The schema for just this value.
	 * @param array $schema   The entire schema document.
	 * @param array $value    The value being filtered.
	 *
	 * @return array|null
	 */
	protected function get_complex_v_schema( $property, $schema, $value ) {

		if ( isset( $property['$ref'] ) ) {
			return $this->handle_ref( $property, $schema );
		}

		if ( isset( $property['oneOf'] ) ) {
			return $this->handle_one_of( $property, $schema, $value );
		}

		return $property;
	}

	/**
	 * Handle a $ref in the schema properties.
	 *
	 * #/definitions/object_title
	 *
	 * @since 2.0.0
	 *
	 * @param array $property
	 * @param array $schema
	 *
	 * @return array|null
	 */
	protected function handle_ref( $property, $schema ) {
		$ref = $property['$ref'];

		$exploded = explode( '/', $ref );

		if ( count( $exploded ) !== 3 ) {
			return null;
		}

		$search = $exploded[1];
		$title  = $exploded[2];

		if ( ! isset( $schema[ $search ], $schema[ $search ][ $title ] ) ) {
			return null;
		}

		return $schema[ $search ][ $title ];
	}

	/**
	 * Handle a oneOf descriptor.
	 *
	 * @since 2.0.0
	 *
	 * @param array $property The schema for just this value.
	 * @param array $schema   The entire schema document.
	 * @param array $value    The value being filtered.
	 *
	 * @return array|null
	 */
	protected function handle_one_of( $property, $schema, $value ) {

		foreach ( $property['oneOf'] as $one_of ) {

			$validator = new Validator( new Factory() );
			$validator->check( $value, $one_of );

			// This is the matched schema.
			if ( count( $validator->getErrors() ) === 0 ) {
				return $one_of;
			}
		}

		return null;
	}

}
