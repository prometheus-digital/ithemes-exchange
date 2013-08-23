<?php
class IT_Exchange_Provider_Settings_Page {
	var $provider = false;
	var $provider_settings = array();

	function IT_Exchange_Provider_Settings_Page( $slug ) {
		if ( ! $provider = it_exchange_get_shipping_provider( $slug ) )
			return false;

		if ( ! $provider->has_settings_page )
			return false;

		$this->provider = $provider;
		$this->provider_settings = $provider->get_provider_settings();

		$this->save_settings();
	}

	function print_settings_page() {

		$settings     = it_exchange_get_option( 'addon_shipping_' . $this->provider->slug, true );
		$form_values  = ! it_exchange_has_messages( 'error' ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => 'it-exchange-add-on-shipping-settings-for-' . $this->provider->slug,
			'enctype' => false,
			'action'  => add_query_arg( array( 'page' => 'it-exchange-settings', 'tab' => 'shipping', 'provider' => $this->provider->slug ), admin_url( 'admin' ) ),
		);  
		$this->form = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-shipping-' . $this->provider->slug ) );
		$this->form->start_form( $form_options, 'it-exchange-shipping-settings-for-' . $this->provider->slug );

		// Print Errors
		if ( it_exchange_has_messages( 'error' ) ) {
			foreach( it_exchange_get_messages( 'error' ) as $message ) {
				ITUtility::show_error_message( $message );
			}
		}
		// Print Notices
		if ( it_exchange_has_messages( 'notice' ) ) {
			foreach( it_exchange_get_messages( 'notice' ) as $message ) {
				ITUtility::show_status_message( $message );
			}
		}
		?>

		<table class="form-table">
			<?php do_action( 'it_exchange_general_settings_shipping_' . $this->provider->slug . '_top' ); ?>
			<?php
			foreach( $this->provider_settings as $key => $options ) {
				if ( 'heading' == $options['type'] ) {
					$this->print_heading_row( $options );
				} else {
					$form_method = 'add_' . $options['type'];
					if ( is_callable( array( $this->form, $form_method ) ) )
						$this->print_setting_row( $options, $form_method );
					else
						$this->print_uncallable_method_row( $options );
				}
			}
			$this->form->add_hidden( 'processing-shipping-settings', true );
			?>
			<?php do_action( 'it_exchange_general_settings_shipping_' . $this->provider->slug . '_bottom' ); ?>
		</table>
		<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
		<?php
		$this->form->end_form();
	}

	function print_heading_row( $heading ) {
		?>
		<tr valign="top">
			<th scope="row"><strong><?php echo $heading['label']; ?></strong></th>
			<td></td>
		</tr>
		<?php
	}

	function print_setting_row( $setting, $form_method ) {
		?>
		<tr valign="top">
			<th scope="row"><label for="<?php esc_attr_e( $setting['slug'] ); ?>"><?php echo $setting['label']; ?></label></th>
			<td>
				<?php $this->form->$form_method( $setting['slug'], $setting['options'] ); ?>
			</td>
		</tr>
		<?php
	}

	function print_uncallable_method_row( $setting ) {
		?>
		<tr valign="top">
			<th scope="row" class="error"><strong><?php _e( 'Coding Error!', 'LION' ); ?></strong></th>
			<td><?php printf( __( 'The setting for %s has an incorrect type argument. No such method exists in the ITForm class', 'LION' ), $setting['slug'] ); ?></td>
		</tr>
		<?php
	}

	function save_settings() {

		// Abandon if not processing
		if ( empty( $_POST['_wpnonce'] ) || empty( $_POST['it-exchange-add-on-shipping-' . $this->provider->slug . '-processing-shipping-settings'] ) )
			return;

		// Log error if nonce wasn't set
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-shipping-settings-for-' . $this->provider->slug ) ) {
			it_exchange_add_message( 'error', 'Problem with nonce' );
			return;
		}

		$values = ITForm::get_post_data();
		unset( $values['processing-shipping-settings'] );

		$this->provider->update_settings( $values );
		it_exchange_add_message( 'notice', sprintf( __( '%s settings updated', 'LION' ), $this->provider->label ) );
	}
}
