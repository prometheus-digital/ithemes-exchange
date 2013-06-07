<?php
/**
 * Offline Transaction Method
 *
 * @since 0.3.0
 * @package IT_Exchange
*/

include( 'confirmation-template-functions.php' );

add_filter( 'it_exchange_get_transaction_method_name_offline-payments', 'it_exchange_get_offline_payments_name', 9 );
add_action( 'it_exchange_do_transaction_offline-payments', 'it_exchange_offline_payments_do_transaction', 10, 2 );
add_filter( 'it_exchange_possible_template_paths', 'it_exchange_offline_payments_add_template_path' );

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
 * Replace Offline name with what is set in admin settings
 *
 * @since 0.3.7
 * @param string $name the name passed in from the WP filter API
 * @return string
*/
function it_exchange_get_offline_payments_name( $name ) {
	$options = it_exchange_get_option( 'addon_offline_payments' );
	if ( ! empty( $options['offline_payments_title'] ) )
		$name = $options['offline_payments_title'];

	return $name;
}

/**
 * Processes the transaction from the cart
 *
 * @since 0.3.7
*/
function it_exchange_offline_payments_do_transaction( $status, $transaction_object ) {
	if ( $status ) //if this has been modified as true already, return.
		return $status;
	// Do transaction
	return it_exchange_add_transaction( 'offline-payments', time(), 'pending', false, $transaction_object );
}

/**
 * Adds manual transactions template path inf on confirmation page
 *
 * @since 0.3.8
 * @return array of possible template paths + offline-payments template path
*/
function it_exchange_offline_payments_add_template_path( $paths ) {
	if ( is_page( it_exchange_get_page_id( 'transaction-confirmation' ) ) )
		$paths[] = dirname( __FILE__ ) . '/templates/';
	return $paths;
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
	return '<input type="button" value="Offline Payments? Really?" />';
}
add_filter( 'it_exchange_get_offline-payments_make_payment_button', 'it_exchange_offline_payments_addon_make_payment_button', 10, 2 );

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
		$default_status_options = IT_Exchange_Offline_Payments_Add_On::get_default_status_options();

		if ( !empty( $settings ) )
			foreach ( $settings as $key => $var )
				$form->set_option( $key, $var );

		?>
		<h3><?php _e( 'Offline Payment Settings', 'LION' ); ?></h3>
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

		$valid_status_options = $this->get_default_status_options();
		if ( empty( $values['offline-payments-default-status'] ) || empty( $valid_status_options[$values['offline-payments-default-status']] ) )
			$errors[] = __( 'Please select a valid default transaction status.', 'LION' );

		return $errors;
	}

	/**
	 * Prints HTML options for default status
	 *
	 * @since 0.3.6
	 * @return void
	*/
	function get_default_status_options() {
		$add_on = it_exchange_get_addon( 'offline-payments' );
		$options = empty( $add_on['options']['supports']['transaction_status']['options'] ) ? array() : $add_on['options']['supports']['transaction_status']['options'];
		return $options;
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
