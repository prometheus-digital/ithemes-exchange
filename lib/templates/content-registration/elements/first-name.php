<?php
/**
 * This is the default template part for the Firstname field in the content-registration template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_registration_fields_before_first-name' ); ?>
<div class="first-name">
	<?php it_exchange( 'registration', 'first-name' ); ?>
</div>
<?php do_action( 'it_exchange_content_registration_fields_after_first-name' ); ?>
