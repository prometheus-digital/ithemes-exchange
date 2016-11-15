<?php
/**
 * Load the coupons module.
 *
 * @since   2.0.0
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/class.coupons-post-type.php';
require_once dirname( __FILE__ ) . '/class.coupon.php';
require_once dirname( __FILE__ ) . '/hooks.php';
require_once dirname( __FILE__ ) . '/class.object-type.php';

add_action( 'it_exchange_register_object_types', function ( ITE_Object_Type_Registry $registry ) {
	$registry->register( new ITE_Coupon_Object_Type() );
} );
