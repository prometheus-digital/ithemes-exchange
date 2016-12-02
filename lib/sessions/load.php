<?php
/**
 * Load the session manager.
 *
 * @since   2.0.0
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/interface.php';
require_once dirname( __FILE__ ) . '/class.session.php';
require_once dirname( __FILE__ ) . '/class.in-memory.php';
require_once dirname( __FILE__ ) . '/db_session_manager/class.table.php';
require_once dirname( __FILE__ ) . '/db_session_manager/class.model.php';

if ( ! isset( $GLOBALS['it_exchange']['session'] ) ) {
	$GLOBALS['it_exchange']['session'] = new IT_Exchange_Session();
}

\IronBound\DB\Manager::register( new ITE_Sessions_Table(), '', 'ITE_Session_Model' );
