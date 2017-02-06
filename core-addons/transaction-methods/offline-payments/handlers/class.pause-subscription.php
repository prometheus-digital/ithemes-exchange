<?php
/**
 * Pause Subscription handler.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Offline_Payments_Pause_Subscription_Handler
 */
class ITE_Offline_Payments_Pause_Subscription_Handler implements ITE_Gateway_Request_Handler {

	/**
	 * @inheritDoc
	 * @param ITE_Pause_Subscription_Request $request
	 */
	public function handle( $request ) {
		$request->get_subscription()->set_paused_by( $request->get_paused_by() );

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === 'pause-subscription'; }
}