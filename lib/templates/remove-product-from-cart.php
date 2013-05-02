<?php
/**
 * HTML for the link that removes a product from the cart
 * @since 0.3.8
 * @package IT_Exchange
*/
$args            = it_exchange_get_template_part_args( 'remove-product-from-cart' );
$cart_product_id = empty( $args['cart_product_id'] ) ? 0 : $args['cart_product_id'];
$url             = add_query_arg( it_exchange_get_field_name( 'remove_product_from_cart' ), $cart_product_id );
$url             = wp_nonce_url( $url, 'it-exchange-remove-product-from-cart-' . $cart_product_id );
?>
<a href="<?php echo $url; ?>">&times;</a>
