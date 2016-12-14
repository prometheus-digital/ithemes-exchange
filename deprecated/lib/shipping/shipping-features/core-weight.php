<?php
/**
 * This is the class for our weight shipping feature
 *
 * @since 1.4.0
*/
class IT_Exchange_Core_Shipping_Feature_Weight extends IT_Exchange_Shipping_Feature {

	var $slug = 'core-weight';

	/**
	 * Constructor
	 *
	 * @param int|bool $product
	 * @param array    $options
	*/
	function __construct( $product=false, $options=array() ) {
		parent::__construct( $product );
	}

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
		// Defaults
		$general_settings = it_exchange_get_option( 'shipping-general' );

		// Post meta
		$pm = get_post_meta( $this->product->ID, '_it_exchange_core_weight', true );

		// Set values
		$values = new stdClass();
		$values->weight             = empty( $pm['weight'] ) ? 0 : $pm['weight'];
		$values->measurement_format = empty( $general_settings['measurements-format'] ) ? 'standard' : $general_settings['measurements-format'];
		$this->values               = $values;
	}

	function update_on_product_save() {
		$data = array();

		$data['weight'] = empty( $_POST['it-exchange-shipping-weight'] ) ? '0' : $_POST['it-exchange-shipping-weight'];

		$this->update_value( $data );
	}

	function update_value( $new_value ) {
		update_post_meta( $this->product->ID, '_it_exchange_core_weight', $new_value );
	}

	/**
	 * Prints the feature box on the add/edit product page.
	 *
	 * This feature overloads the default one in the parent class
	 *
	 * @since  1.4.0
	 * @return void
	*/
	function print_add_edit_feature_box() {
		?>
		<div class="shipping-feature <?php esc_attr_e( $this->slug ); ?> columns-wrapper">
			<?php $this->print_add_edit_feature_box_interior(); ?>
		</div>
		<?php
	}

	/**
	 * Prints the interior of the feature box in the add/edit product view
	*/
	function print_add_edit_feature_box_interior() {
		?>
		<div class="shipping-weight column">
			<label><?php _e( 'Weight', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'Weight of the package. Used to calculate shipping costs.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
			<input type="text" id="it-exchange-shipping-weight" name="it-exchange-shipping-weight" class="small-input" value="<?php esc_attr_e( $this->values->weight ); ?>"/>
			<span class="it-exchange-shipping-weight-format"><?php echo ( 'standard' == $this->values->measurement_format ) ? __( 'lbs', 'it-l10n-ithemes-exchange' ) : __( 'kgs', 'it-l10n-ithemes-exchange' ); ?></span>
		</div>
		<?php
	}
}
