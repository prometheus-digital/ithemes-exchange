<?php
/**
 * Random utility functions for the default shopping cart add-on
 *
 * @since 0.3.7
 * @package IT_Cart_Buddy
*/

/**
 * Returns columns for the shopping cart HTML table
 *
 * @since 0.3.7
 * @return array column slugs / labels
*/
function it_cart_buddy_default_shopping_cart_get_table_columns() {
	$columns = array(
		'product-remove'   => '',
		'product-title'    => __( 'Product', 'LION' ),
		'product-cost'     => __( 'Price', 'LION' ),
		'product-quantity' => __( 'Quantity', 'LION' ),
		'product-subtotal' => __( 'Total', 'LION' ),
	);
	return apply_filters( 'it_cart_buddy_default_shopping_cart_get_table_columns', $columns );
}
