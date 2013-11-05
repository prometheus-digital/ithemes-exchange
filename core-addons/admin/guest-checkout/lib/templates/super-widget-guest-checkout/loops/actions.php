<?php
/**
 * This is the default template for the
 * super-widget-guest-checkout actions loop.
 *
 * @since CHANGEME
 * @version CHANGEME
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-guest-checkout/loops directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_super_widget_guest_checkout_before_actions_loop' ); ?>
<?php do_action( 'it_exchange_super_widget_guest_checkout_begin_actions_loop' ); ?>
<?php foreach ( it_exchange_get_template_part_elements( 'super_widget_guest_checkout', 'actions', array( 'save', 'cancel' ) ) as $action ) : ?>
	<?php
	/**
	 * Theme and add-on devs should add code to this loop by
	 * hooking into it_exchange_get_template_part_elements filter
	 * and adding the appropriate template file to their theme or add-on
	 */
	it_exchange_get_template_part( 'super-widget-guest-checkout/elements/' . $action );
	?>
<?php endforeach; ?>
<?php do_action( 'it_exchange_super_widget_guest_checkout_end_actions_loop' ); ?>
<?php do_action( 'it_exchange_super_widget_guest_checkout_after_actions_loop' ); ?>
