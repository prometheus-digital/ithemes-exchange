<?php
/**
 * Load the location module.
 *
 * @since   2.0.0
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/interface.location.php';
require_once dirname( __FILE__ ) . '/class.table.php';
require_once dirname( __FILE__ ) . '/class.saved-address.php';
require_once dirname( __FILE__ ) . '/class.in-memory-address.php';

require_once dirname( __FILE__ ) . '/interface.zone.php';
require_once dirname( __FILE__ ) . '/class.simple-zone.php';
require_once dirname( __FILE__ ) . '/class.multidimensional-zone.php';

require_once dirname( __FILE__ ) . '/interface.validator.php';
require_once dirname( __FILE__ ) . '/class.validators.php';

add_filter( 'it_exchange_cart_validators', function ( $validators ) {
	return array_merge( $validators, ITE_Location_Validators::all() );
} );

\IronBound\DB\Manager::register( new ITE_Saved_Address_Table() );
