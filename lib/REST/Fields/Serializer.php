<?php
/**
 * Serializer class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Fields;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Fields
 */
class Serializer {

	/** @var array|null */
	private $schema;

	/** @var Field[] */
	private $fields;

	/**
	 * Serializer constructor.
	 *
	 * @param Field[] $fields
	 */
	public function __construct( array $fields = array() ) { $this->fields = $fields; }

	/**
	 * Serialize an object.
	 *
	 * @since 2.0.0
	 *
	 * @param object $object
	 * @param string $context
	 * @param array  $query_args
	 *
	 * @return array
	 */
	public function serialize( $object, $context = 'view', array $query_args = array() ) {

		$data = array();

		foreach ( $this->get_fields() as $key => $field ) {

			$available = $field->available_in_contexts();

			if ( ! $available || in_array( $context, $available, true ) ) {
				$data[ $key ] = $field->serialize( $object, $query_args );
			}
		}

		return $data;
	}

	/**
	 * Generate links for `WP_REST_Response::add_links()`.
	 *
	 * @since 2.0.0
	 *
	 * @param object $object
	 * @param string $context
	 *
	 * @return array
	 */
	public function generate_links( $object, $context = 'view' ) { return array(); }

	/**
	 * Get the schema for an object.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema() {

		if ( $this->schema !== null ) {
			return $this->schema;
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'transaction',
			'type'       => 'object',
			'properties' => array(),
		);

		foreach ( $this->get_fields() as $key => $field ) {
			$this->schema['properties'][ $key ] = $field->get_schema();

			if ( $field->available_in_contexts() && ! isset( $this->schema['properties'][ $key ]['context'] ) ) {
				$this->schema['properties'][ $key ]['context'] = $field->available_in_contexts();
			}
		}

		return $this->schema;
	}

	/**
	 * Get the REST fields.
	 *
	 * @since 2.0.0
	 *
	 * @return Field[]
	 */
	public function get_fields() { return $this->fields; }

	/**
	 * Set the REST fields.
	 *
	 * @since 2.0.0
	 *
	 * @param Field[] $fields
	 */
	public function set_fields( $fields ) {
		$this->fields = $fields;
	}
}