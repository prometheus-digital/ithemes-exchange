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

		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'maybe_init' ) );
	}

	function maybe_init() {
		$enabled_shipping_addons = (boolean) it_exchange_get_enabled_addons( array( 'category' => 'shipping' ) );	
		if ( !$enabled_shipping_addons )
			return;

		add_action( 'it_exchange_print_general_settings_tab_links', array( $this, 'print_shipping_tab_link' ) );
		add_filter( 'it_exchange_general_settings_tab_callback_shipping', array( $this, 'register_settings_tab_callback' ) );
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
}
$IT_Exchange_Shipping = new IT_Exchange_Shipping();

