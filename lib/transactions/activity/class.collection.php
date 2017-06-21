<?php
/**
 * Contains the activity collection class.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Activity_Collection
 */
class IT_Exchange_Txn_Activity_Collection {

	/**
	 * @var IT_Exchange_Transaction
	 */
	private $transaction;

	/**
	 * @var IT_Exchange_Txn_Activity[]
	 */
	private $activity = array();

	/**
	 * @var int
	 */
	private $total;

	/**
	 * IT_Exchange_Txn_Activity_Collection constructor.
	 *
	 * @param IT_Exchange_Transaction $transaction
	 * @param array                   $args {
	 *
	 *      @type int    $per_page   Number of items to return. Defaults to -1 ( all )
	 *      @type int    $page       Page of results to return. Default 1.
	 *      @type string $orderby    Column to order results by. Default is 'date'.
	 *      @type string $order      ASC or DESC order. Default is DESC.
	 *      @type array  $date_query Limit results to a certain date range. {@see WP_Date_Query}
	 *      @type string $type       Activity type. Defaults to all.
	 *      @type string $actor_type Type of actor attached to the activity. This isn't necessarily reliable.
	 * }
	 */
	public function __construct( IT_Exchange_Transaction $transaction, array $args = array() ) {
		$this->transaction = $transaction;

		$args = ITUtility::merge_defaults( $args, array(
			'per_page'   => - 1,
			'page'       => 1,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'date_query' => array()
		) );

		$wp_args = array(
			'post_type'      => 'ite_txn_activity',
			'post_parent'    => $transaction->ID,
			'posts_per_page' => $args['per_page'],
			'paged'          => $args['page'],
			'orderby'        => $args['orderby'],
			'order'          => $args['order'],
			'date_query'     => $args['date_query']
		);

		if ( ! empty( $args['type'] ) && $args['type'] !== 'any' ) {
			$wp_args['tax_query'] = array(
				array(
					'taxonomy' => 'ite_txn_activity_type',
					'field'    => 'name',
					'terms'    => $args['type']
				)
			);
		}

		if ( ! empty( $args['actor_type'] ) && $args['actor_type'] !== 'any' ) {
			$wp_args['meta_query'] = array(
				array(
					'key'   => '_actor_type',
					'value' => $args['actor_type']
				)
			);
		}

		$query       = new WP_Query( $wp_args );
		$this->total = $query->found_posts;

		foreach ( $query->get_posts() as $post ) {
			$activity = it_exchange_get_txn_activity( $post->ID );
			
			if ( $activity ) {
				$this->activity[] = $activity;
			}
		}
	}

	/**
	 * Get the activity items queried.
	 *
	 * @since 1.34
	 *
	 * @return IT_Exchange_Txn_Activity[]
	 */
	public function get_activity() {
		return $this->activity;
	}

	/**
	 * Get the total number of activity items that exist, ignoring pagination.
	 *
	 * @since 1.34
	 *
	 * @return int
	 */
	public function get_total() {
		return $this->total;
	}
}