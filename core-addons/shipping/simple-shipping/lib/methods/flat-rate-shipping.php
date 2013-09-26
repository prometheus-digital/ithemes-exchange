<?php
/**
 * Registers the Shipping Methods we need for Exchange Simple Shipping add-on
 *
 * @since CHANGEME
 *
 * @return void
*/
function it_exchange_addon_simple_shipping_register_flat_rate_shipping_method() {
	// Exchange Flat Rate Shipping Method
	it_exchange_register_shipping_method( 'exchange-flat-rate-shipping', 'IT_Exchange_Simple_Shipping_Flat_Rate_Method' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_addon_simple_shipping_register_flat_rate_shipping_method' );

/**
 * Register exchange flat rate cost shipping feature
 *
*/
function it_exchange_addon_simple_shipping_register_flat_rate_shipping_features() {
	it_exchange_register_shipping_feature( 'exchange-flat-rate-shipping-cost', 'IT_Exchange_Simple_Shipping_Flat_Rate_Shipping_Cost' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_addon_simple_shipping_register_flat_rate_shipping_features' );

class IT_Exchange_Simple_Shipping_Flat_Rate_Method extends IT_Exchange_Shipping_Method {

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
		$this->slug = 'exchange-flat-rate-shipping';
	}

	/**
	 * Sets the label for this shipping method
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function set_label() {
		$this->label = __( 'Exchange Flat Rate Shipping', 'LION' );
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
			'exchange-flat-rate-shipping-cost',
			'core-from-address',
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
		$this->enabled = ! empty( $options['enable-flat-rate-shipping'] );
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
				'label' => __( 'Flat Rate Shipping', 'LION' ),
				'slug'  => 'flat-rate-shipping-heading',
			),
			array(
				'type'    => 'yes_no_drop_down',
				'label'   => __( 'Enable Flat Rate Shipping?', 'LION' ),
				'slug'    => 'enable-flat-rate-shipping',
				'tooltip' => __( 'Do you want flat rate shipping available to your customers as a shipping option?', 'LION' ),
				'default' => 1,
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Shipping Label', 'LION' ),
				'slug'    => 'flat-rate-shipping-label',
				'tooltip' => __( 'This changes the title of this Shipping Method for your customers', 'LION' ),
				'default' => __( 'Standard Shipping (3-5 days)', 'LION' ),
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Default Shipping Amount', 'LION' ),
				'slug'    => 'flat-rate-shipping-amount',
				'tooltip' => __( 'The default shipping amount for new products. This can be overridden by individual products.', 'LION' ),
				'default' => 5,
			),
		);

		foreach ( $settings as $setting ) {
			$this->add_setting( $setting );
		}
	}
}

/**
 * This is the class for our exchange flat rate shipping feature
 *
 * @since CHANGEME
*/
class IT_Exchange_Simple_Shipping_Flat_Rate_Shipping_Cost extends IT_Exchange_Shipping_Feature {
	
	var $slug = 'exchange-flat-rate-shipping-cost';

	/**
	 * Constructor
	*/
	function __construct( $product_id=false ) {
		parent::__construct( $product_id );
	}

	/**
	 * Sets the availability
	*/
	function set_availability() {
		$this->available = true;
	}

	function set_enabled() {
		$this->enabled = true;
	}

	/**
	 * Sets the values
	*/
	function set_values() {

		// Init values object as standard class
		$values = new stdClass();

		// Grab default value
		$defaults     = it_exchange_get_option( 'simple-shipping' );
		$default_cost = $defaults['flat-rate-shipping-amount'];

		// Post meta
		$post_amount  = get_post_meta( $this->product->ID, '_it_exchange_shipping_flat-rate-shipping-default-amount', true );

		// Set value
		$values->cost = empty( $post_amount ) ? $default_cost : $post_amount;
		$this->values = $values;
	}

	/**
	 * Save the values
	 *
	 * Saves the values when the add/edit product screen is saved
	*/
	function update_on_product_save() {
		if ( ! empty( $_POST['it-exchange-flat-rate-shipping-cost'] ) )
			$this->update_value( $_POST['it-exchange-flat-rate-shipping-cost'] );
	}

	/**
	 * Updates the value to the passed paramater
	 *
	*/
	function update_value( $new_value ) {
		update_post_meta( $this->product->ID, '_it_exchange_shipping_flat-rate-shipping-default-amount', $new_value );
	}

	/**
	 * Prints the interior of the feature box in the add/edit product view
	*/
	function print_add_edit_feature_box_interior() {
		?>
		<div class="it-exchange-flat-rate-shipping-cost">
			<label for="it-exchange-flat-rate-shipping-cost"><?php _e( 'Flat Rate Shipping Cost', 'LION' ); ?> <span class="tip" title="<?php _e( 'Shipping costs for this product. Multiplied by quantity purchased.', 'LION' ); ?>">i</span></label>
			<input type="text" id="it-exchange-flat-rate-shipping-cost" name="it-exchange-flat-rate-shipping-cost" class="input-money-small" value="<?php esc_attr_e( $this->values->cost ); ?>"/>
		</div>
		<?php
	}
}
