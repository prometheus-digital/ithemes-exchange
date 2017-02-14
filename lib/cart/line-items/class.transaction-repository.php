<?php
/**
 * Transaction Line Item Repository.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Line_Item_Transaction_Repository
 */
class ITE_Line_Item_Transaction_Repository extends ITE_Line_Item_Repository {

	/** @var \ITE_Line_Item_Repository_Events */
	protected $events;

	/** @var IT_Exchange_Transaction */
	protected $transaction;

	/** @var ITE_Parameter_Bag */
	protected $bag;

	/**
	 * ITE_Line_Item_Transaction_Repository constructor.
	 *
	 * @param \ITE_Line_Item_Repository_Events $events
	 * @param \IT_Exchange_Transaction         $transaction
	 */
	public function __construct( ITE_Line_Item_Repository_Events $events, IT_Exchange_Transaction $transaction ) {
		$this->events      = $events;
		$this->transaction = $transaction;
		$this->bag         = new ITE_Meta_Parameter_Bag( $transaction->get_ID(), 'post', '_it_exchange_cart_' );
	}

	/**
	 * Get the transaction line items are being retrieved from.
	 *
	 * @since 2.0.0
	 *
	 * @return \IT_Exchange_Transaction
	 */
	public function get_transaction() {
		return $this->transaction;
	}

	/**
	 * Set the transaction to retrieve line items from.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Transaction $transaction
	 *
	 * @return $this
	 */
	public function for_transaction( $transaction ) {
		return new self( $this->events, $transaction );
	}

	/**
	 * @inheritDoc
	 */
	public function get( $type, $id ) {

		$model = $this->find_model_for_item( $id, $type );

		if ( ! $model ) {
			return null;
		}

		return $this->model_to_item( $model );
	}

	/**
	 * @inheritDoc
	 */
	public function all( $type = '' ) {

		$models = ITE_Transaction_Line_Item_Model::query()
		                                         ->where( '_parent', true, 0 )
		                                         ->and_where( 'transaction', true, $this->get_transaction()->ID );

		if ( $type ) {
			$models->and_where( 'type', true, $type );
		}

		$models = $models->results();
		$items  = array();

		foreach ( $models as $model ) {
			if ( $item = $this->model_to_item( $model ) ) {
				$items[] = $item;
			}
		}

		return new ITE_Line_Item_Collection( $items, $this );
	}

	/**
	 * Save an item.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Line_Item $item
	 * @param bool           $save_parent
	 * @param array          $search
	 *
	 * @return bool
	 *
	 * @throws \IronBound\DB\Exception
	 * @throws \UnexpectedValueException
	 */
	public function save( ITE_Line_Item $item, $save_parent = true, array $search = array() ) {

		if ( $save_parent && ( $item instanceof ITE_Aggregatable_Line_Item && $item->get_aggregate() ) ) {
			$this->save( $item->get_aggregate(), false, $search );
		}

		$old = $this->get( $item->get_type(), $item->get_id() );

		if ( $search && array_key_exists( $item->get_type(), $search ) && array_key_exists( $item->get_id(), $search[ $item->get_type() ] ) ) {
			$model = $search[ $item->get_type() ][ $item->get_id() ];
		} else {
			$model = $this->find_model_for_item( $item );
		}

		if ( $model ) {
			foreach ( $this->build_attributes_for_item( $item ) as $attribute => $value ) {
				$model->set_attribute( $attribute, $value );
			}

			$model->save();
		} else {
			$model = ITE_Transaction_Line_Item_Model::create( $this->build_attributes_for_item( $item, true, empty( $search ) ? true : $search ) );

			if ( ! $model ) {
				throw new UnexpectedValueException( "Model failed to save for {$item->get_type()} {$item->get_id()}" );
			}
		}

		foreach ( $item->get_params() as $param => $value ) {
			$model->update_meta( $param, $value );
		}

		if ( $item instanceof ITE_Aggregate_Line_Item ) {
			$this->save_many( $item->get_line_items()->to_array(), false, $search );
		}

		$this->events->on_save( $item, $old, $this );

		return true;
	}

	/**
	 * Build the model attributes for a line item.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Line_Item                          $item
	 * @param bool                                   $include_static_data
	 * @param bool|ITE_Transaction_Line_Item_Model[] $search_for_parent
	 *
	 * @return array
	 */
	protected function build_attributes_for_item( ITE_Line_Item $item, $include_static_data = false, $search_for_parent = true ) {
		$attributes = array(
			'name'         => $item->get_name(),
			'description'  => $item->get_description(),
			'amount'       => $item->get_amount(),
			'quantity'     => $item->get_quantity(),
			'total'        => $item->frozen()->has_param( 'total' ) ? $item->frozen()->get_param( 'total' ) : $item->get_total(),
			'summary_only' => $item->is_summary_only(),
			'object_id'    => $item->get_object_id(),
		);

		if ( $include_static_data ) {

			if ( $item instanceof ITE_Aggregatable_Line_Item && $item->get_aggregate() && $search_for_parent ) {

				$parent = null;

				if ( $search_for_parent === true ) {
					$parent = $this->find_model_for_item( $item->get_aggregate() );
				} elseif ( is_array( $search_for_parent ) ) {
					$comp = $item->get_aggregate()->get_type() . $item->get_aggregate()->get_id();

					foreach ( $search_for_parent as $maybe_parent ) {
						if ( $comp === ( $maybe_parent->type . $maybe_parent->id ) ) {
							$parent = $maybe_parent;
							break;
						}
					}
				}

				if ( $parent ) {
					$attributes['_parent'] = $parent->get_pk();
				}
			}

			$attributes['id']          = $item->get_id();
			$attributes['type']        = $item->get_type();
			$attributes['_class']      = get_class( $item );
			$attributes['transaction'] = $this->get_transaction()->ID;
		}

		return $attributes;
	}

	/**
	 * @inheritDoc
	 */
	public function save_many( array $items, $recurse = true, array $search = array() ) {

		$map = array();

		foreach ( $items as $item ) {

			if ( $search && array_key_exists( $item->get_type(), $search ) && array_key_exists( $item->get_id(), $search[ $item->get_type() ] ) ) {
				continue;
			}

			if ( ! isset( $map[ $item->get_type() ] ) ) {
				$map[ $item->get_type() ] = array();
			}

			$map[ $item->get_type() ][] = $item->get_id();
		}

		$models = $this->find_model_for_items( $map, $all_empty );

		if ( $search ) {
			$models = array_merge_recursive( $models, $search );
		}

		if ( $all_empty ) {
			return $this->create_many( $items );
		}

		foreach ( $items as $item ) {
			$this->save( $item, $recurse, $models );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function delete( ITE_Line_Item $item ) {

		$model = $this->find_model_for_item( $item );

		if ( ! $model ) {
			return true;
		}

		$result = $model->delete();

		if ( $result ) {
			if ( $item instanceof ITE_Aggregate_Line_Item ) {
				foreach ( $item->get_line_items() as $aggregatable ) {
					$this->delete( $aggregatable );
				}
			}

			$this->events->on_delete( $item, $this );
		}

		return $result;
	}

	/**
	 * Create multiple items.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Line_Item[] $items
	 */
	protected function create_many( array $items ) {

		// Goal is to minimize writes. So we create records from the most parent down

		$all = $items;

		/** @var ITE_Transaction_Line_Item_Model[] $done */
		$done = array();

		while ( count( $items ) ) {

			$to_insert = array();

			foreach ( $items as $i => $item ) {
				if ( ! $item instanceof ITE_Aggregatable_Line_Item ) {
					// If this can't have a parent, we can insert at any time
					$to_insert[ $i ] = $item;
				} elseif ( ! $item->get_aggregate() ) {
					// If this doesn't have a parent, we can insert at any time
					$to_insert[ $i ] = $item;
				} else {
					// Or if the parent has already been inserted we can add it.
					$key = $item->get_aggregate()->get_type() . $item->get_aggregate()->get_id();

					if ( isset( $done[ $key ] ) ) {
						$to_insert[ $i ] = $item;
					}
				}
			}

			$rows = array();

			foreach ( $to_insert as $insert ) {
				$rows[] = $this->build_attributes_for_item( $insert, true, $done );
			}

			$models = ITE_Transaction_Line_Item_Model::create_many( $rows );

			foreach ( $models as $model ) {
				$done[ $model->type . $model->id ] = $model;
			}

			$items = array_diff_key( $items, $to_insert );
		}

		foreach ( $all as $item ) {
			$model = $done[ $item->get_type() . $item->get_id() ];

			foreach ( $item->get_params() as $key => $value ) {
				$model->update_meta( $key, $value );
			}
		}

	}

	/**
	 * Find the models for a set of items.
	 *
	 * @since 2.0.0
	 *
	 * @param array $items Expects an array of item IDs keyed under their item types.
	 *                     Ex. [ 'product' => [ id, id, id ], 'tax' => [id, id, id] ]
	 * @param bool  $all_empty
	 *
	 * @return array An array of models keyed by their ID, keyed by the type.
	 *               Ex. [ 'product' => [ id => model, id => model ], 'tax' => [ id => model ] ]
	 */
	protected final function find_model_for_items( array $items, &$all_empty ) {

		$query = ITE_Transaction_Line_Item_Model::query();
		$query->and_where( 'transaction', '=', $this->get_transaction()->ID );

		// WHERE t1.transaction = 50 AND ( ( t1.type = 'product' AND t1.pk IN ( id, id ) ) OR ( t1.type = 'tax' AND t1.pk IN ( id, id ) ) )

		$query->add_nested_where( function ( \IronBound\DB\Query\FluentQuery $query ) use ( $items ) {
			foreach ( $items as $type => $ids ) {
				$query->or_where( 'type', '=', $type, function ( \IronBound\DB\Query\FluentQuery $query ) use ( $ids ) {
					$query->and_where( 'pk', '=', $ids );
				} );
			}
		} );

		$models    = $query->results();
		$all_empty = $models->count() === 0;
		$return    = array();

		/** @var ITE_Transaction_Line_Item_Model $model */
		foreach ( $models as $model ) {

			if ( ! isset( $return[ $model->type ] ) ) {
				$return[ $model->type ] = array();
			}

			$return[ $model->type ][ $model->id ] = $model;
		}

		foreach ( $items as $type => $ids ) {
			if ( ! isset( $return[ $type ] ) ) {
				$return[ $type ] = array_fill_keys( $ids, null );
			} else {
				$defaults        = array_fill_keys( $ids, null );
				$return[ $type ] = array_merge( $defaults, $return[ $type ] );
			}
		}

		return $return;
	}

	/**
	 * Find the model for a given item.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Line_Item|string $item_or_id
	 * @param string               $type
	 *
	 * @return ITE_Transaction_Line_Item_Model
	 */
	protected final function find_model_for_item( $item_or_id, $type = '' ) {

		if ( $item_or_id instanceof ITE_Line_Item ) {
			$id   = $item_or_id->get_id();
			$type = $item_or_id->get_type();
		} else {
			$id = $item_or_id;
		}

		return ITE_Transaction_Line_Item_Model::query()
		                                      ->where( 'type', true, $type )
		                                      ->and_where( 'id', true, $id )
		                                      ->and_where( 'transaction', true, $this->get_transaction()->ID )
		                                      ->first();
	}

	/**
	 * Convert a model to its corresponding item object.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Transaction_Line_Item_Model $model
	 * @param \ITE_Aggregate_Line_Item|null    $aggregate
	 *
	 * @return \ITE_Line_Item|null
	 */
	protected final function model_to_item( ITE_Transaction_Line_Item_Model $model, ITE_Aggregate_Line_Item $aggregate = null ) {

		$meta   = $model->get_meta();
		$params = array();

		foreach ( $meta as $key => $values ) {
			$params[ $key ] = $values[0];
		}

		$bag    = new ITE_Array_Parameter_Bag( $params );
		$frozen = new ITE_Array_Parameter_Bag( array(
			'name'         => $model->name,
			'description'  => $model->description,
			'amount'       => $model->amount,
			'quantity'     => $model->quantity,
			'total'        => $model->total,
			'summary_only' => $model->summary_only,
		) );

		$class = $model->_class;

		if ( ! class_exists( $class ) ) {
			return null;
		}

		/** @var ITE_Line_Item $item */
		$item = new $class( $model->id, $bag, $frozen );

		$this->set_additional_properties( $item, $model, $aggregate );

		return $this->events->on_get( $item, $this );
	}

	/**
	 * Set the additional properties on the newly constructed item.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Line_Item                   $item
	 * @param \ITE_Transaction_Line_Item_Model $model
	 * @param \ITE_Aggregate_Line_Item|null    $aggregate
	 */
	protected final function set_additional_properties(
		ITE_Line_Item $item,
		ITE_Transaction_Line_Item_Model $model,
		ITE_Aggregate_Line_Item $aggregate = null
	) {
		$this->set_repository( $item );
		$this->set_aggregate( $item, $model, $aggregate );
		$this->set_aggregatables( $item, $model );
	}

	/**
	 * Set the aggregate for a line item.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Line_Item                   $item
	 * @param \ITE_Transaction_Line_Item_Model $model
	 * @param \ITE_Aggregate_Line_Item|null    $aggregate
	 */
	protected final function set_aggregate(
		ITE_Line_Item $item,
		ITE_Transaction_Line_Item_Model $model,
		ITE_Aggregate_Line_Item $aggregate = null
	) {

		if ( $item instanceof ITE_Aggregatable_Line_Item && $model->_parent ) {
			if ( ! $aggregate ) {
				$aggregate = $this->model_to_item( ITE_Transaction_Line_Item_Model::get( $model->_parent ) );
			}

			if ( $aggregate instanceof ITE_Aggregate_Line_Item ) {
				$item->set_aggregate( $aggregate );
			}
		}
	}

	/**
	 * Set the aggregatables on an item.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Line_Item                   $item
	 * @param \ITE_Transaction_Line_Item_Model $model
	 */
	protected final function set_aggregatables( ITE_Line_Item $item, ITE_Transaction_Line_Item_Model $model ) {

		if ( ! $item instanceof ITE_Aggregate_Line_Item ) {
			return;
		}

		$children = $model->get_children();

		foreach ( $children as $child ) {
			$aggregatable = $this->model_to_item( $child, $item );

			// sanity check
			if ( $aggregatable instanceof ITE_Aggregatable_Line_Item ) {
				$item->add_item( $aggregatable );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_all_meta() {
		return $this->bag->get_params();
	}

	/**
	 * @inheritDoc
	 */
	public function has_meta( $key ) {
		return $this->bag->has_param( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function get_meta( $ley ) {
		return $this->bag->get_param( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function set_meta( $key, $value ) {
		return $this->bag->set_param( $key, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_meta( $key ) {
		return $this->bag->remove_param( $key );
	}

	/**
	 * @inheritDoc
	 */
	public function get_shipping_address() {
		return $this->get_transaction()->get_shipping_address();
	}

	/**
	 * @inheritDoc
	 */
	public function get_billing_address() {
		return $this->get_transaction()->get_billing_address();
	}

	/**
	 * @inheritDoc
	 */
	public function set_billing_address( ITE_Location $location = null ) {

		if ( $location === null || ! $location->offsetGet( 'address1' ) ) {
			$this->get_transaction()->billing = 0;

			return $this->get_transaction()->save();
		} else {
			$saved = ITE_Saved_Address::convert_to_saved(
				$location, $this->get_billing_address(), $this->get_transaction()->get_customer(), 'billing', false
			);

			if ( ! $saved ) {
				return false;
			}

			// If this doesn't actually cause a change, we will just return true, so no need to check the PK changes
			$this->get_transaction()->billing = $saved->get_pk();

			return $this->get_transaction()->save();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function set_shipping_address( ITE_Location $location = null ) {

		if ( $location === null || ! $location->offsetGet( 'address1' ) ) {
			$this->get_transaction()->shipping = 0;

			return $this->get_transaction()->save();
		} else {
			$saved = ITE_Saved_Address::convert_to_saved(
				$location, $this->get_shipping_address(), $this->get_transaction()->get_customer(), 'shipping', false
			);

			if ( ! $saved ) {
				return false;
			}

			// If this doesn't actually cause a change, we will just return true, so no need to check the PK changes
			$this->get_transaction()->shipping = $saved->get_pk();

			return $this->get_transaction()->save();
		}
	}

	/**
	 * Convert a set of models to items.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Transaction_Line_Item_Model[] $models
	 *
	 * @return \ITE_Line_Item[]
	 */
	public static function convert_to_items( array $models ) {

		$by_transaction = array();

		foreach ( $models as $model ) {
			$by_transaction[ $model->transaction ][] = $model;
		}

		$items = array();

		foreach ( $by_transaction as $transaction_id => $models ) {
			$transaction = it_exchange_get_transaction( $transaction_id );
			$repo        = new self( new ITE_Line_Item_Repository_Events(), $transaction );

			foreach ( $models as $model ) {
				$items[] = $repo->model_to_item( $model );
			}
		}

		return $items;
	}

	/**
	 * Convert a set of models to items.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Transaction_Line_Item_Model[] $models
	 *
	 * @return \ITE_Line_Item[]
	 */
	public static function convert_to_items_segmented( array $models ) {

		$by_transaction = array();

		foreach ( $models as $model ) {
			$by_transaction[ $model->transaction ][] = $model;
		}

		$items = array();

		foreach ( $by_transaction as $transaction_id => $models ) {
			$transaction = it_exchange_get_transaction( $transaction_id );
			$repo        = new self( new ITE_Line_Item_Repository_Events(), $transaction );

			foreach ( $models as $model ) {
				$items[ $transaction_id ][] = $repo->model_to_item( $model );
			}
		}

		return $items;
	}
}
