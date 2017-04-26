<?php
/**
 * REST Field interface definition.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Fields;

use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Interface Field
 *
 * @package iThemes\Exchange\REST\Fields
 */
interface Field {

	/**
	 * Get the attribute name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_attribute();

	/**
	 * Get the schema definition for a field.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema();

	/**
	 * Get the list of contexts that this field should be displayed in.
	 *
	 * @since 2.0.0
	 *
	 * @return string[]
	 */
	public function available_in_contexts();

	/**
	 * Serialize this field's value.
	 *
	 * @since 2.0.0
	 *
	 * @param object $object
	 * @param array  $query_args
	 *
	 * @return mixed
	 */
	public function serialize( $object, array $query_args = array() );

	/**
	 * Update a field's value.
	 *
	 * @since 2.0.0
	 *
	 * @param object $object
	 * @param mixed  $new_value
	 */
	public function update( $object, $new_value );

	/**
	 * Check whether the given scope has permission to set this field to the given value.
	 *
	 * @since 2.0.0
	 *
	 * @param AuthScope $scope
	 * @param mixed     $new_value
	 *
	 * @return boolean
	 */
	public function scope_can_set( AuthScope $scope, $new_value );
}