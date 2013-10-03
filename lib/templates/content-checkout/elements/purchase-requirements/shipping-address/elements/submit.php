<?php
/**
 * This is the default template part for the
 * submit action in the shipping-address
 * purchase-requriements in the content-checkout template part.
 *
 * @since CHANGEME
 * @version CHANGEME
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the 
 * /exchange/content-checkout/elements/purchase-requirements/shipping-address/elements/
 * directory located in your theme.
*/
?>
<?php do_action( 'it_exchange_content_checkout_shipping_address_purchase_requirement_before_submit_element' ); ?>
<div class="it-exchange-submit it-exchange-clear-left">
	<?php it_exchange( 'shipping', 'submit' ); ?>
</div>
<?php do_action( 'it_exchange_content_checkout_shipping_address_purchase_requirement_after_submit_element' ); ?>
