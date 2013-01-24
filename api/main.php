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
		 * Are we in the admin?
		 * @var boolean $_is_admin
		*/
		var $_is_admin = false;

		/**
		 * Current post_type var in admin
		 * @var string $_post_type
		*/
		var $_current_post_type = false;

		/**
		 * Current admin page
		 * @var string $_current_page
		*/
		var $_current_page = false;

		/**
		 * Current admin tab
		 * @var string $_current_tab
		*/
		var $_current_tab = false;

		/**
		 * Imploded value of current post type, current page, and current tab
		 * @var array $_current_screen
		*/
		var $_current_screen = false;

		/**
		 * Options passed when registering current page/tab through cartbuddy API
		 * @var array $_current_tab_options
		*/
		var $_current_tab_options = array();

		/**
		 * Link to this pag / tab
		*/
		var $_self_link;

		/**
		 * Class constructor
		 * Sets up several properties used later
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_API() {
			$this->_is_admin = is_admin();
			if ( $this->_is_admin ) {

				$this->_self_link = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) );
				
				$this->_current_post_type  = empty( $_REQUEST['post_type'] ) ? false : $_REQUEST['post_type'];
				if ( ! empty( $this->_current_post_type ) ) {
					$this->_current_screen[] = $this->_current_post_type;
					$this->_self_link .= '?post_type=' . $this->_current_post_type;
				}

				$this->_current_page       = empty( $_REQUEST['page'] ) ? false : $_REQUEST['page'];
				if ( ! empty( $this->_current_page ) ) {
					$this->_current_screen[] = $this->_current_page;
					$this->_self_link .= '&page=' . $this->_current_page;
				}

				$this->_current_tab        = empty( $_REQUEST['current_tab'] ) ? false : $_REQUEST['current_tab'];
				if ( ! empty( $this->_current_tab ) ) {
					$this->_current_screen[] = $this->_current_tab;
					$this->_self_link .= '&current_tab=' . $this->_current_tab;
				}

				if ( is_array( $this->_current_screen ) )
					$this->_current_screen = implode( '_', $this->_current_screen );


				add_action( 'admin_init', array( $this, 'admin_init' ), 9 );
			}
		}

		/**
		 * Set admin properites after admin_init
		 *
		 * @since 0.1
		*/
		function admin_init() {
			// Set current tab options
			if ( ! empty( $this->_current_post_type ) && ! empty ( $this->_current_page) && ! empty( $this->_current_tab ) && isset( $GLOBALS['it_cartbuddy']['admin_tabs'][$this->_current_post_type][$this->_current_page][$this->_current_tab] ) ) {
				$this->_current_tab_options = $GLOBALS['it_cartbuddy']['admin_tabs'][$this->_current_post_type][$this->_current_page][$this->_current_tab];
			} else if ( ! empty( $this->_current_post_type ) && ! empty( $this->_current_page ) && isset( $GLOBALS['it_cartbuddy']['admin_tabs'][$this->_current_post_type][$this->_current_page][$this->_current_page] ) ) {
				$this->_current_tab_options = $GLOBALS['it_cartbuddy']['admin_tabs'][$this->_current_post_type][$this->_current_page][$this->_current_page];
			}
		}

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
			$required_options = array( 'var', 'page_title', 'menu_title', 'capability' );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			// Default to core product post type if none is passed
			if ( empty( $options['post_type'] ) )
				$options['post_type'] = 'it_cb_product';

			// Default callback if none is passed
			if ( empty( $options['callback'] ) )
				$options['callback'] = array( $this, 'do_admin_tab_content' );

			// Default quicklinks if none is passed
			if ( ! isset( $options['quicklinks'] ) )
				$options['quicklinks'] = true;

			// Create tab options from passed admin_page_options
			$tab_options = array(
				'var'        => $options['var'],
				'parent_var' => $options['var'],
				'title'      => $options['menu_title'],
				'callback'   => $options['callback'],
				'quicklinks' => $options['quicklinks'],
			);
			unset( $options['callback'] );


			// Pass styles and scripts to tab and unset
			if ( ! empty( $options['enqueued_scripts'] ) ) {
				$tab_options['enqueued_scripts'] = $options['enqueued_scripts'];
				unset( $options['enqueued_scripts'] );
			}
			if ( ! empty( $options['embedded_scripts'] ) ) {
				$tab_options['embedded_scripts'] = $options['embedded_scripts'];
				unset( $options['embedded_scripts'] );
			}
			if ( ! empty( $options['enqueued_styles'] ) ) {
				$tab_options['enqueued_styles'] = $options['enqueued_styles'];
				unset( $options['enqueued_styles'] );
			}
			if ( ! empty( $options['embedded_styles'] ) ) {
				$tab_options['embedded_styles'] = $options['embedded_styles'];
				unset( $options['embedded_styles'] );
			}

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
			$rel_url = plugins_url( 'core-addon', dirname( __FILE__ ) );

			// Throw error if missing required options
			$required_options = array( 'var', 'parent_var', 'title' );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			// Default settings callback if none is passed
			if ( empty( $options['callback'] ) )
				$options['callback'] = array( $this, 'do_admin_tab_content' );

			// Default quicklinks if none is passed
			if ( ! isset( $options['quicklinks'] ) )
				$options['quicklinks'] = true;

			// Default to core product post type if none is passed
			if ( empty( $options['post_type'] ) )
				$options['post_type'] = 'it_cb_product';

			// Don't add Gateway Tabs if they aren't enabled
			if ( 'gateways' == $options['parent_var'] && ! in_array( $options['var'], $this->get_enabled_gateways() ) )
				return;

			// Format scripts and styles
			$options['enqueued_scripts'] = empty( $options['enqueued_scripts'] ) ? array() : (array) $options['enqueued_scripts'];
			$options['embedded_scripts'] = empty( $options['embedded_scripts'] ) ? array() : (array) $options['embedded_scripts'];
			$options['enqueued_styles']  = empty( $options['enqueued_styles'] ) ? array() : (array) $options['enqueued_styles'];
			$options['embedded_styles']  = empty( $options['embedded_styles'] ) ? array() : (array) $options['embedded_styles'];

			// Add quicklinks css to enqueued styles if true
			if ( $options['quicklinks'] )
				$options['enqueued_styles'][] = array( 'handle' => 'it-cb-quicklinks', 'src' => $rel_url . '/css/quicklinks.css' );
			
			$GLOBALS['it_cartbuddy']['admin_tabs'][$options['post_type']][$options['parent_var']][$options['var']] = $options;

		}

		/**
		 * This is the default callback for the content of an admin page tab.
		 *
		 * It is used when no callback is passed to add_admin_tab() or add_admin_page()
		 *
		 * @since 0.1
		*/
		function do_admin_tab_content( $options=array() ) {
			// Init form objec to pass to metaboxes
            $form = new ITForm();

			// Add quicklinks if flagged
			if ( $this->_current_tab_options['quicklinks'] )
				add_action( $this->_current_screen . '_before_mbs', array( $this, 'print_admin_page_quicklinks' ), 9 );

			// If this is a gateway page, it the settings page callback
			if ( 'gateways' == $this->_current_page && ! empty( $this->_current_tab ) ) {
				if ( ! empty( $GLOBALS['it_cartbuddy']['admin_tabs']['it_cb_product']['gateways'][$this->_current_tab]['callback'] ) ) {
					$callback = $GLOBALS['it_cartbuddy']['admin_tabs']['it_cb_product']['gateways'][$this->_current_tab]['callback'];
					// Abandon if callback is this method
					if ( 'IT_CartBuddy_API' != get_class($callback[0] ) || 'do_admin_tab_content' != $callback[1] ) {
						if ( is_callable( $callback ) )
							call_user_func( $callback );
					}
				}
			}

			// Do actions / metaboxes
			$form->start_form( array( 'action' => $this->_self_link ), $this->_current_screen );
            do_action( $this->_current_screen . '_before_mbs', $form );
			echo '<div class="metabox-holder">';
            do_meta_boxes( $this->_current_screen, 'normal', $form );
			echo '</div>';
			echo '<div class="metabox-holder">';
            do_meta_boxes( $this->_current_screen, 'advanced', $form );
			echo '</div>';
            do_action( $this->_current_screen . '_after_mbs', $form );
			$form->end_form();
		}

		/**
		 * Adds a taxonomy
		 *
		 * @since 0.1
		*/
		function add_taxonomy( $options ) {

			// Grab settings
			$settings = $this->get_options( array( 'var' => 'it_cb_product_settings' ) );

			// Throw error if missing required options
			$required_options = array( 'var', 'name', 'name_plural' );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			// Prevent showing in the UI if not enabled in Settings
			if ( empty( $settings['it_cb_product_enable_taxonomy'] ) || ! in_array( $options['var'], (array) $settings['it_cb_product_enable_taxonomy'] ) )
				$options['settings']['show_ui'] = false;

			return new IT_CartBuddy_Taxonomy( $options );
		}

		/**
		 * Returns all taxonomies for a given post type.
		 *
		 * This passes options on to get_taxonomies. If options are empty, it defaults to it_cb_product
		 *
		*/
		function get_taxonomies( $options=array() ) {

			// Set post type
			$options['object_type'] = empty( $options['object_type'] ) ? array( 'it_cb_product' ) : (array) $options['object_type'];

			// Set return format and clean options
			$return_format = empty( $options['return_format'] ) ? 'object' : $options['return_format'];
			unset( $options['return_format'] );

			return get_taxonomies( $options, $return_format );

		}

		/**
		 * Get's the list of registered meta boxes for an admin page
		 *
		 * @since 0.1
		*/
		function get_admin_page_mbs( $options ) {
			if ( ! $this->_is_admin )
				return;

            global $wp_meta_boxes;

			$boxes = array();

			$defaults = array(
				'page' => $this->_current_screen,
				'context' => 'advanced',
				'priority' => 'default',
			);

			$o = wp_parse_args( $options, $defaults );

			if ( empty( $o['page'] ) )
				return $boxes;

            if ( ! empty ( $wp_meta_boxes[$o['page']][$o['context']][$o['priority']] ) ) 
				$boxes = $wp_meta_boxes[$o['page']][$o['context']][$o['priority']];

			return $boxes;

		}

		/**
		 * Prints quicklinks for an admin settings page. 
		 *
		 * @since 0.1
		*/
		function get_admin_page_quicklinks( $options ) {

			if ( ! $this->_is_admin )
				return;

			$defaults = array(
				'page' => $this->_current_screen,
				'context' => 'advanced',
				'priority' => 'default',
			);

			$o = wp_parse_args( $options, $defaults );

			$boxes = $this->get_admin_page_mbs( $o );

			if ( empty( $boxes ) )
				return;

			$quicklinks = '<div class="quick-links">
				<h4>Quick Links</h4>

				<ul>';
					foreach ( (array) $boxes as $id => $data ) {
						$quicklinks .= '<li><a href="#' . $id . '">' . $data['title'] . '</a></li>';
					}
				$quicklinks .= '</ul>
			</div>
			<br style="clear:both;" />';

			return $quicklinks;
		}

		/**
		 * Prints the quicklinks for an admin page
		 *
		 * @since 0.1
		*/
		function print_admin_page_quicklinks( $options ) {
			echo $this->get_admin_page_quicklinks( $options );
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

		/**
		 * Registers a Gateway Add-on
		 *
		 * @since 0.1
		*/
		function add_gateway( $options ) {
			
			// Throw error if missing required options
			$required_options = array( 'var', 'title' );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			// Default options
			$defaults = array(
				'parent_var' => 'gateways',
				'quicklinks' => false
			);

			// Merge options with defaults
			$o = wp_parse_args( $options, $defaults );

			// Throw an error if var is already registered
			if ( ! empty( $GLOBALS['it_cartbuddy']['gateways'][$options['var']] ) )
				return new WP_Error( 'it-cartbuddy-gateway-registered', __( 'This Gateway has already been registered.', 'LION' ) );

			$GLOBALS['it_cartbuddy']['gateways'][$options['var']] = $o;

			// Is the gateway enabled on the settings page?
			$settings = $this->get_options( array( 'var' => 'it_cb_product_gateways' ) );
			$gateway_enabled = ! empty( $settings['it_cb_product_enable_gateway'] ) && in_array( $options['var'], (array) $settings['it_cb_product_enable_gateway'] );

			// Add admin tab if in admin
			if ( $this->_is_admin && $gateway_enabled )
				$this->add_admin_tab( $o );

		}

		/**
		 * Grabs a list of registered gateways
		 *
		 * @since 0.1
		*/
		function get_gateways( $options=false ) {
			if ( empty( $GLOBALS['it_cartbuddy']['gateways'] ) )
				return new WP_Error( 'it-cartbuddy-no-gateways', __( 'You do not currently have any Gateways installed.', 'LION' ) );

			return $GLOBALS['it_cartbuddy']['gateways'];
		}

		/**
		 * Get enabled gateways
		 *
		 * @todo Make this work with options
		 * @since 0.1
		*/
		function get_enabled_gateways( $options=false ) {
			return array( 'gateways', 'manual_payments', 'paypal_standard', 'stripe' );
		}

		/**
		 * Grab ITStroage2 Object
		 *
		 * $options['var'] represents the options key
		 *
		 * @since 0.1
		*/
		function get_storage( $options ) {
			// Throw error if missing required options
			$required_options = array( 'var' );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			// Init Object
			return new ITStorage2( $options['var'], array( 'version' => '1' ) );
		}

		/**
		 * Grab options from ITStorage2
		 *
		 * @since 0.1
		*/
		function get_options( $options ) {
	
			// Throw error if missing required options
			$required_options = array( 'var' );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			// Init Object
			$storage = $this->get_storage( $options );
			return $storage->load();
		}

		/**
		 * Sets options via ITStorage2
		 *
		 * $options['var'] is the key for the options
		 * $options['options'] is the options
		 *
		 * @since 0.1
		*/
		function save_options( $options ) {

			// Throw error if missing required options
			$required_options = array( 'var', 'options' );
			if ( $error = $this->is_missing_required_params( $required_options, $options ) )
				return $error;

			$storage = $this->get_storage( array( 'var' => $options['var'] ) );
			if ( $storage->save( $options['options'] ) )
				return true;

			return false;
		}
	}
}
