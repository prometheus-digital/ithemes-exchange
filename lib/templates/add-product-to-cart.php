<?php
/**
 * The form for adding a product to the cart
 * @since 0.3.8
 * @package IT_Exchange
*/
// Get args for template part
$args = it_exchange_get_template_part_args( 'add-product-to-cart-link' );

// Don't print anything if we weren't able to get an iThemes Exchange product id
if ( ! $args['product_id'] )
	return;
?>
<?php do_action( 'it_exchange_add_product_to_cart_form_before', $args['product_id'] ); ?>
<form action="" method="post">
	<input type="hidden" name="<?php esc_attr_e( $args['action_var'] ); ?>" value="<?php esc_attr_e( $args['product_id'] ); ?>" />
	<?php do_action( 'it_exchange_add_product_to_cart_form_top', $args['product_id'] ); ?>
	<input type="submit" name="" value="<?php  esc_attr_e( $args['title'] ); ?>" />
	<?php do_action( 'it_exchange_add_product_to_cart_form_bottom', $args['product_id'] ); ?>
	<?php wp_nonce_field( 'it-exchange-add-product-to-cart-' . $args['product_id'] ); ?>
</form>
<?php do_action( 'it_exchange_add_product_to_cart_form_after', $args['product_id'] ); ?>
