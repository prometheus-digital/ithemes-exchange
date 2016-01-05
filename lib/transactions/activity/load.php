<?php
/**
 * Load the transaction activity component.
 *
 * @since   1.34
 * @license GPLv2
 */

// infrastructure
require_once dirname( __FILE__ ) . '/interface.activity.php';
require_once dirname( __FILE__ ) . '/abstract.activity.php';
require_once dirname( __FILE__ ) . '/interface.actor.php';
require_once dirname( __FILE__ ) . '/class.builder.php';
require_once dirname( __FILE__ ) . '/class.factory.php';
require_once dirname( __FILE__ ) . '/class.collection.php';

// activity actors
require_once dirname( __FILE__ ) . '/actors/class.factory.php';
require_once dirname( __FILE__ ) . '/actors/class.customer.php';
require_once dirname( __FILE__ ) . '/actors/class.site.php';
require_once dirname( __FILE__ ) . '/actors/class.user.php';
require_once dirname( __FILE__ ) . '/actors/class.gateway.php';

// activity types
require_once dirname( __FILE__ ) . '/types/class.note.php';
require_once dirname( __FILE__ ) . '/types/class.renewal.php';
require_once dirname( __FILE__ ) . '/types/class.status.php';

require_once dirname( __FILE__ ) . '/functions.php';