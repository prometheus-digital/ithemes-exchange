<?php
/**
 * Load the customers module.
 *
 * @since   1.36.0
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/class.customer.php';
require_once dirname( __FILE__ ) . '/class.guest.php';
require_once dirname( __FILE__ ) . '/class.object-type.php';

add_action( 'it_exchange_register_object_types', function ( ITE_Object_Type_Registry $registry ) {
	$registry->register( new ITE_Customer_Object_Type() );
} );