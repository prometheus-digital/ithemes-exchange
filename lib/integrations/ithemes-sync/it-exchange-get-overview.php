<?php
class Ithemes_Sync_Verb_Ithemes_Exchange_Get_Overview extends Ithemes_Sync_Verb {
	public static $name = 'it-exchange-get-overview';
	public static $description = 'Get overview and status information.';

	/**
	 * Processes a request from Sync for the it-exchange-get-overview Verb
	 *
	 * @param array $args Arguments passed by Sync - unused
	 *
	 * @return array And array of data for the Sync dashboard widget that looks like this:
	 *     array(
	 *         'version'  => {current-exchange-version}
	 *         'overview' => array(
	 *             'salesToday'            => {sales-today-as-currency}
	 *             'salesThisMonth'        => {sales-this-month-as-currency}
	 *             'transactionsToday'     => {number-of-transactions-today-as-int}
	 *             'transactionsThisMonth' => {number-of-transactions-this-month-as-int}
	 *             'recentTransactions'    => {array-of-5-most-recent-transactions}
	 *             'allTransactionsUrl'    => {url-to-wp-admin-transactions-list}
	 *         )
	 *     )
	 */
	public function run( $args ) {

		$overview = array(
			'salesToday'            => it_exchange_basic_reporting_get_total( array( 'start_time' => strtotime( 'today' ), 'end_time' => ( strtotime( 'tomorrow' ) - 1 ) ) ),
			'salesThisMonth'        => it_exchange_basic_reporting_get_total( array( 'start_time' => strtotime( date( 'Y-m-01' ) ) ) ),
			'transactionsToday'     => it_exchange_basic_reporting_get_transactions_count( array( 'start_time' => strtotime( 'today' ), 'end_time' => ( strtotime( 'tomorrow' ) - 1 ) ) ),
			'transactionsThisMonth' => it_exchange_basic_reporting_get_transactions_count( array( 'start_time' => strtotime( date( 'Y-m-01' ) ) ) ),
			'recentTransactions'    => array(),
			'allTransactionsUrl'    => wp_login_url( get_admin_url( null, 'edit.php?post_type=it_exchange_tran' ) ),
		);

		if ( $transactions = it_exchange_get_transactions( array( 'posts_per_page' => 5 ) ) ) {
			/**
			 * In order to use get_edit_post_link() the post type needs to exist
			 * and unfortunately the Sync action runs before the Exchange action
			 * that registers said post type
			 */
			if ( class_exists( 'IT_Exchange_Transaction_Post_Type' ) && ! get_post_type_object( 'it_exchange_tran' ) ) {
				register_post_type( 'it_exchange_tran' );
			}
			foreach( $transactions as $transaction ) {
				$overview['recentTransactions'][] = array(
					'date'               => it_exchange_get_transaction_date( $transaction ),
					'orderNumber'        => it_exchange_get_transaction_order_number( $transaction ),
					'total'              => it_exchange_get_transaction_total( $transaction ),
					'clearedForDelivery' => it_exchange_transaction_is_cleared_for_delivery( $transaction ),
					'statusLabel'        => it_exchange_get_transaction_status_label( $transaction ),
					'editUrl'            => get_edit_post_link( $transaction->ID, 'raw' ),
				);
			}
		}

		return array(
			'version' => $GLOBALS['it_exchange']['version'],
			'overview' => $overview,
		);

	} // End run().

} // End class.
