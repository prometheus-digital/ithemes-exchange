<?php
/**
 * Main CartBuddy API Class
 *
 * @package IT_CartBuddy
 * @since 0.1
*/

if ( ! class_exists( 'IT_CartBuddy_API' ) ) {
	class IT_CartBuddy_API {

		/**
		 * API Version Number
		 * @var string $_version
		*/
		var $_version = '1.0';

		/**
		 * This method creates a post type using /lib/classes/it-post-type.php
		 *
		 * @since 0.1
		*/
		function register_post_type( $options ) {

			// Throw error if missing required options
			$required_options = array( 'var', 'name', 'name_plural', );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			return new IT_CartBuddy_Post_Type( $options );
		}

		/**
		 * Creates a meta box for a post type.
		 *
		*/
		function add_meta_box( $options ) {
			
			// Throw error if missing required options
			$required_options = array( 'var', 'title', 'callback', );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			if ( empty( $options['post_type'] ) )
				$options['post_type'] = 'it_cb_product';

			$GLOBALS['it_cartbuddy']['meta-boxes'][$options['post_type']][$options['var']] = $options;

		}

		/**
		 * Adds an admin page to the CartBuddy Product type
		 *
		 * If the admin page doesn't exist yet, it will be created and the callback will populate it.
		 * If the admin page already exists, it will be added as an additional tab
		 * Default adds menu pages to the 'it_cb_product' post type
		 *
		 * @since 0.1
		*/
		function add_admin_page( $options ) {

			// Throw error if missing required options
			$required_options = array( 'var', 'page_title', 'menu_title', 'capability', 'callback' );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			// Default to core product post type if none is passed
			if ( empty( $options['post_type'] ) )
				$options['post_type'] = 'it_cb_product';

			// Create tab options from passed admin_page_options
			$tab_options = array(
				'var'        => $options['var'],
				'parent_var' => $options['var'],
				'title'      => $options['menu_title'],
				'callback'   => $options['callback'],
			);
			unset( $options['callback'] );

			// If page hasn't been created yet, create page and then add default tab content
			// If page already exists, add a new tab instead of creating the page
			if ( empty( $GLOBALS['it_cartbuddy']['admin_pages'][$options['post_type']][$options['var']] ) ) {
				$GLOBALS['it_cartbuddy']['admin_pages'][$options['post_type']][$options['var']] = $options;
				$this->add_admin_tab( $tab_options );
			} else {
				$this->add_admin_tab( $tab_options );
			}
		}

		/**
		 * Adds page tab to CartBuddy Admin pages
		 *
		*/
		function add_admin_tab( $options ) {

			// Throw error if missing required options
			$required_options = array( 'var', 'parent_var', 'title', 'callback' );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			// Default to core product post type if none is passed
			if ( empty( $options['post_type'] ) )
				$options['post_type'] = 'it_cb_product';

			$GLOBALS['it_cartbuddy']['admin_tabs'][$options['post_type']][$options['parent_var']][$options['var']] = $options;
		}

		/**
		 * Checks required params to ensure all exist.
		 * Throws and error if missing
		 *
		 * @since 0.1	
		*/
		function is_missing_required_params( $required, $options ) {
			foreach( $required as $option ) {
				if ( ! isset( $options[$option] ) )
					return new WP_Error( 'it-cb-api-call-missing-options', __( 'CartBuddy API method called with missing options', 'LION' ) );
			}
			return false;
		}
	}
}
