<?php
/**
 * This is the default template part for the rememberme field in the content-login template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_login_fields_before_rememberme' ); ?>
<div class="rememberme">
	<?php it_exchange( 'login', 'rememberme' ); ?>
</div>
<?php do_action( 'it_exchange_content_login_fields_after_rememberme' ); ?>
