<?php
/**
 * CartBuddy Carts Post Type class.
 *
 * Sets up the carts post type.
 *
 * @package IT_CartBuddy
 * @since 0.1
*/

if ( ! class_exists( 'IT_CartBuddy_Carts' ) ) {

	// Load IT Classes
	it_classes_load( 'it-post-type.php' );
	it_classes_load( 'it-form.php' );
	it_classes_load( 'it-utility.php' );
	it_classes_load( 'it-file-utility.php' );

	class IT_CartBuddy_Carts extends ITPostType {

		/**
		 * Location of current file
		 * @var string $_file
		 * @since 0.1
		*/
		var $_file = __FILE__;

		/**
		 * Post Type Key
		 * @var $_var string
		 * @since 0.1
		*/
		var $_var = 'it_cb_cart';

		/**
		 * Singular Post Type Name 
		 * @var $_name string
		 * @since 0.1
		*/
		var $_name = 'Cart';

		/**
		 * Plural Post Type Name 
		 * @var $_name_plural string
		 * @since 0.1
		*/
		var $_name_plural = 'Carts';

		/**
		 * Settings Array to pass to register_post_type
		 * @var _settings array
		 * @since 0.1
		*/
		var $_settings = array(
			'rewrite'             => array(
				'slug' => 'post-type-cartbuddy-cart',
			),
			'supports'            => array( 'title', 'editor' ),
			'exclude_from_search' => false,
			'show_ui'             => false,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
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
		 * Class Constructor
		 *
		 * @since 0.1
		*/
		function IT_CartBuddy_Carts( &$parent ) {
			$this->_parent = &$parent;
			IT_CartBuddy_Carts::ITPostType();
			if ( is_admin() ) {
				$this->register_meta_boxes();
				$this->register_admin_pages();
				if ( ! empty( $_GET['post_type'] ) && 'it_cb_product' == $_GET['post_type'] && ! empty( $_GET['page'] ) && in_array( $_GET['page'], array_keys( $this->_menu_pages ) ) ) {
					$this->register_admin_page_tabs();
					$this->setup_admin_page_tabs();
				}
			}
		}

		/**
		 * Registers the meta boxes
		 *
		 * @since 0.1
		*/
		function register_meta_boxes() {
			$meta_boxes = apply_filters( 'it_cartbuddy-cart_metaboxes', array() );

			$this->_meta_boxes = $meta_boxes;
		}

		/**
		 * Register Admin Pages by placing them in this array
		 *
		 * @since 0.1
		 * @return void
		*/
		function register_admin_pages() {
			$pages = array();

			// Add pages
			$pages = apply_filters( 'it_cartbuddy-cart_admin_pages', $pages );
			
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
			$this->_page_tabs = apply_filters( 'it_cartbuddy-cart_admin_tabs', array() ); 
		}

		/**
		 * Sets properties used to parse current tab
		 *
		 * @since 0.1
		 * @return void
		*/
		function setup_admin_page_tabs() {
			if ( empty( $_REQUEST['page'] ) )
				return;

			wp_print_r($this->_page_tabs);
			$this->_current_page = $_REQUEST['page'];
			$this->_active_tab = ( ! empty( $_REQUEST['current_tab'] ) ) ? $_REQUEST['current_tab'] : key( $this->_page_tabs[$this->_current_page] );
			$this->_tabless_self_link = array_shift( explode( '?', $_SERVER['REQUEST_URI'] ) ) . '?post_type=' . $this->_var . '&page=' . $_REQUEST['page'];
			$this->self_link = $this->_tabless_self_link;

			if ( $this->_active_tab != key( $this->_page_tabs[$this->_current_page] ) )
				$this->_self_link .= '&current_tab=' . urlencode( $this->_active_tab );

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
					exit;
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
			if ( is_callable( $this->_page_tabs[$this->_current_page][$this->_active_tab]['callback'] ) )
				call_user_func( $this->_page_tabs[$this->_current_page][$this->_active_tab]['callback'] );
			else{
				echo '<p>Tab Content callback is missing or uncallable.</p>';
				echo '<p>To correctly add a new tab, please hook into similar to this:</p>';
				echo '<textarea style="font-family:monospace;width:90%;min-height:400px;">';
				include( $this->_parent->_plugin_path . '/lib/core/example-code/add-admin-page-tabs.txt' );
				echo '</textarea>';
			}
		}
	}

	new IT_CartBuddy_Carts( &$this );
}
