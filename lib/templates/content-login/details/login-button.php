<?php
/**
 * This is the default template part for the login-button detail in the content-login template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_login_fields_before_login-button' ); ?>
<?php it_exchange( 'login', 'login-button' ); ?>
<?php do_action( 'it_exchange_content_login_fields_after_login-button' ); ?>
