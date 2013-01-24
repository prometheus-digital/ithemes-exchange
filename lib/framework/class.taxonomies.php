<?php
/**
 * CartBuddy Taxonomy Factory
 *
 * Relies on classes/it-taxonomy.php
 *
 * @package IT_CartBuddy
 * @since 0.1
*/

if ( ! class_exists( 'IT_CartBuddy_Taxonomy' ) ) {

    // Load IT Classes
    it_classes_load( 'it-taxonomy.php' );
    it_classes_load( 'it-utility.php' );
    it_classes_load( 'it-file-utility.php' );

	class IT_CartBuddy_Taxonomy extends ITTaxonomy {

		/**
		 * Location of current file
		 * @var $_file
		 * @since 0.1
		*/
		var $_file = __FILE__;

		/**
		 * Post Type Key
		 * @var $_var string
		 * @since 0.1
		*/
		var $_var = '';

		/**
		 * Singular Post Type Name 
		 * @var $_name string
		 * @since 0.1
		*/
		var $_name = '';

		/**
		 * Plural Post Type Name 
		 * @var $_name_plural string
		 * @since 0.1
		*/
		var $_name_plural = '';

		/**
		 * Array of post types this taxonomy is associated with
		 *
		 * If nothing is passed through options, we'll attach it to it_cb_product
		 *
		 * @var array $_attach_to_post_types
		 * @since 0.1
		*/
        var $_attach_to_post_types = array();
    
		/**
		 * Array of settings to pass to register_taxonomy
		 * @var array $_settings
		 * @since 0.1
		*/
        var $_settings = array();
    
		/**
		 * An array of default terms associated with this taxonomy on creation
		 * 
		 * Each array item must be an array formatted properly to be passed along to wp_create_term()
		 *
		 * @var array $_settings
		 * @since 0.1
		*/
        var $_default_terms = array();
    
		/**
		 * What should the metabox priority be for the taxonomy on add/edit post_type screens?
		 * 
		 * Options are: 'high', 'default', or 'low'
		 * 
		 * @var string $_priority
		 * @since 0.1
		*/
        var $_priority = '';

		/**
		 * Options passed from API call
		*/
		var $_options = array();

		/**
		 * Class Constructor
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Taxonomy( $options ) {

			// Setup Vars
			$this->_options = $options;
			$this->setup_taxonomy();

			// Register Taxonomy
			IT_CartBuddy_Taxonomy::ITTaxonomy();
		}

		/**
		 * Loads properties from passed options
		 *
		 * @since 0.1
		*/
		function setup_taxonomy() {
			$this->_var                  = $this->_options['var'];
			$this->_name                 = $this->_options['name'];
			$this->_name_plural          = $this->_options['name_plural'];
			$this->_attach_to_post_types = empty( $this->_options['attach_to_post_types'] ) ? 'it_cb_product' : $this->_options['attach_to_post_types'];

			// Merge settings option with filter and defaults
			$settings = $this->_settings;
			if ( ! empty( $this->_options['settings'] ) && is_array( $this->_options['settings'] ) )
				$settings = array_merge( $this->_options['settings'], $settings );
			$this->_settings = apply_filters( $this->_var . '-taxonomy-settings', $settings );

			
			// Merge default terms with filter and defaults
			$default_terms = $this->_default_terms;
			if ( ! empty( $this->_options['default_terms'] ) && is_array( $this->_options['default_terms'] ) )
				$default_terms = array_merge( $this->_options['default_terms'], $default_terms );
			$this->_default_terms = apply_filters( $this->_var . '-taxonomy_default_terms', $default_terms );

			// Set optional priority param
			$priority = empty( $this->_options['priority'] ) ? $this->_priority : $this->_options['priority'];
			$this->_priority = apply_filters( $this->_var . '-taxonomy_priority', $priority );
			
		}
	}
}
