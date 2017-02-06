<?php
/**
 * Resume Subscription handler.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Offline_Payments_Resume_Subscription_Handler
 */
class ITE_Offline_Payments_Resume_Subscription_Handler implements ITE_Gateway_Request_Handler {

	/**
	 * @inheritDoc
	 * @param ITE_Resume_Subscription_Request $request
	 */
	public function handle( $request ) {
		$request->get_subscription()->set_resumed_by( $request->get_resumed_by() );

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === 'resume-subscription'; }
}