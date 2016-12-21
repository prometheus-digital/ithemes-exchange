<?php
/**
 * Load the tokens module.
 *
 * @since   2.0.0
 * @license GPLv2
 */

use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Manager;

require_once dirname( __FILE__ ) . '/class.table.php';
require_once dirname( __FILE__ ) . '/class.object-type.php';
require_once dirname( __FILE__ ) . '/class.payment-token.php';
require_once dirname( __FILE__ ) . '/class.card.php';
require_once dirname( __FILE__ ) . '/class.bank-account.php';

Manager::register( new ITE_Payment_Tokens_Table(), '', 'ITE_Payment_Token' );
Manager::register( new BaseMetaTable( Manager::get( 'ite-payment-tokens' ), array( 'primary_id_column' => 'token' ) ) );

ITE_Payment_Token::register_token_type( 'card', 'ITE_Payment_Token_Card', __( 'Card', 'it-l10n-ithemes-exchange' ) );
ITE_Payment_Token::register_token_type( 'bank', 'ITE_Payment_Token_Bank_Account', 'Bank Account', 'it-10n-ithemes-exchange' );

add_action( 'it_exchange_register_object_types', function ( ITE_Object_Type_Registry $registry ) {
	$registry->register( new ITE_Payment_Token_Object_Type() );
} );
