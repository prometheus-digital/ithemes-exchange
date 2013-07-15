<?php
/**
 * This is the default template part for the Lastname field in the content-profile template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_customer_fields_before_last-name' ); ?>
<div class="last-name">
	<?php it_exchange( 'customer', 'last-name' ); ?>
</div>
<?php do_action( 'it_exchange_content_customer_fields_after_last-name' ); ?>
