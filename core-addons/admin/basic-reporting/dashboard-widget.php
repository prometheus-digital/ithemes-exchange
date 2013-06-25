<?php
/**
 * The file for the dashboard reporting widget
 * @package IT_Exchange
 * @since 0.4.9
*/
?>

<p><?php _e( 'Sales Today', 'LION' ); ?><br /><?php esc_attr_e( it_exchange_basic_reporting_get_total( array( 'start_time' => strtotime( 'today' ), 'end_time' => ( strtotime( 'tomorrow' ) - 1 ) ) ) ); ?></p>
<p><?php _e( 'Sales this Month', 'LION' ); ?><br /><?php esc_attr_e( it_exchange_basic_reporting_get_total( array( 'start_time' => strtotime( 'first day of this month' ), 'end_time' => strtotime( 'first day of next month' ) ) ) ); ?></p>
<p><?php _e( 'Transactions Today', 'LION' ); ?><br /><?php esc_attr_e( it_exchange_basic_reporting_get_transactions_count( array( 'start_time' => strtotime( 'today' ), 'end_time' => ( strtotime( 'tomorrow' ) - 1 ) ) ) ); ?></p>
<p><?php _e( 'Transactions this Month', 'LION' ); ?><br /><?php esc_attr_e( it_exchange_basic_reporting_get_transactions_count( array( 'start_time' => strtotime( 'first day of this month' ), 'end_time' => strtotime( 'first day of next month' ) ) ) ); ?></p>
<p><?php _e( 'Recent Sales', 'LION' ); ?> <a href="<?php echo get_admin_url(); ?>/edit.php?post_type=it_exchange_tran"><?php _e( 'View all', 'LION' ); ?></a><br />
<?php if ( $transactions = it_exchange_get_transactions( array( 'posts_per_page' => 5 ) ) ) : ?>
	<?php foreach( $transactions as $transaction ) : ?>
		<?php esc_attr_e( it_exchange_get_transaction_date( $transaction ) ); ?>
		<?php esc_attr_e( it_exchange_get_transaction_order_number( $transaction ) ); ?>
		<?php esc_attr_e( it_exchange_get_transaction_total( $transaction ) ); ?><br />
	<?php endforeach; ?>
<?php else : ?>
	<?php _e( 'No transactions.', 'LION' ); ?>
<?php endif; ?>
