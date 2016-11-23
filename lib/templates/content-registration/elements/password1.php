<?php
/**
 * This is the default template part for the
 * password1 element in the content-registration
 * template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-registration/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_registration_fields_before_password1' ); ?>
<div class="it-exchange-registration-password1">
	<?php it_exchange( 'registration', 'password1' ); ?>
</div>
<?php do_action( 'it_exchange_content_registration_fields_after_password1' ); ?>
