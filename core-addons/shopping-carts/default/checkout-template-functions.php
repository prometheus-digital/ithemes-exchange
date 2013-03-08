<?php
/**
 * This file contains functions for the Checkout page
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * This is a high level template tag that prints the entire checkout page
 *
 * Theme authors can include this in a page template or use many of the lower-level 
 * functions on this page to build their own cart page.
 *
 * This function is also called by the default cartbuddy cart shortcode
 *
 * @since 0.3.7
 * @param array $shortcode_atts atts passed from WP Shortcode API if function is being invoked by it.
 * @param string $shortcode_content content passed from WP Shortcode API if function is being invoked by it.
 * @return string html for the shopping cart
*/
function it_cart_buddy_default_cart_get_checkout_html( $shortcode_atts=array(), $shortcode_content='' ) {
	$html  = it_cart_buddy_get_errors_div();
	$html .= apply_filters( 'it_cart_buddy_default_shopping_cart_checkout_html_top', '' );

	// Display the Login form if user is not logged in
	if ( ! is_user_logged_in() )
		$html .= it_cart_buddy_get_customer_login_form();

	$html .= it_cart_buddy_get_cart_checkout_form_open_html();
	$html .= it_cart_buddy_get_cart_checkout_customer_form_fields();
	$html .= it_cart_buddy_get_cart_checkout_order_summary();
	$html .= apply_filters( 'it_cart_buddy_default_cart_checkout_before_place_order_html', '' );
	$html .= it_cart_buddy_get_cart_checkout_place_order();
	$html .= it_cart_buddy_get_cart_checkout_form_close_html();
	$html .= apply_filters( 'it_cart_buddy_default_shopping_cart_checkout_html_bottom', '' );
	return apply_filters( 'it_cart_buddy_default_shopping_cart_checkout_html', $html );
}

/**
 * Prints the Customer checkout form fields
 *
 * @since 0.3.7
 * @return string HTML
*/
function it_cart_buddy_default_cart_get_checkout_customer_form_fields() {
	$form = new ITForm();
	$fields = it_cart_buddy_get_customer_profile_fields();
	$html = '';
	$customer = it_cart_buddy_get_current_customer();

	foreach( (array) $fields as $field => $args ) {
		$function = 'get_' . $args['type'];
		$var      = empty( $args['var'] ) ? '' : $args['var'];
		$values   = empty( $args['values'] ) ? array() : (array) $args['values'];
		$label    = empty( $args['label'] ) ? '' : $args['label'];
		$value    = empty( $customer[$var] ) ? '' : esc_attr( $customer[$var] );
		$values['value'] = stripslashes( $value );
		if ( is_callable( array( $form, $function ) ) ) {
			$html .= '<p class="cart_buddy_profile_field">';
			$html .= '<label for="' . esc_attr( $var ) . '">' . $label . '</label>';
			$html .= $form->$function( $var, $values );
			$html .= '</p>';
		} else if ( 'password' == $args['type'] ) {
			$values['type'] = 'password';
			$html .= '<p class="cart_buddy_profile_field">';
			$html .= '<label for="' . esc_attr( $var ) . '">' . $label . '</label>';
			$html .= $form->_get_simple_input( $var, $values, false );
			$html .= '</p>';
		}
	}
	return apply_filters( 'it_cart_buddy_default_cart_get_checkout_customer_form_fields', $html );
}

/**
 * Prints the order summary div
 *
 * @since 0.3.7
 * @param string $summary value passed through by WP filter API. Discarded here.
 * @return html 
*/
function it_cart_buddy_default_cart_get_checkout_order_summary( $summary='' ) {
	$html  = '';
	$html .= '<div id="it_cart_buddy_checkout_order_summary">';
	$html .= '<h3>' . __( 'Order Summary', 'LION' ) . '</h3>';
	$html .= '<table>';
	$html .= '<tr><th>Product</th><th>Quantity</th><th>Totals</th></tr>';
	$html .= it_cart_buddy_default_cart_get_checkout_order_summary_product_table_rows();
	$html .= it_cart_buddy_default_cart_get_checkout_order_summary_cart_subtotal_table_row();
	$html .= apply_filters( 'it_cart_buddy_default_cart_before_cart_total_table_row', '' );
	$html .= it_cart_buddy_default_cart_get_checkout_order_summary_cart_total_table_row();
	$html .= '</table>';
	$html .= '</div>';
	return apply_filters( 'it_cart_buddy_default_cart_checkout_order_summary', $html );
}

/**
 * Returns HTML of rows for order summary table on checkout page
 *
 * @since 0.3.7
 * @return HTML
*/
function it_cart_buddy_default_cart_get_checkout_order_summary_product_table_rows() {
	$products = it_cart_buddy_get_cart_products();
	$html = '';
	foreach( (array) $products as $product ) {
		$row  = '<tr>';
		$row .= '<td>' . it_cart_buddy_get_cart_product_title( $product ) . '</td>';
		$row .= '<td>' . it_cart_buddy_get_cart_product_quantity( $product ) . '</td>';
		$row .= '<td>' . it_cart_buddy_get_cart_product_subtotal( $product ) . '</td>';
		$row .= '</tr>';
		$html .=apply_filters( 'it_cart_buddy_default_cart_checkout_order_summary_product_table_row', $row, $product );
	}
	return apply_filters( 'it_cart_buddy_default_cart_checkout_order_summary_product_table_rows', $html );
}

/**
 * Returns the subtotal of the cart
 *
 * @since 0.3.7
 * @return mixed subtotal for the cart
*/
function it_cart_buddy_default_cart_get_checkout_order_summary_cart_subtotal_table_row() {
	$html  = '<tr>';
	$html .= '<td colspan=2>' . __( 'Cart Subtotal', 'LION' ) . '</td>';
	$html .= '<td>' . it_cart_buddy_get_cart_subtotal() . '</td>';
	$html .= '</tr>';
	return apply_filters( 'it_cart_buddy_default_cart_checkout_order_summary_cart_subtotal_table_row', $html );
}

/**
 * Returns the cart total table row
 *
 * @since 0.3.7
 * @return html table row for cart total
*/
function it_cart_buddy_default_cart_get_checkout_order_summary_cart_total_table_row() {
	$html  = '<tr>';
	$html .= '<td colspan=2>' . __( 'Order Total', 'LION' ) . '</td>';
	$html .= '<td>' . it_cart_buddy_get_cart_total() . '</td>';
	$html .= '</tr>';
	return apply_filters( 'it_cart_buddy_default_cart_checkout_order_summary_cart_total_table_row', $html );
}

/**
 * Returns the HTML for the place order form. Includes transaction_method selection
 *
 * @since 0.3.7
 * @return HTML
*/
function it_cart_buddy_default_cart_get_checkout_place_order() {
	$html  = '<h3>' . __( 'Payment Method', 'LION' ) . '</h3>';
	$html .= '<div id="it-cart-buddy-checkout-place-order-form">';
	$html .= it_cart_buddy_default_cart_get_checkout_transaction_method_option_fields();
	$html .= '</p>';
	$html .= it_cart_buddy_get_cart_checkout_order_button();
	$html .= '&nbsp;<a href="' . it_cart_buddy_get_page_url( 'cart' ) . '">' . __( 'Back to cart', 'LION' ) . '</a>';
	$html .= '</div>';
	return apply_filters( 'it_cart_buddy_default_cart_get_checkout_place_order_html', $html );
}

/**
 * Returns the opening form tag
 *
 * @since 0.3.7
 * @return HTML
*/
function it_cart_buddy_default_cart_get_checkout_form_open_html() {
    $action = it_cart_buddy_get_page_url( 'checkout' );
	$html = '<form action="' . $action . '" method="post" >';
	return apply_filters( 'it_cart_buddy_default_cart_get_checkout_form_open_html', $html );
}

/**
 * Returns the closing form tag
 *
 * @since 0.3.7
 * @return HTML
*/
function it_cart_buddy_default_cart_get_checkout_form_close_html() {
	$html = '</form>';
	return apply_filters( 'it_cart_buddy_default_cart_get_checkout_form_close_html', $html );
}

/**
 * Returns radio buttons for checkout method
 *
 * @since 0.3.7
 * @return string HTML form element
*/
function it_cart_buddy_default_cart_get_checkout_transaction_method_option_fields() {
	if ( ! $transaction_methods = it_cart_buddy_get_enabled_addons( array( 'category' => 'transaction-methods' ) ) )
		return '<p>' . __( 'No payment add-ons enabled!', 'LION' ) . '</p>';

	if ( 1 === count( $transaction_methods ) ) {
		$method = reset( $transaction_methods );
		$html  = '<p>' . it_cart_buddy_get_transaction_method_name( $method['slug'] ) . '</p>';
		$html .= '<input type="hidden" name="' . esc_attr( it_cart_buddy_get_action_var( 'transaction_method' ) ) . '" value="' . esc_attr( $method['slug'] ) . '" />';
	} else {
		$radios = array();
		foreach( (array) $transaction_methods as $method ) {
			$radio = '<label for="transaction-method-' . esc_attr( $method['slug'] ) . '">';
			$radio .= '<input type="radio" id="transaction-method-' . esc_attr( $method['slug'] ) . '" name="' . it_cart_buddy_get_action_var( 'transaction_method' ) . '" value="' . esc_attr( $method['slug'] ) . '" />';
			$radio .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . esc_html( it_cart_buddy_get_transaction_method_name( $method['slug'] ) );
			$radio .= '</label>';
			$radios[] = $radio;
		}

		$html  = '<p>' . __( 'Choose a payment method', 'LION' ) . '</p>';
		$html .= implode( $radios, '<br />' );
	}
	return apply_filters( 'it_cart_buddy_default_cart_get_checkout_transaction_method_option_fields', $html );
}

/**
 * Returns the checkout button
 *
 * @since 0.3.7
 * @return string HTML button
*/
function it_cart_buddy_default_cart_get_checkout_order_button() {
	$html  = '<input type="hidden" name="' . esc_attr( it_cart_buddy_get_action_var( 'purchase_cart' ) ) . '" value=1 />';
	$html .= '<input type="submit" name="it_cart_buddy_place_order" value="' . __( 'Place Order', 'LION' ) . '" />';
	return apply_filters( 'it_cart_buddy_default_cart_get_checkout_order_button', $html );
}
