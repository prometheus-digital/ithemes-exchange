<?php
/**
 * This is the default template part for the
 * lastname element in the content-profile template
 * part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-profile/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_profile_before_last_name_element' ); ?>
<div class="it-exchange-customer-last-name">
	<?php it_exchange( 'customer', 'last-name' ); ?>
</div>
<?php do_action( 'it_exchange_content_profile_after_last_name_element' ); ?>
