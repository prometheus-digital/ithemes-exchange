<?php
/**
 * This file contains the contents of the Licenses page
 * @since 0.4.14
 * @package IT_Exchange
*/

	global $wp_version;

?>

<?php

$license 	  = get_option( 'exchangewp_license_key' );
$status 	  = get_option( 'exchangewp_license_status' );

?>
<div class="wrap">
	<?php
	ITUtility::screen_icon( 'it-exchange' );
	$this->print_general_settings_tabs();
	do_action( 'it_exchange_general_settings_license_page_top' );
	// $form->start_form( $form_options, 'exchange-email-settings' );
	do_action( 'it_exchange_general_settings_license_form_top' );
	?>

	<table class="form-table">
		<?php do_action( 'it_exchange_general_settings_email_top' ); ?>
		<form method="post" action="admin.php?page=it-exchange-settings&tab=license">
		<?php settings_fields('exchangewp_license_key'); ?>
		<tr valign="top">
			<th scope="row"><strong><?php _e( 'License Key', 'it-l10n-ithemes-exchange' ); ?></strong></th>
			<td>
				<input id="exchangewp_license_key" name="exchangewp_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
				<label class="description" for="exchangewp_license_key"><?php _e('Enter your license key'); ?></label>
			</td>
		</tr>
		<?php if( false !== $license ) { ?>
			<tr valign="top">
				<th scope="row" valign="top">
					<?php _e('Activate License'); ?>
				</th>
				<td>
					<?php if( $status !== false && $status == 'valid' ) { ?>
						<span style="color:green;"><?php _e('active'); ?></span>
						<?php wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
						<input type="submit" class="button-secondary" name="edd_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
					<?php } else {
						wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
						<input type="submit" class="button-secondary" name="edd_license_activate" value="<?php _e('Activate License'); ?>"/>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<?php submit_button(); ?>
</form>
