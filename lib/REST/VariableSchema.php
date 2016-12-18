<?php
/**
 * VariableSchema interface.
 *
 * @since 2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Interface VariableSchema
 *
 * @package iThemes\Exchange\REST
 */
interface VariableSchema extends Route {

	/**
	 * Get the methods that a schema varies on.
	 *
	 * Example: 'GET', 'POST'.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function schema_varies_on();

	/**
	 * Get the schema for a method.
	 *
	 * @since 2.0.0
	 *
	 * @param string $method
	 *
	 * @return array
	 */
	public function get_schema_for_method( $method );
}