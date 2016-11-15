<?php
/**
 * In-Memory Session.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_In_Memory_Session
 */
class IT_Exchange_In_Memory_Session implements IT_Exchange_SessionInterface {

	/**
	 * @var callable|null
	 */
	private $save;

	/**
	 * @var array
	 */
	private $session = array();

	/**
	 * IT_Exchange_In_Memory_Session constructor.
	 *
	 * @param callable $save
	 * @param array    $session
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $save, array $session = array() ) {

		if ( $save && ! is_callable( $save ) ) {
			throw new InvalidArgumentException( "save method must be callable." );
		}

		$this->save    = $save;
		$this->session = $session;
	}

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

		$this->save();
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

		$this->save();
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

		$this->save();
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

		if ( $c = it_exchange_get_current_cart( false ) ) {
			$c->destroy();
		}

		$this->save();
	}

	/**
	 * Save the session if a saver method exists.
	 *
	 * @since 2.0.0
	 */
	protected final function save() {
		if ( $this->save ) {
			call_user_func( $this->save, $this->session );
		}
	}

	/**
	 * Set the save method.
	 *
	 * @since 2.0.0
	 *
	 * @param callable $save
	 */
	public function set_save( $save ) {

		if ( ! is_callable( $save ) ) {
			throw new InvalidArgumentException( "save method must be callable." );
		}

		$this->save = $save;
	}
}
