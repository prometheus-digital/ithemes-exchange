<?php
/**
 * Address class.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use IronBound\DB\Extensions\Trash\TrashTable;
use IronBound\DB\Query\FluentQuery;

/**
 * Class ITE_Address
 *
 * @property int                   $ID
 * @property \IT_Exchange_Customer $customer
 * @property string                $label
 * @property \DateTime             $deleted_at
 */
class ITE_Saved_Address extends \IronBound\DB\Model implements ITE_Location {

	const T_SHIPPING = 'shipping';
	const T_BILLING = 'billing';

	/** @var bool */
	private $force_deleting = false;

	protected $_guarded = array( 'deleted_at', 'ID' );

	/**
	 * @inheritDoc
	 */
	public function contains( ITE_Location $location, $upper_bound = '' ) {

		$priority = array( 'country', 'state', 'zip', 'city' );

		foreach ( $priority as $field ) {
			if ( $this[ $field ] !== $location[ $field ] && $location[ $field ] !== self::WILD && $this[ $field ] !== self::WILD ) {
				return false;
			}

			if ( $upper_bound === $field ) {
				return true;
			}
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function equals( ITE_Location $location ) {

		foreach ( $this as $field => $value ) {

			if ( $value === self::WILD ) {
				continue;
			}

			if ( ! isset( $location[ $field ] ) ) {
				return false;
			}

			if ( $location[ $field ] === self::WILD ) {
				continue;
			}

			if ( $value !== $location[ $field ] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function to_array() {
		$data = parent::to_array();

		foreach ( $data as $column => $value ) {
			if ( ! in_array( $column, $this->get_address_columns(), true ) ) {
				unset( $data[ $column ] );
			}
		}

		return $data;
	}

	/**
	 * Is this the primary shipping address for the customer this is attached to.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_primary_shipping() {

		if ( ! $this->customer ) {
			return false;
		}

		$address = $this->customer->get_shipping_address( true );

		return $address && $this->get_pk() == $address->get_pk();
	}

	/**
	 * Is this the primary billing address for the customer this is attached to.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_primary_billing() {

		if ( ! $this->customer ) {
			return false;
		}

		$address = $this->customer->get_billing_address( true );

		return $address && $this->get_pk() == $address->get_pk();
	}

	/**
	 * Get the address type.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		$pb = $this->is_primary_billing();
		$ps = $this->is_primary_shipping();

		if ( $pb && $ps ) {
			return 'both';
		} elseif ( $pb ) {
			return 'billing';
		} elseif ( $ps ) {
			return 'shipping';
		} else {
			return '';
		}
	}

	/**
	 * Get the date th
	 *
	 * @since 2.0.0
	 *
	 * @return DateTime|null
	 */
	public function get_last_used_date() {
		// SELECT `order_date` FROM wp_ite_transactions WHERE billing = 392 OR shipping = 392  ORDER BY `order_date` DESC LIMIT 1

		$r = IT_Exchange_Transaction::query_with_no_global_scopes()
		                            ->or_where( 'billing', '=', $this->get_pk() )
		                            ->or_where( 'shipping', '=', $this->get_pk() )
		                            ->order_by( 'order_date', 'DESC' )
		                            ->take( 1 )
		                            ->select_single( 'order_date' )
		                            ->results();

		$date = $r->first();

		return $date ? new DateTime( $date, new DateTimeZone( 'UTC' ) ) : null;
	}

	/**
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->ID;
	}

	/**
	 * @inheritDoc
	 */
	protected static function get_table() {
		return static::$_db_manager->get( 'ite-address' );
	}

	/**
	 * @inheritDoc
	 */
	public function set_attribute( $attribute, $value ) {
		try {
			return parent::set_attribute( $attribute, $value );
		} catch ( OutOfBoundsException $e ) {
			return $this;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ) {

		if ( ! in_array( $offset, $this->get_address_columns(), true ) ) {
			return false;
		}

		return isset( $this->$offset );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $offset ) {
		if ( ! in_array( $offset, $this->get_address_columns(), true ) ) {
			return null;
		}

		return $this->$offset;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $offset, $value ) {

		if ( ! in_array( $offset, $this->get_address_columns(), true ) ) {
			return;
		}

		$this->$offset = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( $offset ) {

		if ( ! in_array( $offset, $this->get_address_columns(), true ) ) {
			return;
		}

		unset( $this->$offset );
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator() {

		$values = array();

		foreach ( $this->get_address_columns() as $column ) {
			$values[ $column ] = $this->get_attribute( $column );
		}

		return new ArrayIterator( $values );
	}

	/**
	 * @inheritDoc
	 */
	protected static function _do_create( array $attributes = array() ) {

		$attributes = array_intersect_key( $attributes, static::table()->get_column_defaults() );

		return parent::_do_create( $attributes );
	}

	/**
	 * Get the address columns.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	protected function get_address_columns() {

		$columns = array_keys( static::table()->get_column_defaults() );

		return array_diff( $columns, array( 'ID', 'customer', 'label', 'deleted_at' ) );
	}

	/**
	 * Convert a location to a saved address.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location         $location The location to save.
	 * @param \ITE_Location|null    $current  The current address.
	 * @param \IT_Exchange_Customer $customer The customer this address belongs to. Null if a Guest Customer.
	 * @param string                $type     The address type. Either 'billing' or 'shipping'.
	 * @param bool                  $validate Validate the address before saving.
	 * @param bool                  $filter   Apply deprecated save filter when validating.
	 *
	 * @return \ITE_Saved_Address
	 *
	 * @throws \InvalidArgumentException If location fails validation.
	 */
	public static function convert_to_saved(
		ITE_Location $location,
		ITE_Location $current = null,
		IT_Exchange_Customer $customer = null,
		$type = '',
		$validate = true,
		$filter = false
	) {

		if ( ! $type && $location instanceof ITE_Saved_Address ) {
			$type = $location->get_type();
		} elseif ( ! $type && $current instanceof ITE_Saved_Address ) {
			$type = $current->get_type();
		}

		$cid = $customer ? $customer->ID : 0;

		if ( $validate && $type ) {
			$location = self::validate_location( $location, $type, $filter ? $cid : false );
		}

		$fields = array_intersect_key( $location->to_array(), ITE_Saved_Address::table()->get_column_defaults() );

		if ( $customer ) {
			$fields['customer'] = $customer->get_ID();
		}

		if ( $location instanceof ITE_Saved_Address ) {
			$fields['label'] = $location->label;
		} elseif ( $current instanceof ITE_Saved_Address ) {
			$fields['label'] = $current->label;
		}

		if ( $current && $location instanceof ITE_Saved_Address && $customer ) {

			// This is an update to an address that is shared amongst
			// multiple transactions. So as not to affect those other
			// transactions we split this into a new record.
			if ( ( $ctype = $current->get_type() ) === 'both' || $location->is_address_used() ) {

				/** @var ITE_Saved_Address $new */
				$new = ITE_Saved_Address::with_trashed()->first_or_create( array_filter( $fields ) );

				if ( $new->is_trashed() ) {
					$new->untrash();
				}

				if ( $type && $type !== 'both' ) {
					$customer->{"set_{$type}_address"}( $new );
				}

				if ( $ctype !== 'both' ) {
					$current->delete();
				}

				return $new;
			}

			if ( $current instanceof ITE_Saved_Address && $current->get_pk() === $location->get_pk() ) {

				$location->save();

				return $location;
			} elseif ( $location->exists() && $current->get_pk() !== $location->get_pk() ) {
				if ( $current->equals( $location ) ) {
					if ( ! $location->label ) {
						$location->label = $current->label;
					}

					$current->delete();
				}

				$location->customer = $cid;
				$location->save();

				return $location;
			}
		} elseif ( $location instanceof ITE_Saved_Address ) {
			$location->customer = $cid;
			$location->save();

			return $location;
		}

		if ( $cid ) {
			/** @var ITE_Saved_Address $new */
			$new = ITE_Saved_Address::with_trashed()->first_or_create( array_filter( $fields ) );

			if ( $new->is_trashed() ) {
				$new->untrash();
			}

			return $new;
		} else { // We don't want to share addresses amongst guest customers.
			return ITE_Saved_Address::create( $fields );
		}
	}

	/**
	 * Is this address in use.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_address_used() {
		$results = IT_Exchange_Transaction::query_with_no_global_scopes()
		                                  ->where( 'billing', '=', $this->get_pk() )
		                                  ->or_where( 'shipping', '=', $this->get_pk() )
		                                  ->expression( 'count', 'ID', 'count' )->results();

		return $results->get( 'count' ) > 0;
	}

	/**
	 * Validate a location.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location $location
	 * @param string        $type
	 * @param int           $customer_id Pass the customer's ID to fire the deprecated save filter.
	 *
	 * @return \ITE_Location Will return an instance of the original class.
	 *
	 * @throws \InvalidArgumentException
	 */
	private static function validate_location( ITE_Location $location, $type, $customer_id ) {

		foreach ( ITE_Location_Validators::all() as $validator ) {
			if ( ! $validator->can_validate() || $validator->can_validate()->contains( $location ) ) {
				$valid = $validator->validate( $location );

				if ( $valid !== true ) {
					throw new InvalidArgumentException( 'Location failed validation: ' . $valid );
				}
			}
		}

		if ( ! $customer_id || $type === 'both' ) {
			return $location;
		}

		$array    = $location->to_array();
		$filtered = apply_filters_deprecated(
			"it_exchange_save_customer_{$type}_address", array( $array, $customer_id ), '2.0.0'
		);

		if ( ! is_array( $filtered ) ) {
			throw new InvalidArgumentException( 'Location failed validation.' );
		}

		if ( $filtered !== $array ) {
			foreach ( $filtered as $key => $value ) {
				if ( empty( $location[ $key ] ) || $location[ $key ] !== $value ) {
					$location[ $key ] = $value;
				}
			}
		}

		return $location;
	}

	/**
	 * @inheritDoc
	 */
	protected static function boot() {
		parent::boot();

		$table = static::table();

		if ( ! $table instanceof TrashTable ) {
			throw new \UnexpectedValueException( sprintf( "%s model's table must implement TrashTable.", get_called_class() ) );
		}

		static::register_global_scope( 'trash', function ( FluentQuery $query ) use ( $table ) {
			$query->and_where( $table->get_deleted_at_column(), true, null );
		} );

		static::register_global_scope( 'order', function ( FluentQuery $query ) {
			$query->order_by( 'ID', 'ASC' );
		} );
	}

	// Retrieve an IT_Exchange_Customer instead of WP_User for 'customer'
	protected function _access_customer( $value ) {
		return it_exchange_get_customer( $value );
	}

	protected function _mutate_customer( $value ) {

		if ( $this->get_raw_attribute( 'customer' ) ) {
			return $this->get_raw_attribute( 'customer' );
		}

		if ( $value instanceof IT_Exchange_Customer ) {
			$value = $value->wp_user;
		}

		return $value;
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
