<?php
/**
 * This is the default template for the content-login fields loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php foreach( it_exchange_get_content_login_field_details() as $detail ) : ?>
	<?php 
	/** 
	 * Theme and add-on devs should add code to this loop by 
	 * hooking into it_exchange_get_content_login_field_details filter
	 * and adding the appropriate template file to their theme or add-on
	 */
	it_exchange_get_template_part( 'content-login/fields/details/' . $detail ); ?>
<?php endforeach; ?>
