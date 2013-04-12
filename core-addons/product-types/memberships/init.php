<?php
/**
 * Membership Product Types
 *
 * @since 0.3.0
 * @package IT_Exchange
*/

if ( is_admin() ) {
	// Add metabox for membership duration
	add_action( 'it_exchange_product_metabox_callback_memberships-product-type', 'it_exchange_register_membership_product_type_metaboxes' );
	add_action( 'it_exchange_save_product-memberships-product-type', 'it_exchange_save_membership_product_options' );
}

/**
 * Register metaboxes for membership product type
 *
 * @since 0.3.8
 * @return void
*/
function it_exchange_register_membership_product_type_metaboxes( $post ) {
	add_meta_box( 'it-exchange-membership-options', __( 'Membership Options', 'LION' ), 'it_exchange_print_membership_options_metabox', $post->post_type, 'normal' );
}

/**
 * Prints the duration meta box
 *
 * @since 0.3.8
 * @return void
*/
function it_exchange_print_membership_options_metabox( $post ) {
	$membership_options = get_post_meta( $post->ID, '_it_exchange_membership_options', true );
	$duration_type = empty( $membership_options['duration_type'] ) ? 'timeframe' : $membership_options['duration_type'];
	$duration_timeframe_length = empty( $membership_options['duration_timeframe_length'] ) ? '30' : $membership_options['duration_timeframe_length'];
	$duration_timeframe_units = empty( $membership_options['duration_timeframe_units'] ) ? 'months' : $membership_options['duration_timeframe_units'];
	$duration_date = empty( $membership_options['duration_date'] ) ? '' : $membership_options['duration_date'];
	$cancellation_type = empty( $membership_options['cancellation_type'] ) ? 'immediately' : $membership_options['cancellation_type'];
	$customer_role = empty( $membership_options['customer_role'] ) ? 'subscriber' : $membership_options['customer_role'];
	$auto_renew = empty( $membership_options['auto_renew'] ) ? 'yes' : $membership_options['auto_renew'];
	?>
	<h4><?php _e( 'When will this membership expire?', 'LION' ); ?></h4>
	<p>
		<label for="it-exchange-membership-duration-type-timeframe">
			<input type="radio" id="it-exchange-membership-duration-type-timeframe" name="it-exchange-membership-duration-type" value="timeframe" <?php checked( 'timeframe', $duration_type ); ?> />
			&nbsp;<input size="5" type="text" name="it-exchange-membership-duration-timeframe-length" value="<?php esc_attr_e( $duration_timeframe_length ); ?>"/>&nbsp;
			<select name="it-exchange-membership-duration-timeframe-units">
				<option value="days" <?php selected( 'days', $duration_timeframe_units ); ?>><?php _e( 'Day(s)', 'LION' ); ?></option>
				<option value="weeks" <?php selected( 'weeks', $duration_timeframe_units ); ?>><?php _e( 'Week(s)', 'LION' ); ?></option>
				<option value="months" <?php selected( 'months', $duration_timeframe_units ); ?>><?php _e( 'Month(s)', 'LION' ); ?></option>
				<option value="years" <?php selected( 'years', $duration_timeframe_units ); ?>><?php _e( 'Year(s)', 'LION' ); ?></option>
			</select>&nbsp;<?php _e( 'after the purchase date', 'LION' ); ?>
		</label>
		<br />
		<label for="it-exchange-membership-duration-type-date">
			<input type="radio" id="it-exchange-membership-duration-type-date" name="it-exchange-membership-duration-type" value="date" <?php checked( 'date', $duration_type ); ?>/>&nbsp;<?php _e( 'On a specific date:', 'LION' ); ?>
			<input type="text" name="it-exchange-membership-duration-date" value="<?php esc_attr_e( $duration_date ); ?>" />
		</label><br />
		<label for="it-exchange-membership-duration-type-never">
			<input type="radio" id="it-exchange-membership-duration-type-never" name="it-exchange-membership-duration-type" value="never" <?php checked( 'never', $duration_type ); ?> />&nbsp;<?php _e( 'Never', 'LION' ); ?>
		</label>
	</p>

	<h4><?php _e( 'How fast should we revoke membership if the user cancels their purchase?', 'LION' ); ?></h4>
	<p>
		<label for="it-exchange-membership-cancellation-type-immediately">
			<input type="radio" id="it-exchange-membership-cancellation-type-immediately" name="it-exchange-membership-cancellation-type" value="immediately" <?php checked( 'immediately', $cancellation_type ); ?>/>&nbsp;<?php _e( 'Immediately', 'LION' ); ?>
		</label><br />
		<label for="it-exchange-membership-cancellation-type-normally">
			<input type="radio" id="it-exchange-membership-cancellation-type-normally" name="it-exchange-membership-cancellation-type" value="normally" <?php checked( 'normally', $cancellation_type ); ?>/>&nbsp;<?php _e( 'On the original expiration date', 'LION' ); ?>
		</label><br />
	</p>

	<h4><?php _e( 'What WordPress role should new members who purchase this product receive?', 'LION' ); ?></h4>
	<?php
	$roles = new WP_Roles();
	if ( ! empty( $roles->roles ) && is_array( $roles->roles ) ) {
		?><select name="it-exchange-membership-new-customer-role"><?php
		foreach( $roles->roles as $role => $atts ) {
			?><option value="<?php esc_attr_e( $role ); ?>" <?php selected( $role, $customer_role ); ?>><?php esc_attr_e( $atts['name'] ); ?></option><?php
		}
		?></select><?php
	}
	?>

	<h4><?php _e( 'Should we auto-renew the membership if the transaction method supports recurring payments?', 'LION' ); ?></h4>
	<p>
		<label for="it-exchange-membership-attempt-auto-renew-yes">
			<input type="radio" id="it-exchange-membership-attempt-auto-renew-yes" name="it-exchange-membership-attempt-auto-renew" value="yes" <?php checked( 'yes', $auto_renew ); ?>/>&nbsp;<?php _e( 'Yes', 'LION' ); ?>
		</label><br />
		<label for="it-exchange-membership-attempt-auto-renew-no">
			<input type="radio" id="it-exchange-membership-attempt-auto-renew-no" name="it-exchange-membership-attempt-auto-renew" value="no" <?php checked( 'no', $auto_renew ); ?>/>&nbsp;<?php _e( 'No', 'LION' ); ?>
		</label>
	</p>
	<?php
}

/**
 * Saves the membership options information
 *
 * @since 0.3.8
 * @return void
*/
function it_exchange_save_membership_product_options( $post ) {
	
	// Grab $_POST data or set false so that form will use defaults
	$duration_type = empty( $_POST['it-exchange-membership-duration-type'] ) ? false : $_POST['it-exchange-membership-duration-type'];
	$duration_timeframe_length = empty( $_POST['it-exchange-membership-duration-timeframe-length'] ) ? false : $_POST['it-exchange-membership-duration-timeframe-length'];
	$duration_timeframe_units = empty( $_POST['it-exchange-membership-duration-timeframe-units'] ) ? false : $_POST['it-exchange-membership-duration-timeframe-units'];
	$duration_date = empty( $_POST['it-exchange-membership-duration-date'] ) ? false : $_POST['it-exchange-membership-duration-date'];
	$cancellation_type = empty( $_POST['it-exchange-membership-cancellation-type'] ) ? false : $_POST['it-exchange-membership-cancellation-type'];
	$customer_role = empty( $_POST['it-exchange-membership-new-customer-role'] ) ? false : $_POST['it-exchange-membership-new-customer-role'];
	$auto_renew = empty( $_POST['it-exchange-membership-attempt-auto-renew'] ) ? false : $_POST['it-exchange-membership-attempt-auto-renew'];

	$membership_options = compact( 'duration_type', 'duration_timeframe_length', 'duration_timeframe_units', 'duration_date', 'cancellation_type', 'customer_role', 'auto_renew' );

	update_post_meta( $post, '_it_exchange_membership_options', $membership_options );
}
