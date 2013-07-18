<?php
/**
 * This is the default template part for the login field in the content-registration template part
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_registration_fields_before_login' ); ?>
&nbsp;<a href="<?php esc_attr_e( it_exchange_get_page_url( 'login' ) ); ?>"><?php _e( 'Log in', 'LION' ); ?></a>
<?php do_action( 'it_exchange_content_registration_fields_after_login' ); ?>
