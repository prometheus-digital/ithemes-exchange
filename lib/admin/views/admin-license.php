<?php
/**
 * This file contains the contents of the Licenses page
 * @since 0.0.1
 * @package IT_Exchange
*/


$IT_Exchange_Licensing = new IT_Exchange_Licensing();
$IT_Exchange_Licensing->print_settings_page();


class IT_Exchange_Licensing {

  /**
   * @var boolean $_is_admin true or false
   * @since 0.0.1
  */
  var $_is_admin;

  /**
   * @var string $_current_page Current $_GET['page'] value
   * @since 0.0.1
  */
  var $_current_page;

  /**
	 * @var string $_current_tab
	 * @since 0.0.1
	*/
	var $_current_tab;

  /**
   * @var string $_current_add_on Current $_GET['add-on-settings'] value
   * @since 0.0.1
  */
  var $_current_add_on;

  /**
   * @var string $status_message will be displayed if not empty
   * @since 0.0.1
  */
  var $status_message;

  /**
   * @var string $error_message will be displayed if not empty
   * @since 0.0.1
  */
  var $error_message;

  /**
   * Set up the class
   *
   * @since 0.0.1
  */
  function __construct() {
      $this->_is_admin       = is_admin();
      $this->_current_tab   = empty( $_GET['page'] ) ? false : $_GET['page'];
      $this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

      if ( !empty( $_POST ) && $this->_is_admin && 'it-exchange-settings' == $this->_current_tab && 'license' ) {
          add_action( 'it_exchange_save_licensing', array( $this, 'save_settings' ) );
          do_action( 'it_exchange_save_licensing' );
      }

  }


  function print_settings_page() {
    	ITUtility::screen_icon( 'it-exchange' );
  		// Print Admin Settings Tabs
  		$GLOBALS['IT_Exchange_Admin']->print_general_settings_tabs();
  		?>
      <!-- This should all probably be encoded for translation. -->
  		<h2>License Keys</h2>
  		<p>If you have purchased a license key for ExchangeWP and any official add-ons, you can enter that below.
  			If you some how haven't <br />purchased a license and want to get support and updates, you can do so
  			by <a href="https://exchangewp.com/pricing">deciding which pricing plan works for you.</a></p>

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
                <?php $this->get_form_table( $form, $form_values ); ?>
                <?php do_action( 'it_exchange_licenses_settings_form_bottom' ); ?>
                <p class="submit">
                    <?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary button-large' ) ); ?>
                </p>
            <?php $form->end_form(); ?>
            <?php do_action( 'it_exchange_licenses_settings_page_bottom' ); ?>
            <?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
        </div>
        <?php
  }

  /**
   * Builds Settings Form Table
   *
   * @since 0.0.1
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
              <?php $form->add_text_box( 'invoice_license' ); ?>
              <span>
                <?php
                  $exchangewp_invoice_license = it_exchange_get_option( 'exchangewp_licenses' );
                  $license = $exchangewp_license['exchangewp_license'];
                  // var_dump($license);
                  // $exstatus = trim( it_exchange_get_option( 'exchange_license_status' ) );
                  // this might be the only way to get it but I'll try the iThemes way first.
                  $exstatus = trim( get_option( 'exchange_license_status' ) );
                  // var_dump($exstatus);
                  ?>
                <?php if( $exstatus !== false && $exstatus == 'valid' ) { ?>
    							<span style="color:green;"><?php _e('active'); ?></span>
    							<?php wp_nonce_field( 'exchangewp_license_nonce', 'exchangewp_license_nonce' ); ?>
    							<input type="submit" class="button-secondary" name="exchangewp_license_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
    						<?php } else {
    							wp_nonce_field( 'exchangewp_license_nonce', 'exchangewp_license_nonce' ); ?>
    							<input type="submit" class="button-secondary" name="exchangewp_license_license_activate" value="<?php _e('Activate License'); ?>"/>
    						<?php } ?>
              </span>
            </td>
          </tr>
        </tbody>
      </table>
      <?php
  }


  /**
   * Save settings
   *
   * @since 0.0.1
   * @return void
  */
  function save_settings() {
      $defaults = it_exchange_get_option( 'exchangewp_licenses' );
      $new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

      // Check nonce
      if ( !wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-licenses-settings' ) ) {
          $this->error_message = __( 'Error. Please try again', 'LION' );
          return;
      }

      $errors = apply_filters( 'it_exchange_add_on_licenses_validate_settings', $this->get_form_errors( $new_values ), $new_values );
      if ( !$errors && it_exchange_save_option( 'exchangewp_licenses', $new_values ) ) {
          ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
      } else if ( $errors ) {
          $errors = implode( '<br />', $errors );
          $this->error_message = $errors;
      } else {
          $this->status_message = __( 'Settings not saved.', 'LION' );
      }

      if( isset( $_POST['exchangewp_license_activate'] ) ) {

      		// run a quick security check
      	 	if( ! check_admin_referer( 'exchangewp_license_nonce', 'exchangewp_license_nonce' ) )
      			return; // get out if we didn't click the Activate button

      		// retrieve the license from the database
      		// $license = trim( get_option( 'exchangewp_license_license_key' ) );
         $exchangewp_stripe_options = get_option( 'it-storage-exchange_addon_stripe' );
         $license = trim( $exchangewp_stripe_options['stripe_license'] );

      		// data to send in our API request
      		$api_params = array(
      			'edd_action' => 'activate_license',
      			'license'    => $license,
      			'item_name'  => urlencode( 'exchangewp' ), // the name of our product in EDD
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

      						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), 'stripe' );
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
      			return;
      		}

      		//$license_data->license will be either "valid" or "invalid"
      		update_option( 'exchangewp_license_status', $license_data->license );
      		return;
      	}

       // deactivate here
       // listen for our activate button to be clicked
      	if( isset( $_POST['exchangewp_license_deactivate'] ) ) {

      		// run a quick security check
      	 	if( ! check_admin_referer( 'exchangewp_license_nonce', 'exchangewp_license_nonce' ) )
      			return; // get out if we didn't click the Activate button

      		// retrieve the license from the database
      		// $license = trim( get_option( 'exchangewp_license_license_key' ) );

          // this likely needs to be changed.
         $exchangewp_stripe_options = get_option( 'it-storage-exchange_addon_stripe' );
         $license = $exchangewp_stripe_options['stripe_license'];

      		// data to send in our API request
      		$api_params = array(
      			'edd_action' => 'deactivate_license',
      			'license'    => $license,
      			'item_name'  => urlencode( 'exchangewp' ), // the name of our product in EDD
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

      			return;

      		}

      		// decode the license data
      		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
      		// $license_data->license will be either "deactivated" or "failed"
      		if( $license_data->license == 'deactivated' ) {
      			delete_option( 'exchangewp_license_license_status' );
      		}

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


  /**
   * Validates for values
   *
   * Returns string of errors if anything is invalid
   *
   * @since 1.0.0
   * @return array
  */
  public function get_form_errors( $values ) {

      $errors = array();

      return $errors;

  }


}
