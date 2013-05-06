<?php
/**
 * This file prints the content added to the user-edit.php WordPress page
 *
 * @since 0.4.0
 * @package IT_Exchange
*/
?>
<div id="it-exchange-member-options">
    
	<h2>iThemes Exchange <?php _e( 'Options', 'LION' ); ?></h2>
    
	<?php
	$this->print_user_edit_page_tabs(); 
	do_action( 'it_exchange_user_edit_page_top' );
	?>

	<?php
	
	if ( empty( $_REQUEST['user_id'] ) )
		$user_id = get_current_user_id();
	else
		$user_id = $_REQUEST['user_id'];
		
	$tab = !empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'info';
	
	switch ( $tab ) {
		
		case 'info':
		default:
			$list = array();
			break;
			
		case 'products':
			$list = it_exchange_get_users_products( $user_id );
			break;
			
		case 'transactions':
			$list = it_exchange_get_users_transactions( $user_id );
			break;
			
		case 'activity':
			$list = it_exchange_get_users_activity( $user_id );
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
					
				echo '<span class="item">' . $detail . '</span> ';
			
			}
			echo '</div>';
			
		}   
	
	} else if ( 'info' === $tab ) {
		
		echo '<textarea name="it_exchange_customer_note" cols="30" rows="5">' . get_user_meta( $user_id, '_it_exchange_customer_note', true ) . '</textarea>';
		
	} else {
		
		echo '<p>' . __( 'Nothing to show here.', 'LION' ) . '</p>';
		
	}
	
	echo '</div>';
	 
	?> 
</div>
