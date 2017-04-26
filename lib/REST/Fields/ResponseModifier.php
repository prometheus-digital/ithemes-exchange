<?php
/**
 * For query args that modify the repsonse.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Fields;

use iThemes\Exchange\REST\Request;

/**
 * Interface ResponseModifier
 *
 * @package iThemes\Exchange\REST\Fields
 */
interface ResponseModifier {

	/**
	 * Modify the response based on this query arg.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_REST_Response $response
	 * @param Request           $request
	 */
	public function modify_response( \WP_REST_Response $response, Request $request );
}