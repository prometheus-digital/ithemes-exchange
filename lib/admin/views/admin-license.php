<?php
/**
 * This file contains the contents of the Licenses page
 * @since 0.4.14
 * @package IT_Exchange
*/
  ?>
	<div class="wrap">
		<?php
		ITUtility::screen_icon( 'it-exchange' );
		// Print Admin Settings Tabs
		$GLOBALS['IT_Exchange_Admin']->print_general_settings_tabs();
		$license = get_option( 'exchangewp_invoices_license_key' );
		$status  = get_option( 'exchangewp_invoices_status' );
		?>

		<h2>License Keys</h2>
		<p>If you have purchased a licnese key for ExchangeWP, you can enter that below.
			If you'd like to purchase an ExchangeWP license, you can do so
			by <a href="https://exchangewp.com/pricing">going here.</a></p>

		<?php settings_fields('exchangewp_licenses'); ?>

		<table class="form-table">
			<tr>
				<th>Add On</th>
				<th>License Key</th>
				<th>Status</th>
			</tr>
			<!-- Invoices -->
			<?php if (class_exists( 'IT_Exchange_Product_Feature_Invoices' ) ) { ?>
			<tr>
				<td>Invoices</td>
				<td>
					<input id="exchangewp_invoices_license_key" name="exchangewp_invoices_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
					<label class="description" for="exchangewp_invoices_license_key"><?php _e('Enter your license key'); ?></label>
					<?php if( false !== $license ) { ?>
						<?php _e('Activate License'); ?>
					<?php } ?>
				</td>
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
	</div>
	<?php
