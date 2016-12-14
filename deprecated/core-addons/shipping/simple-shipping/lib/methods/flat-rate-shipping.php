<?php
/**
 * Registers the Shipping Methods we need for Exchange Simple Shipping add-on
 *
 * @since 1.4.0
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

/**
 * Default Shipping Cost coming out of DB should always be formated for databse
 *
 * @since 1.14.0 
 *
 * @param array $data the data retrieved from the DB
 * @return array
 */
function it_exchange_addon_simple_shipping_clean_shipping_cost_coming_out_of_db( $data ) {
	if ( ! empty( $data['flat-rate-shipping-amount'] ) && ! is_numeric( $data['flat-rate-shipping-amount'] ) ) {
		$data['flat-rate-shipping-amount'] = it_exchange_convert_to_database_number( $data['flat-rate-shipping-amount'] );
	} else if ( ! empty( $data['flat-rate-shipping-amount'] ) && is_numeric( $data['flat-rate-shipping-amount'] ) ) {
		$data['flat-rate-shipping-amount'] = str_replace( '.', '', $data['flat-rate-shipping-amount'] );
	}
	return $data;
}
add_filter( 'it_exchange_get_option-simple-shipping', 'it_exchange_addon_simple_shipping_clean_shipping_cost_coming_out_of_db' );

class IT_Exchange_Simple_Shipping_Flat_Rate_Method extends IT_Exchange_Shipping_Method {

	/**
	 * Class constructor. Needed to call parent constructor
	 *
	 * @since 1.4.0
	 *
	 * @param int|bool $product_id optional product id for current product
	*/
	function __construct( $product_id=false ) {
		parent::__construct( $product_id );
		add_filter( 'it_exchange_save_admin_form_settings_for_simple-shipping', array( $this, 'convert_to_database_format_on_default_settings_save' ) );
	}

	/**
	 * Sets the identifying slug for this shipping method
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_slug() {
		$this->slug = 'exchange-flat-rate-shipping';
	}

	/**
	 * Sets the label for this shipping method
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_label() {
		$settings = it_exchange_get_option( 'simple-shipping' );
		$this->label = empty( $settings['flat-rate-shipping-label'] ) ? __( 'Flat Rate Shipping', 'it-l10n-ithemes-exchange' ) : $settings['flat-rate-shipping-label'];
	}

	/**
	 * Sets the Shipping Features that this method uses.
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_features() {
		$this->shipping_features = array(
			'exchange-flat-rate-shipping-cost',
		);
	}

	/**
	 * Determines if this shipping method is enabled and sets the property value
	 *
	 * @since 1.4.0
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
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_availability() {
		$this->available = $this->enabled;
	}

	/**
	 * Define any setting fields that you want this method to include on the Provider settings page
	 *
	 * @since 1.4.0
	 *
	 * @return void
	*/
	function set_settings() {
		$general_settings = it_exchange_get_option( 'settings_general' );
		$currency         = it_exchange_get_currency_symbol( $general_settings['default-currency'] );

		$settings = array(
			array(
				'type'  => 'heading',
				'label' => __( 'Flat Rate Shipping', 'it-l10n-ithemes-exchange' ),
				'slug'  => 'flat-rate-shipping-heading',
			),
			array(
				'type'    => 'yes_no_drop_down',
				'label'   => __( 'Enable Flat Rate Shipping?', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'enable-flat-rate-shipping',
				'tooltip' => __( 'Do you want flat rate shipping available to your customers as a shipping option?', 'it-l10n-ithemes-exchange' ),
				'default' => 1,
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Shipping Label', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'flat-rate-shipping-label',
				'tooltip' => __( 'This changes the title of this Shipping Method for your customers', 'it-l10n-ithemes-exchange' ),
				'default' => __( 'Standard Shipping (3-5 days)', 'it-l10n-ithemes-exchange' ),
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Default Shipping Amount', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'flat-rate-shipping-amount',
				'tooltip' => __( 'The default shipping amount for new products. This can be overridden by individual products.', 'it-l10n-ithemes-exchange' ),
				'default' => 500,
				'options' => array(
					'data-symbol'              => esc_attr( $currency ),
					'data-symbol-position'     => esc_attr( $general_settings['currency-symbol-position'] ),
					'data-thousands-separator' => esc_attr( $general_settings['currency-thousands-separator'] ),
					'data-decimals-separator'  => esc_attr( $general_settings['currency-decimals-separator'] ),
				),
				'print_setting_field_override' => array( $this, 'override_default_shipping_field' ),
			),
		);

		foreach ( $settings as $setting ) {
			$this->add_setting( $setting );
		}
	}

	function get_shipping_cost_for_product( $cart_product ) {
		$count = empty( $cart_product['count'] ) ? 1 : $cart_product['count'];
		$cost = it_exchange_get_shipping_feature_for_product( 'exchange-flat-rate-shipping-cost', $this->product->ID );
		$cost = empty( $cost->cost ) ? 0 : $cost->cost;
		$cost = it_exchange_convert_from_database_number( $cost );
		return $cost * $count;
	}

	function override_default_shipping_field( $form_values ) {
		$general_settings = it_exchange_get_option( 'settings_general' );
		$currency         = it_exchange_get_currency_symbol( $general_settings['default-currency'] );
		$field_value      = empty( $form_values['flat-rate-shipping-amount'] ) ? false : $form_values['flat-rate-shipping-amount'];
		?>
		<tr valign="top" id="flat-rate-shipping-amount-table-row">
			<th scope="row" id="flat-rate-shipping-amount-table-row-head">
				<label for="flat-rate-shipping-amount">
					<?php _e( 'Default Shipping Amount', 'it-l10n-ithemes-exchange' ); ?>
					<span class="tip" title="<?php esc_attr_e( 'The default shipping amount for new products. This can be overridden by individual products.', 'it-l10n-ithemes-exchange' ); ?>">i</span>
				</label>
			</th>
			<td id="flat-rate-shipping-amount-wrapper">
				<input data-symbol="<?php esc_attr_e( $currency ); ?>" data-symbol-position="<?php esc_attr_e( $general_settings['currency-symbol-position'] ); ?>" data-thousands-separator="<?php esc_attr_e( $general_settings['currency-thousands-separator'] ); ?>" data-decimals-separator="<?php esc_attr_e( $general_settings['currency-decimals-separator'] ); ?>" type="text" name="simple-shipping-flat-rate-shipping-amount" id="flat-rate-shipping-amount" value="<?php esc_attr_e( it_exchange_format_price( it_exchange_convert_from_database_number( $field_value ) ) ); ?>">
			</td>
		</tr>
		<?php
	}

	function convert_to_database_format_on_default_settings_save( $values ) {
		if ( ! empty( $values['flat-rate-shipping-amount'] ) && empty( $GLOBALS['it_exchange']['shipping']['flat-rate-shipping-amount-converted-on-save'] ) ) {
			$values['flat-rate-shipping-amount'] = it_exchange_convert_to_database_number( $values['flat-rate-shipping-amount'] );
			$GLOBALS['it_exchange']['shipping']['flat-rate-shipping-amount-converted-on-save'] = true;
		}
		return $values;
	}

}

/**
 * This is the class for our exchange flat rate shipping feature
 *
 * @since 1.4.0
*/
class IT_Exchange_Simple_Shipping_Flat_Rate_Shipping_Cost extends IT_Exchange_Shipping_Feature {

	var $slug = 'exchange-flat-rate-shipping-cost';

	/**
	 * Constructor
	 *
	 * @param int|bool $product_id
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
		if ( ! empty( $_POST['it-exchange-flat-rate-shipping-cost'] ) ) {
			$value = it_exchange_convert_to_database_number( $_POST['it-exchange-flat-rate-shipping-cost'] );
			$this->update_value( $value );
		}
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
		$settings = it_exchange_get_option( 'settings_general' );
		$currency = it_exchange_get_currency_symbol( $settings['default-currency'] );
		?>
		<div class="it-exchange-flat-rate-shipping-cost">
			<label for="it-exchange-flat-rate-shipping-cost"><?php _e( 'Flat Rate Shipping Cost', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'Shipping costs for this product. Multiplied by quantity purchased.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
			<input type="text" data-symbol="<?php esc_attr_e( $currency ); ?>" data-symbol-position="<?php esc_attr_e( $settings['currency-symbol-position'] ); ?>" data-thousands-separator="<?php esc_attr_e( $settings['currency-thousands-separator'] ); ?>" data-decimals-separator="<?php esc_attr_e( $settings['currency-decimals-separator'] ); ?>" id="it-exchange-flat-rate-shipping-cost" name="it-exchange-flat-rate-shipping-cost" class="input-money-small" value="<?php esc_attr_e( it_exchange_format_price( it_exchange_convert_from_database_number( $this->values->cost ) ) ); ?>"/>
		</div>
		<?php
	}
}
