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
  		<p>If you have purchased a license key for ExchangeWP, you can enter that below.
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
            <th>Invoice License key</th>
            <td>
              <?php $form->add_text_box( 'invoice_license' ); ?>
              <span>
                <?php
                  $exchangewp_invoice_license = it_exchange_get_option( 'exchangewp_licenses' );
                  $license = $exchangewp_invoice_license['invoice_license'];
                  // var_dump($license);
                  $exstatus = trim( get_option( 'exchange_invoice_license_status' ) );
                  // var_dump($exstatus);
                  ?>
                <?php if( $exstatus !== false && $exstatus == 'valid' ) { ?>
    							<span style="color:green;"><?php _e('active'); ?></span>
    							<?php wp_nonce_field( 'exchange_2checkout_nonce', 'exchange_2checkout_nonce' ); ?>
    							<input type="submit" class="button-secondary" name="exchange_2checkout_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
    						<?php } else {
    							wp_nonce_field( 'exchange_2checkout_nonce', 'exchange_2checkout_nonce' ); ?>
    							<input type="submit" class="button-secondary" name="exchange_2checkout_license_activate" value="<?php _e('Activate License'); ?>"/>
    						<?php } ?>
              </span>
            </td>
          </tr>
          <tr>
            <th>Invoice License key</th>
            <td>
              <?php $form->add_text_box( 'invoice_license' ); ?>
              <span>
                <?php
                  $exchangewp_invoice_license = it_exchange_get_option( 'exchangewp_licenses' );
                  $license = $exchangewp_invoice_license['invoice_license'];
                  // var_dump($license);
                  $exstatus = trim( get_option( 'exchange_invoice_license_status' ) );
                  // var_dump($exstatus);
                  ?>
                <?php if( $exstatus !== false && $exstatus == 'valid' ) { ?>
    							<span style="color:green;"><?php _e('active'); ?></span>
    							<?php wp_nonce_field( 'exchange_2checkout_nonce', 'exchange_2checkout_nonce' ); ?>
    							<input type="submit" class="button-secondary" name="exchange_2checkout_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
    						<?php } else {
    							wp_nonce_field( 'exchange_2checkout_nonce', 'exchange_2checkout_nonce' ); ?>
    							<input type="submit" class="button-secondary" name="exchange_2checkout_license_activate" value="<?php _e('Activate License'); ?>"/>
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
