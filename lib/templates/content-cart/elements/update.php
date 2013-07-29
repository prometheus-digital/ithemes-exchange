<?php
/**
 * This is the default template part for the
 * update_cart action in the content-cart template
 * part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-cart/elements/ directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_cart_before_update_element' ); ?>
<?php it_exchange( 'cart', 'update' ); ?>
<?php do_action( 'it_exchange_content_cart_after_update_element' ); ?>