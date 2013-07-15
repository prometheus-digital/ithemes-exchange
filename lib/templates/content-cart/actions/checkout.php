<?php
/**
 * This is the default template part for the checkout action in the content-cart template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_cart_actions_before_checkout' ); ?>
<?php it_exchange( 'cart', 'checkout' ); ?>
<?php do_action( 'it_exchange_content_cart_actions_after_checkout' ); ?>
