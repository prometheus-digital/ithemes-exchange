<?php
/**
 * This is the class for our available shipping methods shipping feature 
 *
 * @since CHANGEME
*/
class IT_Exchange_Available_Shipping_Methods_Shipping_Feature extends IT_Exchange_Shipping_Feature {
	
	var $slug = 'available-shipping-methods';

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
		$values->override_defaults = false;
		$values->available_methods = array( 'exchange-flat-rate-shipping', 'ups-air', 'ups-ground' );
		$this->values = $values;
	}

	/**
	 * Prints the interior of the feature box in the add/edit product view
	*/
	function print_add_edit_feature_box_interior() {
		?>
		<ul>
			<li>
				<label id="it-exchange-shipping-override-methods-label" for="it-exchange-shipping-override-methods">
					<input type="checkbox" id="it-exchange-shipping-override-methods" name="it-exchange-shipping-override-methods" <?php checked( ! empty( $this->values->override_defaults ) ); ?> /> <?php _e( 'Override Available Shipping Methods', 'LION' ); ?>
				</label>
				<ul class="core-shipping-overridable-methods <?php echo empty( $this->values->override_defaults ) ? 'hidden' : ''; ?>">
					<li>
						<label id="it-exchange-shipping-override-aaa-method-label" for="it-exchange-shipping-override-aaa-method">
							<input type="checkbox" id="it-exchange-shipping-override-aaa-method" name="it-exchange-shipping-override-aaa-methods" /> <?php _e( 'FedEx Ground', 'LION' ); ?>
						</label>
					</li>
					<li>
						<label id="it-exchange-shipping-override-aaa-method-label" for="it-exchange-shipping-override-aaa-method">
							<input type="checkbox" id="it-exchange-shipping-override-aaa-method" name="it-exchange-shipping-override-aaa-methods" /> <?php _e( 'FedEx Air', 'LION' ); ?>
						</label>
					</li>
					<li>
						<label id="it-exchange-shipping-override-aaa-method-label" for="it-exchange-shipping-override-aaa-method">
							<input type="checkbox" id="it-exchange-shipping-override-aaa-method" name="it-exchange-shipping-override-aaa-methods" /> <?php _e( 'UPS Air', 'LION' ); ?>
						</label>
					</li>
				</ul>
			</li>
		</ul>
		<?php
	}
}
