<?php
/**
 * CartBuddy admin class.
 *
 * This class manages the admin side of the plugin
 *
 * @package IT_Cart_Buddy
 * @since 0.1.0
*/
class IT_Cart_Buddy_Admin {

	/**
	 * @var object $_parent parent class
	 * @since 0.1.0
	*/
	var $_parent;

	/**
	 * @var string $_current_page current page based on $_GET['page']
	 * @since 0.3.4
	*/
	var $_current_page;

	/**
	 * @var string $_current_tab
	 * @since 0.3.4
	*/
	var $_current_tab;

	/**
	 * @var object $_storage object for current settings tab 
	 * @since 0.3.6
	*/
	var $_storage;

	/**
	 * @var string $status_message informative message for current settings tab 
	 * @since 0.3.6
	*/
	var $status_message;

	/**
	 * @var string $error_message error message for current settings tab 
	 * @since 0.3.6
	*/
	var $error_message;

	/**
	 * Class constructor
	 *
	 * @uses add_action()
	 * @since 0.1.0
	 * @return void 
	*/
	function IT_Cart_Buddy_Admin( &$parent ) {

		// Set parent property
		$this->_parent = $parent;

		// Admin Menu Capability
		$this->admin_menu_capability = apply_filters( 'it_cart_buddy_admin_menu_capability', 'read' );

		// Set current properties
		$this->set_current_properties();

		// Open cart buddy menu when on add/edit cartbuddy product post type
		add_action( 'parent_file', array( $this, 'open_cart_buddy_menu_on_post_type_views' ) );

		// Load Storage
		add_action( 'admin_init', array( $this, 'load_storage' ) );

		// Add actions for iThemes registration
		add_action( 'admin_menu', array( $this, 'add_cart_buddy_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'enable_disable_registered_add_on' ) );

		// Redirect to Product selection on Add New if needed
		add_action( 'admin_init', array( $this, 'redirect_post_new_to_product_type_selection_screen' ) );

		// Save core settings
		add_action( 'admin_init', array( $this, 'save_core_general_settings' ) );
		add_action( 'admin_init', array( $this, 'save_core_email_settings' ) );
		add_action( 'admin_init', array( $this, 'save_core_page_settings' ) );

		// Email settings callback
		add_filter( 'it_cart_buddy_general_settings_tab_callback-email', array( $this, 'register_email_settings_tab_callback' ) );
		add_action( 'it_cart_buddy_print_general_settings_tab_links', array( $this, 'print_email_settings_tab_link' ) );

		// Page settings callback
		add_filter( 'it_cart_buddy_general_settings_tab_callback-pages', array( $this, 'register_pages_settings_tab_callback' ) );
		add_action( 'it_cart_buddy_print_general_settings_tab_links', array( $this, 'print_pages_settings_tab_link' ) );

		// General Settings Defaults
		add_filter( 'it_storage_get_defaults_cart_buddy_settings_general', array( $this, 'set_general_settings_defaults' ) );
	}

	/**
	 * Sets the _current_page and _current_tab properties
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function set_current_properties() {
		$this->_current_page = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_tab = empty( $_GET['tab'] ) ? false : $_GET['tab'];
	}

	/**
	 * Loads the storage object
	 *
	 * @since 0.3.6
	 * return void
	*/
	function load_storage() {
		if ( 'it-cart-buddy-settings' != $this->_current_page )
			return;

		it_classes_load( 'it-storage.php' );
		$tab = empty( $this->_current_tab ) ? 'general' : $this->_current_tab;
		$key = 'cart_buddy_settings'  . '_' . $tab;

		$this->_storage = new ITStorage2( $key );
	}

	/**
	 * Adds the main Cart Buddy menu item to the WP admin menu
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function add_cart_buddy_admin_menu() {
		// Add main cart buddy menu item
		add_menu_page( 'Cart Buddy', 'Cart Buddy', $this->admin_menu_capability, 'it-cart-buddy', array( $this, 'print_cart_buddy_setup_page' ) );

		// Add setup wizard if not complete or if on page
		if ( 'it-cart-buddy-setup' == $this->_current_page || ! get_option( 'it_cart_buddy_setup_complete' ) )
			add_submenu_page( 'it-cart-buddy', 'Cart Buddy Setup Wizard', 'Setup Wizard', $this->admin_menu_capability, 'it-cart-buddy-setup', array( $this, 'print_cart_buddy_setup_page' ) );

		// Remove default cart buddy sub-menu item created with parent menu item
		remove_submenu_page( 'it-cart-buddy', 'it-cart-buddy' );

		// Add the product submenu pages depending on active product add-ons
		$this->add_product_submenus();

		// Add Transactions menu item
		add_submenu_page( 'it-cart-buddy', 'Cart Buddy ' . __( 'Payments', 'LION' ), __( 'Payments', 'LION' ), $this->admin_menu_capability, 'edit.php?post_type=it_cart_buddy_tran' );

		// Add Settings Menu Item
		$settings_callback = array( $this, 'print_cart_buddy_settings_page' );
		if ( 'it-cart-buddy-settings' == $this->_current_page && ! empty( $this->_current_tab ) )
			$settings_callback = apply_filters( 'it_cart_buddy_general_settings_tab_callback-' . $this->_current_tab, $settings_callback );
		add_submenu_page( 'it-cart-buddy', 'Cart Buddy Settings', 'Settings', $this->admin_menu_capability, 'it-cart-buddy-settings', $settings_callback );

		// Add Add-ons menu item
		$callback = array( $this, 'print_cart_buddy_add_ons_page' );
		if ( 'it-cart-buddy-addons' == $this->_current_page && ! empty( $_GET['add_on_settings'] ) ) {
			if ( $addon = it_cart_buddy_get_addon( $_GET['add_on_settings'] ) ) {
				if ( ! empty( $addon['options']['settings-callback'] ) && is_callable( $addon['options']['settings-callback'] ) )
					$callback = $addon['options']['settings-callback'];
			}
		}
		add_submenu_page( 'it-cart-buddy', 'Cart Buddy Add-ons', 'Add-ons', $this->admin_menu_capability, 'it-cart-buddy-addons', $callback );
	}

	/**
	 * Adds the product submenus based on number of enabled product-type add-ons
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function add_product_submenus() {
		// Check for enabled product add-ons. Don't need product pages if we don't have product add-ons enabled
		if ( $enabled_product_types = it_cart_buddy_get_enabled_addons( array( 'category' => array( 'product-type' ) ) ) ) {
			$add_on_count = count( $enabled_product_types );
			add_submenu_page( 'it-cart-buddy', 'All Products', 'All Products', $this->admin_menu_capability, 'edit.php?post_type=it_cart_buddy_prod' );
			if ( 1 == $add_on_count ) {
				// If we only have one product-type enabled, add standard post_type pages
				$product = reset( $enabled_product_types );
				add_submenu_page( 'it-cart-buddy', 'Add Product', 'Add Product', $this->admin_menu_capability, 'post-new.php?post_type=it_cart_buddy_prod&product_type=' . $product['slug'] );
			} else if ( $add_on_count > 1 ) {
				// If we have more than one product type, add them each separately
				foreach( $enabled_product_types as $type => $params ) {
					$name = empty( $params['options']['labels']['singular_name'] ) ? 'Product' : esc_attr( $params['options']['labels']['singular_name'] );
					add_submenu_page( 'it-cart-buddy', 'Add ' . $name, 'Add ' . $name, $this->admin_menu_capability, 'post-new.php?post_type=it_cart_buddy_prod&product_type=' . esc_attr( $params['slug'] ) );
				}
			}
		}
	}

	/**
	 * Registers the callback for the email tab
	 *
	 * @param mixed default callback for general settings. 
	 * @since 0.3.4
	 * @return mixed function or class method name
	*/
	function register_email_settings_tab_callback( $default ) {
		return array( $this, 'print_email_settings_page' );
	}

	/**
	 * Prints the email tab for general settings
	 *
	 * @since 0.3.4
	 * @param $current_tab the current tab
	 * @return void
	*/
	function print_email_settings_tab_link( $current_tab ) {
		$active = 'email' == $current_tab ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-cart-buddy-settings&tab=email' ); ?>"><?php _e( 'Email Settings', 'LION' ); ?></a><?php
	}

	/**
	 * Registers the callback for the pages tab
	 *
	 * @param mixed default callback for general settings. 
	 * @since 0.3.7
	 * @return mixed function or class method name
	*/
	function register_pages_settings_tab_callback( $default ) {
		return array( $this, 'print_pages_settings_page' );
	}

	/**
	 * Prints the pages tab for general settings
	 *
	 * @since 0.3.7
	 * @param $current_tab the current tab
	 * @return void
	*/
	function print_pages_settings_tab_link( $current_tab ) {
		$active = 'pages' == $current_tab ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-cart-buddy-settings&tab=pages' ); ?>"><?php _e( 'Pages', 'LION' ); ?></a><?php
	}

	/**
	 * Prints the tabs for the Cart Buddy General Settings
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_general_settings_tabs() {
		$active = empty( $this->_current_tab ) ? 'nav-tab-active' : '';
		?>
		<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-cart-buddy-settings' ); ?>"><?php _e( 'General', 'LION' ); ?></a>
		<?php do_action( 'it_cart_buddy_print_general_settings_tab_links', $this->_current_tab ); ?>
		</h2>
		<?php
	}

	/**
	 * Prints the setup page for cart buddy
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function print_cart_buddy_setup_page() {
		?>
		<div class="wrap">
			<?php screen_icon( 'page' ); ?>
			<h2>Cart Buddy Setup</h2>
			<p>Possibly place setup wizzard here</p>
			<p>Definitely replace icon</p>
		</div>
		<?php
	}

	/**
	 * Sets the general settings default values
	 *
	 * @since 0.3.7
	 * @return array
	*/
	function set_general_settings_defaults( $values ) {
		$defaults = array(
			'default_currency'             => 'USD',
			'currency_thousands_separator' => ',',
			'currency_decimals_separator'  => '.',
		);
		$values = ITUtility::merge_defaults( $values, $defaults );
		return $values;
	}

	/**
	 * Prints the settings page for cart buddy
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_cart_buddy_settings_page() {
		$form_values  = empty( $this->error_message ) ? $this->_storage->load() : ITForm::get_post_data();
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_cart_buddy_settings' ) );
		$form_options = array(
			'id'      => apply_filters( 'it_cart_buddy_settings_form_id', 'it-cart-buddy-settings' ),
			'enctype' => apply_filters( 'it_cart_buddy_settings_form_enctype', false ),
		);
		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		include( 'views/admin-settings.php' );
	}

	/**
	 * Prints the email page for cart buddy
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_email_settings_page() {
		$form_values  = empty( $this->error_message ) ? $this->_storage->load() : ITForm::get_post_data();
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_cart_buddy_email_settings' ) );
		$form_options = array(
			'id'      => apply_filters( 'it_cart_buddy_email_settings_form_id', 'it-cart-buddy-email-settings' ),
			'enctype' => apply_filters( 'it_cart_buddy_email_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-cart-buddy-settings&tab=email',
		);
		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		include( 'views/admin-email-settings.php' );
	}

	/**
	 * Prints the pages page for cart buddy
	 *
	 * @since 0.3.7
	 * @return void
	*/
	function print_pages_settings_page() {
		$form_values  = empty( $this->error_message ) ? $this->_storage->load() : ITForm::get_post_data();
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_cart_buddy_page_settings' ) );
		$form_options = array(
			'id'      => apply_filters( 'it_cart_buddy_page_settings_form_id', 'it-cart-buddy-page-settings' ),
			'enctype' => apply_filters( 'it_cart_buddy_page_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-cart-buddy-settings&tab=pages',
		);
		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		include( 'views/admin-page-settings.php' );
	}

	/**
	 * Prints the add-ons page in the admin area
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function print_cart_buddy_add_ons_page() {
		$registered = it_cart_buddy_get_addons();
		$add_on_cats = it_cart_buddy_get_addon_categories();
		$message = empty( $_GET['message'] ) ? false : $_GET['message'];
		if ( 'enabled' == $message ) {
			ITUtility::show_status_message( __( 'Add-on enabled.', 'LION' ) );
		} else if ( 'disabled' == $message ) {
			ITUtility::show_status_message( __( 'Add-on disabled.', 'LION' ) );
		} else if ( 'addon-auto-disabled-' == substr( $message, 0, 20 ) ) {
			$addon_slug = substr( $message, 20 );
			$status_message = __( sprintf( 'CartBuddy has automatically disabled an add-on: %s. This is mostly likely due to it being uninstalled or improperlly registered.', $addon_slug ), 'LION' );
			ITUtility::show_status_message( $status_message );
		}

		$error= empty( $_GET['error'] ) ? false : $_GET['error'];
		if ( 'enabled' == $error )
			ITUtility::show_error_message( __( 'Error: Add-on not enabled.', 'LION' ) );
		else if ( 'disabled' == $error )
			ITUtility::show_error_message( __( 'Error: Add-on not disabled.', 'LION' ) );

		include( 'views/admin-add-ons.php' );
	}

	/**
	 * Adds a registered Add-on to list of enabled add-ons
	 *
	 * @since 0.2.0
	*/
	function enable_disable_registered_add_on() {
		$enable_addon  = empty( $_GET['it-cart-buddy-enable-addon'] ) ? false : $_GET['it-cart-buddy-enable-addon'];
		$disable_addon = empty( $_GET['it-cart-buddy-disable-addon'] ) ? false : $_GET['it-cart-buddy-disable-addon'];

		if ( ! $enable_addon && ! $disable_addon )
			return;

		$registered    = it_cart_buddy_get_addons();

		// Enable or Disable addon requested by user
		if ( $enable_addon ) {
			if ( $nonce_valid = wp_verify_nonce( $_GET['_wpnonce'], 'cart-buddy-enable-add-on' ) )
				$enabled = it_cart_buddy_enable_addon( $enable_addon );
			$message = 'enabled';
		} else if ( $disable_addon ) {
			if ( $nonce_valid = wp_verify_nonce( $_GET['_wpnonce'], 'cart-buddy-disable-add-on' ) )
				$enabled = it_cart_buddy_disable_addon( $disable_addon );
			$message = 'disabled';
		}

		// Redirect if nonce not valid
		if ( ! $nonce_valid ) {
			wp_safe_redirect( admin_url( '/admin.php?page=it-cart-buddy-addons&error=' . $message ) );
			die();
		}
		
		// Disable any enabled add-ons that aren't registered any more while we're here.
		$enabled_addons = it_cart_buddy_get_enabled_addons();
		foreach( (array) $enabled_addons as $slug => $file ) {
			if ( empty( $registered[$slug] ) )
				it_cart_buddy_disable_addon( $slug );
		}
			
		$redirect_to = admin_url( '/admin.php?page=it-cart-buddy-addons&message=' . $message );

		// Redirect to settings page on activation if it exists
		if ( $enable_addon ) {
			if ( $enabled = it_cart_buddy_get_addon( $enable_addon ) )  {
				if ( ! empty( $enabled['options']['settings-callback'] ) && is_callable( $enabled['options']['settings-callback'] ) )
					$redirect_to .= '&add_on_settings=' . $enable_addon;
			}
		}

		wp_safe_redirect( $redirect_to );
		die();
	}

	/**
	 * Opens the Cart Buddy Admin Menu when viewing the Add New page
	 *
	 * @since 0.3.0
	 * @return string
	*/
	function open_cart_buddy_menu_on_post_type_views( $parent_file, $revert=false ) {
		global $submenu_file, $pagenow, $post;

		if ( 'post-new.php' != $pagenow && 'post.php' != $pagenow )
			return $parent_file;

		if ( empty( $post->post_type ) || ( 'it_cart_buddy_prod' != $post->post_type && 'it_cart_buddy_tran' != $post->post_type ) )
			return $parent_file;

		// Set Add New as bold when on the post-new.php screen
		if ( 'post-new.php' == $pagenow )
			$submenu_file = 'it-cart-buddy-choose-product-type';

		// Return it-cart-buddy as the parent (open) menu when on post-new.php and post.php for it_cart_buddy_prod post_types
		return 'it-cart-buddy';
	}

	/**
	 * Redirects post-new.php to it-cart-buddy-choose-product-type when needed
	 *
	 * If we have landed on post-new.php?post_type=it_cart_buddy_prod without the product_type param
	 * and with multiple product-type add-ons enabled.
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function redirect_post_new_to_product_type_selection_screen() {
		global $pagenow;
		$product_type_add_ons = it_cart_buddy_get_enabled_addons( array( 'category' => array( 'product-type' ) ) );
		$post_type            = empty( $_GET['post_type'] ) ? false : $_GET['post_type'];
		$product_type         = empty( $_GET['product_type'] ) ? false : $_GET['product_type'];

		if ( count( $product_type_add_ons ) > 1 && 'post-new.php' == $pagenow && 'it_cart_buddy_prod' == $post_type ) {
			if ( empty( $product_type_add_ons[$product_type] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=it-cart-buddy-choose-product-type' ) );
				die();
			}
		}
	}

	/**
	 * Prints select options for the currency type
	 *
	 * @since 0.3.6
	 * return array 
	*/
	function get_default_currency_options() {
		$options = array();
		$currency_options = it_cart_buddy_get_currency_options();
		foreach( (array) $currency_options as $currency ) {
			$options[$currency->cc] = ucwords( $currency->name ) . ' (' . $currency->symbol . ')'; 
		}
		return $options;
	}

	/**
	 * Prints the page options for use on page settings form
	 *
	 * @since 0.3.7
	 * @return array wp page ID to page title
	*/
	function get_default_page_options() {
		$options = array();
		$options[0] = __( 'Select a Page', 'LION' );
		if ( $page_options = get_pages() ) {
			foreach( $page_options as $page ) {
				$options[$page->ID] = get_the_title( $page->ID );
			}
		}
		return $options;
	}

	/**
	 * Save core general settings
	 *
	 * Validates data and saves to options.
	 *
	 * @todo provide feedback to user
	 * @todo validate data
	 * @since 0.3.4
	 * @return void
	*/
	function save_core_general_settings() {
		if ( empty( $_POST ) || 'it-cart-buddy-settings' != $this->_current_page || ! empty( $this->_current_tab ) )
			return;

		$settings = wp_parse_args( ITForm::get_post_data(), $this->_storage->load() );

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'cart-buddy-general-settings' ) ) { 
            $this->error_message = __( 'Error. Please try again', 'LION' );
            return;
        } 

		if ( ! empty( $this->error_message ) || $error_msg = $this->general_settings_are_invalid( $settings ) ) {
			if ( ! empty( $error_msg ) )
				$this->error_message = $error_msg;
		} else {
			$this->_storage->save( $settings );
			$this->_storage->clear_cache();
			$this->status_message = __( 'Settings Saved.', 'LION' );
		}
	}

	/**
	 * Validate general settings
	 *
	 * @since 0.3.6
	 * @param string $settings submitted settings
	 * @return false or error message
	*/
	function general_settings_are_invalid( $settings ) {
		$errors = array();
		if ( ! empty( $settings['company_email'] ) && ! is_email( $settings['company_email'] ) )
			$errors[] = __( 'Please provide a valid email address.', 'LION' );
		if ( empty( $settings['currency_thousands_separator'] ) )
			$errors[] = __( 'Thousands Separator cannot be empty', 'LION' );
		if ( empty( $settings['currency_decimals_separator'] ) )
			$errors[] = __( 'Decimals Separator cannot be empty', 'LION' );

		$errors = apply_filters( 'it_cart_buddy_general_settings_validation_errors', $errors );
		if ( ! empty ( $errors ) )
			return implode( '<br />', $errors );
		else
			return false;
	}

	/**
	 * Save core email tab settings
	 *
	 * Validates data and saves to options.
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function save_core_email_settings() {
		if ( empty( $_POST ) || 'it-cart-buddy-settings' != $this->_current_page || 'email' != $this->_current_tab )
			return;

		$settings = wp_parse_args( ITForm::get_post_data(), $this->_storage->load() );

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'cart-buddy-email-settings' ) ) { 
            $this->error_message = __( 'Error. Please try again', 'LION' );
            return;
        } 

		if ( ! empty( $this->error_message ) || $error_msg = $this->email_settings_are_invalid( $settings ) ) {
			if ( ! empty( $error_msg ) )
				$this->error_message = $error_msg;
		} else {
			$this->_storage->save( $settings );
			$this->_storage->clear_cache();
			$this->status_message = __( 'Settings Saved.', 'LION' );
		}
	}

	/**
	 * Validate email settings
	 *
	 * @since 0.3.6
	 * @param string $settings submitted settings
	 * @return false or error message
	*/
	function email_settings_are_invalid( $settings ) {
		$errors = array();
		if ( ! empty( $settings['receipt_email_address'] ) && ! is_email( $settings['receipt_email_address'] ) )
			$errors[] = __( 'Please provide a valid email address.', 'LION' );
		if ( empty( $settings['receipt_email_name'] ) )
			$errors[] = __( 'Email Name cannot be empty', 'LION' );
		if ( empty( $settings['receipt_email_subject'] ) )
			$errors[] = __( 'Email Subject cannot be empty', 'LION' );

		$errors = apply_filters( 'it_cart_buddy_email_settings_validation_errors', $errors );
		if ( ! empty ( $errors ) )
			return '<p>' . implode( '<br />', $errors ) . '</p>';
		else
			return false;
	}

	/**
	 * Save core pages tab settings
	 *
	 * Validates data and saves to options.
	 *
	 * @since 0.3.7
	 * @return void
	*/
	function save_core_page_settings() {
		if ( empty( $_POST ) || 'it-cart-buddy-settings' != $this->_current_page || 'pages' != $this->_current_tab )
			return;

		$settings = wp_parse_args( ITForm::get_post_data(), $this->_storage->load() );

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'cart-buddy-page-settings' ) ) { 
            $this->error_message = __( 'Error. Please try again', 'LION' );
            return;
        } 

		if ( ! empty( $this->error_message ) || $error_msg = $this->page_settings_are_invalid( $settings ) ) {
			if ( ! empty( $error_msg ) )
				$this->error_message = $error_msg;
		} else {
			$this->_storage->save( $settings );
			$this->_storage->clear_cache();
			$this->status_message = __( 'Settings Saved.', 'LION' );
		}
	}

	/**
	 * Validate page settings
	 *
	 * @since 0.3.7
	 * @param string $settings submitted settings
	 * @return false or error message
	*/
	function page_settings_are_invalid( $settings ) {
		$errors = array();

		$errors = apply_filters( 'it_cart_buddy_page_settings_validation_errors', $errors );
		if ( ! empty ( $errors ) )
			return '<p>' . implode( '<br />', $errors ) . '</p>';
		else
			return false;
	}
}
if ( is_admin() )
	$IT_Cart_Buddy_Admin = new IT_Cart_Buddy_Admin( $this );
