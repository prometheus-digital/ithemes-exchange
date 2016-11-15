<?php
/**
 * Load the products module.
 *
 * @since   1.36.0
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/class.products-post-type.php';
require_once dirname( __FILE__ ) . '/class.product.php';
require_once dirname( __FILE__ ) . '/class.factory.php';
require_once dirname( __FILE__ ) . '/class.object-type.php';

add_action( 'it_exchange_register_object_types', function ( ITE_Object_Type_Registry $registry ) {
	$registry->register( new ITE_Product_Object_Type() );
} );