<?php
/**
 * HTML for the link that removes a product from the cart
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/
global $cart_product_id;
$url = add_query_arg( it_cart_buddy_get_action_var( 'remove_product_from_cart' ), $cart_product_id );
$url = wp_nonce_url( $url, 'it_cart_buddy_remove_product_from_cart-' . $cart_product_id );
?>
<a href="<?php echo $url; ?>">&times;</a>
