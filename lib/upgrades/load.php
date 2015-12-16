<?php
/**
 * Load the upgrade routine library.
 *
 * @since   1.33
 * @license GPLv2
 */

defined( 'ABSPATH' ) || die();

require_once dirname( __FILE__ ) . '/class.exception.php';
require_once dirname( __FILE__ ) . '/class.config.php';
require_once dirname( __FILE__ ) . '/interface.skin.php';
require_once dirname( __FILE__ ) . '/interface.upgrade.php';
require_once dirname( __FILE__ ) . '/class.upgrader.php';
require_once dirname( __FILE__ ) . '/functions.php';

require_once dirname( __FILE__ ) . '/skins/class.ajax.php';
require_once dirname( __FILE__ ) . '/handlers/class.ajax.php';

if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	$ajax_handler = new IT_Exchange_Upgrade_Handler_Ajax( it_exchange_make_upgrader() );
	$ajax_handler->hooks();
}