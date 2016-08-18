<?php
/**
 * Address class.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Address
 *
 * @property int                   $pk
 * @property \IT_Exchange_Customer $customer
 * @property string                $label
 * @property bool                  $primary
 * @property string                $type
 */
class ITE_Saved_Address extends \IronBound\DB\Model implements ITE_Location {

	const T_SHIPPING = 'shipping';
	const T_BILLING = 'billing';

	/**
	 * Make this the primary address for the customer.
	 *
	 * @since 1.36.0
	 *
	 * @return bool
	 */
	public function make_primary() {

		if ( ! $this->customer || ! $this->customer->ID ) {
			return false;
		}

		/** @var static $other */
		$other = static::query()->where( 'customer', '=', $this->customer->ID )->and_where( 'primary', '=', true )
		               ->and_where( 'type', '=', $this->type )->first();

		if ( $other ) {
			$other->primary = false;
			$other->save();
		}

		$this->primary = true;

		return $this->save();
	}

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
	 * @inheritDoc
	 */
	public function get_pk() {
		return $this->pk;
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
		}
		catch ( OutOfBoundsException $e ) {
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
	 * Get the address columns.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	protected function get_address_columns() {

		$columns = array_keys( static::table()->get_column_defaults() );

		return array_diff( $columns, array( 'pk', 'customer', 'label', 'primary' ) );
	}

	/**
	 * Convert a location to a saved address.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Location         $location
	 * @param \ITE_Location|null    $current
	 * @param \IT_Exchange_Customer $customer
	 * @param string                $type
	 * @param bool                  $validate
	 * @param bool                  $filter Call the deprecated validation filter.
	 *
	 * @return \ITE_Saved_Address
	 *
	 * @throws \InvalidArgumentException If location fails validation.
	 */
	public static function convert_to_saved(
		ITE_Location $location, ITE_Location $current = null, IT_Exchange_Customer $customer = null, $type, $validate = true, $filter = false
	) {

		$cid = $customer ? $customer->ID : 0;

		if ( $validate ) {
			$location = self::validate_location( $location, $type, $filter ? $cid : false );
		}

		if ( $current && $location instanceof ITE_Saved_Address ) {
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
				$location->type     = $type;
				$location->make_primary();

				return $location;
			}
		} elseif ( $location instanceof ITE_Saved_Address ) {
			$location->customer = $cid;
			$location->primary  = true;
			$location->type     = $type;

			return $location;
		}

		return ITE_Saved_Address::create( array_merge( $location->to_array(), array(
			'customer' => $cid,
			'primary'  => true,
			'type'     => $type,
		) ) );
	}

	/**
	 * Validate a location.
	 *
	 * @since 1.36.0
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

		if ( ! $customer_id ) {
			return $location;
		}

		$array    = $location->to_array();
		$filtered = apply_filters_deprecated(
			"it_exchange_save_customer_{$type}_address", array( $array, $customer_id ), '1.36.0'
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
}