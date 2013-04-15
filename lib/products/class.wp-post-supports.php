<?php
/**
 * This add-on will enable the product title (post title ) box on the edit add / edit product page
 *
 * @since 0.3.8
 * @package IT_Exchange
*/

class IT_Exchange_WP_Post_Supports {

	/**
	 * Constructor. Loads hooks for various post supports
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function IT_Exchange_WP_Post_Supports() {

		// WordPress Post Title
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'init_wp_title_support_as_product_feature' ) );
		add_filter( 'it_exchange_get_product_feature_product_title', array( $this, 'get_title' ), 9, 2 );

		// WordPress Post Content (Product Description)
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'init_wp_post_content_as_product_feature' ) );
		add_filter( 'it_exchange_get_product_feature_product_description', array( $this, 'get_product_description' ), 9, 2 );

		// WordPress Featured Image as a Product Feature
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'init_wp_featured_image_as_product_feature' ) );
		add_filter( 'it_exchange_get_product_feature_wp-featured-image', array( $this, 'get_featured_image' ), 9, 2 );

		// WordPress Excerpt
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'init_wp_excerpt_as_product_feature' ) );
		add_filter( 'it_exchange_get_product_feature_wp-excerpt', array( $this, 'get_excerpt' ), 9, 2 );

		// WordPress Post Author
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'init_wp_author_support_as_product_feature' ) );
		add_filter( 'it_exchange_get_product_feature_wp-author', array( $this, 'get_wp_author' ), 9, 2 );

		// WordPress Post Formats
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'init_wp_post_formats_as_product_feature' ) );

		// WordPress Custom Fields
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'init_wp_custom_fields_as_product_feature' ) );

		// WordPress Comments metabox
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'init_wp_comments_support_as_product_feature' ) );

		// WordPress Trackbacks
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'init_wp_trackbacks_as_product_feature' ) );

		// WordPress Post Revisions
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'init_wp_revisions_as_product_feature' ) );
	}

	/**
	 * Register the product and add it to enabled product-type addons
	 *
	 * @since 0.3.8
	*/
	function init_wp_title_support_as_product_feature() {
		// Register the product feature
		$slug        = 'product-title';
		$description = __( 'Adds support for default WordPress Title field', 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$product_types = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $product_types as $key => $product_type ) { 
			it_exchange_add_feature_support_to_product_type( $slug, $product_type['slug'] );
		}   
	}

	/**
	 * Return the product's title
	 *
	 * @since 0.3.8
	 * @param mixed $title the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string post_title
	*/
	function get_title( $title, $product_id ) { 
		return get_the_title( $product_id );
	}

	/**
	 * Register the WP post_content as a Product Feature (product description)
	 *
	 * Register it and tack it onto all registered product-type addons by default
	 *
	 * @since 0.3.8
     * @return void
	*/
	function init_wp_post_content_as_product_feature() {
		// Register the product feature
		$slug        = 'product-description';
		$description = __( 'Adds support for the post content area to product types.', 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$product_types = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $product_types as $key => $product_type ) { 
			it_exchange_add_feature_support_to_product_type( $slug, $product_type['slug'] );
		}   
	}

	/**
	 * Return the product's description
	 *
	 * @since 0.3.8
	 * @param mixed $description the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string post_content (product descritpion) 
	*/
	function get_product_description( $description, $product_id ) { 
		return get_the_content( $product_id );
	}

	/**
	 * Register the WP featured image support as a Product Feature
	 *
	 * Register it and tack it onto all registered product-type addons by default
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function init_wp_featured_image_as_product_feature() {
		// Register the product feature
		$slug        = 'wp-featured-image';
		$description = __( 'Adds support for WP Featured Image to a specific product type', 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$product_types = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $product_types as $key => $product_type ) { 
			it_exchange_add_feature_support_to_product_type( $slug, $product_type['slug'] );
		}   
	}

	/**
	 * Return the product's featured_image
	 *
	 * This returns the image, not the ID
	 *
	 * @since 0.3.8
	 * @param mixed $featured_image the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string featured image
	*/
	function get_featured_image( $featured_image, $product_id ) { 
		if ( has_post_thumbnail( $product_id ) ) 
			return get_the_post_thumbnail( $product_id );
	}

	/*
	 * Register WP Author as a product feature
	 *
	 * While we register this as a product feature, we do not add support for any product types by default.
	 *
	 * @since 0.3.8
     * @return void
	*/
	function init_wp_author_support_as_product_feature() {
		// Register the product feature
		$slug        = 'wp-author';
		$description = __( 'Adds support for WP Author field to a specific product', 'LION' );
		it_exchange_register_product_feature( $slug, $description );
	}

	/**
	 * Return the product's wp_author
	 *
	 * This returns the authors display name
	 *
	 * @since 0.3.8
	 * @param mixed $wp_author the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string author
	*/
	function get_wp_author( $wp_author, $product_id ) {
		$product = it_exchange_get_product( $product_id );
		if ( empty( $product->post_author ) )
			return;

		if ( $author = get_the_author_meta( 'display_name', $product->post_author ) )
			return $author;

		return false;
	}

	/**
	 * Register the product and add it to enabled product-type addons
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function init_wp_custom_fields_as_product_feature() {
		// Register the product feature
		$slug        = 'wp-custom-fields';
		$description = __( 'Adds support for WP Custom Fields as a product Feature', 'LION' );
		it_exchange_register_product_feature( $slug, $description );
	}

	/**
	 * Register the WP Comments as a product feature
	 *
	 * While we register this as a product feature, we do not add support for any product types by default.
     *
	 * @since 0.3.8
	 * @return void
	*/
	function init_wp_comments_support_as_product_feature() {
		// Register the product feature
		$slug        = 'wp-comments';
		$description = __( 'Adds support for the WP Comments field to a specific product type', 'LION' );
		it_exchange_register_product_feature( $slug, $description );
	}

	/**
	 * Register the WP Trackbacks metabox as a product feature
	 *
	 * While we register this as a product feature, we do not add support for any product types by default.
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function init_wp_trackbacks_as_product_feature() {
		// Register the product feature
		$slug        = 'wp-trackbacks';
		$description = __( 'Adds support for the WP Trackbacks metabox.', 'LION' );
		it_exchange_register_product_feature( $slug, $description );
	}

	/**
	 * Register excerpts as a product feature
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function init_wp_excerpt_as_product_feature() {
		// Register the product feature
		$slug        = 'wp-excerpt';
		$description = __( 'Adds support for the WP excerpt of the product', 'LION' );
		it_exchange_register_product_feature( $slug, $description );
	}

	/**
	 * Return the product's excerpt
	 *
	 * @since 0.3.8
	 * @param mixed $excerpt the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string post_excerpt
	*/
	function get_excerpt( $excerpt, $product_id ) { 
		return get_the_excerpt( $product_id );
	}

	/**
	 * Register post formats as a product feature
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function init_wp_post_formats_as_product_feature() {
		// Register the product feature
		$slug        = 'wp-post-formats';
		$description = __( 'Adds support for WP Post Formats to products', 'LION' );
		it_exchange_register_product_feature( $slug, $description );
	}

	/**
	 * Register the WP Post revisions as a Product Feature
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function init_wp_revisions_as_product_feature() {
		// Register the product feature
		$slug        = 'wp-revisions';
		$description = __( 'Adds support for WP Revisions to Product Types', 'LION' );
		it_exchange_register_product_feature( $slug, $description );
	}
}
$IT_Exchange_WP_Post_Supports = new IT_Exchange_WP_Post_Supports();
