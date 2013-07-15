<?php
/**
 * This is the default template part for the password2 field in the content-customer template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_customer_fields_before_password2' ); ?>
<div class="password2">
	<?php it_exchange( 'customer', 'password2' ); ?>
</div>
<?php do_action( 'it_exchange_content_customer_fields_after_password2' ); ?>
