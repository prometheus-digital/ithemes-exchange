<?php
/**
 * iThemes Exchange admin class.
 *
 * This class manages the admin side of the plugin
 *
 * @package IT_Exchange
 * @since 0.1.0
*/
class IT_Exchange_Admin {

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
	function IT_Exchange_Admin( &$parent ) {

		// Set parent property
		$this->_parent = $parent;

		// Admin Menu Capability
		$this->admin_menu_capability = apply_filters( 'it_exchange_admin_menu_capability', 'read' );

		// Set current properties
		$this->set_current_properties();

		// Open iThemes Exchange menu when on add/edit iThemes Exchange product post type
		add_action( 'parent_file', array( $this, 'open_exchange_menu_on_post_type_views' ) );

		// Load Storage
		add_action( 'admin_init', array( $this, 'load_storage' ) );

		// Add actions for iThemes registration
		add_action( 'admin_menu', array( $this, 'add_exchange_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'enable_disable_registered_add_on' ) );

		// Redirect to Product selection on Add New if needed
		add_action( 'admin_init', array( $this, 'redirect_post_new_to_product_type_selection_screen' ) );

		// Init our custom add/edit layout interface
		add_action( 'admin_init', array( $this, 'setup_add_edit_product_screen_layout' ) );

		// Force 2 column view on add / edit products
		add_filter( 'screen_layout_columns', array( $this, 'modify_add_edit_page_layout' ) );
		add_filter( 'get_user_option_screen_layout_it_exchange_prod', 'update_user_column_options' ); // __return_true returns '1' :)

		// Save core settings
		add_action( 'admin_init', array( $this, 'save_core_general_settings' ) );
		add_action( 'admin_init', array( $this, 'save_core_email_settings' ) );
		add_action( 'admin_init', array( $this, 'save_core_page_settings' ) );

		// Email settings callback
		add_filter( 'it_exchange_general_settings_tab_callback_email', array( $this, 'register_email_settings_tab_callback' ) );
		add_action( 'it_exchange_print_general_settings_tab_links', array( $this, 'print_email_settings_tab_link' ) );

		// Page settings callback
		add_filter( 'it_exchange_general_settings_tab_callback_pages', array( $this, 'register_pages_settings_tab_callback' ) );
		add_action( 'it_exchange_print_general_settings_tab_links', array( $this, 'print_pages_settings_tab_link' ) );

		// General Settings Defaults
		add_filter( 'it_storage_get_defaults_exchange_settings_general', array( $this, 'set_general_settings_defaults' ) );
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
		if ( 'it-exchange-settings' != $this->_current_page )
			return;

		it_classes_load( 'it-storage.php' );
		$tab = empty( $this->_current_tab ) ? 'general' : $this->_current_tab;
		$key = 'exchange_settings'  . '_' . $tab;

		$this->_storage = new ITStorage2( $key );
	}

	/**
	 * Adds the main iThemes Exchange menu item to the WP admin menu
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function add_exchange_admin_menu() {
		// Add main iThemes Exchange menu item
		add_menu_page( 'iThemes Exchange', 'Exchange', $this->admin_menu_capability, 'it-exchange', array( $this, 'print_exchange_setup_page' ) );

		// Add setup wizard if not complete or if on page
		if ( 'it-exchange-setup' == $this->_current_page || ! get_option( 'it_exchange_setup_complete' ) )
			add_submenu_page( 'it-exchange', 'iThemes Exchange Setup Wizard', 'Setup Wizard', $this->admin_menu_capability, 'it-exchange-setup', array( $this, 'print_exchange_setup_page' ) );

		// Remove default iThemes Exchange sub-menu item created with parent menu item
		remove_submenu_page( 'it-exchange', 'it-exchange' );

		// Add the product submenu pages depending on active product add-ons
		$this->add_product_submenus();

		// Add Transactions menu item
		add_submenu_page( 'it-exchange', 'iThemes Exchange ' . __( 'Payments', 'LION' ), __( 'Payments', 'LION' ), $this->admin_menu_capability, 'edit.php?post_type=it_exchange_tran' );

		// Add Settings Menu Item
		$settings_callback = array( $this, 'print_exchange_settings_page' );
		if ( 'it-exchange-settings' == $this->_current_page && ! empty( $this->_current_tab ) )
			$settings_callback = apply_filters( 'it_exchange_general_settings_tab_callback_' . $this->_current_tab, $settings_callback );
		add_submenu_page( 'it-exchange', 'iThemes Exchange Settings', 'Settings', $this->admin_menu_capability, 'it-exchange-settings', $settings_callback );

		// Add Add-ons menu item
		$callback = array( $this, 'print_exchange_add_ons_page' );
		if ( 'it-exchange-addons' == $this->_current_page && ! empty( $_GET['add-on-settings'] ) ) {
			if ( $addon = it_exchange_get_addon( $_GET['add-on-settings'] ) ) {
				if ( ! empty( $addon['options']['settings-callback'] ) && is_callable( $addon['options']['settings-callback'] ) )
					$callback = $addon['options']['settings-callback'];
			}
		}
		add_submenu_page( 'it-exchange', 'iThemes Exchange Add-ons', 'Add-ons', $this->admin_menu_capability, 'it-exchange-addons', $callback );
	}

	/**
	 * Adds the product submenus based on number of enabled product-type add-ons
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function add_product_submenus() {
		// Check for enabled product add-ons. Don't need product pages if we don't have product add-ons enabled
		if ( $enabled_product_types = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) ) ) {
			$add_on_count = count( $enabled_product_types );
			add_submenu_page( 'it-exchange', 'All Products', 'All Products', $this->admin_menu_capability, 'edit.php?post_type=it_exchange_prod' );
			if ( 1 == $add_on_count ) {
				// If we only have one product-type enabled, add standard post_type pages
				$product = reset( $enabled_product_types );
				add_submenu_page( 'it-exchange', 'Add Product', 'Add Product', $this->admin_menu_capability, 'post-new.php?post_type=it_exchange_prod&it-exchange-product-type=' . $product['slug'] );
			} else if ( $add_on_count > 1 ) {
				// If we have more than one product type, add them each separately
				foreach( $enabled_product_types as $type => $params ) {
					$name = empty( $params['options']['labels']['singular_name'] ) ? 'Product' : esc_attr( $params['options']['labels']['singular_name'] );
					add_submenu_page( 'it-exchange', 'Add ' . $name, 'Add ' . $name, $this->admin_menu_capability, 'post-new.php?post_type=it_exchange_prod&it-exchange-product-type=' . esc_attr( $params['slug'] ) );
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
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-settings&tab=email' ); ?>"><?php _e( 'Email Settings', 'LION' ); ?></a><?php
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
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-settings&tab=pages' ); ?>"><?php _e( 'Pages', 'LION' ); ?></a><?php
	}

	/**
	 * Prints the tabs for the iThemes Exchange General Settings
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_general_settings_tabs() {
		$active = empty( $this->_current_tab ) ? 'nav-tab-active' : '';
		?>
		<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-settings' ); ?>"><?php _e( 'General', 'LION' ); ?></a>
		<?php do_action( 'it_exchange_print_general_settings_tab_links', $this->_current_tab ); ?>
		</h2>
		<?php
	}

	/**
	 * Prints the setup page for iThemes Exchange
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function print_exchange_setup_page() {
		?>
		<div class="wrap">
			<?php screen_icon( 'page' ); ?>
			<h2>iThemes Exchange Setup</h2>
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
			'default-currency'             => 'USD',
			'currency-thousands-separator' => ',',
			'currency-decimals-separator'  => '.',
		);
		$values = ITUtility::merge_defaults( $values, $defaults );
		return $values;
	}

	/**
	 * Prints the settings page for iThemes Exchange
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_exchange_settings_page() {
		$form_values  = empty( $this->error_message ) ? $this->_storage->load() : ITForm::get_post_data();
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_exchange_settings' ) );
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_settings_form_id', 'it-exchange-settings' ),
			'enctype' => apply_filters( 'it_exchange_settings_form_enctype', false ),
		);
		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		include( 'views/admin-settings.php' );
	}

	/**
	 * Prints the email page for iThemes Exchange
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_email_settings_page() {
		$form_values  = empty( $this->error_message ) ? $this->_storage->load() : ITForm::get_post_data();
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_exchange_email_settings' ) );
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_email_settings_form_id', 'it-exchange-email-settings' ),
			'enctype' => apply_filters( 'it_exchange_email_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-settings&tab=email',
		);
		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		include( 'views/admin-email-settings.php' );
	}

	/**
	 * Prints the pages page for iThemes Exchange
	 *
	 * @since 0.3.7
	 * @return void
	*/
	function print_pages_settings_page() {
		$form_values  = empty( $this->error_message ) ? $this->_storage->load() : ITForm::get_post_data();
		$form         = new ITForm( $form_values, array( 'prefix' => 'it_exchange_page_settings' ) );
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_page_settings_form_id', 'it-exchange-page-settings' ),
			'enctype' => apply_filters( 'it_exchange_page_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-settings&tab=pages',
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
	function print_exchange_add_ons_page() {
		$registered = it_exchange_get_addons();
		$add_on_cats = it_exchange_get_addon_categories();
		$message = empty( $_GET['message'] ) ? false : $_GET['message'];
		if ( 'enabled' == $message ) {
			ITUtility::show_status_message( __( 'Add-on enabled.', 'LION' ) );
		} else if ( 'disabled' == $message ) {
			ITUtility::show_status_message( __( 'Add-on disabled.', 'LION' ) );
		} else if ( 'addon-auto-disabled-' == substr( $message, 0, 20 ) ) {
			$addon_slug = substr( $message, 20 );
			$status_message = __( sprintf( 'iThemes Exchange has automatically disabled an add-on: %s. This is mostly likely due to it being uninstalled or improperlly registered.', $addon_slug ), 'LION' );
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
		$enable_addon  = empty( $_GET['it-exchange-enable-addon'] ) ? false : $_GET['it-exchange-enable-addon'];
		$disable_addon = empty( $_GET['it-exchange-disable-addon'] ) ? false : $_GET['it-exchange-disable-addon'];

		if ( ! $enable_addon && ! $disable_addon )
			return;

		$registered    = it_exchange_get_addons();

		// Enable or Disable addon requested by user
		if ( $enable_addon ) {
			if ( $nonce_valid = wp_verify_nonce( $_GET['_wpnonce'], 'exchange-enable-add-on' ) )
				$enabled = it_exchange_enable_addon( $enable_addon );
			$message = 'enabled';
		} else if ( $disable_addon ) {
			if ( $nonce_valid = wp_verify_nonce( $_GET['_wpnonce'], 'exchange-disable-add-on' ) )
				$enabled = it_exchange_disable_addon( $disable_addon );
			$message = 'disabled';
		}

		// Redirect if nonce not valid
		if ( ! $nonce_valid ) {
			wp_safe_redirect( admin_url( '/admin.php?page=it-exchange-addons&error=' . $message ) );
			die();
		}
		
		// Disable any enabled add-ons that aren't registered any more while we're here.
		$enabled_addons = it_exchange_get_enabled_addons();
		foreach( (array) $enabled_addons as $slug => $file ) {
			if ( empty( $registered[$slug] ) )
				it_exchange_disable_addon( $slug );
		}
			
		$redirect_to = admin_url( '/admin.php?page=it-exchange-addons&message=' . $message );

		// Redirect to settings page on activation if it exists
		if ( $enable_addon ) {
			if ( $enabled = it_exchange_get_addon( $enable_addon ) )  {
				if ( ! empty( $enabled['options']['settings-callback'] ) && is_callable( $enabled['options']['settings-callback'] ) )
					$redirect_to .= '&add-on-settings=' . $enable_addon;
			}
		}

		wp_safe_redirect( $redirect_to );
		die();
	}

	/**
	 * Opens the iThemes Exchange Admin Menu when viewing the Add New page
	 *
	 * @since 0.3.0
	 * @return string
	*/
	function open_exchange_menu_on_post_type_views( $parent_file, $revert=false ) {
		global $submenu_file, $pagenow, $post;

		if ( 'post-new.php' != $pagenow && 'post.php' != $pagenow )
			return $parent_file;

		if ( empty( $post->post_type ) || ( 'it_exchange_prod' != $post->post_type && 'it_exchange_tran' != $post->post_type ) )
			return $parent_file;

		// Set Add New as bold when on the post-new.php screen
		if ( 'post-new.php' == $pagenow )
			$submenu_file = 'it-exchange-choose-product-type';

		// Return it-exchange as the parent (open) menu when on post-new.php and post.php for it_exchange_prod post_types
		return 'it-exchange';
	}

	/**
	 * Redirects post-new.php to it-exchange-choose-product-type when needed
	 *
	 * If we have landed on post-new.php?post_type=it_exchange_prod without the product_type param
	 * and with multiple product-type add-ons enabled.
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function redirect_post_new_to_product_type_selection_screen() {
		global $pagenow;
		$product_type_add_ons = it_exchange_get_enabled_addons( array( 'category' => array( 'product-type' ) ) );
		$post_type            = empty( $_GET['post_type'] ) ? false : $_GET['post_type'];
		$product_type         = empty( $_GET['it-exchange-product-type'] ) ? false : $_GET['it-exchange-product-type'];

		if ( count( $product_type_add_ons ) > 1 && 'post-new.php' == $pagenow && 'it_exchange_prod' == $post_type ) {
			if ( empty( $product_type_add_ons[$product_type] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=it-exchange-choose-product-type' ) );
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
		$currency_options = it_exchange_get_currency_options();
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
		if ( empty( $_POST ) || 'it-exchange-settings' != $this->_current_page || ! empty( $this->_current_tab ) )
			return;

		$settings = wp_parse_args( ITForm::get_post_data(), $this->_storage->load() );

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'exchange-general-settings' ) ) { 
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

		$errors = apply_filters( 'it_exchange_general_settings_validation_errors', $errors );
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
		if ( empty( $_POST ) || 'it-exchange-settings' != $this->_current_page || 'email' != $this->_current_tab )
			return;

		$settings = wp_parse_args( ITForm::get_post_data(), $this->_storage->load() );

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'exchange-email-settings' ) ) { 
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

		$errors = apply_filters( 'it_exchange_email_settings_validation_errors', $errors );
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
		if ( empty( $_POST ) || 'it-exchange-settings' != $this->_current_page || 'pages' != $this->_current_tab )
			return;

		$settings = wp_parse_args( ITForm::get_post_data(), $this->_storage->load() );

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'exchange-page-settings' ) ) { 
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

		$errors = apply_filters( 'it_exchange_page_settings_validation_errors', $errors );
		if ( ! empty ( $errors ) )
			return '<p>' . implode( '<br />', $errors ) . '</p>';
		else
			return false;
	}

	/**
	 * Set the max columns option for the add / edit product page.
	 *
	 * @since 0.4.0
	 *
	 * @param $columns Existing array of how many colunns to show for a post type
	 * @return array Filtered array
	*/
	function modify_add_edit_page_layout( $columns ) {
		$columns['it_exchange_prod'] = 2;
		return $columns;
	}

	/**
	 * Updates the user options for number of columns to use on add / edit product views
	 *
	 * @since 0.4.0
	 *
	 * @return 2
	*/
	function update_user_column_options( $existing ) {
		return 2;
	}

	/**
	 * Inits the add / edit product layout
	 *
	 * @since 0.4.0
	 * @param array $filter_var Don't modify this. Always return it.
	 * @return void
	*/
	function setup_add_edit_product_screen_layout() {
		global $pagenow, $post;
		$post_type = empty( $_REQUEST['post_type'] ) ? false : $_REQUEST['post_type'];
		$post_type = empty( $post_type ) && ! empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : $post_type;
		$post_type = is_numeric( $post_type ) ? get_post_type( $post_type ) : $post_type;

		if ( ( 'post-new.php' != $pagenow && 'post.php' != $pagenow ) || 'it_exchange_prod' != $post_type )
			return;

		// Enqueue styles
		wp_enqueue_style( 'it-exchange-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-edit-product.css' );

		// Enqueue scripts
		wp_enqueue_script( 'it-exchange-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-product.js', array( 'jquery-ui-sortable' ) );

		// Enqueue Media library scripts and styles
		wp_enqueue_media();

		// Temporarially remove post support for post_formats and title
		add_filter( 'post_updated_messages', array( $this, 'temp_remove_theme_supports' ) ); 

		// Register layout metabox
		add_action( 'do_meta_boxes', array( $this, 'register_custom_layout_metabox' ), 999, 2 );

		// Setup custom add / edit product layout
		add_action( 'submitpost_box', array( $this, 'init_add_edit_product_screen_layout' ) );

	}

	/**
	 * Temporarily Remove support for post_formats and title
	 *
	 * @since 0.4.0
	 * @param array $messages We're hijacking a hook. Never modify. Always return
	 * @return void
	*/
	function temp_remove_theme_supports( $messages ) {
		$product_type = it_exchange_get_product_type();

		if ( it_exchange_product_type_supports_feature( $product_type, 'wp-post-formats' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'wp-post-formats', $product_type );
			it_exchange_add_feature_support_to_product_type( 'temp_disabled_wp-post-formats', $product_type );
		}
		if ( it_exchange_product_type_supports_feature( $product_type, 'title' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'title', $product_type );
			it_exchange_add_feature_support_to_product_type( 'temp_disabled_title', $product_type );
		}
		if ( it_exchange_product_type_supports_feature( $product_type, 'extended-description' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'extended-description', $product_type );
			it_exchange_add_feature_support_to_product_type( 'temp_disabled_extended-description', $product_type );
		}
		return $messages;
	}

	/**
	 * Adds the custom layout metabox
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_custom_layout_metabox( $post_type, $context ) {
		if ( 'it_exchange_prod' != $post_type && 'side' != $context )
			return;

		$id       = 'it-exchange-add-edit-product-interface-main';
		$title    = __( 'Main Product Interface', 'LION' ); // Not used
		$callback = array( $this, 'do_add_edit_product_screen_layout_main' );
		add_meta_box( $id, $title, $callback, 'it_exchange_prod', 'normal', 'high' );

		$id       = 'it-exchange-add-edit-product-interface-side';
		$title    = __( 'Side Product Interface', 'LION' ); // Not used
		$callback = array( $this, 'do_add_edit_product_screen_layout_side' );
		add_meta_box( $id, $title, $callback, 'it_exchange_prod', 'side', 'high' );
	}

	/**
	 * Setup the custom screen by shifting meta boxes around in preparation for our meta box
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function init_add_edit_product_screen_layout() {
		global $wp_meta_boxes;
		$product_type = it_exchange_get_product_type();

		// Init it_exchange_advanced_low context
		$wp_meta_boxes['it_exchange_prod']['it_exchange_advanced_low'] = array();
		$custom_layout = array();

		// Remove our layout metaboxes
		if ( ! empty( $wp_meta_boxes['it_exchange_prod']['normal']['high']['it-exchange-add-edit-product-interface-main'] ) ) {
			$custom_layout_normal = $wp_meta_boxes['it_exchange_prod']['normal']['high']['it-exchange-add-edit-product-interface-main'];
			unset( $wp_meta_boxes['it_exchange_prod']['normal']['high']['it-exchange-add-edit-product-interface-main'] );
		}
		if ( ! empty( $wp_meta_boxes['it_exchange_prod']['side']['high']['it-exchange-add-edit-product-interface-side'] ) ) {
			$custom_layout_side = $wp_meta_boxes['it_exchange_prod']['side']['high']['it-exchange-add-edit-product-interface-side'];
			unset( $wp_meta_boxes['it_exchange_prod']['side']['high']['it-exchange-add-edit-product-interface-side'] );
		}
		
		// Loop through side, normal, and advanced contexts and move all metaboxes to it_exchange_advanced_low context
		foreach( array( 'side', 'normal', 'advanced' ) as $context ) {
			if ( ! empty ( $wp_meta_boxes['it_exchange_prod'][$context] ) ) {
				foreach( $wp_meta_boxes['it_exchange_prod'][$context] as $priority => $boxes ) {
					if ( ! isset( $wp_meta_boxes['it_exchange_prod']['it_exchange_advanced']['low'] ) )
						 $wp_meta_boxes['it_exchange_prod']['it_exchange_advanced']['low']= array();
					$wp_meta_boxes['it_exchange_prod']['it_exchange_advanced']['low'] = array_merge(
						$wp_meta_boxes['it_exchange_prod']['it_exchange_advanced']['low'], 
						$wp_meta_boxes['it_exchange_prod'][$context][$priority]
					);
				}

				$wp_meta_boxes['it_exchange_prod'][$context] = array();
			}
		}

		// Add our custom layout back to normal/side high
		if ( ! empty( $custom_layout_normal ) )
			$wp_meta_boxes['it_exchange_prod']['normal']['high']['it-exchange-add-edit-product-interface-main'] = $custom_layout_normal;
		if ( ! empty( $custom_layout_side ) )
			$wp_meta_boxes['it_exchange_prod']['side']['high']['it-exchange-add-edit-product-interface-side'] = $custom_layout_side;

		update_user_option( get_current_user_id(), 'meta-box-order_it_exchange_prod', array() );


		// Add any temporarially disabled features back
		if ( it_exchange_product_type_supports_feature( $product_type, 'temp_disabled_wp-post-formats' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'temp_disabled_wp-post-formats', $product_type );
			it_exchange_add_feature_support_to_product_type( 'wp-post-formats', $product_type );
		}
		if ( it_exchange_product_type_supports_feature( $product_type, 'temp_disabled_title' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'temp_disabled_title', $product_type );
			it_exchange_add_feature_support_to_product_type( 'title', $product_type );
		}
		if ( it_exchange_product_type_supports_feature( $product_type, 'temp_disabled_extended-description' ) ) {
			it_exchange_remove_feature_support_for_product_type( 'temp_disabled_extended-description', $product_type );
			it_exchange_add_feature_support_to_product_type( 'extended-description', $product_type );
		}

		// Move publish to the bottom of normal
		remove_meta_box( 'submitdiv', __( 'Publish' ), 'post_submit_meta_box', null, 'it_exchange_advanced', 'core' );
		add_meta_box( 'submitdiv', __( 'Publish' ), 'post_submit_meta_box', null, 'it_exchange_side', 'high' );

		// Move Featured Image to top of side if supported
		if ( it_exchange_product_type_supports_feature( $product_type, 'featured-image' ) ) {
			add_meta_box('postimagediv', __('Featured Image'), 'post_thumbnail_meta_box', 'it_exchange_prod', 'it_exchange_side' );
		}
	}

	/**
	 * This prints the iThemes Exchange add / edit product custom layout interface (a fancy meta box)
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function do_add_edit_product_screen_layout_main( $post ) {
		do_meta_boxes( 'it_exchange_prod', 'it_exchange_normal', $post );
		do_meta_boxes( 'it_exchange_prod', 'it_exchange_advanced', $post );
	}

	/**
	 * This prints the iThemes Exchange a / edit product custom layout interface for the side column
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function do_add_edit_product_screen_layout_side( $post ) {
		do_meta_boxes( 'it_exchange_prod', 'it_exchange_side', $post );
	}
}
if ( is_admin() )
	$IT_Exchange_Admin = new IT_Exchange_Admin( $this );
