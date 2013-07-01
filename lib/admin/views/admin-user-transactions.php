<?php
/**
 * This file prints the content added to the user-edit.php WordPress page
 *
 * @since 0.4.0
 * @package IT_Exchange
*/

if ( empty( $_REQUEST['user_id'] ) )
	$user_id = get_current_user_id();
else
	$user_id = $_REQUEST['user_id'];

$user_object = get_userdata( $user_id );

$headings = array(
	__( 'Description', 'LION' ),
	__( 'Total', 'LION' ),
	__( 'Order Number', 'LION' ),
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
	
	// Build Transaction Link
	$transaction_url    = get_admin_url() . '/post.php?action=edit&post=' . esc_attr( $transaction->ID );
	$transaction_number = it_exchange_get_transaction_order_number( $transaction->ID );
	$transaction_link   = '<a href="' . $transaction_url . '">' . $transaction_number . '</a>';

	// Actions array
	$actions_array = array( 
		$view_url   => __( 'View', 'LION' ),
		$resend_url => __( 'Resend Confirmation Email', 'LION' ),
		$refund_url =>  sprintf( __( 'Refund from %s', 'LION' ), it_exchange_get_transaction_method_name( $transaction ) ),
	);
	$description  = it_exchange_get_transaction_description( $transaction );
	$price        = it_exchange_get_transaction_total( $transaction );
	$list[]       = array( $description, $price, $transaction_link, $actions_array );
}
?>

<div class="user-edit-block <?php echo $tab; ?>-user-edit-block">

	<div class="heading-row block-row">
		<?php $column = 0; ?>
		<?php foreach ( (array) $headings as $heading ) : ?>
			<?php $column++ ?>
			<div class="heading-column block-column block-column-<?php echo $column; ?>">
				<p class="heading"><?php echo $heading; ?></p>
			</div>
		<?php endforeach; ?>
	</div>
	<?php foreach ( (array) $list as $item_details ) : ?>
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
						<p class="item"><?php echo $detail; ?></p>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

<?php if ( 'transactions' === $tab && false ) : ?>
	<div class="add-manual-transaction">
		<input type="button" class="button button-large" name="add_it_exchange_transaction" value="<?php _e( 'Add Manual Transaction', 'LION' ) ?>" />
	</div>
<?php endif; ?>

</div>
