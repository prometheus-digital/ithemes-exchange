<?php
/**
 * This is the default template part for the cart
 * purchase requirements loop. Exchange core doesn't
 * place anything in here but add-ons like shipping
 * taxing will use it.
 *
 * @since 1.1.3
 * @version 1.1.3
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-checkout/loops/ directory
 * located in your theme.
*/
?>
<?php if ( it_exchange_get_purchase_requirements() ) : ?>
	<?php do_action( 'it_exchange_content_checkout_before_purchase_requirements' ); ?>
	<div id="it-exchange-checkout-purchase-requirements" class="<?php echo ( ! is_user_logged_in() ) ? ' it-exchange-requirements-active' : ''; ?>">
		<?php do_action( 'it_exchange_content_checkout_before_purchase_requirements_loop' ); ?>
		<?php
		/* This loop is a bit different because we are asking add-ons to provide the list of element items by
		   registering them as purchase requirements. */
		$purchase_requirement_template_elements = (array) it_exchange_get_all_purchase_requirement_checkout_element_template_parts();
		?>  
		<?php foreach ( it_exchange_get_template_part_elements( 'content_checkout', 'purchase_requirements', $purchase_requirement_template_elements ) as $item ) : ?>
			<?php
			/** 
			 * Theme and add-on devs should add code to this loop by 
			 * hooking into it_exchange_get_template_part_elements filter
			 * and adding the appropriate template file to their theme or add-on
			 */
			it_exchange_get_template_part( 'content-checkout/elements/purchase-requirements/' . $item );
			?>  
		<?php endforeach; ?>
		<?php do_action( 'it_exchange_content_content_checkout_after_purchase_requirements_loop' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_checkout_after_purchase_requirements' ); ?>
<?php endif; ?>
