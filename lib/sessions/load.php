<?php
/**
 * Load the session manager.
 *
 * @since   1.36
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/interface.php';
require_once dirname( __FILE__ ) . '/class.session.php';

$GLOBALS['it_exchange']['session'] = new IT_Exchange_Session();