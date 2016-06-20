<?php
/**
 * Contains the mock session class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Mock_Session
 */
class IT_Exchange_Mock_Session implements IT_Exchange_SessionInterface {

	/**
	 * @var array
	 */
	private $session = array();

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
	public function get_session_data( $key = false ) {
		if ( $key ) {

			$key = sanitize_key( $key );

			if ( isset( $this->session[ $key ] ) ) {
				if ( is_array( $this->session[ $key ] ) ) {
					return array_map( 'maybe_unserialize', $this->session[ $key ] );
				} else {
					return maybe_unserialize( $this->session[ $key ] );
				}
			} else {
				return array();
			}
		} else {
			return array_map( 'maybe_unserialize', $this->session );
		}
	}

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
	public function add_session_data( $key, $data ) {

		$key = sanitize_key( $key );

		if ( ! empty( $this->session[ $key ] ) ) {
			$current_data          = maybe_unserialize( $this->session[ $key ] );
			$this->session[ $key ] = maybe_serialize( array_merge( $current_data, (array) $data ) );
		} else if ( ! empty( $data ) ) {
			$this->session[ $key ] = maybe_serialize( (array) $data );
		}
	}

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
	public function update_session_data( $key, $data ) {

		$key = sanitize_key( $key );

		$this->session[ $key ] = maybe_serialize( (array) $data );
	}

	/**
	 * Deletes session data. All or by key.
	 *
	 * @since 0.4.0
	 *
	 * @param string|bool $key Specify the key to clear, or clear all data if false.
	 *
	 * @return void
	 */
	public function clear_session_data( $key = false ) {

		if ( $key ) {
			$key = sanitize_key( $key );

			unset( $this->session[ $key ] );
		} else {
			$this->session = array();
		}
	}

	/**
	 * Clears all session data
	 *
	 * @since 0.4.0
	 *
	 * @param bool $hard If true, old delete sessions as well.
	 */
	public function clear_session( $hard = false ) {
		$this->session = array();
	}
}