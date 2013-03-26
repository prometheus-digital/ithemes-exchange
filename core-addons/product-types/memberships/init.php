<?php
/**
 * Membership Product Types
 *
 * @since 0.3.0
 * @package IT_Cart_Buddy
*/

if ( is_admin() ) {
	// Add metabox for membership duration
	add_action( 'it_cart_buddy_product_metabox_callback_memberships-product-type', 'it_cart_buddy_register_membership_product_type_metaboxes' );
	add_action( 'it_cart_buddy_save_product-memberships-product-type', 'it_cart_buddy_save_membership_product_options' );
}

/**
 * Register metaboxes for membership product type
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_register_membership_product_type_metaboxes( $post ) {
	add_meta_box( 'it-cart-buddy-membership-options', __( 'Membership Options', 'LION' ), 'it_cart_buddy_print_membership_options_metabox', $post->post_type, 'normal' );
}

/**
 * Prints the duration meta box
 *
 * @since 0.3.8
 * @return void
*/
function it_cart_buddy_print_membership_options_metabox( $post ) {
	$membership_options = get_post_meta( $post->ID, '_it_cart_buddy_membership_options', true );
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
		<label for="it_cart_buddy_membership_duration_type-timeframe">
			<input type="radio" id="it_cart_buddy_membership_duration_type-timeframe" name="it_cart_buddy_membership_duration_type" value="timeframe" <?php checked( 'timeframe', $duration_type ); ?> />
			&nbsp;<input size="5" type="text" name="it_cart_buddy_membership_duration_timeframe_length" value="<?php esc_attr_e( $duration_timeframe_length ); ?>"/>&nbsp;
			<select name="it_cart_buddy_membership_duration_timeframe_units">
				<option value="days" <?php selected( 'days', $duration_timeframe_units ); ?>><?php _e( 'Day(s)', 'LION' ); ?></option>
				<option value="weeks" <?php selected( 'weeks', $duration_timeframe_units ); ?>><?php _e( 'Week(s)', 'LION' ); ?></option>
				<option value="months" <?php selected( 'months', $duration_timeframe_units ); ?>><?php _e( 'Month(s)', 'LION' ); ?></option>
				<option value="years" <?php selected( 'years', $duration_timeframe_units ); ?>><?php _e( 'Year(s)', 'LION' ); ?></option>
			</select>&nbsp;<?php _e( 'after the purchase date', 'LION' ); ?>
		</label>
		<br />
		<label for="it_cart_buddy_membership_duration_type-date">
			<input type="radio" id="it_cart_buddy_membership_duration_type-date" name="it_cart_buddy_membership_duration_type" value="date" <?php checked( 'date', $duration_type ); ?>/>&nbsp;<?php _e( 'On a specific date:', 'LION' ); ?>
			<input type="text" name="it_cart_buddy_membership_duration_date" value="<?php esc_attr_e( $duration_date ); ?>" />
		</label><br />
		<label for="it_cart_buddy_membership_duration_type-never">
			<input type="radio" id="it_cart_buddy_membership_duration_type-never" name="it_cart_buddy_membership_duration_type" value="never" <?php checked( 'never', $duration_type ); ?> />&nbsp;<?php _e( 'Never', 'LION' ); ?>
		</label>
	</p>

	<h4><?php _e( 'How fast should we revoke membership if the user cancels their purchase?', 'LION' ); ?></h4>
	<p>
		<label for="it_cart_buddy_membership_cancellation_type-immediately">
			<input type="radio" id="it_cart_buddy_membership_cancellation_type-immediately" name="it_cart_buddy_membership_cancellation_type" value="immediately" <?php checked( 'immediately', $cancellation_type ); ?>/>&nbsp;<?php _e( 'Immediately', 'LION' ); ?>
		</label><br />
		<label for="it_cart_buddy_membership_cancellation_type-normally">
			<input type="radio" id="it_cart_buddy_membership_cancellation_type-normally" name="it_cart_buddy_membership_cancellation_type" value="normally" <?php checked( 'normally', $cancellation_type ); ?>/>&nbsp;<?php _e( 'On the original expiration date', 'LION' ); ?>
		</label><br />
	</p>

	<h4><?php _e( 'What WordPress role should new members who purchase this product receive?', 'LION' ); ?></h4>
	<?php
	$roles = new WP_Roles();
	if ( ! empty( $roles->roles ) && is_array( $roles->roles ) ) {
		?><select name="it_cart_buddy_membership_new_customer_role"><?php
		foreach( $roles->roles as $role => $atts ) {
			?><option value="<?php esc_attr_e( $role ); ?>" <?php selected( $role, $customer_role ); ?>><?php esc_attr_e( $atts['name'] ); ?></option><?php
		}
		?></select><?php
	}
	?>

	<h4><?php _e( 'Should we auto-renew the membership if the transaction method supports recurring payments?', 'LION' ); ?></h4>
	<p>
		<label for="it_cart_buddy_membership_attempt_auto_renew-yes">
			<input type="radio" id="it_cart_buddy_membership_attempt_auto_renew-yes" name="it_cart_buddy_membership_attempt_auto_renew" value="yes" <?php checked( 'yes', $auto_renew ); ?>/>&nbsp;<?php _e( 'Yes', 'LION' ); ?>
		</label><br />
		<label for="it_cart_buddy_membership_attempt_auto_renew-no">
			<input type="radio" id="it_cart_buddy_membership_attempt_auto_renew-no" name="it_cart_buddy_membership_attempt_auto_renew" value="no" <?php checked( 'no', $auto_renew ); ?>/>&nbsp;<?php _e( 'No', 'LION' ); ?>
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
function it_cart_buddy_save_membership_product_options( $post ) {
	
	// Grab $_POST data or set false so that form will use defaults
	$duration_type = empty( $_POST['it_cart_buddy_membership_duration_type'] ) ? false : $_POST['it_cart_buddy_membership_duration_type'];
	$duration_timeframe_length = empty( $_POST['it_cart_buddy_membership_duration_timeframe_length'] ) ? false : $_POST['it_cart_buddy_membership_duration_timeframe_length'];
	$duration_timeframe_units = empty( $_POST['it_cart_buddy_membership_duration_timeframe_units'] ) ? false : $_POST['it_cart_buddy_membership_duration_timeframe_units'];
	$duration_date = empty( $_POST['it_cart_buddy_membership_duration_date'] ) ? false : $_POST['it_cart_buddy_membership_duration_date'];
	$cancellation_type = empty( $_POST['it_cart_buddy_membership_cancellation_type'] ) ? false : $_POST['it_cart_buddy_membership_cancellation_type'];
	$customer_role = empty( $_POST['it_cart_buddy_membership_new_customer_role'] ) ? false : $_POST['it_cart_buddy_membership_new_customer_role'];
	$auto_renew = empty( $_POST['it_cart_buddy_membership_attempt_auto_renew'] ) ? false : $_POST['it_cart_buddy_membership_attempt_auto_renew'];

	$membership_options = compact( 'duration_type', 'duration_timeframe_length', 'duration_timeframe_units', 'duration_date', 'cancellation_type', 'customer_role', 'auto_renew' );

	update_post_meta( $post, '_it_cart_buddy_membership_options', $membership_options );
}
