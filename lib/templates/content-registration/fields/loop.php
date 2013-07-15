<?php
/**
 * This is the default template part for the
 * coupon details loop in the content-registration
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

<?php do_action( 'it_exchange_content_registration_fields_before_loop' ); ?>
	<?php do_action( 'it_exchange_content_registration_fields_begin_loop' ); ?>
	<div class="customer-info">
		<?php foreach ( it_exchange_get_content_registration_field_details() as $detail ) : ?>
			<?php
			/** 
			 * Theme and add-on devs should add code to this loop by 
			 * hooking into it_exchange_get_content_profile_fields_details filter
			 * and adding the appropriate template file to their theme or add-on
			*/
			it_exchange_get_template_part( 'content-registration/fields/details/' . $detail ); ?>
		<?php endforeach; ?>
	</div>
   	<?php do_action( 'it_exchange_content_registration_fields_end_loop' ); ?>
<?php do_action( 'it_exchange_content_registration_fields_after_loop' ); ?>
