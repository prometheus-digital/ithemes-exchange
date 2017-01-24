<?php
/**
 * Load the object types module.
 *
 * @since   2.0.0
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/interface.object.php';
require_once dirname( __FILE__ ) . '/interface.type.php';
require_once dirname( __FILE__ ) . '/interface.restful.php';
require_once dirname( __FILE__ ) . '/interface.with-meta.php';

require_once dirname( __FILE__ ) . '/types/class.table.php';
require_once dirname( __FILE__ ) . '/types/class.table-with-meta.php';
require_once dirname( __FILE__ ) . '/types/class.user.php';
require_once dirname( __FILE__ ) . '/types/class.cpt.php';

require_once dirname( __FILE__ ) . '/class.registry.php';

/**
 * Get the object type registry.
 *
 * @since 2.0.0
 *
 * @return ITE_Object_Type_Registry
 */
function it_exchange_object_type_registry() {
	static $registry = null;

	if ( ! $registry ) {
		$registry = new ITE_Object_Type_Registry();

		/**
		 * Register Object Types.
		 *
		 * @since 2.0.0
		 *
		 * @param \ITE_Object_Type_Registry $registry
		 */
		do_action( 'it_exchange_register_object_types', $registry );
	}

	return $registry;
}

add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_object_type_registry' );