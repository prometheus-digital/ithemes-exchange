<?php
/**
 * Contains the activity item interface.
 *
 * @since   1.34
 * @license GPLv2
 */
interface IT_Exchange_Txn_Activity extends ITE_Activity {

	/**
	 * Get the transaction this activity belongs to.
	 *
	 * @since 1.34
	 *
	 * @return IT_Exchange_Transaction
	 */
	public function get_transaction();
}