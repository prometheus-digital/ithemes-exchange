<?php
/**
 * This is the default template part for the
 * coupon details loop in the content-profile
 * template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-profile/fields/ directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_profile_fields_before_loop' ); ?>
	<?php do_action( 'it_exchange_content_profile_fields_begin_loop' ); ?>
	<div class="it-exchange-customer-info">
		<?php foreach ( it_exchange_get_template_part_slugs( 'content_profile', 'fields', array( 'first-name', 'last-name', 'email', 'website', 'password1', 'password2' ) ) as $detail ) : ?>
			<?php
			/** 
			 * Theme and add-on devs should add code to this loop by 
			 * hooking into it_exchange_get_template_part_slugs filter
			 * and adding the appropriate template file to their theme or add-on
			*/
			it_exchange_get_template_part( 'content-profile/fields/details/' . $detail );
			?>
		<?php endforeach; ?>
	</div>
	<?php do_action( 'it_exchange_content_profile_fields_end_loop' ); ?>
<?php do_action( 'it_exchange_content_profile_fields_after_loop' ); ?>
