<?php
/**
 * Default Cart Buddy Shopping Cart
 *
 * @since 0.3.0
 * @package IT_Cart_Buddy
*/

/**
 * Main Class for default Shopping Cart plugin. 
 *
 * Registers all hooks needed for printing shopping cart and for interacting with other add-ons
 *
 * @since 0.3.7
*/
class IT_Cart_Buddy_Add_On_Default_Shopping_Cart {
	
	/**
	 * Constructor. Register's hooks
	 *
	 * @since 0.3.7
	 * @return void
	*/
	function IT_Cart_Buddy_Add_On_Default_Shopping_Cart() {

		if ( ! is_admin() ) {
			// Frontend
			add_action( 'it_cart_buddy_print_shopping_cart-default-shopping-cart', array( $this, 'print_shopping_cart' ) );
			add_action( 'it_cart_buddy_get_shopping_cart_html-default-shopping-cart', array( $this, 'get_shopping_cart_html' ) );
		} else {
			// Backend
		}
	}

	/**
	 * Prints the shopping cart
	 *
	 * @since 0.3.7
	 * @return void
	*/
	function print_shopping_cart() {
		echo $this->get_shopping_cart_html();
	}

	/**
	 * Returns the shopping cart HTML 
	 *
	 * @since 0.3.7
	 * @return string html for the shopping cart 
	*/
	function get_shopping_cart_html() {

		$products = it_cart_buddy_get_session_products();

		$html = '';
		$html .= apply_filters( 'it_cart_buddy_default_shopping_cart-above_cart_div', '' );
		$html .= '<div class="it_cart_buddy_default_shopping_cart">';
		if ( count( $products ) < 1 ) {
			$html .= apply_filters( 'it_cart_buddy_default_shopping_cart-no_items_in_cart', '<p>' . __( 'You have no items in your cart', 'LION' ) . '</p>' );
		} else {
			$html .= "<p>This is a shopping cart</p>";
		}
		$html .= '</div>';
		$html .= apply_filters( 'it_cart_buddy_default_shopping_cart-below_cart_div', '' );

		return $html;
	}
}
$IT_Cart_Buddy_Add_On_Default_Shopping_Cart = new IT_Cart_Buddy_Add_On_Default_Shopping_Cart();
