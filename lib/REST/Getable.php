<?php
/**
 * Getable Route.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Interface Getable
 * @package iThemes\Exchange\REST
 */
interface Getable extends Route {

	/**
	 * Handle a GET request.
	 *
	 * @since 1.36.0
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_get( \WP_REST_Request $request );

	/**
	 * Whether the user has permission to access this route.
	 *
	 * @since 1.36.0
	 *
	 * @param \WP_REST_Request $request
	 * @param \WP_User         $user
	 *
	 * @return bool
	 */
	public function user_can_get( \WP_REST_Request $request, \WP_User $user );
}