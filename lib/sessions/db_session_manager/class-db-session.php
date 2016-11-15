<?php
/**
 * WordPress session managment.
 *
 * Standardizes WordPress session data using database-backed options for storage.
 * for storing user session information.
 *
 * @subpackage Session
 * @since      0.4.0
 */

/**
 * WordPress Session class for managing user session data.
 *
 * @since   0.4.0
 */
final class IT_Exchange_DB_Sessions extends Recursive_ArrayAccess implements Iterator, Countable {
	/**
	 * ID of the current session.
	 *
	 * @var string
	 */
	protected $session_id;

	/**
	 * Option Key of the current session.
	 *
	 * @var string
	 */
	protected $option_key;

	/**
	 * Unix timestamp when session expires.
	 *
	 * @var int
	 */
	protected $expires;

	/**
	 * Unix timestamp indicating when the expiration time needs to be reset.
	 *
	 * @var int
	 */
	protected $exp_variant;

	/** @var ITE_Session_Model */
	protected $model;

	/**
	 * Singleton instance.
	 *
	 * @var bool|IT_Exchange_DB_Sessions
	 */
	private static $instance = false;

	/**
	 * Retrieve the current session instance.
	 *
	 * @param bool $session_id Session ID from which to populate data.
	 *
	 * @return IT_Exchange_DB_Sessions
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Default constructor.
	 * Will rebuild the session collection from the given session ID if it exists. Otherwise, will
	 * create a new session with that ID.
	 *
	 * @uses apply_filters Calls `it_exchange_db_session_expiration` to determine how long until sessions expire.
	 */
	protected function __construct() {

		parent::__construct();

		if ( ! function_exists( 'it_exchange_get_current_customer_id' ) ) {
			return;
		}

		if ( isset( $_COOKIE[ IT_EXCHANGE_SESSION_COOKIE ] ) ) {
			$cookie        = stripslashes( $_COOKIE[ IT_EXCHANGE_SESSION_COOKIE ] );
			$cookie_crumbs = explode( '||', $cookie );

			if ( $this->is_valid_md5( $cookie_crumbs[0] ) ) {
				$this->session_id = $cookie_crumbs[0];
				$this->model      = ITE_Session_Model::get( $this->session_id );

				if ( $this->model && $this->model->customer ) {
					if ( (int) it_exchange_get_current_customer_id() !== (int) $this->model->customer->ID ) {
						$this->remove_cookie();
						unset( $this->session_id, $this->model, $this->container );

						return;
					}
				}

				if ( ! $this->model && ( $cid = it_exchange_get_current_customer_id() ) && is_numeric( $cid ) ) {
					$model = ITE_Session_Model::query()
					                          ->where( 'customer', '=', $cid )
					                          ->order_by( 'updated_at', 'DESC' )
					                          ->first();

					if ( $model ) {
						$this->model      = $model;
						$this->session_id = $model->ID;
					}
				}

				if ( $this->model ) {
					$this->container = $this->model->data;
				}
			} else {
				$this->regenerate_id( true );
			}

			$this->expires     = $cookie_crumbs[1];
			$this->exp_variant = $cookie_crumbs[2];

			// Update the session expiration if we're past the variant time
			if ( time() > $this->exp_variant && $this->model ) {
				$this->set_expiration();

				$this->model->expires_at = new \DateTime( "@{$this->expires}" );
				$this->model->save();
			}

			$this->option_key = $this->generate_option_key();

			$this->set_cookie();
		} elseif ( ! $this->is_rest_request() && is_user_logged_in() ) {
			$this->initialize_new_session();
		}
	}

	/**
	 * Is this a REST request.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	protected function is_rest_request() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		if ( \iThemes\Exchange\REST\get_rest_manager()->is_our_endpoint() ) {
			return true;
		}

		return false;
	}

	/**
	 * Set both the expiration time and the expiration variant.
	 *
	 * If the current time is below the variant, we don't update the session's expiration time. If it's
	 * greater than the variant, then we update the expiration time in the database.  This prevents
	 * writing to the database on every page load for active sessions and only updates the expiration
	 * time if we're nearing when the session actually expires.
	 *
	 * By default, the expiration time is set to 24 hours.
	 * By default, the expiration variant is set to 24 minutes.
	 *
	 * As a result, the session expiration time - at a maximum - will only be written to the database once
	 * every 24 minutes.  After 24 hours, the session will have been expired. No cookie will be sent by
	 * the browser, and the old session will be queued for deletion by the garbage collector.
	 *
	 * @uses apply_filters Calls `it_exchange_db_session_expiration_variant` to get the max update window for session
	 *       data.
	 * @uses apply_filters Calls `it_exchange_db_session_expiration` to get the standard expiration time for sessions.
	 */
	protected function set_expiration() {
		$this->exp_variant = time() + (int) apply_filters( 'it_exchange_db_session_expiration_variant', 24 * 60 );
		$this->expires     = time() + (int) apply_filters( 'it_exchange_db_session_expiration', DAY_IN_SECONDS * 2 );
	}

	/**
	 * Generate the option key.
	 *
	 * @since CHANGEME
	 *
	 * @return string
	 */
	protected function generate_option_key() {
		return '_it_exchange_db_session_' . $this->session_id;
	}

	/**
	 * Set the session cookie
	 */
	protected function set_cookie() {

		if ( ! $this->session_id ) {
			return;
		}

		if ( headers_sent() ) {
			return;
		}

		$secure   = apply_filters( 'wp_session_cookie_secure', false );
		$httponly = apply_filters( 'wp_session_cookie_httponly', false );
		setcookie(
			IT_EXCHANGE_SESSION_COOKIE,
			$this->session_id . '||' . $this->expires . '||' . $this->exp_variant,
			$this->expires,
			COOKIEPATH,
			COOKIE_DOMAIN,
			$secure,
			$httponly
		);
	}

	/**
	 * Remove the cookie.
	 *
	 * @since 2.0.0
	 */
	public function remove_cookie() {
		unset( $_COOKIE[ IT_EXCHANGE_SESSION_COOKIE ] );
		setcookie( IT_EXCHANGE_SESSION_COOKIE, '', time() - 3600, '/' );
	}

	/**
	 * Generate a cryptographically strong unique ID for the session token.
	 *
	 * @return string
	 */
	protected function generate_id() {
		return md5( bin2hex( random_bytes( 32 ) ) );
	}

	/**
	 * Checks if is valid md5 string
	 *
	 * @param string $md5
	 *
	 * @since 1.32.1
	 *
	 * @return int
	 */
	protected function is_valid_md5( $md5 = '' ) {
		return preg_match( '/^[a-f0-9]{32}$/', $md5 );
	}

	/**
	 * Read data from a transient for the current session.
	 *
	 * Automatically resets the expiration time for the session transient to some time in the future.
	 *
	 * @return array
	 */
	protected function read_data() {

		if ( ! $this->session_id ) {
			return array();
		}

		$this->model = ITE_Session_Model::get( $this->session_id );

		if ( ! $this->model && $this->container ) {
			$this->dirty = true;
			$this->model = $this->create_model();
		} else {
			return array();
		}

		$this->container = $this->model->data;

		return $this->container;
	}

	/**
	 * Write the data from the current session to the data storage system.
	 */
	public function write_data() {

		// Only write the collection to the DB if it's changed.
		if ( ! $this->dirty || ! $this->session_id ) {
			return;
		}

		if ( ! empty( $this->container['cart_id'] ) ) {
			$cart_id = unserialize( $this->container['cart_id'] );

			if ( is_array( $cart_id ) ) {
				$cart_id = reset( $cart_id );
			}
		}

		if ( $this->model ) {
			$this->model->data = $this->container;

			if ( ! $this->model->customer && $cid = it_exchange_get_current_customer_id() ) {
				$this->model->customer = $cid;
			}

			if ( ! empty( $cart_id ) ) {
				$this->model->cart_id = $cart_id;
			} else {
				$this->model->cart_id = null;
			}

			$this->model->save();
		} else {
			$this->model = $this->create_model();
		}

		$this->dirty = false;
	}

	/**
	 * Transfer the session
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Session_Model|null $model
	 * @param bool                    $migrate_data
	 */
	public function transfer_session( ITE_Session_Model $model = null, $migrate_data = false ) {

		if ( $migrate_data && $this->container && $model ) {
			$model->data = array_merge( $model->data, $this->container );
		}

		if ( $this->model ) {
			$this->model->delete();
		}

		$this->model = $model;

		$this->session_id = $model ? $model->ID : null;
		$this->container  = $model ? $model->data : array();
	}

	/**
	 * Create the model.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Session_Model
	 */
	private function create_model() {
		$args = array(
			'ID'   => $this->session_id,
			'data' => $this->container
		);

		if ( $cid = it_exchange_get_current_customer_id() ) {
			$args['customer'] = $cid;
		}

		if ( ! empty( $cart_id ) ) {
			$args['cart_id'] = $cart_id;
		}

		return ITE_Session_Model::create( $args );
	}

	/**
	 * Output the current container contents as a JSON-encoded string.
	 *
	 * @return string
	 */
	public function json_out() {
		return json_encode( $this->container );
	}

	/**
	 * Decodes a JSON string and, if the object is an array, overwrites the session container with its contents.
	 *
	 * @param string $data
	 *
	 * @return bool
	 */
	public function json_in( $data ) {
		$array = json_decode( $data );

		if ( is_array( $array ) ) {

			if ( ! $this->session_id ) {
				$this->initialize_new_session();
			}

			$this->container = $array;

			return true;
		}

		return false;
	}

	/**
	 * Initialize a new session.
	 *
	 * This is different then regenerating an ID. This is used when session data is added mid-page request,
	 * and a session cookie hasn't yet been established.
	 *
	 * @since CHANGEME
	 */
	protected function initialize_new_session() {

		if ( is_user_logged_in() ) {
			$existing = ITE_Session_Model::find_best_for_customer( it_exchange_get_current_customer() );

			if ( $existing ) {

				$this->model      = $existing;
				$this->session_id = $existing->ID;
				$this->option_key = $this->generate_option_key();
				$this->container  = $existing->data;
				$this->set_expiration();

				if ( $existing->expires_at ) {
					$this->expires = $existing->expires_at->getTimestamp();
				}

				$this->set_cookie();

				return;
			}
		}

		$this->session_id = $this->generate_id();
		$this->set_expiration();

		$this->option_key = $this->generate_option_key();

		$this->read_data();
		$this->model = $this->create_model();

		$this->set_cookie();
	}

	/**
	 * Offset to set.
	 *
	 * Initialize a session prior to setting the data, if this hasn't been done yet.
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 *
	 * @return void
	 */
	public function offsetSet( $offset, $data ) {

		if ( ! $this->session_id ) {
			$this->initialize_new_session();
		}

		parent::offsetSet( $offset, $data );
	}

	/**
	 * Regenerate the current session's ID.
	 *
	 * @param bool $delete_old Flag whether or not to delete the old session data from the server.
	 */
	public function regenerate_id( $delete_old = false ) {
		if ( $delete_old ) {
			$model = ITE_Session_Model::get( $this->session_id );

			if ( $model ) {
				$model->delete();
			}
		}

		$this->session_id = $this->generate_id();
		$this->model      = $this->create_model();

		$this->set_cookie();
	}

	/**
	 * Check if a session has been initialized.
	 *
	 * @return bool
	 */
	public function session_started() {
		return ! ! self::$instance;
	}

	/**
	 * Return the read-only cache expiration value.
	 *
	 * @return int
	 */
	public function cache_expiration() {
		return $this->expires;
	}

	/**
	 * Flushes all session variables.
	 */
	public function reset() {
		$this->container = array();
	}

	/*****************************************************************/
	/*                     Iterator Implementation                   */
	/*****************************************************************/

	/**
	 * Current position of the array.
	 *
	 * @link http://php.net/manual/en/iterator.current.php
	 *
	 * @return mixed
	 */
	public function current() {
		return current( $this->container );
	}

	/**
	 * Key of the current element.
	 *
	 * @link http://php.net/manual/en/iterator.key.php
	 *
	 * @return mixed
	 */
	public function key() {
		return key( $this->container );
	}

	/**
	 * Move the internal point of the container array to the next item
	 *
	 * @link http://php.net/manual/en/iterator.next.php
	 *
	 * @return void
	 */
	public function next() {
		next( $this->container );
	}

	/**
	 * Rewind the internal point of the container array.
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 *
	 * @return void
	 */
	public function rewind() {
		reset( $this->container );
	}

	/**
	 * Is the current key valid?
	 *
	 * @link http://php.net/manual/en/iterator.rewind.php
	 *
	 * @return bool
	 */
	public function valid() {
		return $this->offsetExists( $this->key() );
	}

	/*****************************************************************/
	/*                    Countable Implementation                   */
	/*****************************************************************/

	/**
	 * Get the count of elements in the container array.
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->container );
	}
}
