<?php
/**
 * Gateway Request Handler.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Gateway_Request_Handler
 */
interface ITE_Gateway_Request_Handler {

	/**
	 * Handle a gateway request.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Gateway_Request $request
	 *
	 * @return mixed
	 */
	public function handle( $request );

	/**
	 * Determine if this request handler can handle a given request.
	 *
	 * @since 2.0.0
	 *
	 * @param string $request_name The name of the request. {@see ITE_Gateway_Request::get_name()}.
	 *
	 * @return bool
	 */
	public static function can_handle( $request_name );
}
