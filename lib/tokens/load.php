<?php
/**
 * Load the tokens module.
 *
 * @since   1.36.0
 * @license GPLv2
 */

use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Manager;

require_once dirname( __FILE__ ) . '/class.table.php';
require_once dirname( __FILE__ ) . '/class.payment-token.php';

Manager::register( new ITE_Payment_Tokens_Table(), '', 'ITE_Payment_Token' );
Manager::register( new BaseMetaTable( Manager::get( 'ite-payment-tokens' ), array( 'primary_id_column' => 'token' ) ) );
Manager::maybe_install_table( Manager::get( 'ite-payment-tokens' ) );
Manager::maybe_install_table( Manager::get( 'ite-payment-tokens-meta' ) );

