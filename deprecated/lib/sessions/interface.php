<?php
/**
 * File Description
 *
 * @author    Iron Bound Designs
 * @since     1.0
 * @license   AGPL
 * @copyright Iron Bound Designs, 2016.
 */

/**
 * The IT_Exchange_Session class holds cart and purchasing details
 *
 * @since 0.3.3
 */
interface IT_Exchange_SessionInterface {

	/**
	 * Returns session data
	 *
	 * All data or optionally, data for a specific key
	 *
	 * @since 0.4.0
	 *
	 * @param string|bool $key Specify the data to retrieve, if false all data will be retrieved.
	 *
	 * @return mixed. serialized string
	 */
	public function get_session_data( $key = false );

	/**
	 * Adds data to the session, associated with a specific key
	 *
	 * @since 0.4.0
	 *
	 * @param string $key  key for the data
	 * @param mixed  $data data to be stored. will be serialized if not already
	 *
	 * @return void
	 */
	public function add_session_data( $key, $data );

	/**
	 * Updates session data by key
	 *
	 * @since 0.4.0
	 *
	 * @param string $key  key for the data
	 * @param mixed  $data data to be stored. will be serialized if not already
	 *
	 * @return void
	 */
	public function update_session_data( $key, $data );

	/**
	 * Deletes session data. All or by key.
	 *
	 * @since 0.4.0
	 *
	 * @param string|bool $key Specify the key to clear, or clear all data if false.
	 *
	 * @return void
	 */
	public function clear_session_data( $key = false );

	/**
	 * Clears all session data
	 *
	 * @since 0.4.0
	 *
	 * @param bool $hard If true, old delete sessions as well.
	 */
	public function clear_session( $hard = false );
}