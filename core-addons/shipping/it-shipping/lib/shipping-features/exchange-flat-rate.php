<?php
/**
 * This is the class for our exchange flat rate shipping feature
 *
 * @since CHANGEME
*/
class IT_Exchange_Exchange_Flat_Rate_Shipping_Feature extends IT_Exchange_Shipping_Feature {
	
	var $slug = 'exchange-flat-rate-shipping';

	/**
	 * Constructor
	*/
	function __construct( $product=false, $options=array() ) {
		parent::__construct( $product, $options );
	}

	/**
	 * Sets the values
	*/
	function set_values() {
		$values = new stdClass();
		$values->cost = '$5.00';
		$this->values = $values;
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
