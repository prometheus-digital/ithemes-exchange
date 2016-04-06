<?php
/**
 * Contains the middleware handler class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Middleware_Handler
 */
class IT_Exchange_Email_Middleware_Handler {

	/**
	 * @var IT_Exchange_Email_Middleware[]
	 */
	private $middleware = array();

	/**
	 * Push a bit of middleware onto the stack.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Middleware $middleware
	 *
	 * @return self
	 */
	public function push( IT_Exchange_Email_Middleware $middleware ) {

		$this->middleware[] = $middleware;

		return $this;
	}

	/**
	 * Execute all middleware on an email.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Sendable_Mutable_Wrapper $sendable
	 *
	 * @return bool
	 */
	public function handle( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

		foreach ( $this->middleware as $middleware ) {
			if ( ! $middleware->handle( $sendable ) ) {
				return false;
			}
		}

		return true;
	}
}