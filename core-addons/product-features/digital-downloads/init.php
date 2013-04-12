<?php
/**
 * This add-on will associate files with any product that registers support for digital-downloads 
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * @since 0.3.8
 * @package IT_Exchange
*/

if ( is_admin() ) {
	add_action( 'init', 'it_exchange_digital_downloads_addon_init_digital_downloads_metaboxes' );
	add_action( 'it_exchange_save_product', 'it_exchange_digital_downloads_addon_save_files_on_product_save' );
}
add_action( 'it_exchange_update_product_feature-digital-downloads', 'it_exchange_digital_downloads_addon_save_files', 9, 2 );
add_filter( 'it_exchange_get_product_feature-digital-downloads', 'it_exchange_digital_downloads_addon_get_digital_downloads', 9, 2 );
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_init_digital_downloads_addon' );

/**
 * Register the product and add it to enabled product-type addons
 *
 * @since 0.3.8
*/
function it_exchange_init_digital_downloads_addon() {
    // Register the product feature
    $this_addon  = it_exchange_get_addon( 'digital-downloads' );
    $slug        = $this_addon['slug'];
    $description = $this_addon['description'];
    it_exchange_register_product_feature( $slug, $description );

    // Add it to the digital-downloads-product type only 
	it_exchange_add_feature_support_to_product_type( $this_addon['slug'], 'digital-downloads-product-type' );
}

/**
 * Register's the metabox for any product type that supports the digital-downloads feature
 *
 * @since 0.3.8
 * @return void
*/
function it_exchange_digital_downloads_addon_init_digital_downloads_metaboxes() {
	// Abord if there are not product addon's currently enabled.
	if ( ! $product_addons = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) )
		return;

	// Loop through product types and register a metabox if it supports digital-downloads 
	foreach( $product_addons as $slug => $args ) {
		if ( it_exchange_product_type_supports_feature( $slug, 'digital-downloads' ) )
			add_action( 'it_exchange_product_metabox_callback_' . $slug, 'it_exchange_digital_downloads_addon_register_metabox' );
	}
}

/**
 * Registers the downloads metabox for a specific product type
 *
 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports digital_downloads 
 *
 * @since 0.3.8
 * @return void
*/
function it_exchange_digital_downloads_addon_register_metabox() {
	add_meta_box( 'it_exchange_digital_downloads', __( 'Product Downloads', 'LION' ), 'it_exchange_digital_downloads_addon_print_metabox', 'it_exchange_prod', 'normal' );
}

/**
 * This echos the downloads metabox.
 *
 * @since 0.3.8
 * @return void
*/
function it_exchange_digital_downloads_addon_print_metabox( $post ) {
	// Grab the iThemes Exchange Product object from the WP $post object
	$product = it_exchange_get_product( $post );

	// Set the value of the product files for this product
	$product_downloads = it_exchange_get_product_feature( $product->ID, 'digital_downloads' );

	// Set description
	$description = __( 'This will be the metabox for digital downloads. == CHANGE THIS DESCRIPTION ==', 'LION' );
	$description = apply_filters( 'it_exchange_digital_downloads_addon_metabox_description', $description, $product );

	// Echo the form field
	?>
	<p>
		<span class="description"><?php esc_html_e( $description ); ?></span><br />
		<input type="text" name="_it_exchange_digital_downloads" value="<?php esc_attr_e( $product_downloads ); ?>" />
	</p>
	<?php
}

/**
 * This saves the downloads
 *
 * @since 0.3.8
 * @param object $post wp post object
 * @return void
*/
function it_exchange_digital_downloads_addon_save_files_on_product_save() {
	// Abort if we can't determine a product type
	if ( ! $product_type = it_exchange_get_product_type() )
		return;

	// Abort if we don't have a product ID
	$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
	if ( ! $product_id )
		return;

	// Abort if this product type doesn't support digital-downloads
	if ( ! it_exchange_product_type_supports_feature( $product_type, 'digital-downloads' ) )
		return;

	// Abort if key for digital downloads option isn't set in POST data
	if ( ! isset( $_POST['_it_exchange_digital_downloads'] ) )
		return;

	// Get new value from post
	$new_value = $_POST['_it_exchange_digital_downloads'];
	
	// Save new value
	it_exchange_update_product_feature( $product_id, 'digital_downloads', $new_value );
}

/**
 * This updates the product files for a product
 *
 * @todo Validate product id and new value 
 *
 * @since 0.3.8
 * @param integer $product_id the product id
 * @param mixed $new_value the new  value
 * @return bolean
*/
function it_exchange_digital_downloads_addon_save_files( $product_id, $new_value ) {
	update_post_meta( $product_id, '_it_exchange_digital_downloads', $new_value );
}

/**
 * Return the product's digital downloads
 *
 * @since 0.3.8
 * @param mixed $files the values passed in by the WP Filter API. Ignored here.
 * @param integer product_id the WordPress post ID
 * @return mixed files
*/
function it_exchange_digital_downloads_addon_get_files( $files, $product_id ) {
	$files = get_post_meta( $product_id, '_it_exchange_digital_downloads', true );
	return $files;
}
