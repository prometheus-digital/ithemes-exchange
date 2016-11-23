<?php
/**
 * REST Route Provider.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Interface Route_Provider
 * @package iThemes\Exchange\REST
 */
interface Route_Provider {

	/**
	 * Get the routes.
	 *
	 * @since 2.0.0
	 *
	 * @return Route[]
	 */
	public function get_routes();
}
