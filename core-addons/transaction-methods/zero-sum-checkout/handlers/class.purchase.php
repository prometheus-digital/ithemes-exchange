<?php
/**
 * Purchase handler for Zero Sum Checkout.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Zero_Sum_Checkout_Purchase_Handler
 */
class ITE_Zero_Sum_Checkout_Purchase_Handler extends ITE_Purchase_Request_Handler {
	/**
	 * @inheritDoc
	 */
	public function render_payment_button( ITE_Gateway_Purchase_Request $request ) {

		$total = it_exchange_get_cart_total( false, array( 'cart' => $request->get_cart() ) );

		if ( $total > 0 ) {
			return '';
		}

		return parent::render_payment_button( $request );
	}

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

		$uniqid = it_exchange_get_zero_sum_checkout_transaction_uniqid();
		$txn_id = it_exchange_add_transaction( 'zero-sum-checkout', $uniqid, 'Completed', $request->get_cart() );

		return it_exchange_get_transaction( $txn_id ) ?: null;
	}

	/**
	 * @inheritDoc
	 */
	public function can_handle_cart( ITE_Cart $cart ) {
		return $cart->get_total() <= 0 && ! $cart->contains_non_recurring_fee();
	}

	/**
	 * @inheritDoc
	 */
	public function supports_feature( ITE_Optionally_Supported_Feature $feature ) {

		switch ( $feature->get_feature_slug() ) {
			case 'recurring-payments':
				return true;
		}

		return parent::supports_feature( $feature );
	}

	/**
	 * @inheritDoc
	 */
	public function supports_feature_and_detail( ITE_Optionally_Supported_Feature $feature, $slug, $detail ) {

		switch ( $feature->get_feature_slug() ) {
			case 'recurring-payments':
				switch ( $slug ) {
					case 'auto-renew':
					case 'profile':
					case 'trial':
					case 'trial-profile':
						return true;
					default:
						return false;
				}
		}

		return parent::supports_feature( $feature );
	}

	/**
	 * @inheritDoc
	 */
	public function get_payment_button_label() {
		/**
		 * Filter the Purchase Button label for Zero-Sum-Checkout.
		 *
		 * @since 1.0.0
		 *
		 * @param string $label
		 */
		return apply_filters( 'zero_sum_checkout_button_label', __( 'Complete Purchase', 'it-l10n-ithemes-exchange' ) );
	}
}
