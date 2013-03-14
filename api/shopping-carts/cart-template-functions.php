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
function it_cart_buddy_get_shopping_cart() {
    $html  = it_cart_buddy_get_errors_div();
    $html .= it_cart_buddy_get_alerts_div();
    
    ob_start();
    it_cart_buddy_get_template_part( 'shopping-cart' );
    $html .= ob_get_clean();
    return $html;
}

/**
 * Generates an add to cart button
 *
 * Theme developers may use this to print the add_to_cart HTML
 * Shopping cart add-on developers will hook to it for their carts.
 * It is also invoked via a shortcode
 *
 * @since 0.3.7
 * @param mixed $product product ID
 * @param array $shortcode_args args passed from WP Shortcode API if function is being invoked by it.
 * @param string $shortcode_content content passed from WP Shortcode API if function is being invoked by it.
 * @return string HTML for the button
*/
function it_cart_buddy_get_add_product_to_shopping_cart_html( $product, $shortcode_args=array(), $shortcode_content=''  ) { 
    return apply_filters( 'it_cart_buddy_get_add_product_to_shopping_cart_html', '', $product, $shortcode_args, $shortcode_content );
}

/**
 * Prints the HTML for the empty cart action
 *
 * This prints HTML for a form element. It is assumed that it will be used inside the shopping cart form
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_get_empty_shopping_cart_html() {
	return apply_filters( 'it_cart_buddy_get_empty_shopping_cart_html', '' );
}

/**
 * Prints the HTML to remove a product from the cart
 *
 * @since 0.3.7
 * @param mixed $cart_product_id the id of the product in the cart
 * @reuturn string HTML
*/
function it_cart_buddy_get_remove_product_from_shopping_cart_html( $cart_product_id ) {
	return apply_filters( 'it_cart_buddy_get_remove_product_from_shopping_cart_html', '', $cart_product_id );
}

/**
 * Prints the HTML for the Update cart action
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_get_update_shopping_cart_html() {
	return apply_filters( 'it_cart_buddy_get_update_shopping_cart_html', '' );
}

/**
 * Prints the HTML for the checkout cart action
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_get_checkout_shopping_cart_html() {
	return apply_filters( 'it_cart_buddy_get_checkout_shopping_cart_html', '' );
}

/**
 * Returns columns for the shopping cart table
 *
 * @since 0.3.8
 * @return array column slugs / labels
*/
function it_cart_buddy_get_cart_table_columns() {
    $columns = array(
        'product-remove'   => '', 
        'product-title'    => __( 'Product', 'LION' ),
        'product-cost'     => __( 'Price', 'LION' ),
        'product-quantity' => __( 'Quantity', 'LION' ),
        'product-subtotal' => __( 'Total', 'LION' ),
    );  
    return apply_filters( 'it_cart_buddy_get_cart_table_columns', $columns );
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
            return it_cart_buddy_get_remove_product_from_shopping_cart_html( $product['product_cart_id'] );
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
