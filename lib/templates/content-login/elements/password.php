<?php
/**
 * This is the default template part for the
 * password element in the content-login template
 * part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-login/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_login_fields_before_password' ); ?>
<div class="it-exchange-password">
	<?php it_exchange( 'login', 'password' ); ?>
</div>
<?php do_action( 'it_exchange_content_login_fields_after_password' ); ?>