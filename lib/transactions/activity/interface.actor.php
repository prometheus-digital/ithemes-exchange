<?php
/**
 * Contains the activity actor interface.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Txn_Activity_Actor
 */
interface IT_Exchange_Txn_Activity_Actor {

	/**
	 * Get the actor's name.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the URL to the icon representing this actor.
	 *
	 * @since 1.34
	 *
	 * @param int $size Suggested size. Do not rely on this value.
	 *
	 * @return string
	 */
	public function get_icon_url( $size );

	/**
	 * Get the URL to view details about this actor.
	 *
	 * This could be a user's profile, for example.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_detail_url();
}