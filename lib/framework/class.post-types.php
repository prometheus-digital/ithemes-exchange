<?php
/**
 * CartBuddy Post Type Factory
 *
 * Relies on classes/it-post-type.php
 *
 * @package IT_CartBuddy
 * @since 0.1
*/

if ( ! class_exists( 'IT_CartBuddy_Post_Type' ) ) {

    // Load IT Classes
    it_classes_load( 'it-post-type.php' );
    it_classes_load( 'it-form.php' );
    it_classes_load( 'it-utility.php' );
    it_classes_load( 'it-file-utility.php' );

	class IT_CartBuddy_Post_Type extends ITPostType {

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
		 * Settings Array to pass to register_post_type
		 * @var _settings array
		 * @since 0.1
		*/
		var $_settings = array(
			'rewrite'             => array(
				'slug' => '',
			),
			'supports'            => array( 'title', 'editor' ),
			'exclude_from_search' => false,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true,
		);

		/**
		 * Menu Pages
		 * @var array $_menu_pages
		 * @since 0.1
		*/
		var $_menu_pages = array();

		/**
		 * Meta Boxes
		 * @var array $_meta_boxes
		 * @since 0.1
		*/
		var $_meta_boxes = array();

		/**
		 * Page Tabs
		 * @var array $_page_tabs
		 * @since 0.1
		*/
		var $_page_tabs = array();

		/**
		 * Current admin page
		 * @var string  $_current_page;
		 * @since 0.1
		*/
		var $_current_page = null;

		/**
		 * Active Tab
		 *
		*/
		var $_active_tab = null;

		/**
		 * Current Screen
		 * @var string $_current_screen
		 * @since 0.1
		*/
		var $_current_screen = null;

		/**
		 * Options passed from API call
		*/
		var $_options = array();

		/**
		 * Class Constructor
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Post_Type( $options ) {

			// Setup Vars
			$this->_options = $options;
			$this->setup_post_type();

			// Register post type
			IT_CartBuddy_Post_Type::ITPostType();

			// Register admin hooks
			if ( is_admin() ) {
				add_action( 'init', array( $this, 'register_meta_boxes' ), 99 );
				add_action( 'init', array( $this, 'register_admin_pages' ), 99 );
				add_action( 'init', array( $this, 'register_admin_page_tabs' ), 99 );
				add_action( 'init', array( $this, 'setup_admin_page_tabs' ), 99 );
				add_action( 'admin_enqueue_scripts', array( $this, 'process_tab_scripts_styles' ) );
				add_action( 'admin_init', array( $this, 'save_data' ), 1 );
			}
		}

		/**
		 * Loads properties from passed options
		 *
		 * @since 0.1
		*/
		function setup_post_type() {
			$this->_var = $this->_options['var'];
			$this->_name = $this->_options['name'];
			$this->_name_plural = $this->_options['name_plural'];

			// Merge settings option with defaults
			if ( ! empty( $this->_options['settings'] ) && is_array( $this->_options['settings'] ) )
				$this->_settings = array_merge( $this->_options['settings'], $this->_settings );

			// If the child_of_post_type setting is active, do some shuffling
			// WP Does this by default by we're removing the word 'All' from the menu title
			if ( ! empty( $this->_settings['show_in_menu'] ) ) {
				$this->_settings['show_ui']      = true;
				$this->_options['show_in_menu']  = $this->_settings['show_in_menu'];
				$this->_settings['show_in_menu'] = false;
				add_action( 'admin_menu', array( $this, 'add_as_submenu_item' ), 9 );
			}
		}

		/**
		 * Places a link to current post_type under an different post type
		 *
		*/
		function add_as_submenu_item() {
			add_submenu_page( $this->_options['show_in_menu'], $this->_name_plural, $this->_name_plural, 'manage_options', 'edit.php?post_type=' . $this->_var );
		}

		/**
		 * Registers the meta boxes
		 *
		 * @since 0.1
		*/
		function register_meta_boxes() {
			$meta_boxes = empty( $GLOBALS['it_cartbuddy']['meta-boxes'][$this->_var] ) ? array() : $GLOBALS['it_cartbuddy']['meta-boxes'][$this->_var];
			$meta_boxes = apply_filters( $this->_var . '_metaboxes', $meta_boxes );

			$this->_meta_boxes = $meta_boxes;
		}

		/**
		 * Register Admin Pages by placing them in this array
		 *
		 * @since 0.1
		 * @return void
		*/
		function register_admin_pages() {
			$pages = empty( $GLOBALS['it_cartbuddy']['admin_pages'][$this->_var] ) ? array() : $GLOBALS['it_cartbuddy']['admin_pages'][$this->_var];

			// Add pages
			$pages = apply_filters( $this->_var . '_admin_pages', $pages );
			
			// Add internal callback array to pages
			foreach( (array) $pages as $page => $args ) {
				$pages[$page]['callback'] = 'init_admin_page';
			}

			$this->_menu_pages = $pages;
		}

		/**
		 * Register Page Tabs for each of the admin pages
		 *
		 * @since 0.1
		 * @return void
		*/
		function register_admin_page_tabs() {
			$tabs = empty( $GLOBALS['it_cartbuddy']['admin_tabs'][$this->_var] ) ? array() : $GLOBALS['it_cartbuddy']['admin_tabs'][$this->_var];
			$this->_page_tabs = apply_filters( $this->_var . '_admin_tabs', $tabs ); 
		}

		/**
		 * Sets properties used to parse current tab
		 *
		 * @since 0.1
		 * @return void
		*/
		function setup_admin_page_tabs() {
			if ( ! empty( $_GET['post_type'] ) && 'it_cb_product' == $_GET['post_type'] && ! empty( $_GET['page'] ) && in_array( $_GET['page'], array_keys( $this->_menu_pages ) ) ) {
				$this->_current_page = $_REQUEST['page'];
				$this->_active_tab = ( ! empty( $_REQUEST['current_tab'] ) ) ? $_REQUEST['current_tab'] : key( $this->_page_tabs[$this->_current_page] );
				$this->_tabless_self_link = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?post_type=' . $this->_var . '&page=' . $_REQUEST['page'];
				$this->_self_link = $this->_tabless_self_link;

				if ( $this->_active_tab != key( $this->_page_tabs[$this->_current_page] ) )
					$this->_self_link .= '&current_tab=' . urlencode( $this->_active_tab );


                $this->_current_post_type  = empty( $_REQUEST['post_type'] ) ? false : $_REQUEST['post_type'];
                if ( ! empty( $this->_current_post_type ) ) 
                    $this->_current_screen[] = $this->_current_post_type;

                if ( ! empty( $this->_current_page ) ) 
                    $this->_current_screen[] = $this->_current_page;

                if ( ! empty( $this->_active_tab ) && $this->_current_page != $this->_active_tab ) 
                    $this->_current_screen[] = $this->_active_tab;

                if ( is_array( $this->_current_screen ) ) 
                    $this->_current_screen = implode( '_', $this->_current_screen );
			}

		}

		/**
		 * If any embedded or enqueued scripts have been registered, hook into WP here
		 *
		 * @since 0.1
		*/
		function process_tab_scripts_styles( $hook ) {
			// Build index for tab
			$tab = empty( $this->_active_tab ) ? $this->_current_page : $this->_active_tab;

			// Abort if index isn't set for some reason
			if ( empty( $GLOBALS['it_cartbuddy']['admin_tabs'][$this->_var][$this->_current_page][$tab] ) )
				return;

			$tab_options = $GLOBALS['it_cartbuddy']['admin_tabs'][$this->_var][$this->_current_page][$tab];

			// Enqueue all enqueued scripts
			foreach( $tab_options['enqueued_scripts'] as $script ) {
				if ( empty( $script['handle'] ) || empty( $script['src'] ) )
					continue;
				$script['dep'] = empty( $script['dep'] ) ? false : (array) $script['dep'];
				wp_enqueue_script( $script['handle'], $script['src'], $script['dep'] );
			}

			// Enqueue all enqueued styles 
			foreach( $tab_options['enqueued_styles'] as $style ) {
				if ( empty( $style['handle'] ) || empty( $style['src'] ) )
					continue;
				wp_enqueue_style( $style['handle'], $style['src'] );
			}

			// Embed all embedded scripts
			foreach( $tab_options['embedded_scripts'] as $script ) {
				if ( is_callable( $script ) )
					add_action( 'wp_print_scripts', $script );
			}

			// Embed all embedded styles 
			foreach( $tab_options['embedded_styles'] as $style ) {
				if ( is_callable( $style ) )
					add_action( 'wp_print_scripts', $style );
			}
		}

		/**
		 * Prints the tabs header for a submenu page
		 *
		 * @since 0.1
		*/
		function print_tabs() {

			$class = ( count( $this->_page_tabs[$this->_current_page] ) > 1 ) ? 'class="nav-tab-wrapper"' : '';
			echo "<h2 $class>";

			foreach ( (array) $this->_page_tabs[$this->_current_page] as $var => $args ) { 
				if ( empty( $class ) ) {
					echo $args['title'] . '</h2>';
					continue;
				}
				$link = $this->_tabless_self_link;
				if ( $var != key( $this->_page_tabs[$this->_current_page] ) ) 
					$link .= '&current_tab=' . urlencode( $var );

				$class = 'nav-tab' . ( ( $var === $this->_active_tab ) ? ' nav-tab-active' : '' );

				echo '<a class="' . $class . '" href="' . $link . '">' . $args['title'] . '</a>';
			}   

			echo "</h2>\n";

		}

		/**
		 * Inits admin pages
		 *
		 * @since 0.1
		 * @return void
		*/
		function init_admin_page() {
			echo '<div class="wrap">';
			screen_icon();
			$this->print_tabs();
			$this->print_admin_page_content();
			echo '</div>';
		}

		/**
		 * Prints content for an admin page
		 *
		 * @since  0.1
		 * @return  void
		*/
		function print_admin_page_content() {
			if ( ! empty( $this->_page_tabs[$this->_current_page][$this->_active_tab]['callback'] ) && is_callable( $this->_page_tabs[$this->_current_page][$this->_active_tab]['callback'] ) )
				call_user_func( $this->_page_tabs[$this->_current_page][$this->_active_tab]['callback'] );
			else{
				echo '<p>Tab Content callback is missing or uncallable.</p>';
				echo '<p>To correctly add a new tab, please hook into similar to this:</p>';
				echo '<textarea style="font-family:monospace;width:90%;min-height:400px;">';
				include( $this->_parent->_plugin_path . '/lib/core/example-code/add-admin-page-tabs.txt' );
				echo '</textarea>';
			}
		}

		/**
		 * This saves data for admin pages and product custom values
		 *
		*/
		function save_data() {
			if ( empty( $_POST ) )
				return;

				// Pages we want to init this on
				$pages = apply_filters( 'it_cartbuddy_storage_pages', array_keys( (array) $this->_menu_pages ) );
				if ( empty( $_REQUEST['post_type'] ) || $this->_var != $_REQUEST['post_type'] ||  empty( $this->_current_page ) || ! in_array( $this->_current_page, $pages ) )
				return;

			// Check nonce
			$nonce = apply_filters( 'it_cartbuddy_save_admin_nonce', $this->_current_screen );
			ITForm::check_nonce( $nonce );

			// Grab the data
			$data = ITForm::get_post_data();
			$data['_validated'] = true;

			// Validate data via this filter. return data with $data['_validated'] = false to prevent update.
			$data = apply_filters( $this->_current_screen . '-validate_saved_data', $data );

			if ( $data['_validated'] ) {
				unset( $data['_validated'] );

				$storage_options = array(
					'var'     => $this->_current_screen,
					'options' => $data,
				);
				$storage = cartbuddy( 'save_options', $storage_options );
				wp_safe_redirect( $this->_self_link );
			} else {
				do_action( $this->_current_screen . '-save_with_invalid_data', $data );
			}
		}
	}
}
