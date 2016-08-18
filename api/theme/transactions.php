<?php
/**
 * Transactions class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Transactions implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'transactions';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'found' => 'found',
		'exist' => 'exist',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	*/
	function __construct() {
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Theme_API_Transaction() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Use this function to in a while loop to determine if there are any more transactions left to loop through.
	 * If there are no more transactions found, it will return false. Otherwise, it returns 'true'.
	 *
	 * @since 0.4.0
	 * @return string
	*/
	public function found( $options=array() ) {
		return count( $this->get_transactions() ) > 0;
	}

	/**
	 * This loops through the transactions GLOBAL and updates the transaction global.
	 *
	 * It return false when it reaches the last transaction
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 *
	 * @return bool
	*/
	public function exist( $options=array() ) {
		// This will init/reset the transactions global and loop through them. the /api/theme/transaction.php file will handle individual transactions.
		if ( empty( $GLOBALS['it_exchange']['transactions'] ) ) {

			$transactions = $this->get_transactions();

			if ( ! $transactions ) {
				return false;
			}

			$GLOBALS['it_exchange']['transactions'] = $transactions;
			$GLOBALS['it_exchange']['transaction'] = reset( $GLOBALS['it_exchange']['transactions'] );

			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['transactions'] ) ) {
				$GLOBALS['it_exchange']['transaction'] = current( $GLOBALS['it_exchange']['transactions'] );
				return true;
			} else {
				$GLOBALS['it_exchange']['transactions'] = array();
				end( $GLOBALS['it_exchange']['transactions'] );
				$GLOBALS['it_exchange']['transaction'] = false;
				return false;
			}
		}
	}

	/**
	 * Retrieve the transactions.
	 *
	 * @since 1.36.0
	 *
	 * @return \IT_Exchange_Transaction[]
	 */
	protected function get_transactions() {

		if ( it_exchange_is_page( 'purchases' ) || it_exchange_is_page( 'downloads' ) ) {

			if ( ! $customer = it_exchange_get_current_customer() ) {
				return array();
			}

			return it_exchange_get_customer_transactions( $customer->id );
		} elseif ( it_exchange_is_page( 'confirmation' ) ) {
			$confirmation_slug = it_exchange_get_page_slug( 'confirmation' );
			$transaction_hash = get_query_var( $confirmation_slug );

			if ( ! $transaction_hash ) {
				return array();
			}

			$transaction = it_exchange_get_transaction_id_from_hash( $transaction_hash );

			if ( ! $transaction ) {
				return array();
			}

			return array( it_exchange_get_transaction( $transaction ) );
		} else {
			return it_exchange_get_transactions();
		}
	}
}
