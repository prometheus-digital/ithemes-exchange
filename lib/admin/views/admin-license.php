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

<?php
      $settings = it_exchange_get_option( 'exchangewp_licenses', true );
      $form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
      $form_options = array(
          'id'      => apply_filters( 'it_exchange_licenses', 'it-exchange-licenses-settings' ),
          'enctype' => apply_filters( 'it_exchange_licenses_settings_form_enctype', false ),
          'action'  => 'admin.php?page=it-exchange-settings&tab=license',
      );
      $form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-licenses' ) );

      if ( !empty ( $this->status_message ) )
          ITUtility::show_status_message( $this->status_message );
      if ( !empty( $this->error_message ) )
          ITUtility::show_error_message( $this->error_message );

      ?>
      <div class="wrap">
          <?php screen_icon( 'it-exchange' ); ?>
          <?php do_action( 'it_exchange_paypal-pro_settings_page_top' ); ?>
          <?php do_action( 'it_exchange_addon_settings_page_top' ); ?>

          <?php $form->start_form( $form_options, 'it-exchange-licenses-settings' ); ?>
              <?php do_action( 'it_exchange_licenses_settings_form_top' ); ?>
              <?php get_form_table( $form, $form_values ); ?>
              <?php do_action( 'it_exchange_licenses_settings_form_bottom' ); ?>
              <p class="submit">
                  <?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary button-large' ) ); ?>
              </p>
          <?php $form->end_form(); ?>
          <?php do_action( 'it_exchange_licenses_settings_page_bottom' ); ?>
          <?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
      </div>
      <?php

/**
 * Builds Settings Form Table
 *
 * @since 1.0.0
 */
function get_form_table( $form, $settings = array() ) {

    if ( !empty( $settings ) ) {
        foreach ( $settings as $key => $var ) {
            $form->set_option( $key, $var );
			}
		}

    ?>

    <!-- This is where the form would start for all of the licenses. -->
    <table class="form-table">
      <tbody>
        <tr>
          <th>License key</th>
          <td>
          <?php $form->add_text_box( 'invoice_license' ); ?></td>
        </tr>
      </tbody>
    </table>
        <?php
            // $exchangewp_licenses_options = get_option( 'it-storage-exchange_addon_licenses' );
            // $license = $exchangewp_licenses_options['licenses_license'];
            // var_dump($license);
            // $exstatus = trim( get_option( 'exchange_licenses_license_status' ) );
            // var_dump($exstatus);
         ?>
        <p>
          <label class="description" for="exchange_licenses_license_key"><?php _e('Enter your license key'); ?></label>
          <!-- <?#php $form->add_text_box( 'licenses_license' ); ?>
          <span>
            <?php if( $exstatus !== false && $exstatus == 'valid' ) { ?>
							<span style="color:green;"><?php _e('active'); ?></span>
							<?php wp_nonce_field( 'exchange_licenses_nonce', 'exchange_licenses_nonce' ); ?>
							<input type="submit" class="button-secondary" name="exchange_licenses_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
						<?php } else {
							wp_nonce_field( 'exchange_licenses_nonce', 'exchange_licenses_nonce' ); ?>
							<input type="submit" class="button-secondary" name="exchange_licenses_license_activate" value="<?php _e('Activate License'); ?>"/>
						<?php } ?>
          </span>
        </p> -->
    <?php
}

/**
 * Save settings
 *
 * @since 1.0.0
 * @return void
*/
function save_settings() {
    $defaults = it_exchange_get_option( 'addon_licenses' );
    $new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

    // Check nonce
    if ( !wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-licenses-settings' ) ) {
        $this->error_message = __( 'Error. Please try again', 'LION' );
        return;
    }

    $errors = apply_filters( 'it_exchange_add_on_licenses_validate_settings', $this->get_form_errors( $new_values ), $new_values );
    if ( !$errors && it_exchange_save_option( 'addon_licenses', $new_values ) ) {
        ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
    } else if ( $errors ) {
        $errors = implode( '<br />', $errors );
        $this->error_message = $errors;
    } else {
        $this->status_message = __( 'Settings not saved.', 'LION' );
    }
    // This is for all things licensing check
    // listen for our activate button to be clicked
  	if( isset( $_POST['exchange_licenses_license_activate'] ) ) {

  		// run a quick security check
  	 	if( ! check_admin_referer( 'exchange_licenses_nonce', 'exchange_licenses_nonce' ) )
  			return; // get out if we didn't click the Activate button

  		// retrieve the license from the database
      $exchangewp_licenses_options = get_option( 'it-storage-exchange_addon_licenses' );
      $license = trim( $exchangewp_licenses_options['licenses_license'] );

  		// data to send in our API request
  		$api_params = array(
  			'edd_action' => 'activate_license',
  			'license'    => $license,
  			'item_name'  => urlencode( 'licenses' ), // the name of our product in EDD
  			'url'        => home_url()
  		);

  		// Call the custom API.
  		$response = wp_remote_post( 'https://exchangewp.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

  		// make sure the response came back okay
  		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

  			if ( is_wp_error( $response ) ) {
  				$message = $response->get_error_message();
  			} else {
  				$message = __( 'An error occurred, please try again.' );
  			}

  		} else {

  			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

  			if ( false === $license_data->success ) {

  				switch( $license_data->error ) {

  					case 'expired' :

  						$message = sprintf(
  							__( 'Your license key expired on %s.' ),
  							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
  						);
  						break;

  					case 'revoked' :

  						$message = __( 'Your license key has been disabled.' );
  						break;

  					case 'missing' :

  						$message = __( 'Invalid license.' );
  						break;

  					case 'invalid' :
  					case 'site_inactive' :

  						$message = __( 'Your license is not active for this URL.' );
  						break;

  					case 'item_name_mismatch' :

  						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), 'licenses' );
  						break;

  					case 'no_activations_left':

  						$message = __( 'Your license key has reached its activation limit.' );
  						break;

  					default :

  						$message = __( 'An error occurred, please try again.' );
  						break;
  				}

  			}

  		}

  		// Check if anything passed on a message constituting a failure
  		if ( ! empty( $message ) ) {
  			$base_url = admin_url( 'admin.php?page=' . 'licenses-license' );
  			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

  			// wp_redirect( $redirect );
  			// exit();
        return;
  		}

  		//$license_data->license will be either "valid" or "invalid"
  		update_option( 'exchange_licenses_license_status', $license_data->license );
  		// wp_redirect( admin_url( 'admin.php?page=' . 'licenses-license' ) );
  		// exit();
      return;
  	}

    // deactivate here
    // listen for our activate button to be clicked
  	if( isset( $_POST['exchange_licenses_license_deactivate'] ) ) {

  		// run a quick security check
  	 	if( ! check_admin_referer( 'exchange_licenses_nonce', 'exchange_licenses_nonce' ) )
  			return; // get out if we didn't click the Activate button

  		// retrieve the license from the database
  		// $license = trim( get_option( 'exchange_licenses_license_key' ) );

      $exchangewp_licenses_options = get_option( 'it-storage-exchange_addon_licenses' );
      $license = $exchangewp_licenses_options['licenses_license'];


  		// data to send in our API request
  		$api_params = array(
  			'edd_action' => 'deactivate_license',
  			'license'    => $license,
  			'item_name'  => urlencode( 'licenses' ), // the name of our product in EDD
  			'url'        => home_url()
  		);
  		// Call the custom API.
  		$response = wp_remote_post( 'https://exchangewp.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

  		// make sure the response came back okay
  		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

  			if ( is_wp_error( $response ) ) {
  				$message = $response->get_error_message();
  			} else {
  				$message = __( 'An error occurred, please try again.' );
  			}

  			// $base_url = admin_url( 'admin.php?page=' . 'licenses-license' );
  			// $redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

  			// wp_redirect( 'admin.php?page=licenses-license' );
  			// exit();
        return;
  		}

  		// decode the license data
  		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
  		// $license_data->license will be either "deactivated" or "failed"
  		if( $license_data->license == 'deactivated' ) {
  			delete_option( 'exchange_licenses_license_status' );
  		}

  		// wp_redirect( admin_url( 'admin.php?page=' . 'licenses-license' ) );
  		// exit();
      return;

  	}

}

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 *
 * @since 1.2.2
 */
function exchange_licenses_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;

		}
	}
}
