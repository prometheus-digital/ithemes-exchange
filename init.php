<?php
/*
 * Plugin Name: iThemes Exchange
 * Version: 2.0.0
 * Text Domain: it-l10n-ithemes-exchange
 * Description: Easily sell your digital goods with iThemes Exchange, simple ecommerce for WordPress
 * Plugin URI: http://ithemes.com/exchange/
 * Author: iThemes
 * Author URI: http://ithemes.com
 *
 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

/**
 * Whether to load deprecated code.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function it_exchange_load_deprecated() {

	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
		return true;
	}

	if ( get_option( 'it_exchange_load_deprecated', false ) ) {
		return true;
	}

	if ( defined( 'IT_EXCHANGE_LOAD_DEPRECATED' ) && IT_EXCHANGE_LOAD_DEPRECATED ) {
		return true;
	}

	if ( it_exchange_get_addons_not_v2_ready() ) {
		return true;
	}

	return false;
}

/**
 * Whether all add-ons meet the requirement for version 2.
 *
 * @since 2.0.0
 *
 * @return array
 */
function it_exchange_get_addons_not_v2_ready() {

	$required = get_option( 'it_exchange_addons_not_met_v2_requirement', false );

	if ( is_array( $required ) && count( $required ) === 0 ) {
		return array();
	}

	if ( ! is_admin() && ! defined( 'WP_TESTS_TABLE_PREFIX' ) ) {
		return $required === false ? array( array( 'name' => 'Filler' ) ) : $required;
	}

	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	$required = array();

	foreach ( it_exchange_addons_requiring_v2() as $addon ) {
		if ( ! is_plugin_active( $addon['plugin'] ) ) {
			continue;
		}

		$data    = get_plugin_data( WP_PLUGIN_DIR . '/' . $addon['plugin'], false, false );
		$version = $data['Version'];

		if ( $version === null ) {
			$required[] = array(
				'name'      => $addon['name'],
				'installed' => null,
			);
		} elseif ( version_compare( $version, '2.0.0', '<' ) ) {
			$required[] = array(
				'name'      => $addon['name'],
				'installed' => $version,
			);
		}
	}

	update_option( 'it_exchange_addons_not_met_v2_requirement', $required );

	return $required;
}

/**
 * Get a list of all add-ons that are required to be version 2 or later.
 *
 * @since 2.0.0
 *
 * @return array
 */
function it_exchange_addons_requiring_v2() {
	return array(
		array(
			'plugin' => 'exchange-addon-abandoned-carts/exchange-addon-abandoned-carts.php',
			'name'   => 'Abandoned Carts',
		),
		array(
			'plugin' => 'exchange-addon-authorizenet/exchange-addon-authorizenet.php',
			'name'   => 'Authorize.Net',
		),
		array(
			'plugin' => 'exchange-addon-easy-canadian-sales-taxes/exchange-addon-easy-canadian-sales-taxes.php',
			'name'   => 'Canadian Sales Taxes',
		),
		array(
			'plugin' => 'exchange-addon-easy-eu-value-added-taxes/exchange-addon-easy-eu-value-added-taxes.php',
			'name'   => 'EU Value Added Taxes',
		),
		array(
			'plugin' => 'exchange-addon-easy-us-sales-taxes/exchange-addon-easy-us-sales-taxes.php',
			'name'   => 'U.S. Sales Taxes',
		),
		array(
			'plugin' => 'exchange-addon-invoices/exchange-addon-invoices.php',
			'name'   => 'Invoices',
		),
		array(
			'plugin' => 'exchange-addon-membership/exchange-addon-membership.php',
			'name'   => 'Memberships',
		),
		array(
			'plugin' => 'exchange-addon-recurring-payments/exchange-addon-recurring-payments.php',
			'name'   => 'Recurring Payments',
		),
		array(
			'plugin' => 'exchange-addon-stripe/exchange-addon-stripe.php',
			'name'   => 'Stripe',
		),
		array(
			'plugin' => 'exchange-addon-table-rate-shipping/exchange-addon-table-rate-shipping.php',
			'name'   => 'Table Rate Shipping',
		),
	);
}

/**
 * Whether loading the deprecated code is toggleable by the user or not.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function it_exchange_load_deprecated_is_toggleable() {

	if ( defined( 'IT_EXCHANGE_LOAD_DEPRECATED' ) ) {
		return false;
	}

	if ( it_exchange_get_addons_not_v2_ready() ) {
		return false;
	}

	return true;
}

if ( it_exchange_load_deprecated() ) {
	require_once dirname( __FILE__ ) . '/deprecated/init.php';
} else {
	require_once dirname( __FILE__ ) . '/ithemes-exchange.php';
}