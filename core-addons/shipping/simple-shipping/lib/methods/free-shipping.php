<?php
/**
delete_option( 'it-storage-exchange_addon-shipping');
delete_option( 'it-storage-exchange_addon-shipping-exchange');
delete_option( 'it-storage-exchange_addon-shipping-exchange-shipping');
delete_option( 'it-storage-exchange_addon-shipping-exchange-standard');
delete_option( 'it-storage-exchange_addon-shipping-general');
delete_option( 'it-storage-exchange_addon-shipping-standard');
delete_option( 'it-storage-exchange_addon_shipping' );
delete_option( 'it-storage-exchange_addon_shipping_exchange-standard' );
delete_option( 'it-storage-exchange_addon_shipping_general' );
delete_option( 'it-storage-exchange_addon_shipping_settings' );
delete_option( 'it-storage-exchange_exchange_addon_shipping_exchange-standard');
delete_option( 'it-storage-exchange_it-exchange-addon-shipping-general' );
delete_option( 'it-storage-exchange_it-exchange-it-exchange-addon-shipping-gener' );
delete_option( 'it-storage-exchange_it-exchange-it-exchangie-addon-shipping-gene' );
delete_option( 'it-storage-exchange_shipping-general' );
delete_option( 'it-storage-exchange_simple-shipping' );
delete_option( 'it-storage-exchange_addon-shipping-simple-enable-flat-rate-shipp' );
delete_option( 'it-storage-exchange_addon-shipping-simple-shipping-enable-flat-r' );
delete_option( 'it-storage-exchange_addon-simple-shipping' );
delete_option( 'it-storage-exchange_addon-simple-shipping-enable-flat-rate-shipp');
delete_option( 'it-storage-exchange_simple-shipping-enable-flat-rate-shipping' );
delete_option( 'it-storage-exchange_simple-shipping-simple-shipping-enable-flat-' );
delete_option( 'it-storage-exchange_simple-shiping-simple-shipping-enable-flat-r');
*/

/**
 * Registers the Shipping Methods we need for Exchange Simple Shipping add-on
 *
 * @since CHANGEME
 *
 * @return void
*/
function it_exchange_addon_simple_shipping_register_free_shipping_method() {
	// Exchange Free Shipping Method
	it_exchange_register_shipping_method( 'exchange-free-shipping', 'IT_Exchange_Simple_Shipping_Free_Method' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_addon_simple_shipping_register_free_shipping_method' );

class IT_Exchange_Simple_Shipping_Free_Method extends IT_Exchange_Shipping_Method {

	/**
	 * Class constructor. Needed to call parent constructor
	 *
	 * @since CHANGEME
	 *
	 * @param integer $product_id optional product id for current product
	 * @return void
	*/
	function __construct( $product_id=false ) {
		parent::__construct( $product_id );
	}

	/**
	 * Sets the identifying slug for this shipping method
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function set_slug() {
		$this->slug = 'exchange-free-shipping';
	}

	/**
	 * Sets the label for this shipping method
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function set_label() {
		$this->label = __( 'Exchange Free Shipping', 'LION' );
	}

	/**
	 * Sets the Shipping Features that this method uses.
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function set_features() {
		$this->shipping_features = array(
			'core-from-address',
			'core-weight-dimensions',
		);
	}

	/**
	 * Determines if this shipping method is enabled and sets the property value
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function set_enabled() {
		$break_cache   = is_admin() && ! empty( $_POST );
		$options       = it_exchange_get_option( 'simple-shipping', $break_cache );
		$this->enabled = ! empty( $options['enable-free-shipping'] );
	}

	/**
	 * Determines if this shipping method is available to the product and sets the property value
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function set_availability() {
		$this->available = $this->enabled;
	}

	/**
	 * Define any setting fields that you want this method to include on the Provider settings page
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function set_settings() {
		$settings = array(
			array(
				'type'  => 'heading',
				'label' => __( 'Free Shipping', 'LION' ),
				'slug'  => 'free-shipping-heading',
			),
			array(
				'type'    => 'yes_no_drop_down',
				'label'   => __( 'Enable Free Shipping?', 'LION' ),
				'slug'    => 'enable-free-shipping',
				'tooltip' => __( 'Do you want free shipping available to your customers as a shipping option?', 'LION' ),
				'default' => 1,
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Shipping Label', 'LION' ),
				'slug'    => 'free-shipping-label',
				'tooltip' => __( 'This changes the title of this Shipping Method for your customers', 'LION' ),
				'default' => __( 'Free Shipping (3-5 days)', 'LION' ),
			),
		);

		foreach ( $settings as $setting ) {
			$this->add_setting( $setting );
		}
	}
}
