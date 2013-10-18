<?php
/**
 * This is the default template part for the
 * options element in the not-logged-in loop for the 
 * purchase-requriements in the content-checkout 
 * template part.
 *
 * @since CHANGEME
 * @version CHANGEME
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the 
 * /exchange/content-checkout/elements/purchase-requirements/logged-in/elements/not-logged-in
 * directory located in your theme.
*/
?>
<?php do_action( 'it_exchange_content_checkout_logged_in_purchase_requirement_not_logged_in_before_options_element' ); ?>
<div class="checkout-purchase-requirement-login-options">
	<?php do_action( 'it_exchange_content_checkout_logged_in_purchase_requirement_not_logged_in_begin_options_element' ); ?>
	<a class="it-exchange-login-requirement-registration" href="<?php echo it_exchange_get_page_url( 'registration' ); ?>">Register</a> |
	<a class="it-exchange-login-requirement-login" href="<?php echo it_exchange_get_page_url( 'login' ); ?>">Log in</a>
	<?php do_action( 'it_exchange_content_checkout_logged_in_purchase_requirement_not_logged_in_end_options_element' ); ?>
</div>
<?php do_action( 'it_exchange_content_checkout_logged_in_purchase_requirement_not_logged_in_after_options_element' ); ?>