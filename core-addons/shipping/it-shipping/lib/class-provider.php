<?php
/**
 * This class setsup a provider
*/
class IT_Exchange_Shipping_Provider{
	
	var $slug;
	var $label;
	var $shipping_methods;
	var $provider_settings;
	var $has_settings_page = false;

	function IT_Exchange_Shipping_Provider( $slug, $options=array() ) {

		$defaults = array(
			'label' => false,
			'shipping-methods' => array(),
		);

		// Merge options with defaults
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Set slug and label properties
		$this->slug  = $slug;
		$this->label = $options['label'];

		// Setup Shipping Methods if needed
		if ( ! empty( $options['shipping-methods'] ) )
			$this->setup_shipping_methods( $options['shipping-methods'] );

		// Setup settings page if needed
		if ( ! empty( $options['provider-settings'] ) )
			$this->setup_provider_settings( $options['provider-settings'] );

		// Default values for settings
		add_filter( 'it_storage_get_defaults_exchange_addon_shipping_' . $this->slug, array( $this, 'get_default_provider_setting_values' ) );
	}

	function get_slug() {
		return $this->slug;
	}

	function get_label() {
		return $this->label;
	}

	/**
	 * Applies an array of shipping methods to the object provider property
	 *
	 * @since CHANGEME
	 *
	 * @param  array $methods the methods array
	 * @return void
	*/
	function setup_shipping_methods( $methods=array() ) {
		if ( ! empty( $methods ) ) {
			foreach( $methods as $slug => $options ) {
				$this->add_shipping_method( $slug, $options );
			}
		}
	}

	/**
	 * Applies an array of provider settings to the object provider property
	 *
	 * @since CHANGEME
	 *
	 * @param  array $settings the settings array
	 * @return void
	*/
	function setup_provider_settings( $settings=array() ) {
		foreach( (array) $settings as $options ) {
			$this->add_provider_setting( $options );
		}
	}

	/**
	 * Adds a shipping method to the provider as an object property
	 *
	 * @since CHANGEME
	 *
	 * @param  string $slug    the slug for the shipping method
	 * @param  array  $options options for the shipping method
	 * @return void
	*/
	function add_shipping_method( $slug, $options=array() ) {
		$this->shipping_methods[$slug] = $options;
	}

	/**
	 * Adds a provider settings to the array of existing provider settings
	 *
	 * @since CHANGEME
	 *
	 * @param  array  $options options for the provider setting 
	 * @return void
	*/
	function add_provider_setting( $options=array() ) {
		if ( empty( $options['type'] ) || empty( $options['slug'] ) )
			return false;

		$options['options'] = empty( $options['options'] ) ? array() : $options['options'];
		$this->provider_settings[$options['slug']] = $options;
		$this->has_settings_page  = true;
	}

	/**
	 * Returns all of the shipping methods for this provider
	 *
	 * @since CHANGEME
	 *
	 * @return array
	*/
	function get_shipping_methods() {
		return (array) $this->shipping_methods;
	}

	/**
	 * Returns all of the settings for this provider
	 *
	 * @since CHANGEME
	 *
	 * @return array
	*/
	function get_provider_settings() {
		return (array) $this->provider_settings;
	}

	/**
	 * Returns the values for the provider settings 
	 *
	 * @since CHANGEME
	 *
	 * @return array
	*/
	function get_setting_values() {
		$options = it_exchange_get_option( 'addon_shipping_' . $this->slug );
		return $options;
	}

	/**
	 * Returns the default values for the provider settings 
	 *
	 * @since CHANGEME
	 *
	 * @return array
	*/
    function get_default_provider_setting_values( $defaults ) { 
        foreach ( (array) $this->provider_settings as $setting ) { 
            if ( 'heading' != $setting['type'] ) { 
                $defaults[$setting['slug']] = empty( $setting['default'] ) ? false : $setting['default'];
            }   
        }   
        return $defaults;
    }

	/**
	 * Save provider settings
	 *
	 * @since CHANGEME
	 *
	 * @param array $settings the settings that will replace current settings
	 * @return void
	*/
	function update_settings( $settings ) {
		it_exchange_save_option( 'addon_shipping_' . $this->slug, $settings );
	}
}
