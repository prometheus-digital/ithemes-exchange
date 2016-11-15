<?php
/**
 * Load the refunds module.
 *
 * @since   2.0.0
 * @license GPLv2
 */

use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Manager;

require_once dirname( __FILE__ ) . '/class.refund.php';
require_once dirname( __FILE__ ) . '/class.table.php';
require_once dirname( __FILE__ ) . '/class.object-type.php';

Manager::register( new ITE_Refunds_Table(), '', 'ITE_Refund' );
Manager::register( new BaseMetaTable( Manager::get( 'ite-refunds' ), array(
	'primary_id_column' => 'refund_id'
) ) );

Manager::maybe_install_table( Manager::get( 'ite-refunds' ) );
Manager::maybe_install_table( Manager::get( 'ite-refunds-meta' ) );

add_action( 'it_exchange_register_object_types', function ( ITE_Object_Type_Registry $registry ) {
	$registry->register( new ITE_Refund_Object_Type() );
} );
