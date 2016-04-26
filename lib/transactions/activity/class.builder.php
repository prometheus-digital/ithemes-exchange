<?php
/**
 * Contains the builder class for creating activity items.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Activity_Builder
 */
final class IT_Exchange_Txn_Activity_Builder {

	/**
	 * @var IT_Exchange_Transaction
	 */
	private $transaction;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $description = '';

	/**
	 * @var IT_Exchange_Txn_Activity_Actor
	 */
	private $actor = null;

	/**
	 * @var DateTime
	 */
	private $time;

	/**
	 * @var bool
	 */
	private $public = false;

	/**
	 * @var IT_Exchange_Transaction
	 */
	private $child;

	/**
	 * IT_Exchange_Txn_Activity_Builder constructor.
	 *
	 * @param IT_Exchange_Transaction $transaction
	 * @param                         $type
	 */
	public function __construct( IT_Exchange_Transaction $transaction, $type ) {
		$this->time = new DateTime( 'now', new DateTimeZone( 'UTC' ) );

		$this->transaction = $transaction;
		$this->type        = $type;
	}

	/**
	 * Set the description.
	 *
	 * @since 1.34
	 *
	 * @param string $description
	 *
	 * @return self
	 */
	public function set_description( $description ) {
		$this->description = $description;

		return $this;
	}

	/**
	 * Set the actor.
	 *
	 * @since 1.34
	 *
	 * @param IT_Exchange_Txn_Activity_Actor $actor
	 *
	 * @return self
	 */
	public function set_actor( IT_Exchange_Txn_Activity_Actor $actor ) {
		$this->actor = $actor;

		return $this;
	}

	/**
	 * Set the time the activity occurred.
	 *
	 * Timezone will be converted to UTC.
	 *
	 * Defaults to 'now'.
	 *
	 * @since 1.34
	 *
	 * @param DateTime $time
	 *
	 * @return self
	 */
	public function set_time( DateTime $time ) {
		$time->setTimezone( new DateTimeZone( 'UTC' ) );
		$this->time = $time;

		return $this;
	}

	/**
	 * Set whether this activity should be public.
	 *
	 * Defaults to 'false'.
	 *
	 * @since 1.34
	 *
	 * @param bool $public
	 *
	 * @return self
	 */
	public function set_public( $public = true ) {
		$this->public = (bool) $public;

		return $this;
	}

	/**
	 * Set the child transaction.
	 *
	 * @since 1.34
	 *
	 * @param IT_Exchange_Transaction $transaction
	 *
	 * @return $this
	 */
	public function set_child( IT_Exchange_Transaction $transaction ) {

		if ( (int) get_post_meta( $transaction->ID, '_it_exchange_parent_tx_id', true ) !== (int) $this->transaction->ID ) {
			throw new InvalidArgumentException( 'Child transaction has invalid parent.' );
		}

		$this->child = $transaction;

		return $this;
	}

	/**
	 * Create the activity item.
	 *
	 * @since 1.34
	 *
	 * @param IT_Exchange_Txn_Activity_Factory $factory
	 *
	 * @return IT_Exchange_Txn_Activity|null
	 */
	public function build( IT_Exchange_Txn_Activity_Factory $factory ) {

		$data = array(
			'post_content' => $this->description,
			'post_type'    => $factory->get_post_type(),
			'post_parent'  => $this->transaction->ID
		);

		if ( $this->time ) {
			$data['post_date_gmt'] = $this->time->format( 'Y-m-d H:i:s' );

			// compat for < 4.4
			$data['post_date'] = get_date_from_gmt( $data['post_date_gmt'] );
		}

		$ID = wp_insert_post( $data );

		if ( is_wp_error( $ID ) ) {
			throw new UnexpectedValueException( 'WP Error: ' . $ID->get_error_message() );
		}

		$term_ids = wp_set_object_terms( $ID, $this->type, $factory->get_type_taxonomy() );

		if ( is_wp_error( $term_ids ) ) {
			throw new UnexpectedValueException( $term_ids->get_error_message() );
		}

		update_post_meta( $ID, '_is_public', $this->public );

		if ( $this->child ) {
			update_post_meta( $ID, '_child_txn', $this->child->ID );
			update_post_meta( $ID, '_child_txn_cleared', it_exchange_transaction_is_cleared_for_delivery( $this->child ) );
		}

		$activity = $factory->make( $ID );

		if ( $activity && $this->actor ) {
			update_post_meta( $ID, '_actor_type', $this->actor->get_type() );
			$this->actor->attach( $activity );
		}

		$activity = $factory->make( $ID );

		/**
		 * Fires when a txn activity is created.
		 *
		 * @since 1.34
		 *
		 * @param IT_Exchange_Txn_Activity         $activity
		 * @param IT_Exchange_Txn_Activity_Builder $this
		 */
		do_action( 'it_exchange_build_txn_activity', $activity, $this );

		return $activity;
	}
}