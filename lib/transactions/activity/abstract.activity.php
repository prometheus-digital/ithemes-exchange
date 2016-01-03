<?php
/**
 * Contains abstract activity class.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_AbstractActivity
 */
abstract class IT_Exchange_Txn_AbstractActivity implements IT_Exchange_Txn_Activity {

	/**
	 * @var WP_Post
	 */
	private $post;

	/**
	 * @var IT_Exchange_Txn_Activity_Actor
	 */
	private $actor;

	/**
	 * IT_Exchange_Txn_AbstractActivity constructor.
	 *
	 * @param WP_Post                             $post
	 * @param IT_Exchange_Txn_Activity_Actor|null $actor
	 */
	public function __construct( WP_Post $post, IT_Exchange_Txn_Activity_Actor $actor = null ) {
		$this->post  = $post;
		$this->actor = $actor;
	}

	/**
	 * Get the ID for this item.
	 *
	 * @since 1.34
	 *
	 * @return int
	 */
	public function get_ID() {
		return $this->post->ID;
	}

	/**
	 * Get the activity description.
	 *
	 * This is typically 1-2 sentences.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->post->post_content;
	}

	/**
	 * Get the time this activity occurred.
	 *
	 * @since 1.34
	 *
	 * @return DateTime
	 */
	public function get_time() {
		return new DateTime( $this->post->post_date_gmt, new DateTimeZone( 'UTC' ) );
	}

	/**
	 * Get the transaction this activity belongs to.
	 *
	 * @since 1.34
	 *
	 * @return IT_Exchange_Transaction
	 */
	public function get_transaction() {
		return it_exchange_get_transaction( $this->post->post_parent );
	}

	/**
	 * Does this activity item have an actor.
	 *
	 * @since 1.34
	 *
	 * @return bool
	 */
	public function has_actor() {
		return $this->get_actor() !== null;
	}

	/**
	 * Get this activity's actor.
	 *
	 * @since 1.34
	 *
	 * @return IT_Exchange_Txn_Activity_Actor
	 */
	public function get_actor() {
		return $this->actor;
	}

	/**
	 * Is this activity public.
	 *
	 * The customer is notified for public activities.
	 *
	 * @since 1.34
	 *
	 * @return bool
	 */
	public function is_public() {
		return (bool) get_post_meta( $this->get_ID(), '_is_public', true );
	}
}