<?php
/**
 * This is the default template part for the
 * coupon actions loop in the content-profile
 * template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-profile/actions/ directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_profile_actions_before_loop' ); ?>
	<?php do_action( 'it_exchange_content_profile_actions_begin_loop' ); ?>
	<div class="it-exchange-customer-actions">
		<?php foreach ( it_exchange_get_template_part_slugs( 'content_profile', 'actions', array( 'save' ) ) as $detail ) : ?>
			<?php
			/** 
			 * Theme and add-on devs should add code to this loop by 
			 * hooking into it_exchange_get_template_part_slugs filter
			 * and adding the appropriate template file to their theme or add-on
			*/
			it_exchange_get_template_part( 'content-profile/actions/details/' . $detail );
			?>
		<?php endforeach; ?>
	</div>
	<?php do_action( 'it_exchange_content_profile_actions_end_loop' ); ?>
<?php do_action( 'it_exchange_content_profile_actions_after_loop' ); ?>
