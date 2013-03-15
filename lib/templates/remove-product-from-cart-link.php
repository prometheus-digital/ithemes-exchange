<?php
/**
 * HTML for the link that removes a product from the cart
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/
global $cart_product_id;
$var = it_cart_buddy_get_action_var( 'remove_product_from_cart' );
?>
<a href="<?php echo add_query_arg( esc_attr( $var ), $cart_product_id ); ?>">&times;</a>
