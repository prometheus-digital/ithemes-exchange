<?php
/**
 * This is the class for our available shipping methods shipping feature 
 *
 * @since CHANGEME
*/
class IT_Exchange_Core_Shipping_Feature_Available_Shipping_Methods extends IT_Exchange_Shipping_Feature {
	
	var $slug = 'core-available-shipping-methods';

	/**
	 * Constructor
	*/
	function __construct( $product=false, $options=array() ) {
		parent::__construct( $product, $options );
	}

	/**
	 * Is this shipping feature available
	 *
	 * @since CHANGEME
	*/
	function set_availability() {
		$options = it_exchange_get_option( 'addon-shipping-general' );
		$this->available = ! empty( $options['products-can-override-available-shipping-methods'] );
	}

	function set_enabled() {
		$this->enabled = true;
	}

	function update_on_product_save() {
	}

	function update_value( $new_value ) {

	}

	/**
	 * Sets the values
	*/
	function set_values() {
		$values = new stdClass();
		$values->override_defaults = $this->enabled;
		$values->available_methods = array( 'exchange-flat-rate-shipping', 'ups-air', 'ups-ground' );
		$this->values = $values;
	}

    /** 
     * Prints the shipping box on the add/edit product page
     *
     * Relies on methods provided by extending classes
     * If the shipping feature isn't availabe to this product, it is hidden
     *
     * @since CHANGEME
     *
     * @return void
    */
    function print_add_edit_feature_box() {
        ?>  
        <div class="shipping-feature <?php esc_attr_e( $this->slug ); ?>">
            <?php $this->print_add_edit_feature_box_interior(); ?>
        </div>
        <?php
    }

	/**
	 * Prints the interior of the feature box in the add/edit product view
	*/
	function print_add_edit_feature_box_interior() {
		?>
		<ul>
			<li>
				<?php if ( $this->available ) : ?>
					<label id="it-exchange-shipping-override-methods-label" for="it-exchange-shipping-override-methods">
						<input type="checkbox" id="it-exchange-shipping-override-methods" name="it-exchange-shipping-override-methods" <?php checked( ! empty( $this->enabled ) ); ?> /> <?php _e( 'Override for this product?', 'LION' ); ?>
					</label>
				<?php endif; ?>
				<span id="it-exchange-avialable-shipping-methods-heading"><?php _e( 'Available Shipping Methods', 'LION' ); ?></span>
				<ul class="core-shipping-overridable-methods <?php echo ( empty( $this->available ) || empty( $this->enabled ) ) ? 'hidden' : ''; ?>">
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
