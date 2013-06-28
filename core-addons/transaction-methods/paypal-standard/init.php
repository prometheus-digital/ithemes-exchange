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
function it_exchange_paypal_standard_addon_wizard_settings( $form ) {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$settings = it_exchange_get_option( 'addon_paypal_standard', true );
	?>
	<div class="field paypal-standard-wizard hide-if-js">
	<?php $IT_Exchange_PayPal_Standard_Add_On->get_paypal_standard_payment_form_table( $form, $settings ); ?>
	</div>
	<?php
}
add_action( 'it_exchange_print_wizard_settings', 'it_exchange_paypal_standard_addon_wizard_settings' );

/**
 * Stripe URL to perform refunds
 *
 * @since 0.4.0
 *
 * @param string $url passed by WP filter.
 * @param string $url transaction URL
*/
function it_exchange_refund_url_for_paypal_standard( $url ) {

	return 'https://paypal.com/';
	
}
add_filter( 'it_exchange_refund_url_for_paypal-standard', 'it_exchange_refund_url_for_paypal_standard' );
/**
 * This proccesses a paypal transaction.
 *
 * @since 0.4.0
 *
 * @param string $status passed by WP filter.
 * @param object $transaction_object The transaction object
*/
function it_exchange_process_paypal_standard_addon_transaction( $status, $transaction_object ) {

	if ( $status ) //if this has been modified as true already, return.
		return $status;

	if ( !empty( $_REQUEST['it-exchange-transaction-method'] ) && 'paypal-standard' === $_REQUEST['it-exchange-transaction-method'] ) {

		if ( !empty( $_REQUEST['tx'] ) ) //if PDT is enabled
			$transaction_id = $_REQUEST['tx'];
		else if ( !empty( $_REQUEST['txn_id'] ) ) //if PDT is not enabled
			$transaction_id = $_REQUEST['txn_id'];
		else
			$transaction_id = NULL;

		if ( !empty( $_REQUEST['amt'] ) ) //if PDT is enabled
			$transaction_amount = $_REQUEST['amt'];
		else if ( !empty( $_REQUEST['mc_gross'] ) ) //if PDT is not enabled
			$transaction_amount = $_REQUEST['mc_gross'];
		else
			$transaction_amount = NULL;

		if ( !empty( $_REQUEST['st'] ) ) //if PDT is enabled
			$transaction_status = $_REQUEST['st'];
		else if ( !empty( $_REQUEST['payment_status'] ) ) //if PDT is not enabled
			$transaction_status = $_REQUEST['payment_status'];
		else
			$transaction_status = NULL;

		if ( !empty( $transaction_id ) && !empty( $transaction_amount ) && !empty( $transaction_status ) ) {

			try {

				$general_settings = it_exchange_get_option( 'settings_general' );
				$paypal_settings = it_exchange_get_option( 'addon_paypal_standard' );

				$it_exchange_customer = it_exchange_get_current_customer();

				$paypal_api_url       = ( $paypal_settings['sandbox-mode'] ) ? PAYPAL_NVP_API_SANDBOX_URL : PAYPAL_NVP_API_LIVE_URL;
				$paypal_api_username  = ( $paypal_settings['sandbox-mode'] ) ? $paypal_settings['sandbox-api-username'] : $paypal_settings['live-api-username'];
				$paypal_api_password  = ( $paypal_settings['sandbox-mode'] ) ? $paypal_settings['sandbox-api-password'] : $paypal_settings['live-api-password'];
				$paypal_api_signature = ( $paypal_settings['sandbox-mode'] ) ? $paypal_settings['sandbox-api-signature'] : $paypal_settings['live-api-signature'];

				$request = array(
					'USER'          => trim( $paypal_api_username ),
					'PWD'           => trim( $paypal_api_password ),
					'SIGNATURE'     => trim( $paypal_api_signature ),
					'VERSION'       => '96.0', //The PayPal API version
					'METHOD'        => 'GetTransactionDetails',
					'TRANSACTIONID' => $transaction_id,
				);

				$response = wp_remote_post( $paypal_api_url, array( 'body' => $request ) );

				if ( !is_wp_error( $response ) ) {

					$array = array();
					parse_str( wp_remote_retrieve_body( $response ) );

					it_exchange_set_paypal_standard_addon_customer_id( $it_exchange_customer->id, $PAYERID );
					it_exchange_set_paypal_standard_addon_customer_email( $it_exchange_customer->id, $EMAIL );
					$transaction_status = $PAYMENTSTATUS;

					if ( $transaction_id != $TRANSACTIONID )
						throw new Exception( __( 'Error: Transaction IDs do not match! %s, %s', 'LION' ) );

					if ( number_format( $AMT, '2', '', '' ) != number_format( $transaction_object->total, '2', '', '' ) )
						throw new Exception( __( 'Error: Amount charged is not the same as the cart total!', 'LION' ) );

				} else {

					throw new Exception( $response->get_error_message() );

				}

			}
			catch ( Exception $e ) {

				it_exchange_add_message( 'error', $e->getMessage() );
				return false;

			}

			return it_exchange_add_transaction( 'paypal-standard', $transaction_id, $transaction_status, $it_exchange_customer->id, $transaction_object );

		}

		it_exchange_add_message( 'error', __( 'Unknown error. Please try again later.', 'LION' ) );

	}
	return false;

}
add_action( 'it_exchange_do_transaction_paypal-standard', 'it_exchange_process_paypal_standard_addon_transaction', 10, 2 );

/**
 * Grab the paypal customer ID for a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP customer ID
 * @return string
*/
function it_exchange_get_paypal_standard_addon_customer_id( $customer_id ) {
	return get_user_meta( $customer_id, '_it_exchange_paypal_standard_id', true );
}

/**
 * Add the paypal customer email as user meta on a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP user ID
 * @param integer $paypal_standard_id the paypal customer ID
 * @return boolean
*/
function it_exchange_set_paypal_standard_addon_customer_id( $customer_id, $paypal_standard_id ) {
	return update_user_meta( $customer_id, '_it_exchange_paypal_standard_id', $paypal_standard_id );
}

/**
 * Grab the paypal customer email for a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP customer ID
 * @return string
*/
function it_exchange_get_paypal_standard_addon_customer_email( $customer_id ) {
	return get_user_meta( $customer_id, '_it_exchange_paypal_standard_email', true );
}

/**
 * Add the paypal customer email as user meta on a WP user
 *
 * @since 0.4.0
 *
 * @param integer $customer_id the WP user ID
 * @param string $paypal_standard_email the paypal customer email
 * @return boolean
*/
function it_exchange_set_paypal_standard_addon_customer_email( $customer_id, $paypal_standard_email ) {
	return update_user_meta( $customer_id, '_it_exchange_paypal_standard_email', $paypal_standard_email );
}

/**
 * This is the function registered in the options array when it_exchange_register_addon was called for paypal
 *
 * It tells Exchange where to find the settings page
 *
 * @return void
*/
function it_exchange_paypal_standard_settings_callback() {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$IT_Exchange_PayPal_Standard_Add_On->print_settings_page();
}

/**
 * This is the function prints the payment form on the Wizard Settings screen
 *
 * @return void
*/
function paypal_standard_print_wizard_settings( $form ) {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$settings = it_exchange_get_option( 'addon_paypal_standard', true );
	?>
	<div class="field paypal_standard-wizard hide-if-js">
	<?php $IT_Exchange_PayPal_Standard_Add_On->get_paypal_standard_payment_form_table( $form, $settings ); ?>
	</div>
	<?php
}

/**
 * Saves paypal settings when the Wizard is saved
 *
 * @since 0.4.0
 *
 * @return void
*/
function it_exchange_paypal_standard_addon_save_wizard_settings() {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$IT_Exchange_PayPal_Standard_Add_On->paypal_standard_save_wizard_settings();
}
add_action( 'it_exchange_save_wizard_settings', 'it_exchange_paypal_standard_addon_save_wizard_settings' );

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
		'paypal-standard-live-email-address'    => '',
		'paypal-standard-live-api-username'     => '',
		'paypal-standard-live-api-password'     => '',
		'paypal-standard-live-api-signature'    => '',
		'paypal-standard-sandbox-email-address' => '',
		'paypal-standard-sandbox-api-username'  => '',
		'paypal-standard-sandbox-api-password'  => '',
		'paypal-standard-sandbox-api-signature' => '',
		'paypal-standard-sandbox-mode'          => false,
	);
	$values = ITUtility::merge_defaults( $values, $defaults );
	return $values;
}
add_filter( 'it_storage_get_defaults_exchange_addon_paypal_standard', 'it_exchange_paypal_standard_addon_default_settings' );

/**
 * Returns the button for making the payment
 *
 * @since 0.4.0
 *
 * @param array $options
 * @return string HTML button
*/
function it_exchange_paypal_standard_addon_make_payment_button( $options ) {

	if ( 0 >= it_exchange_get_cart_total( false ) )
		return;
		
	$general_settings = it_exchange_get_option( 'settings_general' );
	$paypal_settings  = it_exchange_get_option( 'addon_paypal_standard' );

	$payment_form = '';

	$paypal_api_url       = ( $paypal_settings['paypal-standard-sandbox-mode'] ) ? PAYPAL_NVP_API_SANDBOX_URL : PAYPAL_NVP_API_LIVE_URL;
	$paypal_email         = ( $paypal_settings['paypal-standard-sandbox-mode'] ) ? $paypal_settings['paypal-standard-sandbox-email-address'] : $paypal_settings['paypal-standard-live-email-address'];
	$paypal_api_username  = ( $paypal_settings['paypal-standard-sandbox-mode'] ) ? $paypal_settings['paypal-standard-sandbox-api-username'] : $paypal_settings['paypal-standard-live-api-username'];
	$paypal_api_password  = ( $paypal_settings['paypal-standard-sandbox-mode'] ) ? $paypal_settings['paypal-standard-sandbox-api-password'] : $paypal_settings['paypal-standard-live-api-password'];
	$paypal_api_signature = ( $paypal_settings['paypal-standard-sandbox-mode'] ) ? $paypal_settings['paypal-standard-sandbox-api-signature'] : $paypal_settings['paypal-standard-live-api-signature'];

	$it_exchange_customer = it_exchange_get_current_customer();

	$button_request = array(
		'USER'           => trim( $paypal_api_username ),
		'PWD'            => trim( $paypal_api_password ),
		'SIGNATURE'      => trim( $paypal_api_signature ),
		'VERSION'        => '96.0', //The PayPal API version
		'METHOD'         => 'BMCreateButton',
		'BUTTONCODE'     => 'ENCRYPTED',
		'BUTTONTYPE'     => 'BUYNOW',
		'BUTTONIMAGE'    => 'REG',
	//	'BUTTONIMAGEURL' => '', //Use either BUTTONIMAGE or BUTTONIMAGEURL -- not both!
		'BUYNOWTEXT'     => 'PAYNOW',
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
	$L_BUTTONVARS[] = 'notify_url=' . get_site_url() . '/?' . it_exchange_get_webhook( 'paypal-standard' ) . '=1';
	$L_BUTTONVARS[] = 'return=' . it_exchange_get_page_url( 'transaction' ) . '?it-exchange-transaction-method=paypal-standard';
	$L_BUTTONVARS[] = 'rm=2'; //Return  Method - https://developer.paypal.com/webapps/developer/docs/classic/button-manager/integration-guide/ButtonManagerHTMLVariables/
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
				$payment_form = str_replace( array( "\r\n", "\r", "\n" ), '', stripslashes( $WEBSITECODE ) );
				//Strip out the newline characters because parse_str/PayPal adds a \n to the encrypted code, whic breaks the digital ID

		}

	}
	
	return $payment_form;
}
add_filter( 'it_exchange_get_paypal-standard_make_payment_button', 'it_exchange_paypal_standard_addon_make_payment_button', 10, 2 );

/**
 * Adds the paypal webhook to the global array of keys to listen for
 *
 * @since 0.4.0
 *
 * @param array $webhooks existing
 * @return array
*/
function it_exchange_paypal_addon_register_webhook() {
	$key   = 'paypal-standard';
	$param = apply_filters( 'it_exchange_paypal-standard_webhook', 'it_exchange_paypal-standard' );
	it_exchange_register_webhook( $key, $param );
}
add_filter( 'init', 'it_exchange_paypal_addon_register_webhook' );

/**
 * Processes webhooks for PayPal Web Standard
 *
 * @since 0.4.0
 * @todo actually handle the exceptions
 *
 * @param array $request really just passing  $_REQUEST
 */
function it_exchange_paypal_standard_addon_process_webhook( $request ) {

	$general_settings = it_exchange_get_option( 'settings_general' );
	$settings = it_exchange_get_option( 'addon_paypal_standard' );

	// for extra security, retrieve from the Stripe API
	if ( isset( $request['txn_id'] ) ) {

		try {

			switch( $request['payment_status'] ) :

				case 'Completed' :
					it_exchange_paypal_standard_addon_update_transaction_status( $request['txn_id'], $request['payment_status'] );
					break;
				case 'Refunded' :
					it_exchange_paypal_standard_addon_update_transaction_status( $request['parent_txn_id'], $request['payment_status'] );
					it_exchange_paypal_standard_addon_add_refund_to_transaction( $request['parent_txn_id'], $request['mc_gross'] );
				case 'Reversed' :
					it_exchange_paypal_standard_addon_update_transaction_status( $request['parent_txn_id'], $request['reason_code'] );
					break;

			endswitch;

		} catch ( Exception $e ) {

			// What are we going to do here?

		}
	}

}
add_action( 'it_exchange_webhook_it_exchange_paypal-standard', 'it_exchange_paypal_standard_addon_process_webhook' );

/**
 * Grab a transaction from the paypal transaction ID
 *
 * @since 0.4.0
 *
 * @param integer $paypal_standard_id id of paypal transaction
 * @return transaction object
*/
function it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id ) {
	$args = array(
		'meta_key'    => '_it_exchange_transaction_method_id',
		'meta_value'  => $paypal_standard_id,
		'numberposts' => 1, //we should only have one, so limit to 1
	);
	return it_exchange_get_transactions( $args );
}

/**
 * Updates a paypals transaction status based on paypal ID
 *
 * @since 0.4.0
 *
 * @param integer $paypal_standard_id id of paypal transaction
 * @param string $new_status new status
 * @return void
*/
function it_exchange_paypal_standard_addon_update_transaction_status( $paypal_standard_id, $new_status ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id );
	foreach( $transactions as $transaction ) { //really only one
		$current_status = it_exchange_get_transaction_status( $transaction );
		if ( $new_status !== $current_status )
			it_exchange_update_transaction_status( $transaction, $new_status );
	}
}

/**
 * Adds a refund to post_meta for a stripe transaction
 *
 * @since 0.4.0
*/
function it_exchange_paypal_standard_addon_add_refund_to_transaction( $paypal_standard_id, $refund ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id );
	foreach( $transactions as $transaction ) { //really only one
		it_exchange_add_refund_to_transaction( $transaction, number_format( abs( $refund ), '2', '.', '' ) );
	}

}

/**
 * Gets the interpretted transaction status from valid paypal transaction statuses
 *
 * @since 0.4.0
 *
 * @param string $status the string of the paypal transaction
 * @return string translaction transaction status
*/
function it_exchange_paypal_standard_addon_transaction_status_label( $status ) {

	switch ( strtolower( $status ) ) {

		case 'completed':
		case 'success':
		case 'canceled_reversal':
		case 'processed' :
			return __( 'Paid', 'LION' );
			break;
		case 'refunded':
		case 'refund':
			return __( 'Refund', 'LION' );
			break;
		case 'reversed':
			return __( 'Reversed', 'LION' );
			break;
		case 'buyer_complaint':
			return __( 'Buyer Complaint', 'LION' );
			break;
		case 'denied' :
			return __( 'Denied', 'LION' );
			break;
		case 'expired' :
			return __( 'Expired', 'LION' );
			break;
		case 'failed' :
			return __( 'Failed', 'LION' );
			break;
		case 'pending' :
			return __( 'Pending', 'LION' );
			break;
		case 'voided' :
			return __( 'Voided', 'LION' );
			break;
		default:
			return __( 'Unknown', 'LION' );
	}

}
add_filter( 'it_exchange_transaction_status_label_paypal-standard', 'it_exchange_paypal_standard_addon_transaction_status_label' );

/**
 * Returns a boolean. Is this transaction a status that warrants delivery of any products attached to it?
 *
 * @since 0.4.2
 *
 * @param boolean $cleared passed in through WP filter. Ignored here.
 * @param object $transaction
 * @return boolean
*/
function it_exchange_paypal_standard_transaction_is_cleared_for_delivery( $cleared, $transaction ) { 
    $valid_stati = array( 
		'completed',
		'success',
		'canceled_reversal',
		'processed',
	);
    return in_array( strtolower( it_exchange_get_transaction_status( $transaction ) ), $valid_stati );
}
add_filter( 'it_exchange_paypal-standard_transaction_is_cleared_for_delivery', 'it_exchange_paypal_standard_transaction_is_cleared_for_delivery', 10, 2 );

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
					<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary button-large' ) ); ?>
				</p>
			<?php $form->end_form(); ?>
			<?php do_action( 'it_exchange_paypal-standard_settings_page_bottom' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
		</div>
		<?php
	}

	function get_paypal_standard_payment_form_table( $form, $settings = array() ) {

		$general_settings = it_exchange_get_option( 'settings_general' );

		if ( !empty( $settings ) )
			foreach ( $settings as $key => $var )
				$form->set_option( $key, $var );

		?>
		<div class="it-exchange-addon-settings it-exchange-paypal-addon-settings">
			<h3><?php _e( 'PayPal Web Standard Payment Settings', 'LION' ); ?></h3>
            <p><?php _e( 'To get PayPal setup for your ecommerce site, you will need to do a couple of things in PayPal first.<br /><br />

<a href="http://ithemes.com/tutorial/category/exchange" target="_blank">Video: Getting PayPal Setup with Exchange</a>', 'LION' ); ?></p>
			<p><?php _e( 'Do not have a PayPal account yet? <a href="http://paypal.com" target="_blank">Go set one up here</a>.', 'LION' ); ?></p>
            <h4><?php _e( 'Step 1. Fill out your PayPal email address', 'LION' ); ?></h4>
			<p>
				<label for="paypal-standard-live-email-address"><?php _e( 'PayPal Email Address', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'paypal-standard-live-email-address' ); ?>
			</p>
            <h4><?php _e( 'Step 2. Fill out your PayPal API credentials', 'LION' ); ?></h4>
			<p>
				<label for="paypal-standard-live-api-username"><?php _e( 'PayPal API Username', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'paypal-standard-live-api-username' ); ?>
			</p>
			<p>
				<label for="paypal-standard-live-api-password"><?php _e( 'PayPal API Password', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'paypal-standard-live-api-password' ); ?>
			</p>
			<p>
				<label for="paypal-standard-live-api-signature"><?php _e( 'PayPal API Signature', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'paypal-standard-live-api-signature' ); ?>
			</p>
			<p class="hide-if-wizard">
				<label for="paypal-standard-sandbox-email-address"><?php _e( 'PayPal Sandbox Email Address', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'paypal-standard-sandbox-email-address' ); ?>
			</p>
			<p class="hide-if-wizard">
				<label for="paypal-standard-sandbox-api-username"><?php _e( 'PayPal Sandbox API Username', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'paypal-standard-sandbox-api-username' ); ?>
			</p>
			<p class="hide-if-wizard">
				<label for="paypal-standard-sandbox-api-password"><?php _e( 'PayPal Sandbox API Password', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'paypal-standard-sandbox-api-password' ); ?>
			</p>
			<p class="hide-if-wizard">
				<label for="paypal-standard-sandbox-api-signature"><?php _e( 'PayPal Sandbox API Signature', 'LION' ); ?> <span class="tip" title="<?php _e( 'At PayPal&reg;, see: Profile -› API Access (or Request API Credentials).', 'LION' ); ?>">i</span></label>
				<?php $form->add_text_box( 'paypal-standard-sandbox-api-signature' ); ?>
			</p>
			<p class="hide-if-wizard">
				<?php $form->add_check_box( 'paypal-standard-sandbox-mode' ); ?>
				<label for="paypal-standard-sandbox-mode"><?php _e( 'Enable PayPal Sandbox Mode?', 'LION' ); ?> <span class="tip" title="<?php _e( 'Enable PayPal Sandbox Mode', 'LION' ); ?>">i</span></label>
			</p>
            <h4><?php _e( 'Step 3. Setup PayPal Instant Payment Notifications (IPN)', 'LION' ); ?></h4>
			<p><?php _e( 'PayPal IPN must be configured in Account Profile -› Instant Payment Notification Preferences in your PayPal Account', 'LION' ); ?></p>
			<p><?php _e( 'Please log into your account and add this URL to your IPN Settings so iThemes Exchange is notified of things like refunds, payments, etc.', 'LION' ); ?></p>
			<code><?php echo get_site_url(); ?>/?<?php esc_attr_e( it_exchange_get_webhook( 'paypal-standard' ) ); ?>=1</code>
            <h4><?php _e( 'Step 4. Setup PayPal Auto Return', 'LION' ); ?></h4>
			<p><?php _e( 'PayPal Auto Return must be configured in Account Profile -› Website Payment Preferences in your PayPal Account', 'LION' ); ?></p>
			<p><?php _e( 'Please log into your account, set Auto Return to ON and add this URL to your Return URL Settings so your customers are redirected to your site to complete the transactions.', 'LION' ); ?></p>
			<code><?php echo it_exchange_get_page_url( 'transaction' ); ?></code>
            <h4><?php _e( 'Step 5. Setup PayPal Payment Data Transfer (PDT)', 'LION' ); ?></h4>
			<p><?php _e( 'PayPal PDT must be configured in Account Profile -› Website Payment Preferences in your PayPal Account', 'LION' ); ?></p>
		</div>
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

		$errors = apply_filters( 'it_exchange_add_on_paypal_standard_validate_settings', $this->get_form_errors( $new_values ), $new_values );
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
		if ( empty( $_REQUEST['it_exchange_settings-wizard-submitted'] ) )
			return;

		$paypal_standard_settings = array();

		$default_wizard_paypal_standard_settings = apply_filters( 'default_wizard_paypal-standard_settings', array( 'paypal-standard-live-email-address', 'paypal-standard-live-api-username', 'paypal-standard-live-api-password', 'paypal-standard-live-api-signature', 'paypal-standard-sandbox-email-address', 'paypal-standard-sandbox-api-username', 'paypal-standard-sandbox-api-password', 'paypal-standard-sandbox-api-signature',  'paypal-standard-sandbox-mode', ) );

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
		if ( empty( $values['paypal-standard-live-email-address'] ) )
			$errors[] = __( 'Please include your PayPal Email Address', 'LION' );
		if ( empty( $values['paypal-standard-live-api-username'] ) )
			$errors[] = __( 'Please include your PayPal API Username', 'LION' );
		if ( empty( $values['paypal-standard-live-api-password'] ) )
			$errors[] = __( 'Please include your PayPal API password', 'LION' );
		if ( empty( $values['paypal-standard-live-api-signature'] ) )
			$errors[] = __( 'Please include your PayPal API signature', 'LION' );

		if ( !empty( $values['paypal-standard-sandbox-mode' ] ) ) {
			if ( empty( $values['paypal-standard-sandbox-email-address'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox Email Address', 'LION' );
			if ( empty( $values['paypal-standard-sandbox-api-username'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox API Username', 'LION' );
			if ( empty( $values['paypal-standard-sandbox-api-password'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox API password', 'LION' );
			if ( empty( $values['paypal-standard-sandbox-api-signature'] ) )
				$errors[] = __( 'Please include your PayPal Sandbox API signature', 'LION' );
		}

		return $errors;
	}
}
