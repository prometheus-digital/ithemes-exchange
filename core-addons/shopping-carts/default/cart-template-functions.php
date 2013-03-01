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

	$html = '';
	$html .= it_cart_buddy_default_shopping_cart_get_form_open();
	$html .= it_cart_buddy_default_shopping_cart_get_table_open();
	$html .= it_cart_buddy_default_shopping_cart_get_table_header();
	foreach( $products as $itemized_hash => $product ) {
		$html .= it_cart_buddy_default_shopping_cart_get_table_row( $product );	
	}
	$html .= it_cart_buddy_default_shopping_cart_get_table_footer();
	$html .= it_cart_buddy_default_shopping_cart_get_table_close();
	$html .= it_cart_buddy_default_shopping_cart_get_cart_totals_html();
	$html .= it_cart_buddy_default_shopping_cart_get_form_close();

	return $html;
}

/**
 * Returns the opening form element in HTML
 *
 * @since 0.3.7
 * @return string html for opening form element
*/
function it_cart_buddy_default_shopping_cart_get_form_open() {
	$html = '<form action="" method="post">';
	return apply_filters( 'it_cart_buddy_default_shopping_cart_form_open', $html );
}

/**
 * Returns the opening table element in HTML
 *
 * @since 0.3.7
 * @return string html for opening table element
*/
function it_cart_buddy_default_shopping_cart_get_table_open() {
	$html = '<table id="it-cart-buddy-shopping-cart-table">';
	return apply_filters( 'it_cart_buddy_default_shopping_cart_table_open', $html );
}

/**
 * Returns the HTML for the table head
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_default_shopping_cart_get_table_header() {
	$columns = it_cart_buddy_default_shopping_cart_get_table_columns();
	$html = '';
	if ( ! empty( $columns ) ) {
		$html .= '<tr>';
		foreach( $columns as $key => $value ) {
			$html .= '<th id="table_header_' . esc_attr( $key ) . '">' . esc_html( $value ) . '</th>';
		}
		$html .= '</tr>';
	}
	return apply_filters( 'it_cart_buddy_default_shopping_cart_table_header', $html );
}

/**
 * Returns HTML for a cart product table row
 *
 * @since 0.3.7
 * @param $cart_product array product data coming from shopping PHP Session
 * @return string HTML
*/
function it_cart_buddy_default_shopping_cart_get_table_row( $cart_product ) {
	$columns = it_cart_buddy_default_shopping_cart_get_table_columns();
	$html = '';
	$html .= '<tr>';
	foreach( (array) $columns as $cell => $label ) {
		$html .= '<td>' . it_cart_buddy_default_shopping_cart_get_table_cell( $cell, $cart_product ) . '</td>';
	}
	$html .= '</tr>';
	return $html;
}

/**
 * Returns HTML for the table footer
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_default_shopping_cart_get_table_footer() {
	return apply_filters( 'it_cart_buddy_default_shopping_cart_table_footer', '' );
}

/**
 * Returns the HTML for the closing table element
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_default_shopping_cart_get_table_close() {
	$html = '</table>';
	return apply_filters( 'it_cart_buddy_default_shopping_cart_table_footer', $html );
}

/**
 * Returns the HTML for the form footer
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_default_shopping_cart_get_form_close() {
	$html = '';
	$html .= it_cart_buddy_get_empty_shopping_cart_html();
	$html .= '&nbsp;' . it_cart_buddy_get_update_shopping_cart_html();
	$html .= '&nbsp;' . it_cart_buddy_get_checkout_shopping_cart_html();
	$html .= '</form>';
	return apply_filters( 'it_cart_buddy_default_shopping_cart_form_footer', $html );
}

/**
 * Generates the content for each table cell in the itemized products cart table
 *
 * @since 0.3.7
 * @return string HTML for cell
*/
function it_cart_buddy_default_shopping_cart_get_table_cell( $column, $product ) {
	$db_product = it_cart_buddy_get_product( $product['product_id'] );
	switch( $column ) {
		case 'product-remove' :
			$html = it_cart_buddy_get_remove_product_from_shopping_cart_html( $product['product_cart_id'] );
			break;
		case 'product-title' :
			$html = it_cart_buddy_get_cart_product_title( $product );
			break;
		case 'product-cost' :
			$base_price = it_cart_buddy_get_cart_product_base_price( $product );
			$base_price = apply_filters( 'it_cart_buddy_default_shopping_cart_get_product_base_price', $base_price, $product );
			$html = '$' . $base_price;
			break;
		case 'product-quantity' :
			$html = '<input type="text" name="product_quantity[' . $product['product_cart_id'] . ']" value="' . it_cart_buddy_get_cart_product_quantity( $product ) . '" size="4"/>';
			break;
		case 'product-subtotal' :
			$html = '$' . it_cart_buddy_get_cart_product_subtotal( $product );
			break;
		default :
			$html = '';
			break;
	}
	return apply_filters( 'it_cart_buddy_default_shopping_cart_table_cell', $html, $column, $product );
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
	$html = '<a href="' . add_query_arg( 'cart_buddy_remove_product_from_cart', $product_id ) . '">&times;</a>';
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
	$html = '<a href="' . add_query_arg( 'cart_buddy_add_to_cart', $product ) . '">' . __( 'Add to cart', 'LION' ) . '</a>';
	$html = apply_filters( 'it_cart_buddy_default_shopping_cart_add_product_to_cart_html', $html, $product, $shortcode_atts, $shortcode_content );
	return $html;
}

/**
 * Returns the HTML for empty_cart action
 *
 * @since 0.3.7
 * @param string $existing exisiting HTML passed through by WP filter. Ignored here.
 * @return string HTML
*/
function it_cart_buddy_default_cart_get_empty_cart_button( $existing=false ) {
	$html = '<input type="submit" name="cart_buddy_empty_cart" value="' . __( 'Empty Cart', 'LION' ) . '" />';
	apply_filters( 'it_cart_buddy_default_cart_empty_cart_button', $html );
	return $html;
}

/**
 * Returns the HTML for the update cart button
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_default_cart_get_update_cart_button( $existing=false ) {
	$html = '<input type="submit" name="cart_buddy_update_cart" value="' . __( 'Update Cart', 'LION' ) . '" />';
	return apply_filters( 'it_cart_buddy_default_cart_update_cart_button', $html );
}

/**
 * Returns the HTML for the checkout Button
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_default_cart_get_checkout_cart_button( $existing=false ) {
	$html = '<input type="submit" name="cart_buddy_checkout_cart" value="Checkout" />';
	return apply_filters( 'it_cart_buddy_default_cart_checkout_cart_button', $html );
}

/**
 * This prints the html for the cart totals
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_default_shopping_cart_get_cart_totals_html() {
	$html = '<!-- TEMP UI -->';
	$html .= '<div id="cart_buddy_cart_totals"><p>';
	$html .= '<strong>' . __( 'Cart Subtotal', 'LION' ) . '</strong> $' . it_cart_buddy_get_cart_subtotal() . '.00';
	$html .= '</p></div>';
	return $html;
}
