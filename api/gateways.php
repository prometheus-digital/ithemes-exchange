<?php
/**
 * Gateway API functions.
 *
 * @since 2.0.0
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
