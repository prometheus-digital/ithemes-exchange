<?php
/**
 * These functions print HTML elements for the cart
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * This function returns the HTML for the shopping cart
 *
 * Theme developers may use this to print the shopping cart code.
 * Shopping cart add-on developers will hook to it for their carts.
 * It is also invoked via a shortcode
 *
 * @since 0.3.7
 * @return string html for the shopping cart
*/
function it_cart_buddy_get_shopping_cart_html() {
    $html  = it_cart_buddy_get_errors_div();
    $html .= it_cart_buddy_get_alerts_div();
    
    ob_start();
    it_cart_buddy_get_template_part( 'shopping-cart' );
    $html .= ob_get_clean();
    return $html;
}

/**
 * Retrieves the HTML to remove a product from the cart
 *
 * @since 0.3.7
 * @param mixed $cart_product_id the id of the product in the cart
 * @reuturn string HTML
*/
function it_cart_buddy_get_remove_product_from_cart_link( $cart_product_id ) {

	// Set template part vars
	it_cart_buddy_set_template_part_args( array( 'cart_product_id' => $cart_product_id ), 'remove-product-from-cart' );

	ob_start();
	it_cart_buddy_get_template_part( 'remove-product-from-cart' );
	return ob_get_clean();
}

/**
 * Generates the content for each table cell in the itemized products cart table
 *
 * @since 0.3.8
 * @return string HTML for cell
*/
function it_cart_buddy_get_cart_table_product_data( $column, $product ) {
    $db_product = it_cart_buddy_get_product( $product['product_id'] );
    switch( $column ) {
        case 'product-remove' :
            return it_cart_buddy_get_remove_product_from_cart_link( $product['product_cart_id'] );
            break;
        case 'product-title' :
            return it_cart_buddy_get_cart_product_title( $product );
            break;
        case 'product-cost' :
            $base_price = it_cart_buddy_get_cart_product_base_price( $product );
            $base_price = apply_filters( 'it_cart_buddy_default_shopping_cart_get_product_base_price', $base_price, $product );
            return '$' . $base_price;
            break;
        case 'product-quantity' :
            return '<input type="text" name="product_quantity[' . $product['product_cart_id'] . ']" value="' . it_cart_buddy_get_cart_product_quantity( $product ) . '" size="4"/>';
            break;
        case 'product-subtotal' :
            return '$' . it_cart_buddy_get_cart_product_subtotal( $product );
            break;
    }
}

/**
 * Generates an add to cart button
 *
 * Theme developers may use this to print the add_to_cart HTML
 * It is also invoked via a shortcode
 *
 * Default args
 * - product_id Product to add to the cart. If false and viewing a product page, current product will be used
 * - title Button title
 *
 * @since 0.3.7
 * @param array $args an array of args passed through to the template part
 * @return string HTML for the button
*/
function it_cart_buddy_get_add_product_to_shopping_cart_html( $args=array() ) { 

	// Set some default args for the template part
	$default_args = array(
		'action_var' => it_cart_buddy_get_action_var( 'add_product_to_cart' ),
		'product_id' => false,
		'title'      => __( 'Add to Cart', 'LION' ),
	);

	// Merge defaults with incoming args and set template part args
	$args = wp_parse_args( $args, $default_args );
	it_cart_buddy_set_template_part_args( $args, 'add-product-to-cart-link' );

	// Do templating
	ob_start();
	it_cart_buddy_get_template_part( 'add-product-to-cart' );
	return ob_get_clean();
}
