<?php
/**
 * API Functions used to register / retrieve Exchange User information
 *
 * @todo This entire file needs to be rolled into /api/customers.php
 * @package IT_Exchange
 * @since 0.4.0
*/

/**
 * Return the list of products associated with a given user
 *
 * @since 0.4.0
 * @param int $user_id the user ID of user being viewed
 * @return array List of products
*/
function it_exchange_get_users_products( $user_id = NULL ) {
	
	if ( is_null( $user_id ) )
		$user_id = get_current_user_id();
		
	$headings = array(
					__( 'Products', 'LION' ),
					__( 'Expiration', 'LION' ),
					__( 'Download Remaining', 'LION' ),
				);
				
	$list[] = array( 'My Great Ebook', '9/12/16', '3' );
	$list[] = array( 'My Awesome Ebook', '3/24/14', '1' );
	$list[] = array( 'The Old Couch', '2/2/14', '0' );
	$list[] = array( 'My Firstborn', '', '-1' );
				
	return array( $headings, $list );
}

/**
 * Return the list of transactions associated with a given user
 *
 * @since 0.4.0
 * @param int $user_id the user ID of user being viewed
 * @return array List of transaction
*/
function it_exchange_get_users_transactions( $user_id = NULL  ) {
	
	if ( is_null( $user_id ) )
		$user_id = get_current_user_id();
		
	$headings = array(
					__( 'Product', 'LION' ),
					__( 'Total', 'LION' ),
					__( 'Actions', 'LION' ),
				);
	$actions_array = array( 
						'view' => 'View', 
						'resent' => 'Resent Confirmation Email', 
						'refund' => 'Refund', 
						'cancel' => 'Cancel' );
				
	$list[] = array( 'My Great Ebook', '$14.00', $actions_array );
	$list[] = array( 'My Awesome Ebook', '$14.00', $actions_array );
	$list[] = array( 'The Old Couch', '$200.00', $actions_array );
	$list[] = array( 'My Firstborn', '$32,000.00', $actions_array );
				
	return array( $headings, $list );
}

/**
 * Return the list of activities associated with a given user
 *
 * @since 0.4.0
 * @param int $user_id the user ID of user being viewed
 * @return array List of activities
*/
function it_exchange_get_users_activity( $user_id = NULL  ) {
	
	if ( is_null( $user_id ) )
		$user_id = get_current_user_id();
		
	$headings = array(
					__( 'Event', 'LION' ),
					__( 'Date/Time', 'LION' ),
				);
				
	$list[] = array( 'My Great Ebook', '3/11/13 8:43pm' );
	$list[] = array( 'My Awesome Ebook', '3/9/13 3:15pm' );
	$list[] = array( 'The Old Couch', '3/7/13 2:55am' );
	$list[] = array( 'My Firstborn', '2/6/13 12:32pm' );
				
	return array( $headings, $list );
}
