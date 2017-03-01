<?php
/**
 * Deletable Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;
use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Interface Deletable
 *
 * @package iThemes\Exchange\REST
 */
interface Deletable extends Route {

	/**
	 * Handle a DELETE request.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_delete( Request $request );

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
	public function user_can_delete( Request $request, AuthScope $scope );
}
