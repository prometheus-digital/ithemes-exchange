<?php
/**
 * This file contains the call out for the IT Exchange Customer Data box
 * @package IT_Exchange
 * @since 0.4.0
*/
// Just adding internal CSS rule here since it won't be around long.
?>

<div class="it-exchange-customer-data-box">
	<?php
	if ( empty( $_REQUEST['user_id'] ) )
		$user_id = get_current_user_id();
	else
		$user_id = $_REQUEST['user_id'];
		
	$user_object = get_userdata( $user_id );
	?>
    
	<?php _e( 'This user is an Exchange customer.', 'LION' );
	echo "<a class='it-exchange-cust-info' href='" . esc_url( add_query_arg( array( 'wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'it_exchange' => 1 ), get_edit_user_link( $user_object->ID ) ) ) . "'>" . __( 'View Customer Data', 'LION' ) . "</a>"; ?>
</div>
