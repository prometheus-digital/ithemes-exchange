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
require_once dirname( __FILE__ ) . '/class.deprecated-meta.php';

require_once dirname( __FILE__ ) . '/activity/load.php';

\IronBound\DB\Manager::register( new ITE_Transactions_Table(), '', '\IT_Exchange_Transaction' );
\IronBound\DB\Manager::maybe_install_table( \IronBound\DB\Manager::get( 'ite-transactions' ) );

$deprecated_meta = new ITE_Transaction_Deprecated_Meta();
$deprecated_meta->add_pair( '_it_exchange_customer_id', 'customer_id' );
$deprecated_meta->add_pair( '_it_exchange_transaction_status', 'status' );
$deprecated_meta->add_pair( '_it_exchange_transaction_method', 'method' );
$deprecated_meta->add_pair( '_it_exchange_transaction_method_id', 'method_id' );
$deprecated_meta->add_pair( '_it_exchange_transaction_hash', 'hash' );
$deprecated_meta->add_pair( '_it_exchange_cart_id', 'cart_id' );