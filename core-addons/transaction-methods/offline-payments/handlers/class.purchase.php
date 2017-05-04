<?php
/**
 * Offline Payments Purchase Request Handler.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Offline_Payments_Purchase_Request_Handler
 */
class ITE_Offline_Payments_Purchase_Request_Handler extends ITE_Purchase_Request_Handler {

	/**
	 * @inheritDoc
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 */
	public function handle( $request ) {

		if ( ! static::can_handle( $request::get_name() ) ) {
			throw new InvalidArgumentException();
		}

		if ( ! wp_verify_nonce( $request->get_nonce(), $this->get_nonce_action() ) ) {
			$request->get_cart()->get_feedback()->add_error(
				__( 'Purchase failed. Unable to verify security token.', 'it-l10n-ithemes-exchange' )
			);

			return null;
		}

		$status    = $this->gateway->settings()->get( 'offline-payments-default-status' );
		$method_id = it_exchange_get_offline_transaction_uniqid();

		$txn_id = it_exchange_add_transaction(
			'offline-payments',
			$method_id,
			$status,
			$request->get_cart()
		);

		if ( ! $txn_id ) {
			return null;
		}

		return it_exchange_get_transaction( $txn_id );
	}

	/**
	 * @inheritDoc
	 */
	public function get_payment_button_label() {

		if ( $this->get_gateway()->settings()->has( 'offline-payments-title' ) ) {
			return $this->get_gateway()->settings()->get( 'offline-payments-title' );
		}

		return parent::get_payment_button_label();
	}
}
