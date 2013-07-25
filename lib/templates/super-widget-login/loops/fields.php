<?php
/**
 * This is the default template for the
 * super-widget-login loops.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-login/loops directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_super_widget_login_before_fields_loop' ); ?>
<?php do_action( 'it_exchange_super_widget_login_begin_actions_loop' ); ?>
<?php foreach( it_exchange_get_template_part_elements( 'super_widget_login', 'fields', array( 'username', 'password', 'rememberme' ) ) as $field ) : ?>
	<?php
	/**
	 * Theme and add-on devs should add code to this loop by 
	 * hooking into it_exchange_get_template_part_elements filter
	 * and adding the appropriate template file to their theme or add-on
	 */
	it_exchange_get_template_part( 'content-login/elements/' . $field );
	?>
<?php endforeach; ?>
<?php do_action( 'it_exchange_super_widget_login_end_actions_loop' ); ?>
<?php do_action( 'it_exchange_super_widget_login_after_actions_loop' ); ?>
