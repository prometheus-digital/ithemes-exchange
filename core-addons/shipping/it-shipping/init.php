<?php
/**
 * This file inits our Shipping add-on. 
 * It is only included when the add-on is enabled.
 * @package IT_Exchange
 * @since 1.0.0
*/

/**
 * This file includes our functions / hooks for adding a purchase requirement
 * Purchase requirements force certain conditionals to be met before the customer
 * is able to make a purchase. It also provides the add-on with the ability to insert
 * additional template parts into the SuperWidget and checkout page to collect or
 * display information required for the purchase. The supporting API functions can
 * be found in ithemes-exchange/api/misc.php
*/
include( dirname( __FILE__ ) . '/lib/purchase-requirements.php' );

/**
 * This file includes our functions / hooks for adding a settings page and saving those settings.
 * You can roll your own settings page if you want, but our API will create the little gear for you
 * on the Exchange add-ons page.
*/
include( dirname( __FILE__ ) . '/lib/settings.php' );

/**
 * This file contains all the functions / hooks we need to register custom template parts used
 * by our add-on. We can tell Exchange to look in our directory structure for template-parts.
*/
include( dirname( __FILE__ ) . '/lib/template-parts.php' );

/**
 * This file contains common function you might find in any WP plugin. Things like 
 * enqueuing scripts and utility functions
*/
include( dirname( __FILE__ ) . '/lib/functions.php' );

/**
 * This file contains the API calls related to Shipping Providers
*/
include( dirname( __FILE__ ) . '/api/providers.php' );

/**
 * This file contains the default Exchange Shipping Provider code
*/
include( dirname( __FILE__ ) . '/lib/provider-exchange-standard.php' );
