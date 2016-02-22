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

// load skins and handlers
require_once dirname( __FILE__ ) . '/skins/class.ajax.php';
require_once dirname( __FILE__ ) . '/handlers/class.ajax.php';

// load routines
require_once dirname( __FILE__ ) . '/routines/class.coupons.php';
require_once dirname( __FILE__ ) . '/routines/class.txn-activity.php';