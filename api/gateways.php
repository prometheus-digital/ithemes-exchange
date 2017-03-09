<?php
/**
 * Gateway API functions.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Check if a gateway is accepting payments.
 *
 * A payment gateway can be activated, so that past transactions can work properly,
 * but no longer accepting new payments.
 *
 * @since 2.0.0
 *
 * @param \ITE_Gateway|string $gateway
 *
 * @return bool
 */
function it_exchange_is_gateway_accepting_payments( $gateway ) {

	if ( $gateway instanceof ITE_Gateway ) {
		$gateway = $gateway->get_slug();
	}

	if ( $gateway === 'zero-sum-checkout' ) {
		return true;
	}

	$accepting_payments = get_option( 'it_exchange_gateways_accepting_payments', array() );

	if ( ! isset( $accepting_payments[ $gateway ] ) ) {
		return true;
	}

	return (bool) $accepting_payments[ $gateway ];
}

/**
 * Is the gateway in sandbox mode.
 *
 * @since 2.0.0
 *
 * @param string|ITE_Gateway $gateway
 *
 * @return bool
 */
function it_exchange_is_gateway_in_sandbox_mode( $gateway ) {

	$gateway = $gateway instanceof ITE_Gateway ? $gateway : ITE_Gateways::get( $gateway );

	if ( ! $gateway ) {
		return false;
	}

	return $gateway->is_sandbox_mode();
}

/**
 * Get the payment button label for a gateway.
 *
 * @since 2.0.0
 *
 * @param ITE_Gateway|string $gateway
 *
 * @return string
 */
function it_exchange_get_payment_button_label( $gateway ) {

	$gateway = $gateway instanceof ITE_Gateway ? $gateway : ITE_Gateways::get( $gateway );

	if ( $gateway ) {
		$label = $gateway->get_payment_button_label();
	} else {
		$label = __( 'Purchase', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * Filter the payment button label.
	 *
	 * @since 2.0.0
	 *
	 * @param string           $label
	 * @param ITE_Gateway|null $gateway
	 */
	return apply_filters( 'it_exchange_get_payment_button_label', $label, $gateway );
}