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
 * Get the available transaction methods for a cart.
 *
 * @since 2.0.0
 *
 * @param ITE_Cart|null $cart
 *
 * @return ITE_Purchase_Request_Handler[]
 */
function it_exchange_get_available_transaction_methods_for_cart( ITE_Cart $cart = null ) {

	$cart = $cart ?: it_exchange_get_current_cart();

	$methods = array();

	foreach ( ITE_Gateways::accepting() as $gateway ) {

		$handlers = $gateway->get_handlers_by_request_name( 'purchase' );

		/** @var ITE_Purchase_Request_Handler $handler */
		foreach ( $handlers as $handler ) {
			if ( $handler->can_handle_cart( $cart ) ) {
				$methods[] = $handler;
			}
		}
	}

	/**
	 * Filter the available transaction methods for a given cart.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Purchase_Request_Handler[] $methods
	 * @param \ITE_Cart                       $cart
	 */
	return apply_filters( 'it_exchange_available_transaction_methods_for_cart', $methods, $cart );
}

/**
 * Get a purchase handler by its ID.
 *
 * @since 2.0.0
 *
 * @param string        $id
 * @param ITE_Cart|null $cart
 *
 * @return ITE_Purchase_Request_Handler|null
 */
function it_exchange_get_purchase_handler_by_id( $id, ITE_Cart $cart = null ) {

	$available = it_exchange_get_available_transaction_methods_for_cart( $cart );

	foreach ( $available as $handler ) {
		if ( $handler->get_id() === $id ) {
			return $handler;
		}
	}

	return null;
}