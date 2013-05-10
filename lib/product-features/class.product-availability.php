<?php
/**
 * This will control email messages with any product types that register email message support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Product_Availability {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 * @return void
	 * @todo remove it_exchange_enabled_addons_loaded action???
	*/
	function IT_Exchange_Product_Feature_Product_Availability() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_availability', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_availability', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_availability', array( $this, 'product_has_feature') , 9, 3 );
		add_filter( 'it_exchange_product_supports_feature_availability', array( $this, 'product_supports_feature') , 9, 3 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'availability';
		$description = __( 'Availability to purchase the product.', 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'availability', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function init_feature_metaboxes() {
		// Abort if there are not product addon's currently enabled.
		if ( ! $product_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) )
			return;

		// Loop through product types and register a metabox if it supports the feature 
		foreach( $product_addons as $slug => $args ) {
			if ( it_exchange_product_type_supports_feature( $slug, 'availability' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $slug, array( $this, 'register_metabox' ) );
		}
	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature 
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-availability', __( 'Product Availability', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'normal' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the feature for this product
		$product_feature_value = it_exchange_get_product_feature( $product->ID, 'availability' );
		$start_enabled		   = it_exchange_get_product_feature( $product->ID, 'availability', array( 'type' => 'start', 'setting' => 'enabled' ) );
		$end_enabled           = it_exchange_get_product_feature( $product->ID, 'availability', array( 'type' => 'end', 'setting' => 'enabled' ) );
		$start_date            = empty( $product_feature_value['start'] ) ? '' : $product_feature_value['start'];
		$end_date              = empty( $product_feature_value['end'] ) ? '' : $product_feature_value['end'];

		// Set description
		$description = __( 'Use these settings to determine when a product is available to purchase.', 'LION' );
		$description = apply_filters( 'it_exchange_product_availability_metabox_description', $description );

		// Echo the form field
		echo $description;
		?>
		<p>
			<input type="checkbox" name="it-exchange-enable-product-availability-start" value="yes" <?php checked( 'yes', $start_enabled ); ?> />&nbsp;<?php _e( 'Use a start date', 'LION' ); ?>
			<input type="checkbox" name="it-exchange-enable-product-availability-end" value="yes" <?php checked( 'yes', $end_enabled ); ?> />&nbsp;<?php _e( 'Use an end start date', 'LION' ); ?>
		</p>
		<p>
			<input type="text" name="it-exchange-product-availability-start" value="<?php esc_attr_e( $start_date ); ?>" /> Start Date<br />
			<input type="text" name="it-exchange-product-availability-end" value="<?php esc_attr_e( $end_date ); ?>" /> End Date<br />
		</p>
		<?php
	}

	/**
	 * This saves the value
	 *
	 * @todo Convert to use product feature API
	 *
	 * @since 0.3.8
	 * @param object $post wp post object
	 * @return void
	*/
	function save_feature_on_product_save() {
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		// Set enabled
		$start_enabled = empty( $_POST['it-exchange-enable-product-availability-start'] ) ? 'no' : 'yes';
		it_exchange_update_product_feature( $product_id, 'availability', $start_enabled, array( 'type' => 'start', 'setting' => 'enabled' ) );
		$end_enabled = empty( $_POST['it-exchange-enable-product-availability-end'] ) ? 'no' : 'yes';
		it_exchange_update_product_feature( $product_id, 'availability', $end_enabled, array( 'type' => 'end', 'setting' => 'enabled' ) );

		// Abort if this product type doesn't support this feature 
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'availability', array( 'type' => 'either' ) ) )
			return;

		// Get new value from post
		$new_start = empty( $_POST['it-exchange-product-availability-start'] ) ? '' : $_POST['it-exchange-product-availability-start'];
		$new_end   = empty( $_POST['it-exchange-product-availability-end'] ) ? '' : $_POST['it-exchange-product-availability-end'];

		$new_value = array( 
			'start' => $new_start, 
			'end'   => $new_end,
		);
		
		// Save new value
		it_exchange_update_product_feature( $product_id, 'availability', $new_value );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @todo Validate product id and new value 
	 *
	 * @since 0.4.0
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value 
	 * @return bolean
	*/
	function save_feature( $product_id, $new_value, $options=array() ) {
		$defaults['type']    = 'either';
		$defaults['setting'] = 'availability';
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Save enabled options
		if ( 'enabled' == $options['setting'] ) {
			if ( ! in_array( $options['type'], array( 'start', 'end' ) ) )
				return false;

			update_post_meta( $product_id, '_it-exchange-enable-product-availability-' . $options['type'], $new_value );
			return true;
		}

		// If we made it here, we're saving availability dates
		update_post_meta( $product_id, '_it-exchange-product-availability', $new_value );
		return true;
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id, $options=array() ) {
		$defaults['type']    = 'either';
		$defaults['setting'] = 'availability';
		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'enabled' == $options['setting'] ) {
			// Test if its enabled
			switch( $options['type'] ) {
				case 'start' :
				case 'end' :
					$enabled = get_post_meta( $product_id, '_it-exchange-enable-product-availability-' . $options['type'], true );
					if ( ! in_array( $enabled, array( 'yes', 'no' ) ) )
						$enabled = 'no';
					return $enabled;
					break;
				case 'both' :
				case 'either' :
					$start_enabled = get_post_meta( $product_id, '_it-exchange-enable-product-availability-start', true );
					$end_enabled   = get_post_meta( $product_id, '_it-exchange-enable-product-availability-end', true );
					if ( ! in_array( $start_enabled, array( 'yes', 'no' ) ) )
						$start_enabled = 'no';
					if ( ! in_array( $end_enabled, array( 'yes', 'no' ) ) )
						$end_enabled = 'no';

					// If both are set to 'yes', the result is true for 'both' and for 'either' case
					if ( 'yes' == $start_enabled && 'yes' == $end_enabled )
						return 'yes';

					// If both are set to 'no', the result is false for 'both' and for 'either' case
					if ( 'no' == $start_enabled && 'no' == $end_enabled )
						return 'no';

					// If we made it here, one is 'yes' and one is 'no'. If case is 'both', return 'no'. If case is 'either', return 'yes'.
					if ( 'both' == $options['type'] )
						return 'no'; 
					return 'yes';
					break;
			}
		} else if ( 'availability' == $options['setting'] ) {
			// Return availability dates
			// Don't use either here. Only both, start, or end
			$value = get_post_meta( $product_id, '_it-exchange-product-availability', true );
			switch ( $options['type'] ) {
				case 'start' :
					return $value['start'];
					break;
				case 'end' :
					return $value['end'];
					break;
				case 'both' :
				case 'either' :
				default:
					return $value;
					break;
			}
		}
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id, $options=array() ) {
		$defaults['type'] = 'either';
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id, $options ) )
			return false;

		// If it does support, does it have it?
		return (boolean) $this->get_feature( false, $product_id, $options );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can 
	 * support a feature but might not have the feature set.
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id, $options=array() ) {
		$defaults['type'] = 'either';
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'availability' ) ) 
			return false;

		// Determine if this product has turned on product availability
		if ( 'no' == it_exchange_get_product_feature( $product_id, 'availability', array( 'type' => $options['type'], 'setting' => 'enabled' ) ) ) 
			return false;

		return true;
	}
}
$IT_Exchange_Product_Feature_Product_Availability = new IT_Exchange_Product_Feature_Product_Availability();
