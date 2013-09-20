<?php
/**
 * This is the class for our From Address shipping feature
 *
 * @since CHANGEME
*/
class IT_Exchange_Exchange_From_Address_Shipping_Feature extends IT_Exchange_Shipping_Feature {
	
	var $slug = 'core-from-address';

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
		$values->override_defaults = true;
		$values->address1          = '123 Main Street';
		$values->address2          = 'Suite 100';
		$values->city              = 'Oklahoma City';
		$values->state             = 'OK';
		$values->country           = 'US';
		$values->zip               = '12345';
		$this->values              = $values;
	}

	/**
	 * Prints the interior of the feature box in the add/edit product view
	*/
	function print_add_edit_feature_box_interior() {
		?>
		<div class="core-shipping-feature-from-address">
        <ul>
            <li>
                <label id="core-shipping-feature-override-from-address-label" for="core-shipping-feature-override-from-address">
                    <input type="checkbox" id="core-shipping-feature-override-from-address" name="core-shipping-feature-override-from-address" <?php checked( ! empty( $this->values->override_defaults ) ); ?> /> <?php _e( 'Override Default From Address', 'LION' ); ?>
                </label>
			</li>
			<ul class="core-shipping-feature-from-address-ul <?php echo empty( $this->values->override_defaults ) ? 'hidden' : ''; ?>">
				<li><input type="text" name="core-shipping-feature-from-address-address1" value="<?php esc_attr_e( $this->values->address1 ); ?>" placeholder="<?php esc_attr_e( __( 'Address 1', 'LION' ) ); ?>" /></li>
				<li><input type="text" name="core-shipping-feature-from-address-address2" value="<?php esc_attr_e( $this->values->address2 ); ?>" placeholder="<?php esc_attr_e( __( 'Address 2', 'LION' ) ); ?>" /></li>
				<li><input type="text" name="core-shipping-feature-from-address-city" value="<?php esc_attr_e( $this->values->city ); ?>" placeholder="<?php esc_attr_e( __( 'City', 'LION' ) ); ?>" /></li>
				<li>
					<select name="core-shipping-feature-from-address-country">
						<?php foreach( it_exchange_get_data_set( 'countries' ) as $value => $country ) : ?>
							<option value="<?php esc_attr_e( $value ); ?>" <?php selected( $value, $this->values->country ); ?>><?php echo $country; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<?php
					$states = it_exchange_get_data_set( 'states', array( 'country' => $this->values->country ) );
					if ( ! empty ( $states ) ) :
					?>
						<select name="core-shipping-feature-from-address-state">
							<?php foreach( $states as $value => $state ) : ?>
								<option value="<?php esc_attr_e( $value ); ?>" <?php selected( $value, $this->values->state ); ?>><?php echo $state; ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="text" name="core-shipping-feature-from-address-state" value="<?php esc_attr_e( $this->values->state ); ?>" placeholder="<?php esc_attr_e( __( 'State', 'LION' ) ); ?>" />
					<?php endif; ?>
				</li>
				<li><input type="text" name="core-shipping-feature-from-address-zip" value="<?php esc_attr_e( $this->values->zip ); ?>" placeholder="<?php esc_attr_e( __( 'Zip', 'LION' ) ); ?>" /></li>
			</ul>
		</div>
		<?php
	}
}
