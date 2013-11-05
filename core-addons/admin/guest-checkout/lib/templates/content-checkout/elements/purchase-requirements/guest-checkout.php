<?php
/**
 * This is the default template part for the guest-checkout
 * purchase requirement element in the content-checkout
 * template part.
 *
 * @since CHANGEME
 * @version CHANGEME
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-checkout/elements/purchase-requirements directory
 * located in your theme.
*/
?>
<?php do_action( 'it_exchange_content_checkout_guest_checkout_purchase_requirement_before_element' ); ?>
<div class="it-exchange-checkout-guest-checkout-purchase-requirement">
	<h3><?php _e( 'Customer Information', 'LION' ); ?></h3>
	<div class="it-exchange-guest-checkout-form-wrapper it-exchange-clearfix">
		<?php do_action( 'it_exchange_content_checkout_guest_checkout_purchase_requirement_before_form' ); ?>
		<form action="" method="post" >
			<?php
			do_action( 'it_exchange_content_checkout_guest_checkout_purchase_requirement_begin_form' );
			// Include template parts for each of the above loops
			$loops = array( 'fields', 'actions' );
			foreach( it_exchange_get_template_part_loops( 'content-checkout/elements/purchase-requirements/guest-checkout/loops/', '', $loops ) as $loop ) : 
				it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/guest-checkout/loops/' . $loop );
			endforeach;
			do_action( 'it_exchange_content_checkout_guest_checkout_purchase_requirement_end_form' );
			?>	
		</form>
		<?php do_action( 'it_exchange_content_checkout_guest_checkout_purchase_requirement_after_form' ); ?>
	</div>
</div>
<div class="it-exchange-clearfix"></div>
<?php do_action( 'it_exchange_content_checkout_guest_checkout_purchase_requirement_after_element' ); ?>