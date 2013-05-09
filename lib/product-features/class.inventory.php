<?php
/**
 * This will control email messages with any product types that register email message support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.4.0
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Inventory {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 0.4.0
	 * @return void
	 * @todo remove it_exchange_enabled_addons_loaded action???
	*/
	function IT_Exchange_Product_Feature_Inventory() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_inventory', array( $this, 'save_feature' ), 9, 2 );
		add_action( 'it_exchange_update_product_feature_enable_inventory', array( $this, 'save_enable_inventory_feature' ), 9, 2 );
		add_filter( 'it_exchange_get_product_feature_enable_inventory', array( $this, 'get_enable_inventory_feature' ), 9, 2 );
		add_filter( 'it_exchange_get_product_feature_inventory', array( $this, 'get_feature' ), 9, 2 );
		add_filter( 'it_exchange_product_has_feature_inventory', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_inventory', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'inventory';
		$description = __( 'The current inventory number', 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'inventory', $params['slug'] );
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
			if ( it_exchange_product_type_supports_feature( $slug, 'inventory' ) )
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
		add_meta_box( 'it-exchange-product-inventory', __( 'Product Inventory', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'normal' );
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
		$product_feature_enable_value = it_exchange_get_product_feature( $product->ID, 'enable_inventory' );
		$product_feature_value = it_exchange_get_product_feature( $product->ID, 'inventory' );

		// Set description
		$description = __( 'Use this to set the product\'s current inventory number.', 'LION' );
		$description = apply_filters( 'it_exchange_product_inventory_metabox_description', $description );

		// Echo the form field
		echo $description;
		?>
		<p>
			<input type="checkbox" name="it-exchange-enable-product-inventory" <?php checked( 'on' === $product_feature_enable_value ); ?>" /> <?php _e( 'Enable Inventory Tracking for this Product', 'LION' ); ?>
			<input type="text" name="it-exchange-product-inventory" value="<?php esc_attr_e( $product_feature_value ); ?>" /> <?php _e( 'Current Inventory', 'LION' ); ?>
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'inventory' ) )
			return;

		if ( isset( $_POST['it-exchange-enable-product-inventory'] ) )
			it_exchange_update_product_feature( $product_id, 'enable-inventory', 'on' );
		else
			it_exchange_update_product_feature( $product_id, 'enable-inventory', 'off' );
		
		if ( isset( $_POST['it-exchange-product-inventory'] ) )
			it_exchange_update_product_feature( $product_id, 'inventory', $_POST['it-exchange-product-inventory'] );
			return;
		
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
		update_post_meta( $product_id, '_it-exchange-product-inventory', $new_value );
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
	function save_enable_inventory_feature( $product_id, $new_value ) {
		update_post_meta( $product_id, '_it-exchange-product-enable-inventory', $new_value );
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id ) {
		return get_post_meta( $product_id, '_it-exchange-product-inventory', true );
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_enable_inventory_feature( $existing, $product_id ) {
		return get_post_meta( $product_id, '_it-exchange-product-enable-inventory', true );
	}

	/**
	 * Does the product have this feature?
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
		return ( false === $this->get_feature( false, $product_id ) ) ? false : true;
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
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( it_exchange_product_type_supports_feature( $product_type, 'inventory' ) )
			if ( 'on' === get_post_meta( $product_id, '_it-exchange-product-enable-inventory', true ) )
				return true;
			else
				return false;
		else
			return false;
	}
}
$IT_Exchange_Product_Feature_Inventory = new IT_Exchange_Product_Feature_Inventory();
