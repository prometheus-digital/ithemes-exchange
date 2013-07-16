<?php
/**
 * This is the default template part for the
 * login-button detail in the content-login
 * template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-login/actions/details
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_login_actions_before_login-button' ); ?>
<div class="it-exchange-login-button">
	<?php it_exchange( 'login', 'login-button' ); ?>
</div>
<?php do_action( 'it_exchange_content_login_actions_after_login-button' ); ?>
