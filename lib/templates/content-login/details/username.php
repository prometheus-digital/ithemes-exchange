<?php
/**
 * This is the default template part for the empty cart action in the content-cart template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_login_fields_before_username' ); ?>
<div class="user-name">
	<?php it_exchange( 'login', 'username' ); ?>
</div>
<?php do_action( 'it_exchange_content_login_fields_after_username' ); ?>
