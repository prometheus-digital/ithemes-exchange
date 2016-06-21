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
	private $session;

	/**
	 * ITE_Line_Item_Session_Repository constructor.
	 *
	 * @param \IT_Exchange_SessionInterface $session
	 */
	public function __construct( \IT_Exchange_SessionInterface $session ) { $this->session = $session; }

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

		/**
		 * Fires when a line item is saved.
		 *
		 * @since 1.36
		 *
		 * @param \ITE_Line_Item            $item
		 * @param \ITE_Line_Item|null       $old
		 * @param \ITE_Line_Item_Repository $repository
		 */
		do_action( 'it_exchange_save_line_item', $item, $old, $this );

		/**
		 * Fires when a line item is saved.
		 *
		 * The dynamic portion of this hook refers to the type of the line item being saved.
		 * Ex: 'product' or 'tax'.
		 *
		 * @since 1.36
		 *
		 * @param \ITE_Line_Item            $item
		 * @param \ITE_Line_Item|null       $old
		 * @param \ITE_Line_Item_Repository $repository
		 */
		do_action( "it_exchange_save_{$item->get_type()}_item", $item, $old, $this );
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

			$old = $olds[ $item->get_type() ][ $item->get_id() ];

			// This hook is documented in lib/cart/line-items/class.session-repository.php
			do_action( 'it_exchange_save_line_item', $item, $old, $this );

			// This hook is documented in lib/cart/line-items/class.session-repository.php
			do_action( "it_exchange_save_{$item->get_type()}_item", $item, $old, $this );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function delete( ITE_Line_Item $item ) {

		$type = self::normalize_type( $item->get_type() );

		$items = $this->session->get_session_data( $type );
		unset( $items[ $item->get_id() ] );
		$this->session->update_session_data( $type, $items );

		if ( $item instanceof ITE_Aggregate_Line_Item ) {
			foreach ( $item->get_line_items() as $aggregatable ) {
				$this->delete( $aggregatable );
			}
		}

		/**
		 * Fires when a line item is deleted.
		 *
		 * @since 1.36
		 *
		 * @param \ITE_Line_Item            $item
		 * @param \ITE_Line_Item_Repository $repository
		 */
		do_action( 'it_exchange_delete_line_item', $item, $this );

		/**
		 * Fires when a line item is deleted.
		 *
		 * The dynamic portion of this hook refers to the type of the line item being saved.
		 * Ex: 'product' or 'tax'.
		 *
		 * @since 1.36
		 *
		 * @param \ITE_Line_Item            $item
		 * @param \ITE_Line_Item_Repository $repository
		 */
		do_action( "it_exchange_delete_{$item->get_type()}_item", $item, $this );

		return true;
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

		if ( $item instanceof ITE_Aggregatable_Line_Item ) {
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

		return array_merge( $additional, $item->get_data_to_save( $this ) );
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


		$args = array( $id, $data, $this );
		$item = call_user_func_array( array( $class, 'from_data' ), $args );

		if ( ! $item ) {
			return null;
		}

		$this->set_additional_properties( $item, $_data, $aggregate );

		return $item;
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

		$parent        = isset( $data['_parent'] ) ? $data['_parent'] : null;
		$aggregatables = isset( $data['_aggregate'] ) ? $data['_aggregate'] : array();

		if ( $item instanceof ITE_Line_Item_Repository_Aware ) {
			$item->set_line_item_repository( $this );
		}

		if ( $parent && $item instanceof ITE_Aggregatable_Line_Item ) {

			if ( ! $aggregate ) {
				$aggregate = $this->get( $parent['type'], $parent['id'] );
			}

			if ( $aggregate instanceof ITE_Aggregate_Line_Item ) {
				$item->set_aggregate( $aggregate );
			}
		}

		if ( $aggregatables && $item instanceof ITE_Aggregate_Line_Item ) {
			foreach ( $aggregatables as $aggregatable ) {

				$all_of_type = $this->session->get_session_data( self::normalize_type( $aggregatable['type'] ) );

				if ( ! $all_of_type || ! isset( $aggregatable['id'] ) ) {
					continue;
				}

				$id = $aggregatable['id'];

				if ( isset( $all_of_type[ $id ] ) ) {
					$aggregate = $this->construct_item( $id, $all_of_type[ $id ], $item );
				}

				if ( $aggregate instanceof ITE_Aggregatable_Line_Item ) {
					$item->add_item( $aggregate );
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