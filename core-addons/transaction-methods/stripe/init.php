<?php
/**
 * iThemes Exchange Stripe Add-on
 * @package IT_Exchange
 * @since 0.4.0
*/

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
 * Returns the button for making the payment
 *
 * @since 0.4.0
 *
 * @param array $options
 * @return string
*/
function it_exchange_stripe_addon_make_payment_button( $options ) { 
	$data = it_exchange_get_cart_data();
	
	ITDebug::print_r( $data );
	
	return '<script
  src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button"
  data-key="pk_test_SWKa1Yir0c7Euen5rHNQBX3q"
  data-amount="2000"
  data-name="Demo Site"
  data-description="2 widgets ($20.00)"
  data-currency="usd"
  data-image="/128x128.png">
</script>';
}
add_filter( 'it_exchange_get_stripe_make_payment_button', 'it_exchange_stripe_addon_make_payment_button', 10, 2 );

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

		add_filter( 'it_storage_get_defaults_exchange_addon_stripe', array( $this, 'set_default_settings' ) );
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
        <label for="stripe-currency"><?php _e( 'Currency', 'LION' ); ?> <span class="tip" title="<?php _e( 'What currency does your store accept?', 'LION' ); ?>">i</span></label>
        <?php $form->add_drop_down( 'stripe-currency', get_default_currency_options() ); ?>
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
	function get_default_currency_options() {
		$add_on = it_exchange_get_addon( 'stripe' );
		$options = array( 'usd' => __( 'US Dollars', 'LION' ), 'can' => __( 'Canadian Dollars', 'LION' )  );
		return $options;
	}

	/**
	 * Sets the default options for manual payment settings
	 *
	 * @since 0.4.0
	 * @return array settings
	*/
	function set_default_settings( $defaults ) {
		$defaults['stripe-default-currency'] = 'usd';
		return $defaults;
	}

}