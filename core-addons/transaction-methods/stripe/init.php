<?php
/**
 * iThemes Exchange Stripe Add-on
 * @package IT_Exchange
 * @since 0.4.0
*/

// Initialized Stripe...
require_once('stripe-api/lib/Stripe.php');

/**
 * Outputs wizard settings for Stripe
 *
 * @since 0.4.0
 * @todo make this better, probably
 * @param object $form Current IT Form object
 * @return void
*/
function it_exchange_stripe_wizard_settings( $form ) {
	$IT_Exchange_Stripe_Add_On = new IT_Exchange_Stripe_Add_On();
	$settings = it_exchange_get_option( 'addon_stripe', true );
	?>
	<div class="field stripe-wizard hide-if-js">
    <?php $IT_Exchange_Stripe_Add_On->get_stripe_payment_form_table( $form, $settings ); ?>
	</div>
	<?php
}
add_action( 'it_exchange_print_wizard_settings', 'it_exchange_stripe_wizard_settings' );

function it_exchange_process_stripe_transaction( $status, $transaction_object ) {

	if ( $status ) //if this has been modified as true already, return.
		return $status;
	
	// Verify nonce
	if ( ! empty( $_REQUEST['_stripe_nonce'] ) && ! wp_verify_nonce( $_REQUEST['_stripe_nonce'], 'stripe-checkout' ) ) {
		it_exchange_add_message( 'error', __( 'Transaction Failed, unable to verify security token.', 'LION' ) );
		return false;
	}
	
	if ( ! empty( $_POST['stripeToken'] ) ) {
			
		try {
				
			$general_settings = it_exchange_get_option( 'settings_general' );
			$settings = it_exchange_get_option( 'addon_stripe' );
		
			$secret_key = ( $settings['stripe-test-mode'] ) ? $settings['stripe-test-secret-key'] : $settings['stripe-live-secret-key'];
			Stripe::setApiKey( $secret_key );
	
			$token = $_POST['stripeToken'];
			
			$it_exchange_customer = it_exchange_get_current_customer();
			
			if ( $stripe_id = it_exchange_get_stripe_customer_id( $it_exchange_customer->id ) )
				$stripe_customer = Stripe_Customer::retrieve( $stripe_id );
				
			// If the user has been deleted from Stripe, we need to create a new Stripe ID.
			if ( ! empty( $stripe_customer ) ) {
				
				if ( true === $stripe_customer->deleted )
					$stripe_customer = array();
					
			}
						
			// If this user isn't an existing Stripe User, create a new Stripe ID for them...
			if ( ! empty( $stripe_customer ) ) {
				
				$stripe_customer->card = $token;
				$stripe_customer->email = $it_exchange_customer->data->user_email;
				$stripe_customer->save();
				
			} else {
		
				$customer_array = array(
						'email' => $it_exchange_customer->data->user_email,
						'card'  => $token,
				);
				
				// Creates a new Stripe ID for this customer
				$stripe_customer = Stripe_Customer::create( $customer_array );
				
				it_exchange_set_stripe_customer_id( $it_exchange_customer->id, $stripe_customer->id );
				
			}
					
			// Now that we have a valid Customer ID, charge them!
			$charge = Stripe_Charge::create(array(
				'customer' 		=> $stripe_customer->id,
				'amount'   		=> number_format( $transaction_object->total, 2, '', '' ),
				'currency' 		=> $general_settings['default-currency'],
				'description'	=> $transaction_object->description,
			));
			
		}
		catch ( Exception $e ) {
			
			it_exchange_add_message( 'error', $e->getMessage() );
			return false;
				
		}
		
		return it_exchange_add_transaction( 'stripe', $charge->id, 'succeeded', $it_exchange_customer->id, $transaction_object );
		
	}

	it_exchange_add_message( 'error', __( 'Unknown error. Please try again later.', 'LION' ) );
	return false;
	
}
add_action( 'it_exchange_do_transaction_stripe', 'it_exchange_process_stripe_transaction', 10, 2 );

function it_exchange_get_stripe_customer_id( $customer_id ) {
	return get_user_meta( $customer_id, '_it_exchange_stripe_id', true );
}

function it_exchange_set_stripe_customer_id( $customer_id, $stripe_id ) {
	return update_user_meta( $customer_id, '_it_exchange_stripe_id', $stripe_id );
}

function it_exchange_stripe_settings_callback() {
	$IT_Exchange_Stripe_Add_On = new IT_Exchange_Stripe_Add_On();
	$IT_Exchange_Stripe_Add_On->print_settings_page();
}

function stripe_print_wizard_settings( $form ) {
	$IT_Exchange_Stripe_Add_On = new IT_Exchange_Stripe_Add_On();
	$settings = it_exchange_get_option( 'addon_stripe', true );
	?>
	<div class="field stripe-wizard hide-if-js">
    <?php $IT_Exchange_Stripe_Add_On->get_stripe_payment_form_table( $form, $settings ); ?>
	</div>
	<?php
}

function stripe_save_wizard_settings() {
	$IT_Exchange_Stripe_Add_On = new IT_Exchange_Stripe_Add_On();
	$IT_Exchange_Stripe_Add_On->stripe_save_wizard_settings();
}
add_action( 'it_exchange_save_wizard_settings', 'stripe_save_wizard_settings' );

/**
 * Default settings for stripe
 *
 * @since 0.4.0
 *
 * @param array $values
 * @return array
*/
function it_exchange_stripe_addon_default_settings( $values ) {
	$defaults = array(
		'stripe-test-mode'            => false,
		'stripe-live-secret-key'      => '',
		'stripe-live-publishable-key' => '', 
		'stripe-test-secret-key'      => '',
		'stripe-test-publishable-key' => '', 
	);   
	$values = ITUtility::merge_defaults( $values, $defaults );
	return $values;
}
add_filter( 'it_storage_get_defaults_exchange_addon_stripe', 'it_exchange_stripe_addon_default_settings' );

/**
 * Returns the button for making the payment
 *
 * @since 0.4.0
 *
 * @param array $options
 * @return string HTML button
*/
function it_exchange_stripe_addon_make_payment_button( $options ) { 
	
	$general_settings = it_exchange_get_option( 'settings_general' );
	$stripe_settings = it_exchange_get_option( 'addon_stripe' );
	
	$publishable_key = ( $stripe_settings['stripe-test-mode'] ) ? $stripe_settings['stripe-test-publishable-key'] : $stripe_settings['stripe-live-publishable-key'];

	$products = it_exchange_get_cart_data( 'products' );
	
	$payment_form = '<form action="' . it_exchange_get_page_url( 'transaction' ) . '" method="post">';
	$payment_form .= '<input type="hidden" name="it-exchange-transaction-method" value="stripe" />';
	$payment_form .= wp_nonce_field( 'stripe-checkout', '_stripe_nonce', true, false );
	
	$payment_form .= '<div class="hide-if-no-js">';
	$payment_form .= '<script
						  src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button"
						  data-key="' . $publishable_key . '"
						  data-amount="' . number_format( it_exchange_get_cart_total( false ), 2, '', '' ) . '"
						  data-name="' . $general_settings['company-name'] . '"
						  data-description="' . it_exchange_get_cart_description() . '"
						  data-currency="' . $general_settings['default-currency'] . '">
						</script>';
			
	$payment_form .= '</form>';
	$payment_form .= '</div>';
	
	$payment_form .= '<div class="hide-if-js">';
	
	$payment_form .= '<h3>' . __( 'JavaScript disabled: Stripe Payment Gateway cannot be loaded!', 'LION' ) . '</h3>';
	
	$payment_form .= '</div>';
	
	return $payment_form;
}
add_filter( 'it_exchange_get_stripe_make_payment_button', 'it_exchange_stripe_addon_make_payment_button', 10, 2 );

/**
 * Filters default currencies to only display those supported by Stripe
 *
 * @since 0.4.0
 * 
 * @param array $default_currencies Array of default currencies supplied by iThemes Exchange
 * @return array filtered list of currencies only supported by Stripe
 */
function it_exchange_get_stripe_currency_options( $default_currencies ) {
	
	$stripe_currencies = IT_Exchange_Stripe_Add_On::get_supported_currency_options();
	
	return array_intersect_key( $default_currencies, $stripe_currencies );
	
}
add_filter( 'it_exchange_get_currency_options', 'it_exchange_get_stripe_currency_options' );


function it_exchange_stripe_webhook_key( $webhooks ) {

	$webhooks[] = apply_filters( 'it_exchange_stripe_webhook', 'it_exchange_stripe' );
	
	return $webhooks;
	
}
add_filter( 'it_exchange_webhook_keys', 'it_exchange_stripe_webhook_key' );

/**
 * Processes webhooks for Stripe
 *
 * @since 0.4.0
 * @todo actually handle the exceptions 
 *
 * @param array $request really just passing  $_REQUEST
 */
function it_exchange_stripe_process_webhook( $request ) {

	$general_settings = it_exchange_get_option( 'settings_general' );
	$settings = it_exchange_get_option( 'addon_stripe' );
	
	$secret_key = ( $settings['stripe-test-mode'] ) ? $settings['stripe-test-secret-key'] : $settings['stripe-live-secret-key'];
	Stripe::setApiKey( $secret_key );

	$body = @file_get_contents('php://input');
	$stripe_event = json_decode( $body );
	
	$f = fopen( 'webhooks.txt', 'a' );
	fwrite( $f, print_r( $stripe_event, true ) );
	fclose( $f );
	
	// for extra security, retrieve from the Stripe API
	if ( isset( $stripe_event->id ) ) {
				
		try {
			
			if ( isset( $stripe_event->customer ) )
				$stripe_id = $stripe_event->customer;
				
			$stripe_object = $stripe_event->data->object;

			//https://stripe.com/docs/api#event_types
			switch( $stripe_event->type ) :

				case 'charge.succeeded' :
					it_exchange_update_transaction_status_for_stripe( $stripe_object->id, 'succeeded' );
					break;
				case 'charge.failed' :
					it_exchange_update_transaction_status_for_stripe( $stripe_object->id, 'failed' );
					break;
				case 'charge.refunded' :
					if ( $stripe_object->refunded )
						it_exchange_update_transaction_status_for_stripe( $stripe_object->id, 'refunded' );
					else
						it_exchange_update_transaction_status_for_stripe( $stripe_object->id, 'partial-refund' );
					
					it_ecxhange_add_refund_to_transaction_for_stripe( $stripe_object->id, $stripe_object->amount_refunded );
						
					break;
				case 'charge.dispute.created' :
				case 'charge.dispute.updated' :
				case 'charge.dispute.closed' :
					it_exchange_update_transaction_status_for_stripe( $stripe_object->charge, $stripe_object->status );
					break;
				case 'customer.deleted' :
					it_exchange_delete_stripe_id_from_customer( $stripe_object->id );
					break;

			endswitch;

		} catch ( Exception $e ) {
			
			// What are we going to do here?
			
		}
	}
	
}
add_action( 'it_exchange_webhook_it_exchange_stripe', 'it_exchange_stripe_process_webhook' );

function it_exchange_get_transaction_from_stripe_id( $stripe_id ) {
	$args = array(
		'meta_key'    => '_it_exchange_transaction_method_id',
		'meta_value'  => $stripe_id,
		'numberposts' => 1, //we should only have one, so limit to 1
	);
	return it_exchange_get_transactions( $args );
}

function it_exchange_update_transaction_status_for_stripe( $stripe_id, $new_status ) {
	$transactions = it_exchange_get_transaction_from_stripe_id( $stripe_id );
	foreach( $transactions as $transaction ) { //really only one
		$current_status = $transaction->get_transaction_status();
		if ( $new_status !== $current_status )
			$transaction->update_transaction_status( $new_status );
	}	
}

function it_ecxhange_add_refund_to_transaction_for_stripe( $stripe_id, $refund ) {
	$transactions = it_exchange_get_transaction_from_stripe_id( $stripe_id );
	foreach( $transactions as $transaction ) { //really only one
		$refunds = $transaction->get_transaction_refunds();
		
		$refunded_amount = 0;
		foreach( $refunds as $refund_meta ) {
			$refunded_amount += number_format( $refund_meta['amount'], '2', '', '' );
		}
		
		// In Stripe the Refund is the total amount that has been refunded, not just this transaction
		$this_refund = $refund - $refunded_amount;
		
		$transaction->add_transaction_refund( number_format( $this_refund, '2', '.', '' ) );
	}	
	
}

function it_exchange_delete_stripe_id_from_customer( $stripe_id ) {
	$transactions = it_exchange_get_transaction_from_stripe_id( $stripe_id );
	foreach( $transactions as $transaction ) { //really only one
		$customer_id = get_post_meta( $transaction->ID, '_it_exchange_customer_id', true );
		if ( false !== $current_stripe_id = it_exchange_get_stripe_customer_id( $customer_id ) ) {
			
			if ( $current_stripe_id === $stripe_id )
				delete_user_meta( $customer_id, '_it_exchange_stripe_id' );
				
		}
	}	
}

function it_exchange_transaction_status_label_stripe( $status ) {

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
add_filter( 'it_exchange_transaction_status_label_stripe', 'it_exchange_transaction_status_label_stripe' );

/**
 * Class for Stripe
 * @since 0.4.0
*/
class IT_Exchange_Stripe_Add_On {

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
	function IT_Exchange_Stripe_Add_On() {
		$this->_is_admin                      = is_admin();
		$this->_current_page                 = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'stripe' == $this->_current_add_on ) {
			add_action( 'it_exchange_save_add_on_settings_stripe', array( $this, 'save_settings' ) );
			do_action( 'it_exchange_save_add_on_settings_stripe' );
		}

		//add_filter( 'it_storage_get_defaults_exchange_addon_stripe', array( $this, 'set_default_settings' ) );
		
	}

	function print_settings_page() {
		$settings = it_exchange_get_option( 'addon_stripe', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_add_on_stripe', 'it-exchange-add-on-stripe-settings' ),
			'enctype' => apply_filters( 'it_exchange_add_on_stripe_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=stripe',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-stripe' ) );
	
		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		
		?>
		<div class="wrap">
			<?php $form->start_form( $form_options, 'it-exchange-stripe-settings' ); ?>
				<?php do_action( 'it_exchange_stripe_settings_form_top' ); ?>
				<?php $this->get_stripe_payment_form_table( $form, $form_values ); ?>
				<?php do_action( 'it_exchange_stripe_settings_form_bottom' ); ?>
				<p class="submit">
					<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary' ) ); ?>
				</p>
			<?php $form->end_form(); ?>
			<?php do_action( 'it_exchange_stripe_settings_page_bottom' ); ?>
		</div>
		<?php
	}
	
	function get_stripe_payment_form_table( $form, $settings = array() ) {	
		
		$general_settings = it_exchange_get_option( 'settings_general' );
		
		if ( !empty( $settings ) )
			foreach ( $settings as $key => $var )
				$form->set_option( $key, $var );
				
		?>
		<h3><?php _e( 'Stripe Payment Settings', 'LION' ); ?></h3>
        <p><?php _e( 'Do not have a Stripe account yet? <a href="http://stripe.com" target="_blank">Go set one up here</a>.', 'LION' ); ?></p>
        <label for="stripe-test-mode"><?php _e( 'Enable Stripe Test Mode?', 'LION' ); ?> <span class="tip" title="<?php _e( 'Enable Stripe Test Mode', 'LION' ); ?>">i</span></label>
        <?php $form->add_check_box( 'stripe-test-mode' ); ?>
        <label for="stripe-live-secret-key"><?php _e( 'Live Secret Key', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'stripe-live-secret-key' ); ?>
        <label for="stripe-live-publishable-key"><?php _e( 'Live Publishable Key', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'stripe-live-publishable-key' ); ?>
        <label for="stripe-test-secret-key"><?php _e( 'Test Secret Key', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'stripe-test-secret-key' ); ?>
        <label for="stripe-test-publishable-key"><?php _e( 'Test Publishable Key', 'LION' ); ?> <span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'LION' ); ?>">i</span></label>
        <?php $form->add_text_box( 'stripe-test-publishable-key' ); ?>
        <?php
		
			if ( !in_array( $general_settings['default-currency'], $this->get_supported_currency_options() ) ) {
			
				echo '<h3>' . sprintf( __( 'You are currently using a currency that is not supported by Stripe. <a href="%s">Please update your currency settings</a>.', 'LION' ), add_query_arg( 'page', 'it-exchange-settings' ) ) . '</h3>';
				
			}
			
		?>
        <h5><?php _e( 'Stripe Webhooks', 'LION' ); ?></h5>
        <p><?php _e( 'Webhooks can be configured in the <a href="https://manage.stripe.com/account/webhooks">webhook settings section</a> of the Stripe dashboard. Clicking Add URL will reveal a form to add a new URL for receiving webhooks.', 'LION' ); ?></p>
        <p><?php _e( 'Please log into your account and add this URL to your Webhooks so iThemes Exchange is notified of things like refunds, payments, etc.', 'LION' ); ?></p>
        <code><?php echo get_site_url(); ?>/?<?php echo apply_filters( 'it_exchange_stripe_webhook', 'it_exchange_stripe' ); ?>=1</code>
        
        <?php	
	}

	/**
	 * Save settings
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function save_settings() {
		$defaults = it_exchange_get_option( 'addon_stripe' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-stripe-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'LION' );
			return;
		}

		$errors = apply_filters( 'it_exchange_add_on_manual_transaction_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_stripe', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
		} else if ( $errors ) {
			$errors = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'LION' );
		}
	}
	
	function stripe_save_wizard_settings() {
		if ( !isset( $_REQUEST['it_exchange_settings-wizard-submitted'] ) )
			return;
			
		$stripe_settings = array();
		
		$default_wizard_stripe_settings = apply_filters( 'default_wizard_stripe_settings', array( 'stripe-title', 'stripe-instructions', 'stripe-default-status' ) );
		
		foreach( $default_wizard_stripe_settings as $var ) {
		
			if ( isset( $_REQUEST['it_exchange_settings-' . $var] ) ) {
				$stripe_settings[$var] = $_REQUEST['it_exchange_settings-' . $var];	
			}
			
		}
		
		$settings = wp_parse_args( $stripe_settings, it_exchange_get_option( 'addon_stripe' ) );
		
		if ( ! empty( $this->error_message ) || $error_msg = $this->get_form_errors( $settings ) ) {
			
			if ( ! empty( $error_msg ) ) {
				
				$this->error_message = $error_msg;
				return;
				
			}
				
		} else {
			it_exchange_save_option( 'addon_stripe', $settings );
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
		if ( empty( $values['stripe-live-secret-key'] ) )
			$errors[] = __( 'Please include your Stripe Live Secret Key', 'LION' );
		if ( empty( $values['stripe-live-publishable-key'] ) )
			$errors[] = __( 'Please include your Stripe Live Publishable Key', 'LION' );
			
		if ( !empty( $values['stripe-test-mode' ] ) ) {
			if ( empty( $values['stripe-test-secret-key'] ) )
				$errors[] = __( 'Please include your Stripe Test Secret Key', 'LION' );
			if ( empty( $values['stripe-test-publishable-key'] ) )
				$errors[] = __( 'Please include your Stripe Test Publishable Key', 'LION' );
		}

		return $errors;
	}

	/**
	 * Prints HTML options for default status
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function get_supported_currency_options() {
		$options = array( 'USD' => __( 'US Dollar' ), 'CAD' => __( 'Canadian Dollar' ) );
		return $options;
	}

	/**
	 * Sets the default options for manual payment settings
	 *
	 * @since 0.4.0
	 * @return array settings
	*/
	function set_default_settings( $defaults ) {
		return $defaults;
	}

}
