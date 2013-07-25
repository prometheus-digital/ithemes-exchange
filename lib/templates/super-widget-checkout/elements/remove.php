<?php
/**
 * This is the default template for the 
 * super-widget-checkout renive element.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-checkout/elements directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_super_widget_checkout_before_remove_element' ); ?>
<?php it_exchange( 'cart-item', 'remove' ); ?>
<?php do_action( 'it_exchange_super_widget_checkout_after_remove_element' ); ?>
