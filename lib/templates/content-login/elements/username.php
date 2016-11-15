<?php
/**
 * This is the default template part for the
 * empty cart element in the content-cart template
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

<?php do_action( 'it_exchange_content_login_before_username_element' ); ?>
<div class="it-exchange-username">
	<?php it_exchange( 'login', 'username' ); ?>
</div>
<?php do_action( 'it_exchange_content_login_after_username_element' ); ?>
