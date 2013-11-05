<?php
/**
 * Adds our template directory to the list of possible sources
 *
 * Only adds it if were looking for one of our templates. No need
 * to scan our directory if we know we don't have the template being requeste
 *
 * @since CHANGEME
 *
 * @return array
*/
function it_exchange_guest_checkout_add_template_directory( $template_paths, $template_names ) {

	/** 
	 * Use the template_names array to target a specific template part you want to add
	 * In this example, we're adding the following template part: super-widget-registration/elements/guest.php
	 * So we're going to only add our templates directory if Exchange is looking for that part.
	*/
	$found = false;
	foreach( $template_names as $template_name ) {
		if ( false !== ( strpos( 'guest-checkout', $template_name ) ) ) {
			$found = true;
			continue;
		}
		if ( 'super-widget-registration/elements/guest.php' == $template_name ) {
			$found = true;
			continue;
		}
	}
	if ( $found )
		return $template_paths;

	/** 
	 * If we are looking for the mailchimp-signup template part, go ahead and add our add_ons directory to the list
	 * No trailing slash
	*/
	$template_paths[] = dirname( __FILE__ ) . '/templates';

	return $template_paths;

}
add_filter( 'it_exchange_possible_template_paths', 'it_exchange_guest_checkout_add_template_directory', 10, 2 );

/**
 * Add the register and login links if settings allows them
 *
 * @since CHANGEME
 *
 * @param  array $actions
 * @return array
*/
function it_exchange_guest_checkout_modify_guest_checkout_purchase_requirement_form_actions( $actions ) {
	$general_settings = it_exchange_get_option( 'settings_general' );
	if ( 'wp' == $general_settings['site-registration'] && ! get_option( 'users_can_register' ) ) 
		return $actions;

	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );

	// Remove cancel action if it is present
	$cancel = array_search( 'cancel', $actions );
	if ( false !== $cancel )
		unset( $actions[$cancel] );

	// Show Reg link if settings have enabled it.
	if ( ! empty( $guest_checkout_settings['show-registration-link'] ) )
		$actions[] = 'register';

	// Show Log in link if settings have enabled it.
	if ( ! empty( $guest_checkout_settings['show-log-in-link'] ) )
		$actions[] = 'login';

	// Add cancel back to array
	$actions[] = 'cancel';

	return $actions;
}
add_filter( 'it_exchange_get_super_widget_guest_checkout_actions_elements', 'it_exchange_guest_checkout_modify_guest_checkout_purchase_requirement_form_actions' );

/**
 * Remove the login link from the super-widget 'Register' state if guest checkout settings have disabled it.
 *
 * @since CHANGEME
 *
 * @param  array $actions incoming actions already registered
 * @return array
*/
function it_exchange_remove_login_link_from_register_sw_state( $actions ) {
	$general_settings = it_exchange_get_option( 'settings_general' );
	if ( 'wp' == $general_settings['site-registration'] && ! get_option( 'users_can_register' ) ) 
		return $actions;

	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
	$index = array_search( 'cancel', $actions );
	if ( false !== $index && empty( $guest_checkout_settings['show-log-in-link'] ) )
		unset( $actions[$index] );

	return $actions;
}
add_filter( 'it_exchange_get_super_widget_registration_fields_elements', 'it_exchange_remove_login_link_from_register_sw_state' );

/**
 * Remove the register link from the super-widget 'Log in' state if guest checkout settings have disabled it.
 *
 * @since CHANGEME
 *
 * @param  array $actions incoming actions already registered
 * @return array
*/
function it_exchange_remove_register_link_from_login_sw_state( $actions ) {
	$general_settings = it_exchange_get_option( 'settings_general' );
	if ( 'wp' == $general_settings['site-registration'] && ! get_option( 'users_can_register' ) ) 
		return $actions;

	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
	$index = array_search( 'register', $actions );
	if ( false !== $index && empty( $guest_checkout_settings['show-registration-link'] ) )
		unset( $actions[$index] );

	return $actions;
}
add_filter( 'it_exchange_get_super_widget_login_actions_elements', 'it_exchange_remove_register_link_from_login_sw_state' );

/**
 * Returns the Guest Checkout title
 *
 * @since CHANGME
 * @return string
*/
function it_exchange_guest_checkout_get_heading() {
	$class = (bool) it_exchange_in_superwidget() ? ' class="in-super-widget"' : '';
	$heading = '<h3' . $class . '>' . __( 'Guest Checkout', 'LION' ) . '</h3>';
	$heading = apply_filters( 'it_exchange_guest_checkout_get_heading', $heading, $class );
	return $heading;
}

/**
 * Prints the continue link for the Checkout page purchase requirement
 *
 * @since CHANGEME
 *
 * @return string
*/
function it_exchange_guest_checkout_get_purchase_requirement_continue_action() {
	?>
	<input type="submit" id="it-exchange-guest-checkout-action" name="continue" value="<?php esc_attr_e( 'Continue as guest', 'LION' ); ?>" />
	<?php
}

/**
 * Prints the link to checkout as Guest in the SW
 *
 * @since CHANGEME
 *
 * @param string $label What do you wan the link to say
 *
 * @return string
*/
function it_exchange_guest_checkout_sw_link( $label ) {
	return '<a class="it-exchange-guest-checkout-link" href=""><input type="button" value="' . esc_attr( $label ) . '" /></a>';
}

/**
 * This prints the email field in the various template parts
 *
 * @since CHANGEME
 *
 * @param array $options Options for format, output, etc
 * @return string
*/
function it_exchange_guest_checkout_get_email_field( $options=array() ) {
	$email = ! empty( $_POST['email'] ) && ! is_email( $_POST['email'] ) ? $_POST['email'] : '';
	$field = '<input type="text" name="email" class="it-exchange-guest-checkout-email" value="' . esc_attr( $email ) . '" placeholder="' . __( 'Email address', 'LION' ) . '" />';
	return $field;
}

/**
 * This prints the continue link in the SW
 *
 * @since CHANGEME
 *
 * @param array $options Options for format, output, etc
 * @return string
*/
function it_exchange_guest_checkout_get_sw_save_link( $options=array() ) {
	$link = '<input type="submit" class="it-exchange-guest-checkout-save-link" value="' . __( 'Continue', 'LION' ) . '" />';
	return $link;
}

/**
 * This prints the cancel link in the SW
 *
 * @since CHANGEME
 *
 * @param array $options Options for format, output, etc
 * @return string
*/
function it_exchange_guest_checkout_get_sw_cancel_link( $options=array() ) {
	$link = '<a href="" class="it-exchange-sw-cancel-guest-checkout-link">' . __( 'Cancel', 'LION' ) . '</a>';
	return $link;
}

/**
 * Adds guest-checkout as a valid super-widget state
 *
 * @since CHANGEME
 *
 * @param array $valid_states existing valid states
 * @return array
*/
function it_exchange_guest_checkout_modify_valid_sw_states( $valid_states ) {
	$valid_states[] = 'guest-checkout';
	return $valid_states;
}
add_filter( 'it_exchange_super_widget_valid_states', 'it_exchange_guest_checkout_modify_valid_sw_states' );

/**
 * Overwrites the core default_form setting for the guest_checkout value on the Checkout page.
 *
 * @since CHANGEME
 *
 * @param string $template_part
 * @return string
*/
function it_exchange_guest_checkout_override_logged_in_checkout_template_part( $template_part ) {
	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
	$form = empty( $guest_checkout_settings['default-form'] ) ? $template_part : 'guest-checkout';
	return $form;
}
add_filter( 'it_exchange_get_default_content_checkout_mode', 'it_exchange_guest_checkout_override_logged_in_checkout_template_part' );

/**
 * Overwrites the core default_form setting for the guest_checkout value in the SuperWidget.
 *
 * @since CHANGEME
 *
 * @param string $template_part
 * @return string
*/
function it_exchange_guest_checkout_override_logged_in_supwer_widget_template_part( $template_part ) {
	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
	$form = empty( $guest_checkout_settings['default-form'] ) ? $template_part : 'guest-checkout';
	return $form;
}
add_filter( 'it_exchange_get_default_sw_checkout_mode', 'it_exchange_guest_checkout_override_logged_in_supwer_widget_template_part' );

/**
 * Add the Guest Checkin UI to the checkout page registration view
 *
 * @since CHANGEME
 *
 * @param array $elements existing elements in the content loop of the logged-in requiremnt template
 * @return array
*/
function it_exchagne_guest_checkout_add_guest_checkout_template_part_to_logged_in_purchase_requirement( $elements ) {
	$elements[] = 'guest-checkout';
	return $elements;
}
add_filter( 'it_exchange_get_content-checkout-logged-in-purchase-requirements-not-logged-in_content_elements', 'it_exchagne_guest_checkout_add_guest_checkout_template_part_to_logged_in_purchase_requirement' );

/**
 * Add link back to Guest Checkout from Registration and Login forms on Checkout Page
 *
 * @since CHANGEME
 *
 * @param array $links incoming links
 * @return array
*/
function it_exchange_add_guest_checkout_links_to_logged_in_purchase_requirement_on_checkout_page( $links ) {
	$links[] = 'guest-checkout';
	return $links;
}
add_filter( 'it_exchange_get_content-checkout-logged-in-purchase-requirements-not-logged-in_links_elements', 'it_exchange_add_guest_checkout_links_to_logged_in_purchase_requirement_on_checkout_page' );

/**
 * Add Guest Checkout links to the SuperWidget Login / Registration Forms
 *
 * @since CHANGEME
 *
 * @param array $links incoming template parts from WP filter
 * @return array
*/
function it_exchange_add_guest_checkout_link_to_sw_registration_and_login_states( $links ) {
	$links[] = 'guest-checkout-link';
	return $links;
}
add_filter( 'it_exchange_get_super_widget_registration_actions_elements', 'it_exchange_add_guest_checkout_link_to_sw_registration_and_login_states' );

/**
 * Add Guest Checkout links to the SuperWidget Log in form
 *
 * @since CHANGEME
 *
 * @param array $links incoming template parts from WP filter
 * @return array
*/
function it_exchange_add_guest_checkout_link_to_sw_login_states( $links ) {

	if ( ! ( $index = array_search( 'recover', $links ) ) )
		$index = count( $links );
	array_splice( $links, $index, 0, 'guest-checkout-link' );

	return $links;
}
add_filter( 'it_exchange_get_super_widget_login_actions_elements', 'it_exchange_add_guest_checkout_link_to_sw_login_states' );

/**
 * Removes the User Menu links from the confirmation page if doing guest checkout
 *
 * @since CHANGEME
 *
 * @param  array $loops   the array of loops to include in the header
 * @return array modified loops missing the menu
*/
function it_exchange_remove_customer_menu_when_doing_guest_checkout( $loops ) { 
    if ( ! it_exchange_doing_guest_checkout() )
        return $loops;

	if ( false !== ( $index = array_search( 'menu', $loops ) ) )
		unset( $loops[$index] );

	return $loops;
}
add_filter( 'it_exchange_get_content_confirmation_header_loops', 'it_exchange_remove_customer_menu_when_doing_guest_checkout' );
