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
	
	<h2>
		<?php echo $user_object->display_name; ?>
		<a href="<?php echo esc_url( add_query_arg( 'wp_http_referer', urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user_object->ID ) ) ); ?>" class="edit-user add-new-h2"><?php echo esc_html_x( 'Edit User', 'LION' ); ?></a>
	</h2>
	
	<p class="top-description"><?php echo sprintf( __( 'Here you can view %1$s\'s customer information. Click the Edit User link to go back to %1$s\'s edit user page.', 'LION' ), $user_object->display_name ); ?></p>
	
	<?php
		$this->print_user_edit_page_tabs(); 
		do_action( 'it_exchange_user_edit_page_top' );
	?>
	
	<?php
		
		$tab = ! empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'products';
		
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
	?>
	
	<div class="user-edit-block <?php echo $tab; ?>-user-edit-block">
		<?php if ( ! empty( $list ) && 'info' !== $tab ) : ?>
			<div class="heading-row block-row">
				<?php $column = 0; ?>
				<?php foreach ( (array) $list[0] as $heading ) : ?>
					<?php $column++ ?>
					<div class="heading-column block-column block-column-<?php echo $column; ?>">
						<span class="heading"><?php echo $heading; ?></span>
					</div>
				<?php endforeach; ?>
			</div>
			<?php foreach ( (array) $list[1] as $item_details ) : ?>
				<?php $column = 0; ?>
				<div class="item-row block-row">
					<?php foreach ( (array) $item_details as $detail ) : ?>
						<?php $column++ ?>
						<?php if ( is_array( $detail ) ) : ?>
							<div class="item-column block-column block-column-<?php echo $column; ?>">
								<?php foreach ( $detail as $action => $label ) : ?>
									<input type="button" class="button" name="it_exchange_<?php echo $action; ?>" value="<?php echo $label; ?>" /> 
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<div class="item-column block-column block-column-<?php echo $column; ?>">
								<span class="item"><?php echo $detail; ?></span>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		<?php elseif ( 'info' === $tab ) : ?>
			<div class="notes">
				<label for="it_exchange_customer_note"><?php _e( 'Notes', 'LION' ); ?></label>
				<textarea name="it_exchange_customer_note" cols="30" rows="10"><?php echo get_user_meta( $user_id, '_it_exchange_customer_note', true ); ?></textarea>
			</div>
			<div class="avatar"><?php echo get_avatar( $user_id, 160 ); ?></div>
		<?php else : ?>
			<p><?php _e( 'Nothing to show here.', 'LION' ) ?></p>
		<?php endif; ?>
	</div>
	
	<?php if ( 'transactions' === $tab ) : ?>
		<div class="add-manual-transaction">
			<input type="button" class="button button-large" name="add_it_exchange_transaction" value="<?php _e( 'Add Manual Transaction', 'LION' ) ?>" />
		</div>
	<?php endif; ?>
</div>
