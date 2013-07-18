<?php
/**
 * This is the default template part for the Username field in the content-registration template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_registration_fields_before_username' ); ?>
<div class="user-name">
	<?php it_exchange( 'registration', 'username' ); ?>
</div>
<?php do_action( 'it_exchange_content_registration_fields_after_username' ); ?>
