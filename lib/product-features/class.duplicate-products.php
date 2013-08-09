<?php
/**
 * This will control email messages with any product types that register email message support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 1.1.2
 * @package IT_Exchange_Addon_Duplicate
*/


class IT_Exchange_Addon_Duplicate_Product_Feature {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.1.2
	 * @todo we're still trying to figure out the best place to insert this function on the product page
	 * @return void
	*/
	function IT_Exchange_Addon_Duplicate_Product_Feature() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.1.2
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'duplicate-product-type';
		$description = __( 'Duplicates product as a new product to edit.', 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'duplicate-product-type', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 1.1.2
	 * @return void
	*/
	function init_feature_metaboxes() {
		
		global $post;
		
		if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		} else {
			if ( isset( $_REQUEST['post'] ) )
				$post_id = (int) $_REQUEST['post'];
			elseif ( isset( $_REQUEST['post_ID'] ) )
				$post_id = (int) $_REQUEST['post_ID'];
			else
				$post_id = 0;

			if ( $post_id )
				$post = get_post( $post_id );

			if ( isset( $post ) && !empty( $post ) )
				$post_type = $post->post_type;
		}
			
		if ( !empty( $_REQUEST['it-exchange-product-type'] ) )
			$product_type = $_REQUEST['it-exchange-product-type'];
		else
			$product_type = it_exchange_get_product_type( $post );
		
		if ( !empty( $post_type ) && 'it_exchange_prod' === $post_type ) {
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'duplicate-product-type' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}
		
	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature
	 *
	 * @since 1.1.2
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-duplicate-product-type', __( 'Duplicate Product', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_side', 'default' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.1.2
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );
		
		if ( 'auto-draft' !== $post->post_status ) {
			// Set the value of the feature for this product
			$product_feature_value = it_exchange_get_product_feature( $product->ID, 'duplicate-product-type' );
			$url = it_exchange_duplicate_product_addon_get_duplicating_url( $post );
			echo '<a class="button" href="' . $url . '">' . __( 'Duplicate Product', 'LION' ) . '</a>';
		}
	}
}
//$IT_Exchange_Addon_Duplicate_Product_Feature = new IT_Exchange_Addon_Duplicate_Product_Feature();