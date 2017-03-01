<?php
/**
 * Postable Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;
use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Interface Postable
 *
 * @package iThemes\Exchange\REST
 */
interface Postable extends Route {

	/**
	 * Handle a POST request.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_post( Request $request );

	/**
	 * Whether the user has permission to access this route.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 * @param AuthScope                      $scope
	 *
	 * @return bool
	 */
	public function user_can_post( Request $request, AuthScope $scope );
}
