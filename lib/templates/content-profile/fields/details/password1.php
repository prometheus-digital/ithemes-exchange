<?php
/**
 * This is the default template part for the password1 field in the content-profile template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_customer_fields_before_password1' ); ?>
<div class="password1">
	<?php it_exchange( 'customer', 'password1' ); ?>
</div>
<?php do_action( 'it_exchange_content_customer_fields_after_password1' ); ?>
