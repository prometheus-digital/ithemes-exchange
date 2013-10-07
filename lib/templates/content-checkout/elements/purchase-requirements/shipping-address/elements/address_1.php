<?php
/**
 * This is the default template part for the
 * address1 element in the shipping-address
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
<?php do_action( 'it_exchange_content_checkout_shipping_address_purchase_requirement_before_address1_element' ); ?>
<div class="it-exchange-address1 it-exchange-clear-left">
	<?php it_exchange( 'shipping', 'address1' ); ?>
</div>
<?php do_action( 'it_exchange_content_checkout_shipping_address_purchase_requirement_after_address1_element' ); ?>
