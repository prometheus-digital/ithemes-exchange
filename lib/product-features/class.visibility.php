<?php
/**
 * This will associate a visiblity with all product types.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Visibility {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function IT_Exchange_Product_Visibility() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'init_visibility_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_visibility_on_product_save' ) );
		}
		add_action( 'it_exchange_update_product_feature_visibility', array( $this, 'save_visibility' ), 9, 2 );
		add_filter( 'it_exchange_get_product_feature_visibility', array( $this, 'get_visibility' ), 9, 2 );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_visibility_support_to_product_types' ) );
		add_filter( 'it_exchange_product_has_feature_visibility', array( $this, 'product_has_visibility') , 9, 2 );
	}

	/**
	 * Register the product and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_visibility_support_to_product_types() {
		// Register the visibility_addon
		$slug        = 'visibility';
		$description = 'The visibility of a product';
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'visibility', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the visibility feature
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function init_visibility_metaboxes() {
		// Abord if there are not product addon's currently enabled.
		if ( ! $product_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) )
			return;

		// Loop through product types and register a metabox if it supports visibility
		foreach( $product_addons as $slug => $args ) {
			if ( it_exchange_product_type_supports_feature( $slug, 'visibility' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $slug, array( $this, 'register_metabox' ) );
		}
	}

	/**
	 * Registers the visibility metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports visibility
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-visibility', __( 'Visibility', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal', 'high' );
	}

	/**
	 * This echos the Visibility metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the visibility for this product
		$product_visibility = it_exchange_get_product_feature( $product->ID, 'visibility' );

		// Set description
		$description = __( 'Visibility', 'LION' );
		$description = apply_filters( 'it_exchange_visibility_addon_metabox_description', $description );

		// Echo the form field
		?>
			<label for="it-exchange-visibility"><?php esc_html_e( $description ); ?></label>
            <select name="it-exchange-visibility">
            	<option value="visible" <?php selected( 'visible', $product_visibility ); ?>><?php _e( 'Visible', 'LION' ); ?></option>
            	<option value="hidden" <?php selected( 'hidden', $product_visibility ); ?>><?php _e( 'Hidden', 'LION' ); ?></option>
            </select>
		<?php
	}

	/**
	 * This saves the Visibility value
	 *
	 * @todo Convert to use product feature API
	 *
	 * @since 0.4.0
	 * @param object $post wp post object
	 * @return void
	*/
	function save_visibility_on_product_save() {
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		// Abort if this product type doesn't support visibility
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'visibility' ) )
			return;

		// Abort if key for visibility option isn't set in POST data
		if ( ! isset( $_POST['it-exchange-visibility'] ) )
			return;

		// Get new value from post
		$new_visibility = $_POST['it-exchange-visibility'];
		
		// Save new value
		it_exchange_update_product_feature( $product_id, 'visibility', $new_visibility );
	}

	/**
	 * This updates the Visibility for a product
	 *
	 * @todo Validate product id and new visibilty
	 *
	 * @since 0.4.0
	 * @param integer $product_id the product id
	 * @param mixed $new_visibility the new visibility
	 * @return bolean
	*/
	function save_visibility( $product_id, $new_visibility ) {
		update_post_meta( $product_id, '_it-exchange-visibility', $new_visibility );
	}

	/**
	 * Return the product's Visibility
	 *
	 * @since 0.4.0
	 * @param mixed $visibility the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string visibility
	*/
	function get_visibility( $visibility, $product_id ) {
		$visibility = get_post_meta( $product_id, '_it-exchange-visibility', true );
		return $visibility;
	}

	/**
	 * Does the product have a Visibility?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_visibility( $result, $product_id ) {
		// Does this product type support Visibility?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'visibility' ) ) 
			return false;
		return (boolean) $this->get_visibility( false, $product_id );
	}
}
$IT_Exchange_Product_Visibility = new IT_Exchange_Product_Visibility();
