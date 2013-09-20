<?php
/**
 * This is the class for our weight and dimensions shipping feature
 *
 * @since CHANGEME
*/
class IT_Exchange_Exchange_Weight_Dimensions_Shipping_Feature extends IT_Exchange_Shipping_Feature {
	
	var $slug = 'exchange-weight-dimensions';

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
		$values->weight             = '12';
		$values->length             = '48';
		$values->width              = '18';
		$values->height             = '4';
		$values->measurement_format = 'standard';
		$this->values               = $values;
	}

	/**
	 * Prints the feature box on the add/edit product page.
	 *
	 * This feature overloads the default one in the parent class
	 *
	 * @since  CHANGEME
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
			<label><?php _e( 'Weight', 'LION' ); ?> <span class="tip" title="<?php _e( 'Weight of the package. Used to calculate shipping costs.', 'LION' ); ?>">i</span></label>
			<input type="text" id="it-exchange-shipping-weight" name="it-exchange-weight" class="small-input" value="<?php esc_attr_e( $this->values->weight ); ?>"/>
			<span class="it-exchange-shipping-weight-format"><?php echo ( 'standard' == $this->values->measurement_format ) ? __( 'lbs', 'LION' ) : __( 'kgs', 'LION' ); ?></span>
		</div>
		<div class="shipping-dimensions column">
			<label><?php _e( 'Dimensions', 'LION' ); ?> <span class="tip" title="<?php _e( 'Size of the package: length, width and height of the package. Used to calculate shipping costs.', 'LION' ); ?>">i</span></label>
			<input type="text" id="it-exchange-shipping-length" name="it-exchange-shipping-length" class="small-input" value="<?php esc_attr_e( $this->values->length ); ?>"/>
			<span class="it-exchange-shipping-dimensions-times">&times;</span>
			<input type="text" id="it-exchange-shipping-width" name="it-exchange-shipping-width" class="small-input" value="<?php esc_attr_e( $this->values->width ); ?>"/>
			<span class="it-exchange-shipping-dimensions-times">&times;</span>
			<input type="text" id="it-exchange-shipping-height" name="it-exchange-shipping-height" class="small-input" value="<?php esc_attr_e( $this->values->height); ?>"/>
			<span class="it-exchange-shipping-dimensions-format"><?php echo ( 'standard' == $this->values->measurement_format ) ? __( 'inches', 'LION' ) : __( 'cm', 'LION' ); ?></span>
		</div>
		<?php
	}
}
