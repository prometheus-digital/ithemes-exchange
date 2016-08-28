<?php
/**
 * Transaction Line Item Repository.
 *
 * @since   1.36.0
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
		$this->bag         = new ITE_Meta_Parameter_Bag( $transaction->ID, 'post', '_it_exchange_cart_' );
	}

	/**
	 * Get the transaction line items are being retrieved from.
	 *
	 * @since 1.36.0
	 *
	 * @return \IT_Exchange_Transaction
	 */
	public function get_transaction() {
		return $this->transaction;
	}

	/**
	 * Set the transaction to retrieve line items from.
	 *
	 * @since 1.36.0
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
	 * @inheritDoc
	 */
	public function save( ITE_Line_Item $item, $recurse = true ) {

		if ( $recurse && ( $item instanceof ITE_Aggregatable_Line_Item && $item->get_aggregate() ) ) {
			$this->save( $item->get_aggregate(), false );
		}

		$old   = $this->get( $item->get_type(), $item->get_id() );
		$model = $this->find_model_for_item( $item );

		$attributes = array(
			'name'         => $item->get_name(),
			'description'  => $item->get_description(),
			'amount'       => $item->get_amount(),
			'quantity'     => $item->get_quantity(),
			'total'        => $item->frozen()->has_param( 'total' ) ? $item->frozen()->get_param( 'total' ) : $item->get_total(),
			'summary_only' => $item->is_summary_only(),
			'object_id'    => $item->get_object_id(),
		);

		if ( $model ) {

			foreach ( $attributes as $attribute => $value ) {
				$model->set_attribute( $attribute, $value );
			}

			$model->save();

		} else {

			if ( $item instanceof ITE_Aggregatable_Line_Item && $item->get_aggregate() ) {
				$parent = $this->find_model_for_item( $item->get_aggregate() );

				if ( $parent ) {
					$attributes['_parent'] = $parent->get_pk();
				}
			}

			$attributes['id']          = $item->get_id();
			$attributes['type']        = $item->get_type();
			$attributes['_class']      = get_class( $item );
			$attributes['transaction'] = $this->get_transaction()->ID;

			$model = ITE_Transaction_Line_Item_Model::create( $attributes );

			if ( ! $model ) {
				throw new UnexpectedValueException( "Model failed to save for {$item->get_type()} {$item->get_id()}" );
			}
		}

		foreach ( $item->get_params() as $param => $value ) {
			$model->update_meta( $param, $value );
		}

		if ( $item instanceof ITE_Aggregate_Line_Item ) {
			$this->save_many( $item->get_line_items()->to_array(), false );
		}

		$this->events->on_save( $item, $old, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function save_many( array $items, $recurse = true ) {
		foreach ( $items as $item ) {
			$this->save( $item, $recurse );// this can be optimized
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
	 * Find the model for a given item.
	 *
	 * @since 1.36.0
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
	 * @since 1.36.0
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
	 * @since 1.36.0
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
	 * @since 1.36.0
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
	 * @since 1.36.0
	 *
	 * @param \ITE_Line_Item                   $item
	 * @param \ITE_Transaction_Line_Item_Model $model
	 */
	protected final function set_aggregatables( ITE_Line_Item $item, ITE_Transaction_Line_Item_Model $model ) {

		if ( ! $item instanceof ITE_Aggregate_Line_Item ) {
			return;
		}

		$children = ITE_Transaction_Line_Item_Model::query()->where( '_parent', true, $model->get_pk() )->results();

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

		if ( $location === null ) {
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

		if ( $location === null ) {
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
}
