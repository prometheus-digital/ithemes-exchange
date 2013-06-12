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

	<?php
		if ( empty( $_REQUEST['user_id'] ) )
			$user_id = get_current_user_id();
		else
			$user_id = $_REQUEST['user_id'];
		
		$user_object = get_userdata( $user_id );
	?>

	<?php
	
	if ( !empty( $_POST['_it_exchange_customer_info_nonce'] ) && !wp_verify_nonce( $_POST['_it_exchange_customer_info_nonce'], 'update-it-exchange-customer-info' ) ) {	
	
		it_exchange_get_add_message( 'error', __( 'Error verifying security token. Please try again.', 'LION' ) );	
		
	} else {
		
		if ( isset( $_REQUEST['it_exchange_customer_note'] ) )
			update_user_meta( $user_id, '_it_exchange_customer_note', $_REQUEST['it_exchange_customer_note'] );
		
	}
	
	?>

    <form action="" method="post">
            
	<?php screen_icon(); ?>
	
	<h2>
		<?php echo $user_object->display_name; ?>
		<a href="<?php echo esc_url( add_query_arg( 'wp_http_referer', urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user_object->ID ) ) ); ?>" class="edit-user add-new-h2"><?php echo esc_html_x( 'Edit User', 'LION' ); ?></a>
	</h2>
	<?php
	// Show update messages
	if ( $notices = it_exchange_get_messages( 'notice' ) ) {
		foreach( $notices as $notice ) {
			ITUtility::show_status_message( $notice );
		}
		it_exchange_clear_messages( 'notice' );
	}
	// Show errror messages
	if ( $errors = it_exchange_get_messages( 'error' ) ) {
		foreach( $errors as $error ) {
			ITUtility::show_error_message( $error );
		}
		it_exchange_clear_messages( 'error' );
	}
	?>
	
	<p class="top-description"><?php echo sprintf( __( 'Here you can view %1$s\'s customer information. Click the Edit User link to go back to %1$s\'s edit user page.', 'LION' ), $user_object->display_name ); ?></p>
	
	<?php

		// Print tabs
		$this->print_user_edit_page_tabs(); 
		do_action( 'it_exchange_user_edit_page_top' );
	?>
	
	<?php
		
		$tab = ! empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'products';
		$user_id = empty( $user_id ) ? get_current_user_id() : $user_id;
		
		switch ( $tab ) {
			case 'transactions':
				$headings = array(
					__( 'Description', 'LION' ),
					__( 'Total', 'LION' ),
					__( 'Actions', 'LION' ),
				);  

				$list = array();
				foreach( (array) it_exchange_get_customer_transactions( $user_id ) as $transaction ) {
					// View URL
					$view_url   = get_admin_url() . '/post.php?action=edit&post=' . esc_attr( $transaction->ID );
					$view_url   = remove_query_arg( 'it-exchange-customer-transaction-action', $view_url );
					$view_url   = remove_query_arg( '_wpnonce', $view_url );

					// Resend URL
					$resend_url = add_query_arg( array( 'it-exchange-customer-transaction-action' => 'resend', 'id' => $transaction->ID ) );
					$resend_url = remove_query_arg( 'wp_http_referer', $resend_url );
					$resend_url = wp_nonce_url( $resend_url, 'it-exchange-resend-confirmation-' . $transaction->ID );
					$resend_url = remove_query_arg( 'it-exchange-customer-transaction-action', $resend_url );
					$resend_url = remove_query_arg( '_wpnonce', $resend_url );

					// Refund URL
					$refund_url = add_query_arg( array( 'it-exchange-customer-transaction-action' => 'refund', 'id' => $transaction->ID ) );
					$refund_url = remove_query_arg( 'wp_http_referer', $refund_url );
					$refund_url = remove_query_arg( 'it-exchange-customer-transaction-action', $refund_url );
					$refund_url = remove_query_arg( '_wpnonce', $refund_url );
					$refund_url = apply_filters( 'it_exchange_refund_url_for_' . it_exchange_get_transaction_method( $transaction ), $refund_url );

					// Actions array
					$actions_array = array( 
						$view_url   => __( 'View', 'LION' ),
						$resend_url => __( 'Resend Confirmation Email', 'LION' ),
						$refund_url =>  sprintf( __( 'Refund from %s', 'LION' ), it_exchange_get_transaction_method_name( $transaction ) ),
					);
					$description = it_exchange_get_transaction_description( $transaction );
					$price       = it_exchange_get_transaction_total( $transaction );
					$list[]      = array( $description, $price, $actions_array );
				}
				$list = array( $headings, $list );
				break;
			case 'info':
				$list = array();
				break;
			case 'products':
				$headings = array(
					__( 'Products', 'LION' ),
					__( 'Transaction', 'LION' ),
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

					// Build Downloads list
					$downloads_list = array();
					if ( $downloads = it_exchange_get_product_feature( $product['product_id'], 'downloads' ) ) {
						foreach ( $downloads as $download ) {
							$remaining = 'Line ' . __LINE__;
							// Change this function
							//$remaining = it_exchange_get_download_data_from_transaction_product( $transaction_id, $product, $download['id'], 'download_limit' );
							$downloads_list[] = apply_filters( 'the_title', $download['name'] ) . ' (' . $remaining . ')';
						}
						$downloads_list = implode( '<br />', $downloads_list );
					} else {
						$downloads_list = __( 'No downloads found', 'LION' );
					}
					$list[] = array(
						$product_link,
						$transaction_link,
					);
				}
				$list = array( $headings, $list );
				break;
			default :
				$list = apply_filters( 'it_exchange_print_user_edit_page_content', '', $tab );
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
	
	<?php if ( 'transactions' === $tab && false ) : ?>
		<div class="add-manual-transaction">
			<input type="button" class="button button-large" name="add_it_exchange_transaction" value="<?php _e( 'Add Manual Transaction', 'LION' ) ?>" />
		</div>
    <?php elseif ( 'info' === $tab ) : ?>
        <div class="update-user-info">
            <input type="submit" class="button button-large" name="update_it_exchange_customer" value="<?php _e( 'Update Customer Info', 'LION' ) ?>" />
        </div>
	<?php endif; ?>
    
    <?php wp_nonce_field( 'update-it-exchange-customer-info', '_it_exchange_customer_info_nonce' ); ?>
    
     </form>
     
</div>