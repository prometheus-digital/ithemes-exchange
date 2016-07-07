<?php
/**
 * Session Repository class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Line_Item_Session_Repository
 */
class ITE_Line_Item_Session_Repository extends ITE_Line_Item_Repository {

	/** @var IT_Exchange_SessionInterface */
	protected $session;

	/** @var ITE_Line_Item_Repository_Events */
	private $events;

	/**
	 * ITE_Line_Item_Session_Repository constructor.
	 *
	 * @param \IT_Exchange_SessionInterface    $session
	 * @param \ITE_Line_Item_Repository_Events $events
	 */
	public function __construct( \IT_Exchange_SessionInterface $session, ITE_Line_Item_Repository_Events $events ) {
		$this->session = $session;
		$this->events  = $events;
	}

	/**
	 * @inheritDoc
	 */
	public function get( $type, $id ) {
		$data = $this->session->get_session_data( self::normalize_type( $type ) );

		if ( ! isset( $data[ $id ] ) ) {
			return null;
		}

		return $this->construct_item( $id, $data[ $id ] );
	}

	/**
	 * @inheritDoc
	 */
	public function all( $type = '' ) {

		if ( $type ) {
			$type = self::normalize_type( $type );

			$data = $this->session->get_session_data( $type );

			$items = array();

			foreach ( $data as $id => $item_data ) {
				if ( ! empty( $item_data['_parent'] ) ) {
					continue;
				}

				$item = $this->construct_item( $id, $item_data );

				if ( $item ) {
					$items[] = $item;
				}
			}

			return new ITE_Line_Item_Collection( $items, $this );
		}

		$items    = array();
		$all_data = $this->session->get_session_data();

		foreach ( $all_data as $type => $data ) {

			if ( ! is_array( $data ) ) {
				continue;
			}

			$first = reset( $data );

			if ( ! isset( $first['_class'] ) ) {
				continue;
			}

			foreach ( $data as $id => $item_data ) {

				if ( ! empty( $item_data['_parent'] ) ) {
					continue;
				}

				$item = $this->construct_item( $id, $item_data );

				if ( $item ) {
					$items[] = $item;
				}
			}
		}

		return new ITE_Line_Item_Collection( $items, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function save( ITE_Line_Item $item ) {

		$old = $this->get( $item->get_type(), $item->get_id() );

		$type = self::normalize_type( $item->get_type() );
		$this->session->add_session_data( $type, array( $item->get_id() => $this->get_data( $item ) ) );

		$this->events->on_save( $item, $old, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function save_many( array $items ) {

		$data = array();
		$olds = array();

		foreach ( $items as $item ) {
			$data[ $item->get_type() ][ $item->get_id() ] = $this->get_data( $item );
			$olds[ $item->get_type() ][ $item->get_id() ] = $this->get( $item->get_type(), $item->get_id() );
		}

		foreach ( $data as $type => $item_data ) {
			$this->session->add_session_data( self::normalize_type( $type ), $item_data );
		}

		foreach ( $items as $item ) {
			$this->events->on_save( $item, $olds[ $item->get_type() ][ $item->get_id() ], $this );
		}

		return true;
	}
	

	/**
	 * @inheritDoc
	 */
	public function delete( ITE_Line_Item $item ) {

		if ( $item instanceof ITE_Aggregatable_Line_Item && $item->get_aggregate() ) {
			$item->get_aggregate()->remove_item( $item->get_type(), $item->get_id() );
			$this->save( $item->get_aggregate() );
		}

		if ( $item instanceof ITE_Aggregate_Line_Item ) {
			foreach ( $item->get_line_items() as $aggregatable ) {
				$this->delete( $aggregatable );
			}
		}

		$type = self::normalize_type( $item->get_type() );

		$items = $this->session->get_session_data( $type );
		unset( $items[ $item->get_id() ] );
		$this->session->update_session_data( $type, $items );

		$this->events->on_delete( $item, $this );

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_shipping_address() {
		$shipping = it_exchange_get_cart_shipping_address();

		if ( ! is_array( $shipping ) ) {
			return array();
		}

		return $shipping;
	}

	/**
	 * @inheritDoc
	 */
	public function get_billing_address() {
		$billing = it_exchange_get_cart_billing_address();

		if ( ! is_array( $billing ) ) {
			return array();
		}

		return $billing;
	}

	/**
	 * Get the data that will be committed.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item $item
	 *
	 * @return array
	 */
	protected final function get_data( ITE_Line_Item $item ) {

		$additional = array(
			'_class' => get_class( $item )
		);

		if ( $item instanceof ITE_Aggregatable_Line_Item && $item->get_aggregate() ) {
			$additional['_parent'] = array(
				'type' => $item->get_aggregate()->get_type(),
				'id'   => $item->get_aggregate()->get_id(),
			);
		}

		if ( $item instanceof ITE_Aggregate_Line_Item ) {
			foreach ( $item->get_line_items() as $aggregatable ) {
				$this->save( $aggregatable );
				$additional['_aggregate'][] = array(
					'type' => $aggregatable->get_type(),
					'id'   => $aggregatable->get_id(),
				);
			}
		}

		$data = $item instanceof ITE_Cart_Product ? $item->bc() : array( '_params' => $item->get_params() );

		return array_merge( $additional, $data );
	}

	/**
	 * Construct an item.
	 *
	 * @since 1.36
	 *
	 * @param string|int               $id
	 * @param array                    $data
	 * @param \ITE_Aggregate_Line_Item $aggregate Provide the aggregate instance to prevent an infinite loop
	 *                                            where the aggregate constructs its aggregatables, and the
	 *                                            aggregatables construct the aggregate.
	 *
	 * @return \ITE_Line_Item|null
	 */
	protected final function construct_item( $id, array $data, ITE_Aggregate_Line_Item $aggregate = null ) {

		if ( ! isset( $data['_class'] ) ) {
			return null;
		}

		$class = $data['_class'];
		$_data = $data;
		unset( $data['_class'], $data['_parent'], $data['_aggregate'] );

		if ( ! class_exists( $class ) ) {
			return null;
		}

		if ( $class === 'ITE_Cart_Product' ) {
			$data = $this->back_compat_cart_product( $data );
		}

		$params = isset( $data['_params'] ) && is_array( $data['_params'] ) ? $data['_params'] : array();
		$item   = new $class( $id, new ITE_Array_Parameter_Bag( $params ), new ITE_Array_Parameter_Bag() );

		if ( ! $item ) {
			return null;
		}

		$this->set_additional_properties( $item, $_data, $aggregate );

		return $this->events->on_get( $item, $this );
	}

	/**
	 * Back-compat for cart products.
	 *
	 * @since 1.36.0
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function back_compat_cart_product( $data ) {

		if ( isset( $data['itemized_data'] ) && is_serialized( $data['itemized_data'] ) ) {
			$data['itemized_data'] = unserialize( $data['itemized_data'] );
		}

		if ( isset( $data['additional_data'] ) && is_serialized( $data['additional_data'] ) ) {
			$data['additional_data'] = unserialize( $data['additional_data'] );
		}

		return array( '_params' => $data );
	}

	/**
	 * Set the additional properties on the newly constructed item.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item           $item
	 * @param array                    $data
	 * @param \ITE_Aggregate_Line_Item $aggregate Provide the aggregate instance to prevent an infinite loop
	 *                                            where the aggregate constructs its aggregatables, and the
	 *                                            aggregatables construct the aggregate.
	 */
	protected final function set_additional_properties( ITE_Line_Item $item, array $data, ITE_Aggregate_Line_Item $aggregate = null ) {
		$this->set_repository( $item );
		$this->set_aggregate( $item, $data, $aggregate );
		$this->set_aggregatables( $item, $data );
	}

	/**
	 * Set the aggregate on a line item if necessary.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item                $item
	 * @param array                         $data
	 * @param \ITE_Aggregate_Line_Item|null $aggregate
	 */
	protected final function set_aggregate( ITE_Line_Item $item, array $data, ITE_Aggregate_Line_Item $aggregate = null ) {

		if ( $item instanceof ITE_Aggregatable_Line_Item && ! empty( $data['_parent'] ) ) {

			if ( ! $aggregate && ! empty( $data['_parent']['type'] ) && ! empty( $data['_parent']['id'] ) ) {
				$aggregate = $this->get( $data['_parent']['type'], $data['_parent']['type'] );
			}

			if ( $aggregate instanceof ITE_Aggregate_Line_Item ) {
				$item->set_aggregate( $aggregate );
			}
		}
	}

	/**
	 * Set the aggregatable line items on the given line item if necessary.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item $item
	 * @param array          $data
	 */
	protected final function set_aggregatables( ITE_Line_Item $item, array $data ) {

		if ( $item instanceof ITE_Aggregate_Line_Item && ! empty( $data['_aggregate'] ) ) {
			foreach ( $data['_aggregate'] as $aggregatable_data ) {

				$all_of_type = $this->session->get_session_data( self::normalize_type( $aggregatable_data['type'] ) );

				if ( ! $all_of_type || empty( $aggregatable_data['id'] ) ) {
					continue;
				}

				$id = $aggregatable_data['id'];

				if ( isset( $all_of_type[ $id ] ) ) {
					$aggregatable = $this->construct_item( $id, $all_of_type[ $id ], $item );

					if ( $aggregatable instanceof ITE_Aggregatable_Line_Item ) {
						$item->add_item( $aggregatable );
					}
				}
			}
		}
	}

	/**
	 * Normalize the type.
	 *
	 * @since 1.36
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	protected static function normalize_type( $type ) {
		switch ( $type ) {
			case 'product':
				$type = 'products'; // back-compat
				break;
		}

		return $type;
	}
}