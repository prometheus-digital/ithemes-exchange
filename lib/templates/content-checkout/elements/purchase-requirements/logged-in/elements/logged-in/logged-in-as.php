<?php
/**
 * This is the default template part for the
 * logged_in_as element in the logged-in loop for the
 * purchase-requriements in the content-checkout
 * template part.
 *
 * @since 1.5.0
 * @version 1.2.1
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/content-checkout/elements/purchase-requirements/logged-in/elements/logged-in
 * directory located in your theme.
*/
?>
<?php do_action( 'it_exchange_content_checkout_logged_in_purchase_requirement_logged_in_before_logged_in_as_element' ); ?>
<div class="it-exchange-logged-in-purchase-requirement-logged-in-logged-in-as">
	<?php
	$display_name = it_exchange( 'customer', 'get-display-name', array( 'format' => 'field-value' ) );
	printf( __( 'Checking out as: %s', 'LION' ), $display_name );
	?>
</div>
<?php do_action( 'it_exchange_content_checkout_logged_in_purchase_requirement_logged_in_after_logged_in_as_element' ); ?>
