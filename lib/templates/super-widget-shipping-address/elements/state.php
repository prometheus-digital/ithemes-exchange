<?php
/**
 * This is the default template part for the
 * state element in the shipping-address
 * purchase-requriements in the super-widget-shipping-address template part.
 *
 * @since CHANGEME
 * @version CHANGEME
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the 
 * /exchange/super-widget-shipping-address/elements/
 * directory located in your theme.
*/
?>
<?php do_action( 'it_exchange_super_widget_shipping_address_purchase_requirement_before_state_element' ); ?>
<div class="it-exchange-state">
	<?php it_exchange( 'shipping', 'state' ); ?>
</div>
<?php do_action( 'it_exchange_super_widge_shipping_address_purchase_requirement_after_state_element' ); ?>
