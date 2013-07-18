<?php
/**
 * This is the default template part for the
 * apply_coupon action in the content-checkout
 * template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-checkout/totals/ directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_checkout_actions_before_loop' ); ?>
<?php foreach ( it_exchange_get_template_part_elements( 'content_checkout', 'actions', array( 'transaction-methods', 'cancel' ) ) as $detail ) : ?>
		<?php
		/** 
		 * Theme and add-on devs should add code to this loop by 
		 * hooking into it_exchange_get_template_part_elements filter
		 * and adding the appropriate template file to their theme or add-on
		*/
		it_exchange_get_template_part( 'content-checkout/actions/details/' . $detail );
		?>
<?php endforeach; ?>
<?php do_action( 'it_exchange_content_checkout_actions_after_loop' ); ?>