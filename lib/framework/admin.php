<?php
/**
 * CartBuddy admin class.
 *
 * This class manages the admin side of the plugin
 *
 * @package IT_CartBuddy
 * @since 0.1
*/
if ( ! class_exists( 'IT_CartBuddy_Admin' ) ) {
	class IT_CartBuddy_Admin {

		/**
		 * Parent Class
		 * @var _parent object Parent Class
		 * @since 0.1
		*/
		var $_parent;

		/**
		 * Class constructor
		 *
		 * @uses add_action()
		 * @since 0.1
		 * @return null
		*/
		function IT_CartBuddy_Admin( &$parent ) {

			// Set parent property
			$this->_parent = $parent;

			// Add actions for iThemes registration
			add_action( 'init', array( &$parent, 'upgrader_register' ), 50 );
			add_action( 'init', array( &$parent, 'upgrader_select' ), 100 );
			add_action( 'init', array( &$parent, 'upgrader_instantiate' ), 101 );

		}
	}
}
