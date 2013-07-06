<?php
/**
 * The file for the dashboard reporting widget
 * @package IT_Exchange
 * @since 0.4.9
*/
?>

<div class="columns-wrapper columns-totals">
	<div class="column column-top column-sales">
		<label><?php _e( 'Sales Today', 'LION' ); ?></label>
		<p><?php esc_attr_e( it_exchange_basic_reporting_get_total( array( 'start_time' => strtotime( 'today' ), 'end_time' => ( strtotime( 'tomorrow' ) - 1 ) ) ) ); ?></p>
		<label><?php _e( 'Sales this Month', 'LION' ); ?></label>
		<p><?php esc_attr_e( it_exchange_basic_reporting_get_total( array( 'start_time' => strtotime( 'first day of this month' ), 'end_time' => strtotime( 'first day of next month' ) ) ) ); ?></p>
	</div>
	<div class="column column-top column-transactions">
		<label><?php _e( 'Transactions Today', 'LION' ); ?></label>
		<p><?php esc_attr_e( it_exchange_basic_reporting_get_transactions_count( array( 'start_time' => strtotime( 'today' ), 'end_time' => ( strtotime( 'tomorrow' ) - 1 ) ) ) ); ?></p>
		<label><?php _e( 'Transactions this Month', 'LION' ); ?></label>
		<p><?php esc_attr_e( it_exchange_basic_reporting_get_transactions_count( array( 'start_time' => strtotime( 'first day of this month' ), 'end_time' => strtotime( 'first day of next month' ) ) ) ); ?></p>
	</div>
</div>

<div class="recent-transactions">
	<p><label><?php _e( 'Recent Sales', 'LION' ); ?></label> <a href="<?php echo get_admin_url(); ?>edit.php?post_type=it_exchange_tran" class="view-all"><?php _e( 'View all', 'LION' ); ?></a></p>
	<?php if ( $transactions = it_exchange_get_transactions( array( 'posts_per_page' => 5 ) ) ) : ?>
		<?php foreach( $transactions as $transaction ) : ?>
			<div class="columns-wrapper columns-recent">
				<div class="column column-date">
					<span><?php esc_attr_e( it_exchange_get_transaction_date( $transaction ) ); ?></span>
				</div>
				<div class="column column-number">
					<span><?php esc_attr_e( it_exchange_get_transaction_order_number( $transaction ) ); ?></span>
				</div>
				<div class="column column-total">
					<span><?php esc_attr_e( it_exchange_get_transaction_total( $transaction ) ); ?></span>
				</div>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<?php _e( 'No transactions.', 'LION' ); ?>
	<?php endif; ?>
	<p><a href="<?php echo get_admin_url(); ?>edit.php?post_type=it_exchange_tran" class="view-all"><?php _e( 'View all', 'LION' ); ?></a></p>
</div>
