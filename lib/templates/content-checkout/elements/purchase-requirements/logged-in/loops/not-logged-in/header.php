<?php
/**
 * This is the default template part for the
 * header loop in the not-logged-in detail for the logged-in
 * purchase-requriements in the content-checkout 
 * template part.
 *
 * @since 1.5.0
 * @version 1.0.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the 
 * /exchange/content-checkout/elements/purchase-requirements/logged-in/loops/not-logged-in
 * directory located in your theme.
*/
?>
<?php do_action( 'it_exchange_content_checkout_logged_in_purchase_requirement_not_logged_in_before_header_loop' ); ?>
<?php foreach( it_exchange_get_template_part_elements( 'content-checkout-logged-in-purchase-requirements-not-logged-in', 'header', array( 'heading' ) ) as $element ) : ?>
	<?php
	/**
	 * Theme and add-on devs should add code to this loop by 
	 * hooking into it_exchange_get_template_part_elements filter
	 * and adding the appropriate template file to their theme or add-on
	 */
	it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/logged-in/elements/not-logged-in/' . $element );
	?>
<?php endforeach; ?>
<?php do_action( 'it_exchange_content_checkout_logged_in_purchase_requirement_not_logged_in_after_header_loop' ); ?>
