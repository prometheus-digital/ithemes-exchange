<?php
/**
 * Contains the middleware interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Email_Middleware
 */
interface IT_Exchange_Email_Middleware {

	/**
	 * Handle a sendable object before it has been sent.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Sendable_Mutable_Wrapper $sendable
	 *
	 * @return bool True to continue, false to stop email sending.
	 */
	public function handle( IT_Exchange_Sendable_Mutable_Wrapper $sendable );
}