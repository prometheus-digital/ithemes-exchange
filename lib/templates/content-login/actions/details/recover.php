<?php
/**
 * This is the default template part for the Recover Password detail in the content-login template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_login_fields_before_recover' ); ?>
<div class="recover_url">
	<?php it_exchange( 'login', 'recover' ); ?>
</div>
<?php do_action( 'it_exchange_content_login_fields_after_recover' ); ?>
