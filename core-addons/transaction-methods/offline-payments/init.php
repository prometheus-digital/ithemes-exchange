<?php
/**
 * Offline Transaction Method
 *
 * @since 0.3.0
 * @package IT_Exchange
*/

/**
 * Mark this transaction method as okay to manually change transactions
 *
 * @since 0.4.11
*/
add_filter( 'it_exchange_offline-payments_transaction_status_can_be_manually_changed', '__return_true' );

/**
 * Returns status options
 *
 * @since 0.3.6
 * @return void
*/
function it_exchange_offline_payments_get_default_status_options() {
	$add_on = it_exchange_get_addon( 'offline-payments' );
	$options = empty( $add_on['options']['supports']['transaction_status']['options'] ) ? array() : $add_on['options']['supports']['transaction_status']['options'];
	return $options;
}
add_filter( 'it_exchange_get_status_options_for_offline-payments_transaction', 'it_exchange_offline_payments_get_default_status_options' );

/**
 * Call back for settings page
 *
 * This is set in options array when registering the add-on and called from it_exchange_enable_addon()
 *
 * @since 0.3.6
 * @return void
*/
function it_exchange_offline_payments_settings_callback() {
	$IT_Exchange_Offline_Payments_Add_On = new IT_Exchange_Offline_Payments_Add_On();
	$IT_Exchange_Offline_Payments_Add_On->print_settings_page();
}

/**
 * Outputs wizard settings for Offline Payments
 *
 * @since 0.4.0
 * @todo make this better, probably
 * @param object $form Current IT Form object
 * @return void
*/
function offline_payments_print_wizard_settings( $form ) {
	$IT_Exchange_Offline_Payments_Add_On = new IT_Exchange_Offline_Payments_Add_On();
	$settings = it_exchange_get_option( 'addon_offline_payments', true );
	?>
	<div class="field offline-payments-wizard hide-if-js">
	<?php $IT_Exchange_Offline_Payments_Add_On->get_offline_payment_form_table( $form, $settings ); ?>
	</div>
	<?php
}
add_action( 'it_exchange_print_wizard_settings', 'offline_payments_print_wizard_settings' );

function offline_payments_save_wizard_settings() {
	$IT_Exchange_Offline_Payments_Add_On = new IT_Exchange_Offline_Payments_Add_On();
	$IT_Exchange_Offline_Payments_Add_On->offline_payments_save_wizard_settings();
}
add_action( 'it_exchange_save_wizard_settings', 'offline_payments_save_wizard_settings' );
/**
 * This proccesses a stripe transaction.
 *
 * @since 0.4.0
 *
 * @param string $status passed by WP filter.
 * @param object $transaction_object The transaction object
*/
function it_exchange_offline_payments_addon_process_transaction( $status, $transaction_object ) {

	// If this has been modified as true already, return.
	if ( $status )
		return $status;

	// Verify nonce
	if ( ! empty( $_REQUEST['_offline_payments_nonce'] ) && ! wp_verify_nonce( $_REQUEST['_offline_payments_nonce'], 'offline-payments-checkout' ) ) {
		it_exchange_add_message( 'error', __( 'Transaction Failed, unable to verify security token.', 'LION' ) );
		return false;
		
	} else {

		$settings = it_exchange_get_option( 'addon_offline_payments' );
		
		$uniqid = it_exchange_get_offline_transaction_uniqid();

		// Get customer ID data
		$it_exchange_customer = it_exchange_get_current_customer();

		return it_exchange_add_transaction( 'offline-payments', $uniqid, $settings['offline-payments-default-status'], $it_exchange_customer->id, $transaction_object );
		
	}
	
	return false;

}
add_action( 'it_exchange_do_transaction_offline-payments', 'it_exchange_offline_payments_addon_process_transaction', 10, 2 );


function it_exchange_get_offline_transaction_uniqid() {
	
	$uniqid = uniqid( '', true );

	if( !it_exchange_verify_offline_transaction_unique_uniqid( $uniqid ) )
		$uniqid = it_exchange_get_offline_transaction_uniqid();

	return $uniqid;
	
}

function it_exchange_verify_offline_transaction_unique_uniqid( $uniqid ) {
	
	if ( !empty( $uniqid ) ) { //verify we get a valid 32 character md5 hash
		
		$args = array(
			'post_type' => 'it_exchange_tran',
			'meta_query' => array(
				array(
					'key' => '_it_exchange_transaction_method',
					'value' => 'offline-payments',
				),
				array(
					'key' => '_it_exchange_transaction_method_id',
					'value' => $uniqid ,
				),
			),
		);
		
		$query = new WP_Query( $args );
		
		return ( !empty( $query ) );
	
	}
	
	return false;
	
}

/**
 * Returns the button for making the payment
 *
 * @since 0.4.0
 *
 * @param array $options
 * @return string
*/
function it_exchange_offline_payments_addon_make_payment_button( $options ) {
	
	if ( 0 >= it_exchange_get_cart_total( false ) )
		return;

	$general_settings = it_exchange_get_option( 'settings_general' );
	$stripe_settings = it_exchange_get_option( 'addon_offline_payments' );
	
	$products = it_exchange_get_cart_data( 'products' );

	$payment_form = '<form id="offline_payment_form" action="' . it_exchange_get_page_url( 'transaction' ) . '" method="post">';
	$payment_form .= '<input type="hidden" name="it-exchange-transaction-method" value="offline-payments" />';
	$payment_form .= wp_nonce_field( 'offline-payments-checkout', '_offline_payments_nonce', true, false );

	$payment_form .= '<input type="submit" id="offline-payments-button" name="offline_payments_purchase" value="' . it_exchange_get_transaction_method_name_from_slug( 'offline-payments' ) .'" />';

	$payment_form .= '</form>';

	/*
	 * Going to remove this for now. It should be
	 * the responsibility of the site owner to
	 * notify if Javascript is disabled, but I will
	 * revisit this in case we want to a notifications.
	 *
	$payment_form .= '<div class="hide-if-js">';

	$payment_form .= '<h3>' . __( 'JavaScript disabled: Stripe Payment Gateway cannot be loaded!', 'LION' ) . '</h3>';

	$payment_form .= '</div>';
	*/

	return $payment_form;
	
}
add_filter( 'it_exchange_get_offline-payments_make_payment_button', 'it_exchange_offline_payments_addon_make_payment_button', 10, 2 );

/**
 * Replace Offline name with what is set in admin settings
 *
 * @since 0.3.7
 * @param string $name the name passed in from the WP filter API
 * @return string
*/
function it_exchange_get_offline_payments_name( $name ) {
	$options = it_exchange_get_option( 'addon_offline_payments' );
	if ( ! empty( $options['offline-payments-title'] ) )
		$name = $options['offline-payments-title'];
	return $name;
}
add_filter( 'it_exchange_get_transaction_method_name_offline-payments', 'it_exchange_get_offline_payments_name', 9 );

/**
 * Adds manual transactions template path inf on confirmation page
 *
 * @since 0.3.8
 * @return array of possible template paths + offline-payments template path
*/
function it_exchange_offline_payments_add_template_path( $paths ) {
	if ( it_exchange_is_page( 'confirmation' ) )
		$paths[] = dirname( __FILE__ ) . '/templates/';
	return $paths;
}
add_filter( 'it_exchange_possible_template_paths', 'it_exchange_offline_payments_add_template_path' );

function it_exchange_transaction_instructions_offline_payments( $instructions ) {
	$options = it_exchange_get_option( 'addon_offline_payments' );
	if ( ! empty( $options['offline-payments-instructions'] ) )
		$instructions = $options['offline-payments-instructions'];
	return $instructions;
	
}
add_filter( 'it_exchange_transaction_instructions_offline-payments', 'it_exchange_transaction_instructions_offline_payments' );

/**
 * Gets the interpretted transaction status from valid transaction statuses
 *
 * @since 0.4.0
 *
 * @param string $status the string of the stripe transaction
 * @return string translaction transaction status
*/
function it_exchange_offline_payments_addon_transaction_status_label( $status ) {

	switch ( $status ) {
		case 'succeeded':
		case 'paid':
			return __( 'Paid', 'LION' );
			break;
		case 'refunded':
			return __( 'Refunded', 'LION' );
			break;
		case 'pending':
			return __( 'Pending', 'LION' );
			break;
		case 'voided':
			return __( 'Voided', 'LION' );
			break;
		default:
			return __( 'Unknown', 'LION' );
	}

}
add_filter( 'it_exchange_transaction_status_label_offline-payments', 'it_exchange_offline_payments_addon_transaction_status_label' );

/**
 * Returns a boolean. Is this transaction a status that warrants delivery of any products attached to it?
 *
 * @since 0.4.2
 *
 * @param boolean $cleared passed in through WP filter. Ignored here.
 * @param object $transaction
 * @return boolean
*/
function it_exchange_offline_payments_transaction_is_cleared_for_delivery( $cleared, $transaction ) { 
    $valid_stati = array( 'succeeded', 'paid' );
    return in_array( it_exchange_get_transaction_status( $transaction ), $valid_stati );
}
add_filter( 'it_exchange_offline-payments_transaction_is_cleared_for_delivery', 'it_exchange_offline_payments_transaction_is_cleared_for_delivery', 10, 2 );

/**
 * Class for Offline
 * @since 0.3.6
*/
class IT_Exchange_Offline_Payments_Add_On {

	/**
	 * @var boolean $_is_admin true or false
	 * @since 0.3.6
	*/
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 * @since 0.3.6
	*/
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 * @since 0.3.6
	*/
	var $_current_add_on;

	/**
	 * @var string $status_message will be displayed if not empty
	 * @since 0.3.6
	*/
	var $status_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 * @since 0.3.6
	*/
	var $error_message;

	/**
 	 * Class constructor
	 *
	 * Sets up the class.
	 * @since 0.3.6
	 * @return void
	*/
	function IT_Exchange_Offline_Payments_Add_On() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'offline-payments' == $this->_current_add_on ) {
			add_action( 'it_exchange_save_add_on_settings_offline-payments', array( $this, 'save_settings' ) );
			do_action( 'it_exchange_save_add_on_settings_offline-payments' );
		}

		add_filter( 'it_storage_get_defaults_exchange_addon_offline_payments', array( $this, 'set_default_settings' ) );
	}

	function print_settings_page() {
		$settings = it_exchange_get_option( 'addon_offline_payments', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_add_on_offline_payments', 'it-exchange-add-on-offline-payments-settings' ),
			'enctype' => apply_filters( 'it_exchange_add_on_offline_payments_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=offline-payments',
		);
		$form = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-offline-payments' ) );

		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );
		include( 'view-add-on-settings.php' );
	}

	function get_offline_payment_form_table( $form, $settings = array() ) {
		$default_status_options = it_exchange_offline_payments_get_default_status_options();

		if ( !empty( $settings ) )
			foreach ( $settings as $key => $var )
				$form->set_option( $key, $var );

		?>
        <p><?php _e( 'Offline payments allow you the option to allow people to buy your products from your site but to pay via check or cash. Transactions can be set as pending until you get paid.', 'LION' ); ?></p>
		<table class="form-table">
			<?php do_action( 'it_exchange_offline_payments_settings_table_top' ); ?>
			<tr valign="top">
				<th scope="row"><label for="offline-payments-title"><?php _e( 'Title', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'offline-payments-title', array( 'class' => 'normal-text' ) ); ?>
					<br /><span class="description"><?php _e( 'What would you like to title this payment option? eg: Check', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="offline-payments-instructions"><?php _e( 'Instructions after purchase', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_area( 'offline-payments-instructions', array( 'cols' => 50, 'rows' => 5, 'class' => 'normal-text' ) ); ?>
					<br /><span class="description"><?php _e( 'Use this field to give your customers instructions for payment after purchase.', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="offline-payments-default-status"><?php _e( 'Default Payment Status', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_drop_down( 'offline-payments-default-status', $default_status_options ); ?>
				</td>
			</tr>
			<?php do_action( 'it_exchange_offline_payments_settings_table_bottom' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
		</table>
		<?php
	}

	/**
	 * Save settings
	 *
	 * @since 0.3.6
	 * @return void
	*/
	function save_settings() {
		$defaults = it_exchange_get_option( 'addon_offline_payments' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-offline-payments-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'LION' );
			return;
		}

		$errors = apply_filters( 'it_exchange_add_on_manual_transaction_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_offline_payments', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
		} else if ( $errors ) {
			$errors = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'LION' );
		}
	}

	function offline_payments_save_wizard_settings() {
		if ( !isset( $_REQUEST['it_exchange_settings-wizard-submitted'] ) )
			return;

		$offline_payments_settings = array();

		$default_wizard_offline_payments_settings = apply_filters( 'default_wizard_offline_payments_settings', array( 'offline-payments-title', 'offline-payments-instructions', 'offline-payments-default-status' ) );

		foreach( $default_wizard_offline_payments_settings as $var ) {

			if ( isset( $_REQUEST['it_exchange_settings-' . $var] ) ) {
				$offline_payments_settings[$var] = $_REQUEST['it_exchange_settings-' . $var];
			}

		}

		$settings = wp_parse_args( $offline_payments_settings, it_exchange_get_option( 'addon_offline_payments' ) );

		if ( ! empty( $this->error_message ) || $error_msg = $this->get_form_errors( $settings ) ) {

			if ( ! empty( $error_msg ) ) {

				$this->error_message = $error_msg;
				return;

			}

		} else {
			it_exchange_save_option( 'addon_offline_payments', $settings );
			$this->status_message = __( 'Settings Saved.', 'LION' );
		}

	}

	/**
	 * Validates for values
	 *
	 * Returns string of errors if anything is invalid
	 *
	 * @since 0.3.6
	 * @return void
	*/
	function get_form_errors( $values ) {
		$errors = array();
		if ( empty( $values['offline-payments-title'] ) )
			$errors[] = __( 'The Title field cannot be left blank', 'LION' );
		if ( empty( $values['offline-payments-instructions'] ) )
			$errors[] = __( 'Please leave some instructions for customers checking out with this transaction method', 'LION' );

		$valid_status_options = it_exchange_offline_payments_get_default_status_options();
		if ( empty( $values['offline-payments-default-status'] ) || empty( $valid_status_options[$values['offline-payments-default-status']] ) )
			$errors[] = __( 'Please select a valid default transaction status.', 'LION' );

		return $errors;
	}

	/**
	 * Sets the default options for manual payment settings
	 *
	 * @since 0.3.6
	 * @return array settings
	*/
	function set_default_settings( $defaults ) {
		$defaults['offline-payments-title']          = __( 'Pay with check', 'LION' );
		$defaults['offline-payments-instructions']   = __( 'Thank you for your order. We will contact you shortly for payment.', 'LION' );
		$defaults['offline-payments-default-status'] = 'pending';
		return $defaults;
	}
}
