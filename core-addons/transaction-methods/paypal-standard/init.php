<?php
/**
 * Hooks for PayPal Standard add-on
 *
 * @package IT_Exchange
 * @since 0.2.0
*/

define( 'PAYPAL_NVP_API_SANDBOX_URL', 'https://api-3t.sandbox.paypal.com/nvp' );
define( 'PAYPAL_NVP_API_LIVE_URL', 'https://api-3t.paypal.com/nvp' );

/**
 * Outputs wizard settings for PayPal
 *
 * @since 0.4.0
 * @todo make this better, probably
 * @param object $form Current IT Form object
 * @return void
*/
function it_exchange_paypal_standard_wizard_settings( $form ) {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$settings = it_exchange_get_option( 'addon_paypal_standard', true );
	?>
	<div class="field paypal-standard-wizard hide-if-js">
    <?php $IT_Exchange_PayPal_Standard_Add_On->get_paypal_standard_payment_form_table( $form, $settings ); ?>
	</div>
	<?php
}
add_action( 'it_exchange_print_wizard_settings', 'it_exchange_paypal_standard_wizard_settings' );

function it_exchange_process_paypal_standard_transaction( $status, $transaction_object ) {

	if ( $status ) //if this has been modified as true already, return.
		return $status;
	
}
add_action( 'it_exchange_do_transaction_paypal-standard', 'it_exchange_process_paypal_standard_transaction', 10, 2 );

function it_exchange_get_paypal_standard_customer_id( $customer_id ) {
	return get_user_meta( $customer_id, '_it_exchange_paypal_standard_id', true );
}

function it_exchange_set_paypal_standard_customer_id( $customer_id, $paypal_standard_id ) {
	return update_user_meta( $customer_id, '_it_exchange_paypal_standard_id', $paypal_standard_id );
}

function it_exchange_paypal_standard_settings_callback() {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$IT_Exchange_PayPal_Standard_Add_On->print_settings_page();
}

function paypal_standard_print_wizard_settings( $form ) {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$settings = it_exchange_get_option( 'addon_paypal_standard', true );
	?>
	<div class="field paypal_standard-wizard hide-if-js">
    <?php $IT_Exchange_PayPal_Standard_Add_On->get_paypal_standard_payment_form_table( $form, $settings ); ?>
	</div>
	<?php
}

function paypal_standard_save_wizard_settings() {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$IT_Exchange_PayPal_Standard_Add_On->paypal_standard_save_wizard_settings();
}
add_action( 'it_exchange_save_wizard_settings', 'paypal_standard_save_wizard_settings' );

/**
 * Default settings for paypal_standard
 *
 * @since 0.4.0
 *
 * @param array $values
 * @return array
*/
function it_exchange_paypal_standard_addon_default_settings( $values ) {
	$defaults = array(
		'paypal_standard-sandbox-mode'            => false,
		'paypal_standard-live-email-address'      => '',
		'paypal_standard-live-api-username'       => '',
		'paypal_standard-live-api-password'       => '',
		'paypal_standard-live-api-signature'      => '',
		'paypal_standard-live-pdt-identity-token' => '',
		'paypal_standard-sandbox-email-address'   => '',
		'paypal_standard-sandbox-api-username'    => '',
		'paypal_standard-sandbox-api-password'    => '',
		'paypal_standard-sandbox-api-signature'   => '',
	);   
	$values = ITUtility::merge_defaults( $values, $defaults );
	return $values;
}
add_filter( 'it_storage_get_defaults_exchange_addon_paypal-standard', 'it_exchange_paypal_standard_addon_default_settings' );

/**
 * Returns the button for making the payment
 *
 * @since 0.4.0
 *
 * @param array $options
 * @return string HTML button
*/
function it_exchange_paypal_standard_addon_make_payment_button( $options ) { 
	
	$general_settings = it_exchange_get_option( 'settings_general' );
	$paypal_settings = it_exchange_get_option( 'addon_paypal_standard' );
	
	$payment_form = '';
	
	$paypal_api_url        = ( $paypal_settings['sandbox-mode'] ) ? PAYPAL_NVP_API_SANDBOX_URL : PAYPAL_NVP_API_LIVE_URL;
	$paypal_email        = ( $paypal_settings['sandbox-mode'] ) ? $paypal_settings['sandbox-email-address'] : $paypal_settings['live-email-address'];
	$paypal_api_username = ( $paypal_settings['sandbox-mode'] ) ? $paypal_settings['sandbox-api-username'] : $paypal_settings['live-api-username'];
	$paypal_api_password = ( $paypal_settings['sandbox-mode'] ) ? $paypal_settings['sandbox-api-password'] : $paypal_settings['live-api-password'];
	$paypal_api_signature = ( $paypal_settings['sandbox-mode'] ) ? $paypal_settings['sandbox-api-signature'] : $paypal_settings['live-api-signature'];

	$it_exchange_customer = it_exchange_get_current_customer();
			
	
	$button_request = array(
		'USER'	        => trim( $paypal_api_username ),
		'PWD'	        => trim( $paypal_api_password ),
		'SIGNATURE'	    => trim( $paypal_api_signature ),
		'VERSION'       => '96.0', //The PayPal API version
		'METHOD'	    => 'BMCreateButton',
		'BUTTONCODE'    => 'ENCRYPTED',
		'BUTTONTYPE'    => 'BUYNOW',
		'BUTTONIMAGE'   => 'REG',
		'BUYNOWTEXT'    => 'PAYNOW',
	);
	
	$L_BUTTONVARS[] = 'business=' . $paypal_email;
	$L_BUTTONVARS[] = 'item_name=' . $general_settings['company-name'] . ' ' . __( 'Shopping Cart', 'LION' );
	$L_BUTTONVARS[] = 'amount=' . number_format( it_exchange_get_cart_total( false ), 2, '.', '' );
	$L_BUTTONVARS[] = 'currency_code=' . $general_settings['default-currency'];
	$L_BUTTONVARS[] = 'quantity=1';
	$L_BUTTONVARS[] = 'no_note=1';
	$L_BUTTONVARS[] = 'no_shipping=1';
	$L_BUTTONVARS[] = 'shipping=0';
	$L_BUTTONVARS[] = 'email=' . $it_exchange_customer->data->user_email;
	$L_BUTTONVARS[] = 'notify_url=' . get_site_url() . '/?' . apply_filters( 'it_exchange_paypal-standard_webhook', 'it_exchange_paypal_standard' ) . '=1';
	$L_BUTTONVARS[] = 'return=' . it_exchange_get_page_url( 'cart' );
	$L_BUTTONVARS[] = 'cancel_return=' . it_exchange_get_page_url( 'cart' );
	
	$count = 0;
	foreach( $L_BUTTONVARS as $L_BUTTONVAR ) {
		
		$button_request['L_BUTTONVAR' . $count] = $L_BUTTONVAR;
		$count++;
		
	}
		
	$response = wp_remote_post( $paypal_api_url, array( 'body' => $button_request ) );
			
	if ( !is_wp_error( $response ) ) {
		
		parse_str( wp_remote_retrieve_body( $response ) );
		
		if ( !empty( $ACK ) && 'Success' === $ACK ) {
			
			if ( !empty( $WEBSITECODE ) )
				$payment_form = str_replace( array( "\r\n", "\r", "\n" ), '', $WEBSITECODE );
				//Strip out the newline characters because parse_str/PayPal adds a \n to the encrypted code, whic breaks the digital ID
			
		}
		
	}
	
	return $payment_form;
}
add_filter( 'it_exchange_get_paypal-standard_make_payment_button', 'it_exchange_paypal_standard_addon_make_payment_button', 10, 2 );

function it_exchange_paypal_standard_webhook_key( $webhooks ) {

	$webhooks[] = apply_filters( 'it_exchange_paypal-standard_webhook', 'it_exchange_paypal_standard' );
	
	return $webhooks;
	
}
add_filter( 'it_exchange_webhook_keys', 'it_exchange_paypal_standard_webhook_key' );

/**
 * Processes webhooks for PayPal Web Standard
 *
 * @since 0.4.0
 * @todo actually handle the exceptions 
 *
 * @param array $request really just passing  $_REQUEST
 */
function it_exchange_paypal_standard_process_webhook( $request ) {

	$general_settings = it_exchange_get_option( 'settings_general' );
	$settings = it_exchange_get_option( 'addon_paypal_standard' );
	
}
add_action( 'it_exchange_webhook_it_exchange_paypal-standard', 'it_exchange_paypal_standard_process_webhook' );

function it_exchange_get_transaction_from_paypal_standard_id( $paypal_standard_id ) {
	$args = array(
		'meta_key'    => '_it_exchange_transaction_method_id',
		'meta_value'  => $paypal_standard_id,
		'numberposts' => 1, //we should only have one, so limit to 1
	);
	return it_exchange_get_transactions( $args );
}

function it_exchange_update_transaction_status_for_paypal_standard( $paypal_standard_id, $new_status ) {
	$transactions = it_exchange_get_transaction_from_paypal_standard_id( $paypal_standard_id );
	foreach( $transactions as $transaction ) { //really only one
		$current_status = $transaction->get_transaction_status();
		if ( $new_status !== $current_status )
			$transaction->update_transaction_status( $new_status );
	}	
}

function it_ecxhange_add_refund_to_transaction_for_paypal_standard( $paypal_standard_id, $refund ) {
	$transactions = it_exchange_get_transaction_from_paypal_standard_id( $paypal_standard_id );
	foreach( $transactions as $transaction ) { //really only one
		$refunds = $transaction->get_transaction_refunds();
		
		$refunded_amount = 0;
		foreach( $refunds as $refund_meta ) {
			$refunded_amount += number_format( $refund_meta['amount'], '2', '', '' );
		}
		
		$this_refund = $refund - $refunded_amount;
		
		$transaction->add_transaction_refund( number_format( $this_refund, '2', '.', '' ) );
	}	
	
}

function it_exchange_delete_paypal_standard_id_from_customer( $paypal_standard_id ) {
	$transactions = it_exchange_get_transaction_from_paypal_standard_id( $paypal_standard_id );
	foreach( $transactions as $transaction ) { //really only one
		$customer_id = get_post_meta( $transaction->ID, '_it_exchange_customer_id', true );
		if ( false !== $current_paypal_standard_id = it_exchange_get_paypal_standard_customer_id( $customer_id ) ) {
			
			if ( $current_paypal_standard_id === $paypal_standard_id )
				delete_user_meta( $customer_id, '_it_exchange_paypal_standard_id' );
				
		}
	}	
}

function it_exchange_transaction_status_label_paypal_standard( $status ) {

	switch ( $status ) {
	
		case 'succeeded':
			return __( 'Paid', 'LION' );
		case 'refunded':
			return __( 'Refunded', 'LION' );
		case 'partial-refund':
			return __( 'Partially Refunded', 'LION' );
		case 'needs_response':
			return __( 'Disputed: Stripe needs a response', 'LION' );
		case 'under_review':
			return __( 'Disputed: Under review', 'LION' );
		case 'won':
			return __( 'Disputed: Won, Paid', 'LION' );
		default:
			return __( 'Unknown', 'LION' );
		
	}
	
}
add_filter( 'it_exchange_transaction_status_label_paypal-standard', 'it_exchange_transaction_status_label_paypal_standard' );

/**
 * Class for Stripe
 * @since 0.4.0
*/
class IT_Exchange_PayPal_Standard_Add_On {

	/**
	 * @var boolean $_is_admin true or false
	 * @since 0.4.0
	*/
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 * @since 0.4.0
	*/
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 * @since 0.4.0
	*/
	var $_current_add_on;

	/**
	 * @var string $status_message will be displayed if not empty
	 * @since 0.4.0
	*/
	var $status_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 * @since 0.4.0
	*/
	var $error_message;

	/**
 	 * Class constructor
	 *
	 * Sets up the class.
	 * @since 0.4.0
	 * @return void
	*/
	function IT_Exchange_PayPal_Standard_Add_On() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'paypal-standard' == $this->_current_add_on ) {
			$this->save_settings();
		}
		
	}

	function print_settings_page() {
		$settings = it_exchange_get_option( 'addon_paypal_standard', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_add_on_paypal-standard', 'it-exchange-add-on-paypal-standard-settings' ),
			'enctype' => apply_filters( 'it_exchange_add_on_paypal-standard_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=paypal-standard',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-paypal_standard' ) );
	
		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		
		?>
		<div class="wrap">
			<?php $form->start_form( $form_options, 'it-exchange-paypal-standard-settings' ); ?>
				<?php do_action( 'it_exchange_paypal-standard_settings_form_top' ); ?>
				<?php $this->get_paypal_standard_payment_form_table( $form, $form_values ); ?>
				<?php do_action( 'it_exchange_paypal-standard_settings_form_bottom' ); ?>
				<p class="submit">
					<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary' ) ); ?>
				</p>
			<?php $form->end_form(); ?>
			<?php do_action( 'it_exchange_paypal-standard_settings_page_bottom' ); ?>
		</div>
		<?php
	}
	
	function get_paypal_standard_payment_form_table( $form, $settings = array() ) {	
		
		$general_settings = it_exchange_get_option( 'settings_general' );
		
		if ( !empty( $settings ) )
			foreach ( $settings as $key => $var )
				$form->set_option( $key, $var );
				
		?>
		<h3><?php _e( 'PayPal Web Standard Payment Settings', 'LION' ); ?></h3>
        <p><?php _e( 'Do not have a PayPal account yet? <a href="http://paypal.com" target="_blank">Go set one up here</a>.', 'LION' ); ?></p>
        <label for="sandbox-mode"><?php _e( 'Enable PayPal Sandbox Mode?', 'LION' ); ?> <span class="tip" title="<?php _e( 'Enable PayPal Sandbox Mode', 'LION' ); ?>">i</span></label>
        <?php $form->add_check_box( 'sandbox-mode' ); ?>
        <label for="live-email-address"><?php _e( 'PayPal Email Address', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'live-email-address' ); ?>
        <label for="live-api-username"><?php _e( 'PayPal API Username', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'live-api-username' ); ?>
        <label for="live-api-password"><?php _e( 'PayPal API Password', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'live-api-password' ); ?>
        <label for="live-api-signature"><?php _e( 'PayPal API Signature', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'live-api-signature' ); ?>
        <label for="sandbox-email-address"><?php _e( 'PayPal Sandbox Email Address', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'sandbox-email-address' ); ?>
        <label for="sandbox-api-username"><?php _e( 'PayPal Sandbox API Username', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'sandbox-api-username' ); ?>
        <label for="sandbox-api-password"><?php _e( 'PayPal Sandbox API Password', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'sandbox-api-password' ); ?>
        <label for="sandbox-api-signature"><?php _e( 'PayPal Sandbox API Signature', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'sandbox-api-signature' ); ?>
        <h5><?php _e( 'PayPal Instant Payment Notification (IPN)', 'LION' ); ?></h5>
        <p><?php _e( 'PayPal IPN must be configured in Account Profile -› Instant Payment Notification Preferences in your PayPal Account', 'LION' ); ?></p>
        <p><?php _e( 'Please log into your account and add this URL to your IPN Settings so iThemes Exchange is notified of things like refunds, payments, etc.', 'LION' ); ?></p>
        <code><?php echo get_site_url(); ?>/?<?php echo apply_filters( 'it_exchange_paypal-standard_webhook', 'it_exchange_paypal_standard' ); ?>=1</code>
        <h5><?php _e( 'PayPal Payment Data Transfer (PDT) Identity Token', 'LION' ); ?></h5>
        <p><?php _e( 'PayPal PDT must be configured in Account Profile -› Website Payment Preferences in your PayPal Account', 'LION' ); ?></p>
        <p><?php _e( 'Turn the PDT feature ON and paste the PDT Identity Token here.', 'LION' ); ?></p>
        <label for="live-pdt-identity-token"><?php _e( 'PayPal PDT Identity Token', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› Website Payment Preferences.', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'live-pdt-identity-token' ); ?>
        
        <?php	
	}

	/**
	 * Save settings
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function save_settings() {
		$defaults = it_exchange_get_option( 'addon_paypal_standard' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );
		
		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-paypal-standard-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'LION' );
			return;
		}

		$errors = apply_filters( 'it_exchange_add_on_manual_transaction_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_paypal_standard', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
		} else if ( $errors ) {
			$errors = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'LION' );
		}
		
		do_action( 'it_exchange_save_add_on_settings_paypal-standard' );

	}
	
	function paypal_standard_save_wizard_settings() {
		if ( !isset( $_REQUEST['it_exchange_settings-wizard-submitted'] ) )
			return;
			
		$paypal_standard_settings = array();
		
		$default_wizard_paypal_standard_settings = apply_filters( 'default_wizard_paypal-standard_settings', array( 'paypal-standard-title', 'paypal-standard-instructions', 'paypal-standard-default-status' ) );
		
		foreach( $default_wizard_paypal_standard_settings as $var ) {
		
			if ( isset( $_REQUEST['it_exchange_settings-' . $var] ) ) {
				$paypal_standard_settings[$var] = $_REQUEST['it_exchange_settings-' . $var];	
			}
			
		}
		
		$settings = wp_parse_args( $paypal_standard_settings, it_exchange_get_option( 'addon_paypal_standard' ) );
		
		if ( ! empty( $this->error_message ) || $error_msg = $this->get_form_errors( $settings ) ) {
			
			if ( ! empty( $error_msg ) ) {
				
				$this->error_message = $error_msg;
				return;
				
			}
				
		} else {
			it_exchange_save_option( 'addon_paypal_standard', $settings );
			$this->status_message = __( 'Settings Saved.', 'LION' );
		}
		
	}

	/**
	 * Validates for values
	 *
	 * Returns string of errors if anything is invalid
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function get_form_errors( $values ) {
				
		$errors = array();
		if ( empty( $values['live-email-address'] ) )
			$errors[] = __( 'Please include your PayPal Email Address', 'LION' );
		if ( empty( $values['live-api-username'] ) )
			$errors[] = __( 'Please include your PayPal API Username', 'LION' );
		if ( empty( $values['live-api-password'] ) )
			$errors[] = __( 'Please include your PayPal API password', 'LION' );
		if ( empty( $values['live-api-signature'] ) )
			$errors[] = __( 'Please include your PayPal API signature', 'LION' );
		if ( empty( $values['live-pdt-identity-token'] ) )
			$errors[] = __( 'Please include your PayPal PDT Identity Token', 'LION' );
			
		if ( !empty( $values['sandbox-mode' ] ) ) {
			if ( empty( $values['sandbox-email-address'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox Email Address', 'LION' );
			if ( empty( $values['sandbox-api-username'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox API Username', 'LION' );
			if ( empty( $values['sandbox-api-password'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox API password', 'LION' );
			if ( empty( $values['sandbox-api-signature'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox API signature', 'LION' );
		}

		return $errors;
	}

}
