<?php
/**
 * This is the default template for the
 * content-login fields loop.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-login/fields directory
 * located in your theme.
*/
?>

<?php foreach( it_exchange_get_template_part_slugs( 'content_login', 'fields', array( 'username', 'password', 'rememberme' ) ) as $detail ) : ?>
	<?php
	/**
	 * Theme and add-on devs should add code to this loop by 
	 * hooking into it_exchange_get_template_part_slugs filter
	 * and adding the appropriate template file to their theme or add-on
	 */
	it_exchange_get_template_part( 'content-login/fields/details/' . $detail );
	?>
<?php endforeach; ?>
