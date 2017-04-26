<?php
/**
 * REST QueryArg interface defintion.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Fields;

use Doctrine\Common\Collections\Criteria;
use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Interface QueryArg
 *
 * @package iThemes\Exchange\REST\Fields
 */
interface QueryArg {

	/**
	 * Get the attribute name for this query arg.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_attribute();

	/**
	 * Get the schema definition for this query arg.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema();

	/**
	 * Check whether the given scope has permission to set this field to the given value.
	 *
	 * @since 2.0.0
	 *
	 * @param AuthScope $scope
	 * @param string    $value
	 *
	 * @return boolean
	 */
	public function scope_can_use( AuthScope $scope, $value = '' );

	/**
	 * Check whether a query arg's value is valid.
	 *
	 * In most cases, the schema definition should be handling definition. This is used for validating the value
	 * in a more dynamic way. For example, checking that the ID corresponds to a valid post.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_valid( $value );

	/**
	 * Modify the criteria based on the given value.
	 *
	 * @since 2.0.0
	 *
	 * @param Criteria $criteria
	 * @param mixed    $value
	 * @param array    $all_query_args
	 */
	public function add_criteria( Criteria $criteria, $value, array $all_query_args );
}
