<?php
/**
 * Load the transaction module.
 *
 * @since   1.36.0
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/class.transaction.php';
require_once dirname( __FILE__ ) . '/class.transactions-post-type.php';
require_once dirname( __FILE__ ) . '/class.purchase-table.php';
require_once dirname( __FILE__ ) . '/class.purchase.php';

require_once dirname( __FILE__ ) . '/activity/load.php';

\IronBound\DB\Manager::register( new ITE_Purchase_Table(), '', '\ITE_Purchase' );
\IronBound\DB\Manager::maybe_install_table( \IronBound\DB\Manager::get( 'ite-purchases' ) );