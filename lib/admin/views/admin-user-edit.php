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
	$tab = !empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'products';
	
	switch ( $tab ) {
			
		case 'transactions':
			$list = it_exchange_get_users_transactions();
			break;
			
		case 'activity':
			$list = it_exchange_get_users_activity();
			break;
	
		case 'products':
		default:
			$list = it_exchange_get_users_products();
			break;
		
	}
	
	if ( !empty( $list ) ) { 
		
		echo '<div class="user-edit-block ' . $tab . '-user-edit-block">';
		
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
		
		echo '</div>';
	
	} else {
		
		echo '<p>' . __( 'Nothing to show here.', 'LION' ) . '</p>';
		
	}
	 
	?> 
</div>
