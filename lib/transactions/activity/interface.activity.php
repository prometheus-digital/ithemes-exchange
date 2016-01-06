<?php
/**
 * Contains the activity item interface.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Txn_Activity
 */
interface IT_Exchange_Txn_Activity {

	/**
	 * Get the ID for this item.
	 *
	 * @since 1.34
	 *
	 * @return int
	 */
	public function get_ID();

	/**
	 * Get the activity description.
	 *
	 * This is typically 1-2 sentences.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Get the type of the activity.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_type();

	/**
	 * Get the time this activity occurred.
	 *
	 * @since 1.34
	 *
	 * @return DateTime
	 */
	public function get_time();

	/**
	 * Is this activity public.
	 *
	 * The customer is notified for public activities.
	 *
	 * @since 1.34
	 *
	 * @return bool
	 */
	public function is_public();

	/**
	 * Get the transaction this activity belongs to.
	 *
	 * @since 1.34
	 *
	 * @return IT_Exchange_Transaction
	 */
	public function get_transaction();

	/**
	 * Does this activity item have an actor.
	 *
	 * @since 1.34
	 *
	 * @return bool
	 */
	public function has_actor();

	/**
	 * Get this activity's actor.
	 *
	 * @since 1.34
	 *
	 * @return IT_Exchange_Txn_Activity_Actor
	 */
	public function get_actor();

	/**
	 * Delete an activity item.
	 *
	 * @since 1.34
	 *
	 * @return bool
	 */
	public function delete();

	/**
	 * Convert the activity to an array of data.
	 *
	 * Substitute for jsonSerialize because 5.2 ;(
	 *
	 * @since 1.34
	 *
	 * @return array
	 */
	public function to_array();
}