<?php
/** 
 * This class get initiated when a shipping add-on is enabled
 * @package IT_Exchagne
*/

class IT_Exchange_Shipping {

	function IT_Exchange_Shipping() {
		// We need to include the abstract methods class regardless
		include_once( dirname( __FILE__ ) . '/class-method.php' );
		include_once( dirname( __FILE__ ) . '/class-shipping-feature.php' );
		include_once( dirname( __FILE__ ) . '/shipping-features/init.php' );

		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'maybe_init' ) );
	}

	function maybe_init() {
		$enabled_shipping_addons = (boolean) it_exchange_get_enabled_addons( array( 'category' => 'shipping' ) );	
		if ( !$enabled_shipping_addons )
			return;

		add_action( 'it_exchange_print_general_settings_tab_links', array( $this, 'print_shipping_tab_link' ) );
		add_filter( 'it_exchange_general_settings_tab_callback_shipping', array( $this, 'register_settings_tab_callback' ) );

		// Setup purchase requirement
		$this->init_shipping_address_purchase_requirement();

		// Template part filters
		add_filter( 'it_exchange_get_content_cart_totals_elements', array( $this, 'add_shipping_to_template_totals_loops' ) );
		add_filter( 'it_exchange_get_content_checkout_totals_elements', array( $this, 'add_shipping_to_template_totals_loops' ) );
		add_filter( 'it_exchange_get_super-widget-checkout_after-cart-items_loops', array( $this, 'add_shipping_to_sw_template_totals_loops' ) );

		// Ajax Request to update shipping address
		add_action( 'it_exchange_processing_super_widget_ajax_update-shipping', array( $this, 'process_ajax_request' ) );

		// Process the update address request
		add_action( 'template_redirect', array( $this, 'process_update_address_request' ) );

		// Clear the cart address when the cart is cleared
		add_action( 'it_exchange_empty_shopping_cart', array( $this, 'clear_cart_address' ) );

		// Updates the general settings states field in the admin
		add_action( 'it_exchange_admin_country_states_sync_for_addon-shipping-general', array( $this, 'update_general_settings_state_field' ) );

		// Enqueue the JS needed for the super widget
		add_action( 'it_exchange_enqueue_super_widget_scripts', array( $this, 'enqueue_sw_js' ) );

		// Enqueue the admin CSS
		add_action( 'admin_print_styles', array( $this, 'enqueue_admin_css' ) );

		// Adjusts the cart total
		add_filter( 'it_exchange_get_cart_total', array( $this, 'modify_shipping_total' ) );
	}

	/**
	 * Init Shipping Address Purchase Requirement
	 *
	*/
	function init_shipping_address_purchase_requirement() {
		$this->register_purchase_requirement();
	}
	
	/**
	 * Registers the shipping purchase requirements
	 *
	 * Use the it_exchange_register_purchase_requirement function to tell exchange
	 * that your add-on requires certain conditionals to be set prior to purchase.
	 * For more details see api/misc.php
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function register_purchase_requirement() {
		// User must have a shipping address to purchase
		$properties = array(
			'requirement-met'        => 'it_exchange_is_shipping_address_valid', // This is a PHP callback
			'sw-template-part'       => 'shipping',
			'checkout-template-part' => 'shipping',
			'notification'           => __( 'You must enter a shipping address before you can checkout', 'LION' ),
			'priority'               => 4,
		);  
		it_exchange_register_purchase_requirement( 'shipping-has-address', $properties );
	}

	/**
	 * Is cart address valid?
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	*/
	function is_address_valid() {
		$cart_address  = it_exchange_get_cart_data( 'shipping-address' );
		$cart_customer = empty( $cart_address['customer'] ) ? 0 : $cart_address['customer'];
		$customer_id   = it_exchange_get_current_customer_id();
		$customer_id   = empty( $customer_id ) ? $cart_customer : $customer_id;

		return (boolean) get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
	}

	/**
	 * Prints the Shipping tab on the Exchange Settings admin page
	 *
	 * @since CHANGEME
	 *
	 * @param  string $current_tab the current tab being requested
	 * @return void
	*/
	function print_shipping_tab_link( $current_tab ) {
		$active = 'shipping' == $current_tab ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-settings&tab=shipping' ); ?>"><?php _e( 'Shipping', 'LION' ); ?></a><?php
	}

	/**
	 * Register the callback for the settings page
	 *
	 * I hate that this was setup like this. Seems like an uneeded function
	 *
	 * @since CHANGEME
	 *
	 * @return string the callback
	*/
	function register_settings_tab_callback() {
		return array( $this, 'print_shipping_tab' );
	}

	/**
	 * Prints the contents of the Shipping Tab
	 *
	 * First looks to see if a registered shipping provider's settins are being requested
	 * If so, it inits those fields.
	 * If not, it loads the general shipping settings
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function print_shipping_tab() {
		$settings = it_exchange_get_option( 'addon_shipping', true );

		?>
		<div class="wrap">
			<?php 
			// Print Admin Settings Tabs
			$GLOBALS['IT_Exchange_Admin']->print_general_settings_tabs(); 

			// Print shipping provider tabs
			$this->print_provider_settings_tabs();

			// Print active shipping page
			$provider          = ( ! empty( $_GET['provider'] ) && it_exchange_is_shipping_provider_registered( $_GET['provider'] ) ) ? it_exchange_get_registered_shipping_provider( $_GET['provider'] ) : 'shipping-general';
			$prefix            = is_object( $provider ) ? $provider->slug : 'shipping-general';
			$action            = add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping' ), admin_url( 'admin' ) );
			$action            = is_object( $provider ) ? add_query_arg( array( 'provider' => $provider->slug ), $action ) : $action;
			$fields            = is_object( $provider ) ? $provider->provider_settings : $this->get_general_settings_fields();
			$country_states_js = is_object( $provider ) ? $provider->country_states_js : $this->get_general_settings_country_states_js();
			
			// Set admin setting form class options
			$options = array(
				'prefix'       => $prefix,
				'form-options' => array(
					'action'            => $action,
					'country-states-js' => $country_states_js,
				),
				'form-fields'  => $fields,
			);
			it_exchange_print_admin_settings_form( $options );
			?>
		</div>
		<?php
	}

	/**
	 * Prints the tabs for all registered shipping providers
	 *
	 * @since CHANGEME
	 *
	 * @return html
	*/
	function print_provider_settings_tabs() {

		// Return empty string if there aren't any registered shipping providers
		if ( ! $providers = it_exchange_get_registered_shipping_providers() )
			return ''; 

		// Set the currently requested shipping provider tab. Defaults to General
		$current = empty( $_GET['provider'] ) ? false : $_GET['provider'];
		$current = ( ! empty( $current ) && ! it_exchange_is_shipping_provider_registered( $current ) ) ? false : $current;

		// Print the HTML
		?>  
		<div class="it-exchange-secondary-tabs it-exchange-shipping-provider-tabs">
			<a class="shipping-provider-link <?php echo ( empty( $current ) ) ? 'it-exchange-current' : ''; ?>" href="<?php esc_attr_e( add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping' ), admin_url( 'admin' ) ) ); ?>">
				<?php _e( 'General', 'LION' ); ?>
			</a>
			<?php 
			foreach( $providers as $provider )  {
				$provider = it_exchange_get_registered_shipping_provider( $provider['slug'] );
				if ( empty( $provider->has_settings_page ) )
					continue;
				$url = add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping', 'provider' => $provider->get_slug() ), admin_url( 'admin' ) );
				?><a class="shipping-provider-link<?php echo ( $current == $provider->get_slug() ) ? ' it-exchange-current' : ''; ?>" href="<?php echo $url; ?>"><?php esc_html_e( $provider->get_label() ); ?></a><?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * This returns the country-state-js options for general settings
	 *
	 * @since CHANGEME
	 *
	 * @return array
	*/
	function get_general_settings_country_states_js() {
		$country_state_option = array(
			'country-id'        => 'product-ships-from-country',
			'states-id'         => '#product-ships-from-state',
			'states-wrapper'    => '#product-ships-from-state-wrapper',
		);
		return $country_state_option;
	}

	/**
	 * This returns the settings fields array for general shipping settings
	 *
	 * @since CHANGEME
	 *
	 * @return array
	*/
	function get_general_settings_fields() {
		$form_fields = array(
			array(
				'type'  => 'heading',
				'label' => __( 'General Shipping Settings', 'LION' ),
				'slug'  => 'general-shipping-label',
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Products Ship From', 'LION' ),
				'slug'    => 'product-ships-from-address1',
				'tooltip' => __( 'The default from address used when shipping your products.', 'LION' ),
				'default' => '',
				'options' => array(
					'class'       => 'large-text',
					'placeholder' => __( 'Address 1', 'LION' ),
				),
			),
			array(
				'type'    => 'text_box',
				'label'   => '',
				'slug'    => 'product-ships-from-address2',
				'default' => '',
				'options' => array(
					'class'       => 'large-text',
					'placeholder' => __( 'Address 2', 'LION' ),
				),
			),
			array(
				'type'    => 'text_box',
				'label'   => '',
				'slug'    => 'product-ships-from-city',
				'default' => '',
				'options' => array(
					'class'       => 'large-text',
					'placeholder' => __( 'City', 'LION' ),
				),
			),
			array(
				'type'    => 'drop_down',
				'label'   => '',
				'slug'    => 'product-ships-from-country',
				'default' => 'US',
				'options' => it_exchange_get_data_set( 'countries' ),
			),
			array(
				'type'    => 'drop_down',
				'label'   => '',
				'slug'    => 'product-ships-from-state',
				'default' => 'NC',
				'options' => it_exchange_get_data_set( 'states', array( 'country' => 'US' ) ),
			),
			array(
				'type'    => 'text_box',
				'label'   => '',
				'slug'    => 'product-ships-from-zip',
				'default' => '',
				'options' => array(
					'class'       => 'normal-text',
					'placeholder' => __( 'Zip', 'LION' ),
				),
			),
			array(
				'type'    => 'yes_no_drop_down',
				'label'   => __( 'Can products override default ships from address?', 'LION' ),
				'slug'    => 'products-can-override-ships-from',
				'tooltip' => __( 'Selecting "yes" will place these fields on the Add/Edit product screen.', 'LION' ),
				'default' => '1',
			),
			array(
				'type'    => 'yes_no_drop_down',
				'label'   => __( 'Can products override available shipping methods?', 'LION' ),
				'slug'    => 'products-can-override-available-shipping-methods',
				'tooltip' => __( 'Selecting "yes" will place options the Add/Edit product screen.', 'LION' ),
				'default' => '0',
			),
			array(
				'type'    => 'drop_down',
				'label'   => __( 'Measurements Format', 'LION' ),
				'slug'    => 'measurements-format',
				'tooltip' => __( 'Use standard for lbs and inches. Use metric for kg and cm.', 'LION' ),
				'default' => 'standard',
				'options' => array(
					'standard' => __( 'Standard', 'LION' ),
					'metric'   => __( 'Metric', 'LION' ),
				),
			),
		);
		return $form_fields;
	}

	/**
	 * Add Shipping to the content-cart totals and content-checkout loop
	 *
	 * @since CHANGEMEEME
	 *
	 * @param array $elements list of existing elements
	 * @return array
	*/
	function add_shipping_to_template_totals_loops( $elements ) { 
		$shipping_options = it_exchange_get_option( 'addon_shipping_settings' );

		// Locate the discounts key in elements array (if it exists)
		$index = array_search( 'totals-savings', $elements );
		if ( false === $index )
			$index = -1; 

		array_splice( $elements, $index, 0, 'totals-shipping' );
		return $elements;
	}

	/**
	 * Add Shipping to the super-widget-checkout totals loop
	 *
	 * @since CHANGEME
	 *
	 * @param array $loops list of existing elements
	 * @return array
	*/
	function add_shipping_to_sw_template_totals_loops( $loops ) { 
		$shipping_options      = it_exchange_get_option( 'addon_shipping_settings' );

		// Shipping Address 
		array_splice( $loops, -1, 0, 'shipping-address' );

		// Locate the discounts key in elements array (if it exists)
		$index = array_search( 'discounts', $loops );
		if ( false === $index )
			$index = -1; 

		// Shipping Costs
		array_splice( $loops, $index, 0, 'shipping-cost' );
		return $loops;
	}

	/**
	 * Process Adding the shipping address to the SW via ajax
	 *
	 * Processes the POST request. If data is good, it updates the DB (where we store the data)
	 * permanantly as well as the session where we store it for the template part.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function process_ajax_request() {
		// Parse data
		$name     = empty( $_POST['shippingName'] ) ? false : $_POST['shippingName'];
		$address1 = empty( $_POST['shippingAddress1'] ) ? false : $_POST['shippingAddress1'];
		$address2 = empty( $_POST['shippingAddress2'] ) ? false : $_POST['shippingAddress2'];
		$city     = empty( $_POST['shippingCity'] ) ? false : $_POST['shippingCity'];
		$state    = empty( $_POST['shippingState'] ) ? false : $_POST['shippingState'];
		$zip      = empty( $_POST['shippingZip'] ) ? false : $_POST['shippingZip'];
		$country  = empty( $_POST['shippingCountry'] ) ? false : $_POST['shippingCountry'];
		$customer = empty( $_POST['shippingCustomer'] ) ? false : $_POST['shippingCustomer'];
		$invalid  = ( ! $name || ! $address1 || ! $city || ! $state || ! $zip || ! $country || ! $customer );

		// Update object with what we have
		$address = compact( 'name', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'customer' );
		it_exchange_update_cart_data( 'shipping-address', $address );
		unset( $address['customer'] );

		// Register fail or success
		if ( $invalid ) {
			it_exchange_add_message( 'error', __( 'Please fill out all required fields' ) );
			die('0');
		} else {
			it_exchange_save_shipping_address( $address, $customer );
			die('1');
		}
	}

	/**
	 * Process Adding the shipping address to the checkout page via POST request
	 *
	 * Processes the POST request. If data is good, it updates the DB (where we store the data)
	 * permanantly as well as the session where we store it for the template part.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function process_update_address_request() {

		// Abandon if not processing
		if ( ! it_exchange_is_page( 'checkout' ) || empty( $_POST['it-exchange-shipping-add-address-from-checkout'] ) )
			return;

		// Parse data
		$name     = empty( $_POST['it-exchange-addon-shipping-name'] ) ? false : $_POST['it-exchange-addon-shipping-name'];
		$address1 = empty( $_POST['it-exchange-addon-shipping-address-1'] ) ? false : $_POST['it-exchange-addon-shipping-address-1'];
		$address2 = empty( $_POST['it-exchange-addon-shipping-address-2'] ) ? false : $_POST['it-exchange-addon-shipping-address-2'];
		$city     = empty( $_POST['it-exchange-addon-shipping-city'] ) ? false : $_POST['it-exchange-addon-shipping-city'];
		$state    = empty( $_POST['it-exchange-addon-shipping-state'] ) ? false : $_POST['it-exchange-addon-shipping-state'];
		$zip      = empty( $_POST['it-exchange-addon-shipping-zip'] ) ? false : $_POST['it-exchange-addon-shipping-zip'];
		$country  = empty( $_POST['it-exchange-addon-shipping-country'] ) ? false : $_POST['it-exchange-addon-shipping-country'];
		$invalid  = ( ! $name || ! $address1 || ! $city || ! $state || ! $zip || ! $country );

		// Update object with what we have
		$address = compact( 'name', 'address1', 'address2', 'city', 'state', 'zip', 'country' );
		it_exchange_update_cart_data( 'shipping-address', $address );

		// Register fail or success
		if ( $invalid ) {
			it_exchange_add_message( 'error', __( 'Please fill out all required fields' ) );
		} else {
			it_exchange_save_shipping_address( $address );
			it_exchange_add_message( 'notice', __( 'Shipping Address Updated' ) );
		}
	}

	/**
	 * Clears the shipping address value when the cart is emptied
	 *
	 * @since 1.1.0
	 *
	 * @return void
	*/
	function clear_cart_address() {
		it_exchange_remove_cart_data( 'shipping-address' );
	}

	/**
	 * Adjusts the cart total
	 *
	 * @since 1.0.0
	 *
	 * @param $total the total passed to us by Exchange.
	 * @return
	*/
	function modify_shipping_total( $total ) {
		$shipping = it_exchange_get_shipping_cost_for_cart( false );
		return $total + $shipping;
	}

	/**
	 * Enqueue css for settings page
	 *
	 * @since 1.1.0
	 *
	 * @return void
	*/
	function enqueue_admin_css() {
		$current_screen = get_current_screen();
		if ( ! empty( $current_screen->base ) && 'exchange_page_it-exchange-addons' == $current_screen->base && ! empty( $_GET['add-on-settings'] ) && 'shipping' == $_GET['add-on-settings'] )
			wp_enqueue_style( 'it-exchange-addon-shipping-settings', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/css/settings.css' );
	}

	/**
	 * Enqueue SW Javascript
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function enqueue_sw_js() {
		wp_enqueue_script( 'it-exchange-addon-shipping-sw-js', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) ) . '/js/super-widget.js' );
	}

	/**
	 * This function hooks into the AJAX call generated in general settings for country/states sync
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function update_general_settings_state_field() {
		$base_country = empty( $_POST['ite_base_country_ajax'] ) ? false : $_POST['ite_base_country_ajax'];
		$base_state   = empty( $_POST['ite_base_state_ajax'] ) ? '' : $_POST['ite_base_state_ajax'];
		$states       = it_exchange_get_data_set( 'states', array( 'country' => $base_country ) );

		if ( empty( $states ) ) {
			?>
			<input type="text" id="product-ships-from-state" name="addon-shipping-general-product-ships-from-state" maxlength="3" placeholder="<?php _e( 'State', 'LION' ); ?>" class="small-text" value="<?php esc_attr_e( $base_state ); ?>" />&nbsp;
			<?php $open_tag = '<a href="http://en.wikipedia.org/wiki/ISO_3166-2" target="_blank">'; ?>
			<span class="description"><?php printf( __( 'Please use the 2-3 character %sISO 3166-2 Country Subdivision Code%s', 'LION' ), $open_tag, '</a>' ); ?></span>
			<?php
		} else {
			?>
			<select id="product-ships-from-state" name="addon-shipping-general-product-ships-from-state">
			<?php
			foreach( (array) $states as $key => $value ) {
				?><option value="<?php esc_attr_e( $key ); ?>" <?php selected( $key, $base_state ); ?>><?php esc_html_e( $value ); ?></option><?php
			}
			?></select><?php
		}
		die();
	}

	/**
	 * Enqueues frontend javascript needed on checkout page
	 *
	 *
	 * @since 1.2.0
	 *
	 * @return void
	*/
	function it_exchange_addon_shipping_frontend_js() {
		// Load Registration purchase requirement JS if not logged in and on checkout page.
		if ( it_exchange_is_page( 'checkout' ) && ! is_user_logged_in() )
			wp_enqueue_script( 'it-exchange-shipping-purchase-requirement', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/js/checkout.js' ), array( 'jquery' ), false, true );
	}
}
$IT_Exchange_Shipping = new IT_Exchange_Shipping();

