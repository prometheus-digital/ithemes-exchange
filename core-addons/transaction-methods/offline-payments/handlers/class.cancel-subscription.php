<?php
/**
 * Offline Payments Cancel Subscription Handler.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Offline_Payments_Cancel_Subscription_Handler
 */
class ITE_Offline_Payments_Cancel_Subscription_Handler implements ITE_Gateway_Request_Handler {

	/**
	 * @inheritDoc
	 *
	 * @param ITE_Cancel_Subscription_Request $request
	 */
	public function handle( $request ) {

		if ( $request->should_set_status() ) {
			$request->get_subscription()->set_status( IT_Exchange_Subscription::STATUS_CANCELLED );
		}

		if ( $request->get_cancelled_by() ) {
			$request->get_subscription()->set_cancelled_by( $request->get_cancelled_by() );
		}

		if ( $request->get_reason() ) {
			$request->get_subscription()->set_cancellation_reason( $request->get_reason() );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public static function can_handle( $request_name ) { return $request_name === 'cancel-subscription'; }
}