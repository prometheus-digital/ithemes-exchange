<?php
/**
 * This will associate a monetary price with all product types.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.3.8
 * @package IT_Exchange
*/


class IT_Exchange_Base_Price {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.3.8
	 * @return void
	 * @todo remove it_exchange_enabled_addons_loaded action???
	*/
	function IT_Exchange_Base_Price() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_update_product_feature_base-price', array( $this, 'save_feature' ), 9, 2 );
		add_filter( 'it_exchange_get_product_feature_base-price', array( $this, 'get_feature' ), 9, 2 );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_filter( 'it_exchange_product_has_feature_base-price', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_base-price', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product and add it to enabled product-type addons
	 *
	 * @since 0.3.8
	*/
	function add_feature_support_to_product_types() {
		// Register the base-price_addon
		$slug        = 'base-price';
		$description = 'The base price for a product';
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'base-price', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the base-price feature
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function init_feature_metaboxes() {
		// Abord if there are not product addon's currently enabled.
		if ( ! $product_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) )
			return;

		// Loop through product types and register a metabox if it supports base-price
		foreach( $product_addons as $slug => $args ) {
			if ( it_exchange_product_type_supports_feature( $slug, 'base-price' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $slug, array( $this, 'register_metabox' ) );
		}
	}

	/**
	 * Registers the price metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports base-price
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-base-price', __( 'Base Price', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal', 'high' );
	}

	/**
	 * This echos the base price metabox.
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the base-price for this product
		$product_base_price = it_exchange_get_product_feature( $product->ID, 'base-price' );

		// Set description
		$description = __( 'Price', 'LION' );
		$description = apply_filters( 'it_exchange_base-price_addon_metabox_description', $description );

		// Echo the form field
		?>
			<label for="it-exchange-base-price"><?php esc_html_e( $description ); ?></label>
			<input type="text" name="it-exchange-base-price" value="<?php esc_attr_e( $product_base_price ); ?>" />
		<?php
	}

	/**
	 * This saves the base price value
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

		// Abort if this product type doesn't support base-price
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'base-price' ) )
			return;

		// Abort if key for base-price option isn't set in POST data
		if ( ! isset( $_POST['it-exchange-base-price'] ) )
			return;

		// Get new value from post
		$new_price = $_POST['it-exchange-base-price'];
		
		// Save new value
		it_exchange_update_product_feature( $product_id, 'base-price', $new_price );
	}

	/**
	 * This updates the base price for a product
	 *
	 * @todo Validate product id and new price
	 *
	 * @since 0.3.8
	 * @param integer $product_id the product id
	 * @param mixed $new_price the new price
	 * @return bolean
	*/
	function save_feature( $product_id, $new_price ) {
		update_post_meta( $product_id, '_it-exchange-base-price', $new_price );
	}

	/**
	 * Return the product's base price
	 *
	 * @since 0.3.8
	 * @param mixed $base_price the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string base-price
	*/
	function get_feature( $base_price, $product_id ) {
		$base_price = get_post_meta( $product_id, '_it-exchange-base-price', true );
		return $base_price;
	}

	/**
	 * Does the product have a base price?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id ) )
			return false;
		return (boolean) $this->get_feature( false, $product_id );
	}

	/**
	 * Does the product support a base price?
	 *
	 * This is different than if it has the feature, a product can 
	 * support a feature but might not have the feature set.
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		return it_exchange_product_type_supports_feature( $product_type, 'base-price' );
	}
}
$IT_Exchange_Base_Price = new IT_Exchange_Base_Price();
