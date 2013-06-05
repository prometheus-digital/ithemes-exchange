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
		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;
		
		switch ( $tab ) {
			case 'products':
			default:
				$headings = array(
					__( 'Products', 'LION' ),
					__( 'Transaction', 'LION' ),
					__( 'Expiration', 'LION' ),
					__( 'Download Remaining', 'LION' ),
				);

				$list     = array();
				foreach( (array) it_exchange_get_customer_products( $user_id ) as $product ) {
					// Build Product Link
					$product_id   = $product['product_id'];
					$product_url  = get_admin_url() . '/post.php?action=edit&post=' . esc_attr( $product_id );
					$product_name = it_exchange_get_transaction_product_feature( $product, 'product_name' );
					$product_link = '<a href="' . $product_url . '">' . $product_name . '</a>';

					// Build Transaction Link
					$transaction_id     = it_exchange_get_transaction_product_feature( $product, 'transaction_id' );
					$transaction_url    = get_admin_url() . '/post.php?action=edit&post=' . esc_attr( $transaction_id );
					$transaction_number = it_exchange_get_transaction_order_number( $transaction_id );
					$transaction_link   = '<a href="' . $transaction_url . '">' . $transaction_number . '</a>';
					$list[] = array(
						$product_link,
						$transaction_link,
						it_exchange_get_transaction_product_feature( $product, 'expiration_date' ),
						it_exchange_get_transaction_product_feature( $product, 'download_limit' ),
					);
				}
				$list = array( $headings, $list );
				break;
			case 'transactions':
				
				$headings = array(
					__( 'Description', 'LION' ),
					__( 'Total', 'LION' ),
					__( 'Actions', 'LION' ),
				);  

				$list = array();
				foreach( (array) it_exchange_get_customer_transactions( $user_id ) as $transaction ) {
					$view_url   = get_admin_url() . '/post.php?action=edit&post=' . esc_attr( $transaction->ID );
					$resend_url = add_query_arg( array( 'it-exchange-customer-transaction-action' => 'resend', 'id' => $transaction->ID ) );
					$resend_url = remove_query_arg( 'wp_http_referer', $resend_url );
					$refund_url = add_query_arg( array( 'it-exchange-customer-transaction-action' => 'refund', 'id' => $transaction->ID ) );
					$refund_url = remove_query_arg( 'wp_http_referer', $refund_url );
					$actions_array = array( 
						$view_url   => 'View',
						$resend_url => 'Resend Confirmation Email',
						$refund_url => 'Refund',
					);
					$description = it_exchange_get_transaction_description( $transaction );
					$price       = it_exchange_get_transaction_total( $transaction );
					$list[]      = array( $description, $price, $actions_array );
				}
				$list = array( $headings, $list );
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
									<a class="button" href="<?php esc_attr_e( $action ); ?>"><?php esc_attr_e( $label ); ?></a>
									<!--
									<input type="button" class="button" name="it_exchange_<?php echo $action; ?>" value="<?php echo $label; ?>" /> 
									-->
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
