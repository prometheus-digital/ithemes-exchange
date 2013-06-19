<?php
/**
 * Hooks for Digital Downloads Add-on
 *
 * @package IT_Exchange
 * @since 0.2.0
*/

/**
 * This is the function registered in the options array when it_exchange_register_addon 
 * was called for Digital Downloads
 *
 * It tells Exchange where to find the settings page
 *
 * @since 0.4.5
 *
 * @return void
*/
function it_exchange_digital_downloads_settings_callback() {
	$IT_Exchange_Digital_Downloads_Add_On = new IT_Exchange_Digital_Downloads_Add_On();
	$IT_Exchange_Digital_Downloads_Add_On->print_settings_page();
}

class IT_Exchange_Digital_Downloads_Add_On {

	/**
	 * @var boolean $_is_admin true or false
	 * @since 0.4.5
	*/
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 * @since 0.4.5
	*/
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 * @since 0.4.5
	*/
	var $_current_add_on;

	/**
	 * @var string $status_message will be displayed if not empty
	 * @since 0.4.5
	*/
	var $status_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 * @since 0.4.5
	*/
	var $error_message;

	/**
	 * Class constructor
	 *
	 * Sets up the class.
	 * @since 0.4.5
	 * @return void
	*/
	function IT_Exchange_Digital_Downloads_Add_On() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'digital-downloads-product-type' == $this->_current_add_on ) {
			add_action( 'it_exchange_save_add_on_settings_digital_downloads', array( $this, 'save_settings' ) );
			do_action( 'it_exchange_save_add_on_settings_digital_downloads' );
		}
		
		add_filter( 'it_storage_get_defaults_exchange_addon_digital_downloads', array( $this, 'set_default_settings' ) );
	}

	/**
	 * Prints settings page
	 *
	 * @since 0.4.5
	 * @return void
	*/
	function print_settings_page() {
		$settings = it_exchange_get_option( 'addon_digital_downloads', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'it_exchange_add_on_digital_downloads', 'it-exchange-add-on-digital-downloads-settings' ),
			'enctype' => apply_filters( 'it_exchange_add_on_digital_downloads_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=digital-downloads-product-type',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-digital-downloads-product-type' ) );

		if ( ! empty ( $this->status_message ) )
			ITUtility::show_status_message( $this->status_message );
		if ( ! empty( $this->error_message ) )
			ITUtility::show_error_message( $this->error_message );

		?>
		<div class="wrap">
			<?php $form->start_form( $form_options, 'it-exchange-digitald-downloads-settings' ); ?>
				<?php 
				do_action( 'it_exchange_digital_downloads_settings_form_top' );
                		
				if ( !empty( $settings ) )
					foreach ( $settings as $key => $var )
						$form->set_option( $key, $var );
		
				?>
				<div class="it-exchange-addon-settings it-exchange-digital-downloads-addon-settings">
					<h3><?php _e( 'Digital Downloads Settings', 'LION' ); ?></h3>
					<p>
						<?php $form->add_check_box( 'require-user-login' ); ?>
						<label for="require-user-login"><?php _e( 'Require Users to Log In before downloading their products?', 'LION' ); ?> <span class="tip" title="<?php _e( 'If unchecked, users can simply download their products using their download links', 'LION' ); ?>">i</span></label>
					</p>
				</div>
				<?php

				do_action( 'it_exchange_digital_downloads_settings_form_bottom' ); 
				?>
				<p class="submit">
					<?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary button-large' ) ); ?>
				</p>
			<?php $form->end_form(); ?>
			<?php do_action( 'it_exchange_digital_downloads_settings_page_bottom' ); ?>
		</div>
		<?php
	}

	/**
	 * Save settings
	 *
	 * @since 0.4.5
	 * @return void
	*/
	function save_settings() {
		$defaults = it_exchange_get_option( 'addon_digital_downloads' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-digitald-downloads-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'LION' );
			return;
		}

		$errors = apply_filters( 'it_exchange_add_on_digital_downloads_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_digital_downloads', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
		} else if ( $errors ) {
			$errors = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'LION' );
		}
	}

	/**
	 * Validates for values
	 *
	 * Returns string of errors if anything is invalid
	 *
	 * @since 0.4.5
	 * @return void
	*/
	function get_form_errors( $values ) {

		$errors = array();

		return $errors;
	}

	/**
	 * Sets the default options for manual payment settings
	 *
	 * @since 0.4.5
	 * @return array settings
	*/
	function set_default_settings( $defaults ) {
		$defaults['require-user-login'] = true;
		return $defaults;
	}

}