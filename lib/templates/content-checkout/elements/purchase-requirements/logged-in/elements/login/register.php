<?php
/**
 * This is the default template part for the
 * cancel element in the login loop for the 
 * purchase-requriements in the content-checkout 
 * template part.
 *
 * @since 1.2.0
 * @version 1.2.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the 
 * /exchange/content-checkout/elements/purchase-requirements/logged-in/elements/login
 * directory located in your theme.
*/
?>
<?php do_action( 'it_exchange_content_checkout_logged_in_purchase_requirement_login_before_register_element' ); ?>
<div class="it-exchange-register-url">
	<a class="it-exchange-login-requirement-registration" href="<?php echo it_exchange_get_page_url( 'registration' ); ?>"><?php _e( 'Register', 'LION' ); ?></a>
</div>
<?php do_action( 'it_exchange_content_checkout_logged_in_purchase_requirement_login_after_register_element' ); ?>
