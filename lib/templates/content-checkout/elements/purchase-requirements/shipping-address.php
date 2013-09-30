<?php
/**
 * This is the default template part for the core shipping-address
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

// Don't show anything if login-requirement exists and hasn't been met
if ( in_array( 'logged-in', it_exchange_get_pending_purchase_requirements() ) )
	return;
$editing_shipping = ( ! empty( $_REQUEST['it-exchange-update-shipping-address'] ) && ! empty( $GLOBALS['it_exchange']['shipping-address-error'] ) ) ? true : false;
?>
<?php do_action( 'it_exchange_content_checkout_shipping_address_purchase_requirement_before_element' ); ?>
<div class="it-exchange-checkout-shipping-address-purchase-requirement">
	<h3><?php _e( 'Shipping Address', 'LION' ); ?></h3>
	<?php if ( false !== ( $shipping_address = it_exchange_get_customer_shipping_address() ) && empty( $editing_shipping ) ) : ?>
		<div class="checkout-purchase-requirement-shipping-address-options">
			<div class="existing-shipping-address">
				<?php echo it_exchange_get_formatted_shipping_address(); ?>	
			</div>
		</div>
	<?php endif; ?>
	<div class="<?php echo $editing_shipping ? 'it-exchange-hidden ' : ''; ?>checkout-purchase-requirement-shipping-address-options">
		<a class="it-exchange-purchase-requirement-edit-shipping" href="">Edit Shipping Address</a>
	</div>
	<div class="<?php echo $editing_shipping ? '' : 'it-exchange-hidden '; ?>checkout-purchase-requirement-shipping-address-edit">
		<?php
		$loops = array( 'fields', 'actions' );
		?>
		<div class="it-exchange-shipping-address-form">
			<?php 
			do_action( 'it_exchange_content_checkout_shipping_address_purchase_requirement_before_form' );
			?>
			<form action="" method="post" >
				<?php
				do_action( 'it_exchange_content_checkout_shipping_address_purchase_requirement_begin_form' );
				// Include template parts for each of the above loops
				foreach( it_exchange_get_template_part_loops( 'content-checkout/elements/purchase-requirements/shipping-address/loops/', '', $loops ) as $loop ) : 
					it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/shipping-address/loops/' . $loop );
				endforeach;
				do_action( 'it_exchange_content_checkout_shipping_address_purchase_requirement_end_form' );
				?>	
			</form>
		</div>
	</div>
</div>
<div class="it-exchange-clearfix"></div>
<?php do_action( 'it_exchange_content_checkout_shipping_address_purchase_requirement_after_element' ); ?>
