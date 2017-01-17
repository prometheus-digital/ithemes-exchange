<?php
/**
 * Contains upgrade routine for transaction table.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Upgrade_Routine_Txn_Table
 */
class IT_Exchange_Upgrade_Routine_Txn_Table implements IT_Exchange_UpgradeInterface {

	/**
	 * Get the iThemes Exchange version this upgrade applies to.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_version() {
		return '2.0.0';
	}

	/**
	 * Get the name of this upgrade.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Transaction Tables', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * Get the slug for this upgrade. This should be globally unique.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_slug() {
		return 'txn-table';
	}

	/**
	 * Get the description for this upgrade. 1-3 sentences.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_description() {
		return
			__( 'Move transactions to their own database table for increased performance and reporting.', 'it-l10n-ithemes-exchange' )
			. ' <b>' .
			__( 'Ensure all tax and shipping add-ons ever used have been activated before proceeding.', 'it-l10n-ithemes-exchange' )
			 . '</b>';
	}

	/**
	 * Get the group this upgrade belongs to.
	 *
	 * Example 'Core' or 'Membership'.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_group() {
		return __( 'Core', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * Get the total records needed to be processed for this upgrade.
	 *
	 * This is used to build the upgrade UI.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_total_records_to_process() {

		global $wpdb;

		$t1 = $wpdb->posts;
		$t2 = $wpdb->prefix . 'ite_transactions';

		$results = $wpdb->get_results(
			"SELECT COUNT(1) AS count FROM $t1 AS t1 " .
			"LEFT JOIN $t2 t2 ON ( t1.ID = t2.ID ) WHERE t1.post_type = 'it_exchange_tran' AND t2.ID IS NULL"
		);

		if ( empty( $results[0] ) ) {
			return 0;
		}

		if ( empty( $results[0]->count ) ) {
			return 0;
		}

		return $results[0]->count;
	}

	/**
	 * Get all transactions we need to upgrade.
	 *
	 * @since 2.0.0
	 *
	 * @param int $number
	 *
	 * @return int[]
	 */
	protected function get_transactions( $number = 5 ) {

		global $wpdb;

		$t1 = $wpdb->posts;
		$t2 = $wpdb->prefix . 'ite_transactions';

		$number = absint( $number );

		$results = $wpdb->get_results(
			"SELECT t1.ID FROM $t1 AS t1 " .
			"LEFT JOIN $t2 t2 ON ( t1.ID = t2.ID ) WHERE t1.post_type = 'it_exchange_tran' AND t2.ID IS NULL LIMIT $number"
		);

		$ids = array();

		foreach ( $results as $result ) {
			$ids[] = $result->ID;
		}

		return $ids;
	}

	/**
	 * Upgrade a transaction.
	 *
	 * @since 2.0.0
	 *
	 * @param int                               $transaction_id
	 * @param IT_Exchange_Upgrade_SkinInterface $skin
	 * @param bool                              $verbose
	 */
	protected function upgrade_transaction( $transaction_id, IT_Exchange_Upgrade_SkinInterface $skin, $verbose ) {

		if ( $verbose ) {
			$skin->debug( 'Upgrading Txn: ' . $transaction_id );
		}

		$post = get_post( $transaction_id );

		if ( ! $post instanceof WP_Post ) {
			$skin->warn( "Unable to retrieve post for #{$transaction_id}" );

			return;
		}

		if ( $transaction = IT_Exchange_Transaction::get( $transaction_id ) ) {
			if ( $verbose ) {
				$skin->debug( "Transaction #{$transaction_id} already had table converted." );
			}
		} else {
			try {
				$transaction = IT_Exchange_Transaction::upgrade( $post );
			} catch ( Exception $e ) {
				$skin->error( "Error upgrading table for #{$transaction_id}: {$e->getMessage()}" );

				return;
			}

			if ( ! $transaction ) {
				$skin->error( "Unable to upgrade transaction #{$transaction_id} table." );

				return;
			}

			if ( $verbose ) {
				$skin->debug( 'Upgraded table.' );
			}
		}

		$transaction->convert_cart_object();

		if ( $transaction->get_meta( 'failed_tax_upgrade' ) ) {
			$skin->warn( "Failed upgrading taxes for #{$transaction_id}" );
		}

		if ( $verbose ) {
			$skin->debug( 'Upgraded cart object.' );
		}

		/** @var array[] $refunds */
		$refunds = get_post_meta( $transaction_id, '_it_exchange_transaction_refunds' );

		foreach ( $refunds as $refund ) {
			try {
				$created = new DateTime( $refund['date'] );
			} catch ( Exception $e ) {
				$created = null;
			}

			$model = ITE_Refund::create( array(
				'transaction' => $transaction_id,
				'amount'      => $refund['amount'],
				'created_at'  => $created,
				'reason'      => isset( $refund['options']['reason'] ) ? $refund['options']['reason'] : '',
			) );

			if ( ! empty( $refund['options'] ) && is_array( $refund['options'] ) ) {
				foreach ( $refund['options'] as $key => $value ) {
					if ( $key !== 'reason' ) {
						$model->update_meta( $key, $value );
					}
				}
			}

			if ( $verbose ) {
				$skin->debug( "Upgraded refund of {$refund['amount']}" );
			}
		}

		if ( $verbose ) {
			$skin->debug( 'Upgraded Txn: ' . $transaction_id );
			$skin->debug( '' );
		}
	}

	/**
	 * Get the suggested rate at which the upgrade routine should be processed.
	 *
	 * The rate refers to how many items are upgraded in one step.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_suggested_rate() {
		return 5;
	}

	/**
	 * Perform the upgrade according to the given configuration.
	 *
	 * Throwing an upgrade exception will halt the upgrade process and notify the user.
	 *
	 * @param IT_Exchange_Upgrade_Config        $config
	 * @param IT_Exchange_Upgrade_SkinInterface $skin
	 *
	 * @return void
	 *
	 * @throws IT_Exchange_Upgrade_Exception
	 */
	public function upgrade( IT_Exchange_Upgrade_Config $config, IT_Exchange_Upgrade_SkinInterface $skin ) {

		$transactions = $this->get_transactions( $config->get_number() );

		foreach ( $transactions as $transaction ) {
			$this->upgrade_transaction( $transaction, $skin, $config->is_verbose() );
			$skin->tick();
		}
	}
}
