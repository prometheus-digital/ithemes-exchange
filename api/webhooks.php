<?php
/**
 * Webhooks API functions.
 *
 * @since 2.0.0
 * @license GPLv2
 */

/**
 * Grab all registered webhook / IPN keys
 *
 * @since 0.4.0
 *
 * @return array
 */
function it_exchange_get_webhooks() {
	$webhooks = empty( $GLOBALS['it_exchange']['webhooks'] ) ? array() : (array) $GLOBALS['it_exchange']['webhooks'];
	return apply_filters( 'it_exchange_get_webhooks', $webhooks );
}

/**
 * Register a webhook / IPN key
 *
 * @since 0.4.0
 * @since 2.0.0 Add $options parameter.
 *
 * @param string $key   the addon slug or ID
 * @param string $param the REQUEST param we are listening for
 * @param array  $options
 *
 * @return void
 */
function it_exchange_register_webhook( $key, $param, array $options = array() ) {

	$options = ITUtility::merge_defaults( $options, array(
		'use_path' => false,
	) );

	$GLOBALS['it_exchange']['webhooks'][$key] = $param;
	$GLOBALS['it_exchange']['webhooks_options'][$key] = $options;

	/**
	 * Fires when a webhook is registered.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Add $options parameter.
	 *
	 * @param string $key
	 * @param string $param
	 * @param array  $options
	 */
	do_action( 'it_exchange_register_webhook', $key, $param, $options );
}

/**
 * Grab a specific registered webhook / IPN param
 *
 * @since 0.4.0
 *
 * @param string $key the key for the param we are looking for
 *
 * @return string|bool or false
 */
function it_exchange_get_webhook( $key ) {

	$webhook = empty( $GLOBALS['it_exchange']['webhooks'][$key] ) ? false : $GLOBALS['it_exchange']['webhooks'][$key];

	return apply_filters( 'it_exchange_get_webhook', $webhook, $key );
}

/**
 * Get webhook options.
 *
 * @since 2.0.0
 *
 * @param string $key
 *
 * @return array
 */
function it_exchange_get_webhook_options( $key ) {

	if ( ! isset( $GLOBALS['it_exchange']['webhooks_options'][$key] ) ) {
		return array();
	}

	$options = $GLOBALS['it_exchange']['webhooks_options'][$key];

	/**
	 * Get webhook options.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $options
	 * @param string $key
	 */
	return apply_filters( 'it_exchange_get_webhook_options', $options, $key );
}

/**
 * Check what webhook is being processed.
 *
 * @since 1.34
 *
 * @param string $webhook Optionally, specify the webhook to compare against.
 *
 * @return string|bool If called with $webhook parameter, will return whether that webhook is being evaluated.
 *                     If called without, will return the webhook key of the currently firing webhook, or false.
 */
function it_exchange_doing_webhook( $webhook = '' ) {

	if ( $webhook ) {

		$param   = it_exchange_get_webhook( $webhook );
		$options = it_exchange_get_webhook_options( $webhook );

		if ( $options && ! $options['use_path'] && ! isset( $_REQUEST[ $param ] ) ) {
			return false;
		}

		$request_scheme = is_ssl() ? 'https://' : 'http://';

		// REQUEST_URI includes the slash
		$requested_url        = untrailingslashit( $request_scheme . $_SERVER['HTTP_HOST'] ) . $_SERVER['REQUEST_URI'];
		$requested_url        = trailingslashit( $requested_url );
		$requested_url_parsed = parse_url( $requested_url );

		$required_url        = trailingslashit( it_exchange_get_webhook_url( $webhook ) ); //add the slash to make sure we match
		$required_url_parsed = parse_url( $required_url );

		return $requested_url_parsed === $required_url_parsed;
	}

	foreach ( it_exchange_get_webhooks() as $key => $param ) {

		if ( it_exchange_doing_webhook( $key ) ) {
			return $key;
		}
	}

	return false;
}

/**
 * Get a URL for a webhook.
 *
 * @since 2.0.0
 *
 * @param string $webhook_key
 *
 * @return string
 */
function it_exchange_get_webhook_url( $webhook_key ) {

	$param   = it_exchange_get_webhook( $webhook_key );
	$options = it_exchange_get_webhook_options( $webhook_key );

	if ( ! $param ) {
		return '';
	}

	if ( $options['use_path'] ) {
		$url = home_url( "it_exchange_webhook/{$param}" );
	} else {
		$url = get_home_url() . '/?' . $param . '=1';
	}

	return $url;
}