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
	*/
	function IT_Exchange_Product_Feature_Product_Availability() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_availability', array( $this, 'save_feature' ), 9, 2 );
		add_filter( 'it_exchange_get_product_feature_availability', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_availability', array( $this, 'product_has_feature') , 9, 3 );
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
		$start = empty( $product_feature_value['start'] ) ? '' : $product_feature_value['start'];
		$end   = empty( $product_feature_value['end'] ) ? '' : $product_feature_value['end'];

		// Set description
		$description = __( 'Use these settings to determine when a product is available to purchase.', 'LION' );
		$description = apply_filters( 'it_exchange_product_availability_metabox_description', $description );

		// Echo the form field
		echo $description;
		?>
		<p>
			<input type="text" name="it-exchange-product-availability-start" value="<?php esc_attr_e( $start ); ?>" /> Start Date<br />
			<input type="text" name="it-exchange-product-availability-end" value="<?php esc_attr_e( $end ); ?>" /> End Date<br />
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

		// Abort if this product type doesn't support this feature 
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'availability' ) )
			return;

		// Abort if key for feature option isn't set in POST data
		if ( ! isset( $_POST['it-exchange-product-availability-start'] ) && ! isset( $_POST['it-exchange-product-availability-end'] ) )
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
	function save_feature( $product_id, $new_value ) {
		update_post_meta( $product_id, '_it-exchange-product-availability', $new_value );
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id, $options ) {
		$defaults['type'] = 'both';
		$options = ITUtility::merge_defaults( $options, $defaults );

		$value = get_post_meta( $product_id, '_it-exchange-product-availability', true );

		if ( 'start' == $options['type'] )
			return $value['start'];
		else if ( 'end' == $options['type'] )
			return $value['end'];

		// This will be returned if value of type was both or anything other than start / end
		return $value;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id, $options ) {
		$defaults['type'] = 'both';
		$options = ITUtility::merge_defaults( $defaults, $options );

		// Does this product type support feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'availability' ) ) 
			return false;
		return (boolean) $this->get_feature( false, $product_id, $options );
	}
}
$IT_Exchange_Product_Feature_Product_Availability = new IT_Exchange_Product_Feature_Product_Availability();
