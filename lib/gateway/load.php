<?php
/**
 * Load the gateway API.
 *
 * @since   1.36
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/interface.request.php';
require_once dirname( __FILE__ ) . '/interface.request-handler.php';
require_once dirname( __FILE__ ) . '/class.request-factory.php';
require_once dirname( __FILE__ ) . '/requests/class.purchase.php';
require_once dirname( __FILE__ ) . '/handlers/class.purchase.php';
require_once dirname( __FILE__ ) . '/handlers/class.dialog-purchase.php';
require_once dirname( __FILE__ ) . '/handlers/class.iframe-purchase.php';
require_once dirname( __FILE__ ) . '/handlers/class.redirect-purchase.php';

require_once dirname( __FILE__ ) . '/class.gateway.php';
require_once dirname( __FILE__ ) . '/class.gateways.php';

add_action( 'it_exchange_enabled_addons_loaded', function () {

	/**
	 * Register payment gateways.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Gateways $gateways
	 */
	do_action( 'it_exchange_register_gateways', new ITE_Gateways() );
} );