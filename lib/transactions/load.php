<?php
/**
 * Load the transaction module.
 *
 * @since   1.36.0
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/class.transaction.php';
require_once dirname( __FILE__ ) . '/class.transactions-post-type.php';
require_once dirname( __FILE__ ) . '/class.transactions-table.php';
require_once dirname( __FILE__ ) . '/class.query.php';
require_once dirname( __FILE__ ) . '/class.meta-sync.php';

require_once dirname( __FILE__ ) . '/activity/load.php';

\IronBound\DB\Manager::register( new ITE_Transactions_Table(), '', '\IT_Exchange_Transaction' );
\IronBound\DB\Manager::maybe_install_table( \IronBound\DB\Manager::get( 'ite-transactions' ) );

$meta_sync = new ITE_Transaction_Meta_Sync();
$meta_sync->add_pair( '_it_exchange_customer_id', 'customer_id' );
$meta_sync->add_pair( '_it_exchange_transaction_status', 'status' );
$meta_sync->add_pair( '_it_exchange_transaction_method', 'method' );
$meta_sync->add_pair( '_it_exchange_transaction_method_id', 'method_id' );
$meta_sync->add_pair( '_it_exchange_transaction_hash', 'hash' );
$meta_sync->add_pair( '_it_exchange_cart_id', 'cart_id' );