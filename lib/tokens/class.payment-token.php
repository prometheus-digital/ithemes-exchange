<?php
/**
 * Payment Token model.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use IronBound\Cache\Cache;
use IronBound\DB\Extensions\Meta\ModelWithMeta;
use IronBound\DB\Extensions\Trash\TrashTable;
use IronBound\DB\Query\FluentQuery;

/**
 * Class ITE_Payment_Token
 *
 * @property int                        $ID
 * @property-read \IT_Exchange_Customer $customer
 * @property-read string                $token
 * @property-read ITE_Gateway           $gateway
 * @property string                     $label
 * @property string                     $redacted
 * @property-read bool                  $primary
 * @property-read string                $mode
 * @property \DateTime                  $expires_at
 * @property \DateTime                  $deleted
 */
class ITE_Payment_Token extends ModelWithMeta implements ITE_Object, ITE_Gateway_Payment_Source {

	/** @var array */
	protected static $token_types = array();

	/** @var string */
	protected static $token_type;

	/** @var bool */
	private $force_deleting = false;

	/**
	 * @inheritDoc
	 */
	public function get_pk() { return $this->ID; }

	/**
	 * Make this the primary payment token for the customer.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function make_primary() {

		if ( ! $this->customer || ! $this->customer->ID ) {
			return false;
		}

		if ( $this->primary ) {
			return true;
		}

		/** @var static $other */
		$other = static::query()
		               ->where( 'customer', '=', $this->customer->ID )
		               ->and_where( 'primary', '=', true )
		               ->and_where( 'gateway', '=', $this->get_raw_attribute( 'gateway' ) )
		               ->and_where( 'mode', '=', $this->mode )
		               ->first();

		if ( $other ) {
			$other->set_attribute( 'primary', false );

			if ( ! $other->save() ) {
				return false;
			}
		}

		$this->set_attribute( 'primary', true );

		return $this->save();
	}

	/**
	 * Make this a non-primary payment token.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 *
	 * @throws \InvalidArgumentException
	 */
	public function make_non_primary() {

		if ( ! $this->primary ) {
			return true;
		}

		/** @var static $other */
		$other = static::without_global_scope( 'active' )
		               ->where( 'customer', '=', $this->customer->ID )
		               ->and_where( 'primary', '=', false )
		               ->and_where( 'gateway', '=', $this->get_raw_attribute( 'gateway' ) )
		               ->and_where( 'mode', '=', $this->mode )
		               ->and_where( 'ID', '!=', $this->get_ID() )
		               ->first();

		if ( ! $other ) {
			throw new InvalidArgumentException( 'At least one payment token must be primary.' );
		}

		$other->set_attribute( 'primary', true );

		if ( ! $other->save() ) {
			return false;
		}

		$this->set_attribute( 'primary', false );

		return $this->save();
	}

	/**
	 * Is this payment token expired.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_expired() {

		$now = new DateTime( 'now', new DateTimeZone( 'UTC' ) );

		return $this->expires_at && $now >= $this->expires_at;
	}

	/**
	 * Is this token expiring soon.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_expiring_soon() {

		if ( $this->is_expired() ) {
			return false;
		}

		if ( ! $this->expires_at ) {
			return false;
		}

		$now  = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$diff = $this->expires_at->diff( $now );

		return $diff->days < 30;
	}

	/**
	 * Get the token's label.
	 *
	 * This should fallback to a default value if a user-provided value is not available.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->redacted;
	}

	/**
	 * @inheritDoc
	 */
	public function get_identifier() { return (string) $this->get_ID(); }

	/**
	 * @inheritDoc
	 */
	public function get_ID() { return $this->get_pk(); }

	/**
	 * @inheritDoc
	 */
	public static function get_object_type() { return it_exchange_object_type_registry()->get( 'payment-token' ); }

	/**
	 * @inheritDoc
	 */
	public static function get( $pk ) {

		if ( ! $pk ) {
			return null;
		}

		if ( ! is_scalar( $pk ) ) {
			throw new \InvalidArgumentException( 'Primary key must be scalar.' );
		}

		$data = static::get_data_from_pk( $pk );

		if ( $data && isset( static::$token_types[ $data->type ] ) ) {

			$class = static::$token_types[ $data->type ]['class'];

			/** @var ITE_Payment_Token $object */
			$object = new $class( new \stdClass() );
			$object->set_raw_attributes( (array) $data, true );
			$object->_exists = true;

			if ( static::$_cache && ! static::is_data_cached( $pk ) ) {
				Cache::update( $object );
			}

			foreach ( static::$_eager_load as $eager_load ) {
				if ( ! $object->is_relation_loaded( $eager_load ) ) {
					$object->get_relation( $eager_load )->eager_load( array( $object ) );
				}
			}

			return $object;
		} else {
			return null;
		}
	}

	/**
	 * @inheritDoc
	 */
	public static function from_query( array $attributes = array() ) {

		if ( empty( $attributes['type'] ) || ! isset( static::$token_types[ $attributes['type'] ] ) ) {
			return null;
		}

		$class = static::$token_types[ $attributes['type'] ]['class'];

		/** @var ITE_Payment_Token $instance */
		$instance = new $class( new \stdClass() );
		$instance->set_raw_attributes( $attributes, true );
		$instance->_exists = true;

		if ( static::$_cache && ! static::is_data_cached( $instance->get_pk() ) ) {
			Cache::update( $instance );
		}

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	protected static function _do_create( array $attributes = array() ) {

		$attributes['type'] = static::$token_type;

		if ( isset( $attributes['gateway'] ) && empty( $attributes['mode'] ) ) {
			$attributes['mode'] = ITE_Gateways::get( $attributes['gateway'] )->is_sandbox_mode() ? 'sandbox' : 'live';
		}

		return parent::_do_create( $attributes );
	}

	/**
	 * @inheritDoc
	 * @throws \InvalidArgumentException
	 */
	protected static function boot() {
		parent::boot();

		static::register_global_scope( 'active', function ( FluentQuery $query ) {

			$gateways = ITE_Gateways::handles( 'tokenize' );

			$query->and_where( 'gateway', false, null, function ( FluentQuery $query ) use ( $gateways ) {
				foreach ( $gateways as $i => $gateway ) {
					$query->where( 'gateway', '=', $gateway->get_slug(), function ( FluentQuery $query ) use ( $gateway ) {
						$query->and_where( 'mode', '=', $gateway->is_sandbox_mode() ? 'sandbox' : 'live' );
					}, $i === 0 ? 'and' : 'or' );
				}
			} );
		} );

		static::register_global_scope( 'order', function ( FluentQuery $query ) {
			$query->order_by( 'primary', 'DESC' )->order_by( 'ID', 'ASC' );
		} );

		static::register_global_scope( 'expires_at', function ( FluentQuery $query ) {

			$now = current_time( 'mysql', true );

			$where = new \IronBound\DB\Query\Tag\Where_Raw(
				"(t1.`expires_at` > '$now' OR t1.`expires_at` IS NULL)"
			);
			$query->and_where( $where );
		} );

		$table = static::table();

		if ( ! $table instanceof TrashTable ) {
			throw new \UnexpectedValueException( sprintf( "%s model's table must implement TrashTable.", get_called_class() ) );
		}

		static::register_global_scope( 'trash', function ( FluentQuery $query ) use ( $table ) {
			$query->and_where( $table->get_deleted_at_column(), true, null );
		} );
	}

	/**
	 * Register a token type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 * @param string $class
	 * @param string $label
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function register_token_type( $type, $class, $label = '' ) {

		if ( ! is_subclass_of( $class, 'ITE_Payment_Token' ) ) {
			throw new InvalidArgumentException( 'Class must be a subclass of ITE_Payment_Token.' );
		}

		static::$token_types[ $type ] = array( 'class' => $class, 'label' => $label );
	}

	/**
	 * Get the token type label.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public static function get_token_type_label( $type ) {

		$data = static::$token_types[ $type ];

		if ( ! $data ) {
			return '';
		}

		if ( $data['label'] ) {
			return $data['label'];
		}

		return ucfirst( $type );
	}

	/**
	 * @inheritdoc
	 */
	public function delete() {

		if ( $this->force_deleting ) {
			return parent::delete();
		} else {

			$this->fire_model_event( 'trashing' );

			$table = static::table();

			$this->{$table->get_deleted_at_column()} = $this->fresh_timestamp();

			$this->fire_model_event( 'trashed' );

			return $this->save();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function force_delete() {
		$this->force_deleting = true;
		$this->delete();
		$this->force_deleting = false;
	}

	/**
	 * @inheritdoc
	 */
	public function untrash() {

		$this->fire_model_event( 'untrashing' );

		$table                                   = static::table();
		$this->{$table->get_deleted_at_column()} = null;

		$this->fire_model_event( 'untrashed' );

		return $this->save();
	}

	/**
	 * @inheritdoc
	 */
	public function is_trashed() {

		$table  = static::table();
		$column = $table->get_deleted_at_column();

		return $this->{$column} !== null;
	}

	/**
	 * @inheritdoc
	 */
	public static function with_trashed() {
		return static::without_global_scope( 'trash' );
	}

	/**
	 * @inheritdoc
	 */
	public static function only_trashed() {

		/** @var FluentQuery $query */
		$query = static::with_trashed();

		$query->where( static::table()->get_deleted_at_column(), false, null );

		return $query;
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() { return static::$_db_manager->get( 'ite-payment-tokens' ); }

	/**
	 * @inheritdoc
	 */
	public static function get_meta_table() { return static::$_db_manager->get( 'ite-payment-tokens-meta' ); }

	// Retrieve an IT_Exchange_Customer instead of WP_User for 'customer'
	protected function _access_customer( $value ) {
		return it_exchange_get_customer( $value );
	}

	protected function _mutate_customer( $value ) {

		if ( $value instanceof IT_Exchange_Customer ) {
			$value = $value->wp_user;
		}

		return $value;
	}

	// Retrieve an ITE_Gateway instead of the gateway slug.
	protected function _access_gateway( $value ) {
		return ITE_Gateways::get( $value );
	}

	/**
	 * @inheritdoc
	 */
	public static function trashing( $callback, $priority = 10, $accepted_args = 3 ) {
		return static::register_model_event( 'trashing', $callback, $priority, $accepted_args );
	}

	/**
	 * @inheritdoc
	 */
	public static function trashed( $callback, $priority = 10, $accepted_args = 3 ) {
		return static::register_model_event( 'trashed', $callback, $priority, $accepted_args );
	}

	/**
	 * @inheritdoc
	 */
	public static function untrashing( $callback, $priority = 10, $accepted_args = 3 ) {
		return static::register_model_event( 'untrashing', $callback, $priority, $accepted_args );
	}

	/**
	 * @inheritdoc
	 */
	public static function untrashed( $callback, $priority = 10, $accepted_args = 3 ) {
		return static::register_model_event( 'untrashed', $callback, $priority, $accepted_args );
	}
}
