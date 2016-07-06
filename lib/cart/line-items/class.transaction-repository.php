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

	/**
	 * ITE_Line_Item_Transaction_Repository constructor.
	 *
	 * @param \ITE_Line_Item_Repository_Events $events
	 * @param \IT_Exchange_Transaction         $transaction
	 */
	public function __construct( ITE_Line_Item_Repository_Events $events, IT_Exchange_Transaction $transaction ) {
		$this->events      = $events;
		$this->transaction = $transaction;
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

		$models = ITE_Transaction_Line_Item_Model::query()->where( 'transaction', $this->get_transaction()->ID );

		if ( $type ) {
			$models->and_where( 'type', $type );
		}

		$models = $models->results();
		$items  = array();

		foreach ( $models as $model ) {
			$items[] = $this->model_to_item( $model );
		}

		return $items;
	}

	/**
	 * @inheritDoc
	 */
	public function save( ITE_Line_Item $item ) {

		$old   = $this->get( $item->get_type(), $item->get_id() );
		$model = $this->find_model_for_item( $item );

		if ( ! $model ) {

			if ( $item instanceof ITE_Aggregatable_Line_Item && $item->get_aggregate() ) {
				$parent = $this->find_model_for_item( $item->get_aggregate() );
				$parent = $parent ? $parent->get_pk() : 0;
			} else {
				$parent = 0;
			}

			$model = ITE_Transaction_Line_Item_Model::create( array(
				'id'           => $item->get_id(),
				'type'         => $item->get_type(),
				'name'         => $item->get_name(),
				'description'  => $item->get_description(),
				'amount'       => $item->get_amount(),
				'quantity'     => $item->get_quantity(),
				'total'        => $item->get_amount() * $item->get_quantity(),
				'summary_only' => $item->is_summary_only(),
				'transaction'  => $this->get_transaction()->ID,
				'_class'       => get_class( $item ),
				'_parent'      => $parent
			) );
		}

		if ( ! $model ) {
			throw new UnexpectedValueException( "Model failed to save for {$item->get_type()} {$item->get_id()}" );
		}

		foreach ( $item->get_params() as $param => $value ) {
			$model->update_meta( $param, $value );
		}

		$this->events->on_save( $item, $old, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function save_many( array $items ) {
		foreach ( $items as $item ) {
			$this->save( $item );// this can be optimized
		}
	}

	/**
	 * @inheritDoc
	 */
	public function delete( ITE_Line_Item $item ) {

		if ( $item instanceof ITE_Aggregate_Line_Item ) {
			foreach ( $item->get_line_items() as $aggregatable ) {
				$this->delete( $aggregatable );
			}
		}

		$model = $this->find_model_for_item( $item );

		if ( ! $model ) {
			return true;
		}

		$result = $model->delete();

		if ( $result ) {
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
		                                      ->where( 'type', $type )
		                                      ->and_where( 'id', $id )
		                                      ->and_where( 'transaction', $this->get_transaction()->ID )
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
	 * @return \ITE_Line_Item
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

		$children = ITE_Transaction_Line_Item_Model::query()->where( '_parent', $model->get_pk() )->results();

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
	public function get_shipping_address() {
		$address = it_exchange_get_transaction_shipping_address( $this->get_transaction() );

		if ( ! is_array( $address ) ) {
			return array();
		}

		return $address;
	}

	/**
	 * @inheritDoc
	 */
	public function get_billing_address() {
		$address = it_exchange_get_transaction_billing_address( $this->get_transaction() );

		if ( ! is_array( $address ) ) {
			return array();
		}

		return $address;
	}
}