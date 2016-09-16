<?php
/**
 * REST Controller Interface.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Interface Controller
 * @package iThemes\Exchange\REST
 */
interface Controller extends Route_Provider {

	/**
	 * Get all routes in this controller.
	 *
	 * @since 1.36.0
	 *
	 * @return Route[]
	 */
	public function get_routes();

}