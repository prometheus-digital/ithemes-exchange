<?php
/**
 * This file prints the content added to the user-edit.php WordPress page
 *
 * @since 0.4.0
 * @package IT_Exchange
 * @todo get the transaction extra buttons to work
*/
?>
<div id="profile-page" class="wrap">

    <?php screen_icon(); ?>
    <?php
	
	if ( empty( $_REQUEST['user_id'] ) )
		$user_id = get_current_user_id();
	else
		$user_id = $_REQUEST['user_id'];
		
	$user_object = get_userdata( $user_id );
	
	?>
    
	<h2><?php echo $user_object->display_name; ?> <a href="<?php echo esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user_object->ID ) ) ); ?>" class="edit-user"><?php echo esc_html_x( 'Edit User', 'LION' ); ?></a>
</h2>
    
	<?php
	$this->print_user_edit_page_tabs(); 
	do_action( 'it_exchange_user_edit_page_top' );
	?>

	<?php
		
	$tab = !empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'products';
	
	switch ( $tab ) {
			
		case 'products':
			$list = it_exchange_get_users_products( $user_id );
		default:
			break;
			
		case 'transactions':
			$list = it_exchange_get_users_transactions( $user_id );
			break;
			
		case 'activity':
			$list = it_exchange_get_users_activity( $user_id );
			break;
		
		case 'info':
			$list = array();
			break;
		
	}
	
	echo '<div class="user-edit-block ' . $tab . '-user-edit-block">';
	
	if ( !empty( $list ) && 'info' !== $tab ) { 
		
		echo '<div class="heading-row">';
		foreach( (array) $list[0] as $heading ) {
			
			echo '<span class="heading">' . $heading . '</span> ';
			
		}   
		echo '</div>';
	
		foreach( (array) $list[1] as $item_details ) {
			
			echo '<div class="item-row">';
			foreach ( (array) $item_details as $detail ) {
				
				if ( is_array( $detail ) ) {
					
					foreach( $detail as $action => $label ) {
						
						echo '<input type="button" name="it_exchange_' . $action . '" value="' . $label . '" /> ';
						
					}
				
				} else {
					
					echo '<span class="item">' . $detail . '</span> ';
				
				}
				
			}
			echo '</div>';
			
		}   
		
		if ( 'transactions' === $tab ) {
		
			echo '<input type="button" name="add_it_exchange_transaction" value="' . __( 'Add Manual Transaction', 'LION' ) . '" />';
			
		}
	
	} else if ( 'info' === $tab ) {
		
		echo '<h3>' . __( 'Notes', 'LION' ) . '</h3>';
		echo '<textarea name="it_exchange_customer_note" cols="30" rows="5">' . get_user_meta( $user_id, '_it_exchange_customer_note', true ) . '</textarea>';
		//Justin change the # for the proper dimensions from get_avatar and remove this comment
		echo '<div class="avatar">' . get_avatar( $user_id, 128 ) . '</div>'; 
		
	} else {
		
		echo '<p>' . __( 'Nothing to show here.', 'LION' ) . '</p>';
		
	}
	
	echo '</div>';
	 
	?> 
</div>
