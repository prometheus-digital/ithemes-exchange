<?php
/**
 * This add-on will associate files with any product that registers support for protected-content 
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/

if ( is_admin() ) {
	add_action( 'init', 'it_cart_buddy_protected_content_addon_init_protected_content_metaboxes' );
}
add_action( 'it_cart_buddy_enabled_addons_loaded', 'it_cart_buddy_init_protected_content_addon' );

/**
 * Register the product and add it to enabled product-type addons
 *
 * @since 0.3.8
*/
function it_cart_buddy_init_protected_content_addon() {
    // Register the product feature
    $this_addon  = it_cart_buddy_get_addon( 'protected-content' );
    $slug        = $this_addon['slug'];
    $description = $this_addon['description'];
    it_cart_buddy_register_product_feature( $slug, $description );

    // Add it to the membership product type only 
	it_cart_buddy_add_feature_support_to_product_type( $this_addon['slug'], 'memberships-product-type' );
}

/**
 * Register's the metabox for any product type that supports the protected-content feature
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_protected_content_addon_init_protected_content_metaboxes() {
	// Abord if there are not product addon's currently enabled.
	if ( ! $product_addons = it_cart_buddy_get_enabled_addons( array( 'category' => 'product-type' ) ) )
		return;

	// Loop through product types and register a metabox if it supports protected-content 
	foreach( $product_addons as $slug => $args ) {
		if ( it_cart_buddy_product_type_supports_feature( $slug, 'protected-content' ) )
			add_action( 'it_cart_buddy_product_metabox_callback_' . $slug, 'it_cart_buddy_protected_content_addon_register_metabox' );
	}
}

/**
 * Registers the protected content metabox for a specific product type
 *
 * Hooked to it_cart_buddy_product_metabox_callback_[product-type] where product type supports protected_content 
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_protected_content_addon_register_metabox() {
	add_meta_box( 'it_cart_buddy_protected_content', __( 'This is a Protected Content Product', 'LION' ), 'it_cart_buddy_protected_content_addon_print_metabox', 'it_cart_buddy_prod', 'side', 'high' );
}

/**
 * This echos the protected content options metabox.
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_protected_content_addon_print_metabox( $post ) {
	// Grab the Cart Buddy Product object from the WP $post object
	$product = it_cart_buddy_get_product( $post );

	// Set the value of the product files for this product
	$enable_protected_content = it_cart_buddy_get_product_feature( $product->ID, 'protected-content' );

	// Set description
	$description = __( 'You will be able to protect site content based on if a customer has purchased this product.', 'LION' );
	$description = apply_filters( 'it_cart_buddy_protected_content_addon_metabox_description', $description, $product );

	// Echo the form field
	?>
	<p class="description">
		<?php esc_html_e( $description ); ?>
	</p>
	<?php
}
