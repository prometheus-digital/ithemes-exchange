<?php
/**
 * Default Cart Buddy Shopping Cart Template Tags
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Returns all products that are currently in the cart.
 *
 * Returns an array of products in the cart. This is attached to the it_cart_buddy_cart_products_get() API function
 * We are assuming that we are the first filter to make this request. Any products previously passed into this filter will be overwritten.
 *
 * @since 0.3.7
 * @return array of products
*/
function it_cart_buddy_default_cart_get_products( $products=false ) {
	if ( $products = it_cart_buddy_get_session_products() )
		return $products;

	return array();
}

/**
 * Returns a specific product from the cart based on the passed ID
 *
 * @since 0.3.7
 * @param $product param provided by WP filter. Discarded here.
 * @param $product_id the id of the cart product being requested
 * @return object $product an array of product data taken from the cart
*/
function it_cart_buddy_default_cart_get_product( $product=false, $product_id ) {
	if ( ! $products = it_cart_buddy_get_cart_products() )
		return false;

	if ( empty( $products[$product_id] ) )
		return false;

	return $products['product_id'];
}

/**
 * Returns a product attribute from a specific product in the cart.
 *
 * This returns an attribute stored in the cart's version of the product, not the database
 *
 * @since 0.3.7
 * @todo this isn't finished
 * @param mixed $attribute param provided by WP hook. Dicarded here.
 * @param string $product_id the id of the cart product
 * @param string $attribute_key the key for the attribute being requested
 * @return mixed the value for the attribute being requested or false
*/
function it_cart_buddy_default_cart_get_product_attribute( $attribute, $product_id, $attribute_key ) {
	if ( ! $product = it_cart_buddy_get_cart_product( $product_id ) )
		return false;
}

/**
 * This returns a list of all vars used with the shopping cart form
 *
 * It is just a list of the vars. To get attributes for each one, see it_cart_buddy_cart_form_get_field_attributes()
 *
 * @since 0.3.7
 * @return array
*/
function it_cart_buddy_default_cart_get_form_vars( $form_vars, $args ) {
	$defaults = array(
		'product_remove' => 'cart_buddy_remove_product',
		'quantity'       => 'cart_buddy_product_quantity',
		'update_cart'    => 'cart_buddy_update_cart',
		'empty_cart'     => 'cart_buddy_empty_cart',
		'checkout'       => 'cart_buddy_proceed_to_checkout',
		'nonce'          => 'cart_buddy_process_cart',
	);

	$vars = wp_parse_args( $form_vars, $defaults );

	// If single key has been requested, just return it
	if ( ! empty( $args['key'] ) )
		return empty( $vars[$args['key']] ) ? false: $vars[$args['key']];
	
	return $vars;
}

/**
 * Returns the shopping cart HTML 
 *
 * @since 0.3.7
 * @return string html for the shopping cart 
*/
function it_cart_buddy_default_cart_get_cart_html( $args, $content=''  ) {
	$products = it_cart_buddy_get_cart_products();

	if ( ! $products )
		return '<p>' . __( 'Your cart is empty', 'LION' ) . '</p>';

	$html  = it_cart_buddy_get_errors_div();
	$html  = it_cart_buddy_get_alerts_div();
	
	ob_start();
	it_cart_buddy_get_template_part( 'shopping-cart' );
	$html .= ob_get_clean();
	return $html;
}

/**
 * Returns HTML link for removing a product from the cart
 *
 * @since 0.3.7
 * @param string $original_html html passed through by WP filter. Ignored here.
 * @param string $product_id cart product id
 * @return string HTML
*/
function it_cart_buddy_default_cart_get_remove_product_from_shopping_cart_html( $original_html, $product_id ) {
	$var  = it_cart_buddy_get_action_var( 'remove_product_from_cart' );
	$html = '<a href="' . add_query_arg( esc_attr( $var ), $product_id ) . '">&times;</a>';
	return apply_filters( 'it_cart_buddy_default_cart_remove_product_from_shopping_cart_html', $html, $product_id );
}

/**
 * Returns HTML for add_to_cart action
 *
 * @since 0.3.7
 * @param string $existing_html Existing HTML passed through by WP filter. Not used here.
 * @param mixed $product Product ID in wp_posts table for product post_type
 * @param array $shortcode_atts attritbutes passed through by WP Shortcode API if invoked by it
 * @param string $shortcode_contnte content passed through by WP Shortcode API if invoked by it
 * @return string HTML
*/
function it_cart_buddy_default_cart_get_add_product_to_cart_html( $existing_html, $product, $shortcode_atts=array(), $shortcode_content='' ) {
	$var = it_cart_buddy_get_action_var( 'add_product_to_cart' );
	$html  = it_cart_buddy_get_alerts_div();
	$html .= '<form action="" method="post">';
	$html .= '<input type="hidden" name="' . esc_attr( $var ) . '" value="' . esc_attr( $product ) . '" />';
	$html .= '<input type="submit" name="" value="' . __( 'Add to cart', 'LION' ) . '" />';
	$html .= '</form>';
	$html = apply_filters( 'it_cart_buddy_default_shopping_cart_add_product_to_cart_html', $html, $product, $shortcode_atts, $shortcode_content );
	return $html;
}
