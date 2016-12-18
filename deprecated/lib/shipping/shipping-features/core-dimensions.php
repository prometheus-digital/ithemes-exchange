<?php
/**
 * This is the class for our dimensions shipping feature
 *
 * @since 1.4.0
*/
class IT_Exchange_Core_Shipping_Feature_Dimensions extends IT_Exchange_Shipping_Feature {

	var $slug = 'core-dimensions';

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
		$pm = get_post_meta( $this->product->ID, '_it_exchange_core_dimensions', true );

		// Set values
		$values = new stdClass();
		$values->length             = empty( $pm['length'] ) ? 0 : $pm['length'];
		$values->width              = empty( $pm['width'] )  ? 0 : $pm['width'];
		$values->height             = empty( $pm['height'] ) ? 0 : $pm['height'];
		$values->measurement_format = empty( $general_settings['measurements-format'] ) ? 'standard' : $general_settings['measurements-format'];
		$this->values               = $values;
	}

	function update_on_product_save() {
		$data = array();

		$data['length'] = empty( $_POST['it-exchange-shipping-length'] ) ? '0' : $_POST['it-exchange-shipping-length'];
		$data['height'] = empty( $_POST['it-exchange-shipping-height'] ) ? '0' : $_POST['it-exchange-shipping-height'];
		$data['width']  = empty( $_POST['it-exchange-shipping-width'] ) ? '0' : $_POST['it-exchange-shipping-width'];

		$this->update_value( $data );
	}

	function update_value( $new_value ) {
		update_post_meta( $this->product->ID, '_it_exchange_core_dimensions', $new_value );
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
		<div class="shipping-dimensions column">
			<label><?php _e( 'Dimensions', 'it-l10n-ithemes-exchange' ); ?> <span class="tip" title="<?php _e( 'Size of the package: length, width and height of the package. Used to calculate shipping costs.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
			<input type="text" id="it-exchange-shipping-length" name="it-exchange-shipping-length" class="small-input" value="<?php esc_attr_e( $this->values->length ); ?>"/>
			<span class="it-exchange-shipping-dimensions-times">&times;</span>
			<input type="text" id="it-exchange-shipping-width" name="it-exchange-shipping-width" class="small-input" value="<?php esc_attr_e( $this->values->width ); ?>"/>
			<span class="it-exchange-shipping-dimensions-times">&times;</span>
			<input type="text" id="it-exchange-shipping-height" name="it-exchange-shipping-height" class="small-input" value="<?php esc_attr_e( $this->values->height); ?>"/>
			<span class="it-exchange-shipping-dimensions-format"><?php echo ( 'standard' == $this->values->measurement_format ) ? __( 'inches', 'it-l10n-ithemes-exchange' ) : __( 'cm', 'it-l10n-ithemes-exchange' ); ?></span>
		</div>
		<?php
	}
}
