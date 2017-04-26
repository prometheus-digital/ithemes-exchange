<?php
/**
 * AggregateField class definition.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Fields;

use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Class AggregateField
 *
 * @package iThemes\Exchange\REST\Fields
 */
class AggregateField implements Field {

	/** @var Field[] */
	private $fields = array();

	/** @var string */
	private $attribute;

	/** @var callable */
	private $can_set_callback;

	/**
	 * AggregateField constructor.
	 *
	 * @param string        $attribute
	 * @param Field[]       $fields
	 * @param callable|null $can_set_callback
	 */
	public function __construct( $attribute, array $fields = array(), $can_set_callback = null ) {
		$this->attribute        = $attribute;
		$this->fields           = $fields;
		$this->can_set_callback = $can_set_callback;
	}

	/**
	 * @inheritDoc
	 */
	public function get_attribute() {
		return $this->attribute;
	}

	/**
	 * @inheritDoc
	 */
	public function serialize( $object, array $query_args = array() ) {
		$data = array();

		foreach ( $this->fields as $field ) {
			$data[ $field->get_attribute() ] = $field->serialize( $object, $query_args );
		}

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function update( $object, $new_value ) {
		foreach ( $this->fields as $field ) {
			if ( array_key_exists( $field->get_attribute(), $new_value ) ) {
				$field->update( $object, $new_value[ $field->get_attribute() ] );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		$schema = array(
			'type'       => 'object',
			'properties' => array(),
		);

		foreach ( $this->fields as $field ) {
			$schema['properties'][ $field->get_attribute() ] = $field->get_schema();
		}

		return $schema;
	}

	/**
	 * @inheritDoc
	 */
	public function available_in_contexts() {
		$contexts = array();

		foreach ( $this->fields as $field ) {
			$contexts = array_merge( $contexts, $field->available_in_contexts() );
		}

		return array_unique( $contexts );
	}

	/**
	 * @inheritDoc
	 */
	public function scope_can_set( AuthScope $scope, $new_value ) {

		if ( $this->can_set_callback && ! call_user_func( $this->can_set_callback, $scope, $new_value ) ) {
			return false;
		}

		foreach ( $this->fields as $field ) {
			if ( array_key_exists( $field->get_attribute(), $new_value ) ) {
				if ( ! $field->scope_can_set( $scope, $new_value[ $field->get_attribute() ] ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Add an additional field to this aggregate.
	 *
	 * @since 2.0.0
	 *
	 * @param Field $field
	 *
	 * @return $this
	 */
	public function add_field( Field $field ) {
		$this->fields[ $field->get_attribute() ] = $field;

		return $this;
	}
}