<?php
/**
 * REST Route Interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Interface Route
 * @package iThemes\Exchange\REST
 */
interface Route {

	/**
	 * Get the route major version number.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_version();

	/**
	 * Get the route path.
	 *
	 * No starting slash. Include trailing slash. This should not exclude the version number, or any parent paths.
	 *
	 * For example:
	 *
	 * transactions/(?P<id>[\d+])/
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_path();

	/**
	 * Get route args.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_query_args();

	/**
	 * Get the route schema.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema();

	/**
	 * Whether this has a parent route.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function has_parent();

	/**
	 * Get the parent route.
	 *
	 * @since 2.0.0
	 *
	 * @return Route
	 *
	 * @throws \UnexpectedValueException If no parent route exists.
	 */
	public function get_parent();
}
