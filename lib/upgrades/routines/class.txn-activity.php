<?php
/**
 * Contains upgrade routine for transaction activity.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Upgrade_Routine_Activity
 */
class IT_Exchange_Upgrade_Routine_Txn_Activity implements IT_Exchange_UpgradeInterface {

	/**
	 * @var IT_Exchange_Txn_Activity_Factory
	 */
	private $activity_factory;

	/**
	 * Get the iThemes Exchange version this upgrade applies to.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_version() {
		return '1.34';
	}

	/**
	 * Get the name of this upgrade.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Transaction Activity', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * Get the slug for this upgrade. This should be globally unique.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_slug() {
		return 'txn-activity';
	}

	/**
	 * Get the description for this upgrade. 1-3 sentences.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_description() {
		return __( 'Upgrade transactions to provide renewal activity.', 'it-l10n-ithemes-exchange' );
	}

	/**
	 * Get the group this upgrade belongs to.
	 *
	 * Example 'Core' or 'Membership'.
	 *
	 * @since 1.34
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
	 * @since 1.34
	 *
	 * @return int
	 */
	public function get_total_records_to_process() {
		return count( $this->get_transactions() );
	}

	/**
	 * Get all coupons we need to upgrade.
	 *
	 * @since 1.34
	 *
	 * @param int $number
	 * @param int $page
	 *
	 * @return IT_Exchange_Transaction[]
	 */
	protected function get_transactions( $number = - 1, $page = 1 ) {

		$args = array(
			'posts_per_page' => $number,
			'page'           => $page,
			'post_parent'    => 0,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_upgrade_completed',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key'     => '_upgrade_completed',
					'value'   => $this->get_slug(),
					'compare' => '!='
				)
			)
		);

		return it_exchange_get_transactions( $args );
	}

	/**
	 * Upgrade a transaction.
	 *
	 * @since 1.34
	 *
	 * @param IT_Exchange_Transaction           $transaction
	 * @param IT_Exchange_Upgrade_SkinInterface $skin
	 * @param bool                              $verbose
	 */
	protected function upgrade_transaction( IT_Exchange_Transaction $transaction, IT_Exchange_Upgrade_SkinInterface $skin, $verbose ) {

		if ( $verbose ) {
			$skin->debug( 'Upgrading Txn: ' . $transaction->ID );
		}

		$child_txns = new WP_Query( array(
			'post_type'   => 'it_exchange_tran',
			'post_parent' => $transaction->ID
		) );

		foreach ( $child_txns->get_posts() as $post ) {

			try {
				$child = it_exchange_get_transaction( $post );

				if ( ! $child ) {
					continue;
				}

				if ( $verbose ) {
					$skin->debug( 'Adding Activity: ' . $child->ID );
				}

				$builder = new IT_Exchange_Txn_Activity_Builder( $transaction, 'renewal' );
				$builder->set_child( $child );

				try {
					$builder->set_time( new DateTime( $child->post_date_gmt, new DateTimeZone( 'UTC' ) ) );
				}
				catch ( Exception $e ) {
					$skin->error( sprintf( 'Transaction %d – Child %d: Exception while setting activity date %s.',
						$transaction->ID, $child->ID, $e->getMessage()
					) );
				}

				try {
					$builder->set_actor( new IT_Exchange_Txn_Activity_Gateway_Actor( it_exchange_get_addon(
						it_exchange_get_transaction_method( $transaction )
					) ) );
				}
				catch ( InvalidArgumentException $e ) {
					$skin->error( sprintf( 'Transaction %d – Child %d: Exception while setting actor %s.',
						$transaction->ID, $child->ID, $e->getMessage()
					) );
				}

				try {
					$builder->build( $this->activity_factory );
				}
				catch ( UnexpectedValueException $e ) {
					$skin->error( sprintf( 'Transaction %d – Child %d: Exception while building activity %s.',
						$transaction->ID, $child->ID, $e->getMessage()
					) );
				}
			}
			catch ( InvalidArgumentException $e ) {
				$skin->error( sprintf( 'Transaction %d: Exception thrown %s', $transaction->ID, $e->getMessage() ) );
			}
			catch ( UnexpectedValueException $e ) {
				$skin->error( sprintf( 'Transaction %d: Exception thrown %s', $transaction->ID, $e->getMessage() ) );
			}
		}

		update_post_meta( $transaction->ID, '_upgrade_completed', $this->get_slug() );

		if ( $verbose ) {
			$skin->debug( 'Upgraded Txn: ' . $transaction->ID );
			$skin->debug( '' );
		}
	}

	/**
	 * Get the suggested rate at which the upgrade routine should be processed.
	 *
	 * The rate refers to how many items are upgraded in one step.
	 *
	 * @since 1.34
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

		$transactions = $this->get_transactions( $config->get_number(), $config->get_step() );

		$this->activity_factory = it_exchange_get_txn_activity_factory();

		foreach ( $transactions as $coupon ) {
			$this->upgrade_transaction( $coupon, $skin, $config->is_verbose() );
			$skin->tick();
		}
	}
}