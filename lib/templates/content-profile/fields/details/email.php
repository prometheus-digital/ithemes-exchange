<?php
/**
 * This is the default template part for the email field in the content-profile template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_customer_fields_before_email' ); ?>
<div class="customer-email">
	<?php it_exchange( 'customer', 'email' ); ?>
</div>
<?php do_action( 'it_exchange_content_customer_fields_after_email' ); ?>
