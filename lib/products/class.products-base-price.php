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
	*/
	function IT_Exchange_Base_Price() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'init_base_price_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_base_price_on_product_save' ) );
		}
		add_action( 'it_exchange_update_product_feature_base_price', array( $this, 'save_base_price' ), 9, 2 );
		add_filter( 'it_exchange_get_product_feature_base_price', array( $this, 'get_base_price' ), 9, 2 );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_base_price_support_to_product_types' ) );
	}

	/**
	 * Register the product and add it to enabled product-type addons
	 *
	 * @since 0.3.8
	*/
	function add_base_price_support_to_product_types() {
		// Register the base_price_addon
		$slug        = 'base_price';
		$description = 'The base price for a product';
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'base_price', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the base_price feature
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function init_base_price_metaboxes() {
		// Abord if there are not product addon's currently enabled.
		if ( ! $product_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) )
			return;

		// Loop through product types and register a metabox if it supports base_price
		foreach( $product_addons as $slug => $args ) {
			if ( it_exchange_product_type_supports_feature( $slug, 'base_price' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $slug, array( $this, 'register_metabox' ) );
		}
	}

	/**
	 * Registers the price metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports base_price
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it_exchange_base_price', __( 'Base Price', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'side' );
	}

	/**
	 * This echos the base price metabox.
	 *
	 * It uses queries the product object for the key the base price is stored in. This allows for some product-types to store base_price elswere if they so choose
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the base_price for this product
		$product_base_price     = it_exchange_get_product_feature( $product->ID, 'base_price' );

		// Set description
		$description = __( 'This will be the standard price before discounts, taxes, fees, or any other modifications', 'LION' );
		$description = apply_filters( 'it_exchange_base_price_addon_metabox_description', $description );

		// Echo the form field
		?>
		<p>
			<span class="description"><?php esc_html_e( $description ); ?></span><br />
			<input type="text" name="_it_exchange_base_price" value="<?php esc_attr_e( $product_base_price ); ?>" />
		</p>
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
	function save_base_price_on_product_save() {
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		// Abort if this product type doesn't support base_price
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'base_price' ) )
			return;

		// Abort if key for base_price option isn't set in POST data
		if ( ! isset( $_POST['_it_exchange_base_price'] ) )
			return;

		// Get new value from post
		$new_price = $_POST['_it_exchange_base_price'];
		
		// Save new value
		it_exchange_update_product_feature( $product_id, 'base_price', $new_price );
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
	function save_base_price( $product_id, $new_price ) {
		update_post_meta( $product_id, '_it_exchange_base_price', $new_price );
	}

	/**
	 * Return the product's base price
	 *
	 * @since 0.3.8
	 * @param mixed $base_price the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string base_price
	*/
	function get_base_price( $base_price, $product_id ) {
		$base_price = get_post_meta( $product_id, '_it_exchange_base_price', true );
		return $base_price;
	}
}
$IT_Exchange_Base_Price = new IT_Exchange_Base_Price();
