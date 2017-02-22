<?php
/**
 * Line Items Collection.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Line_Item_Collection
 */
class ITE_Line_Item_Collection implements Countable, ArrayAccess, IteratorAggregate {

	/**
	 * @var ITE_Line_Item[]
	 */
	private $items = array();

	/**
	 * @var ITE_Line_Item_Repository
	 */
	private $repository;

	/**
	 * ITE_Line_Item_Collection constructor.
	 *
	 * @param \ITE_Line_Item[]          $items
	 * @param \ITE_Line_Item_Repository $repository
	 */
	public function __construct( array $items, \ITE_Line_Item_Repository $repository ) {
		$this->items      = $items;
		$this->repository = $repository;

		foreach ( $this->items as $item ) {
			if ( $item instanceof ITE_Line_Item_Repository_Aware ) {
				$item->set_line_item_repository( $repository );
			}
		}
	}

	/**
	 * Clone this collection without items of a given type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function without( $type ) {

		$types   = array_filter( func_get_args() );
		$without = array();

		foreach ( $this->items as $item ) {
			if ( ! in_array( $item->get_type(), $types ) ) {
				$without[] = $item;
			}
		}

		return new self( $without, $this->repository );
	}

	/**
	 * Clone this collection with only items of a given type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function with_only( $type ) {

		$types     = func_get_args();
		$with_only = array();

		foreach ( $this->items as $item ) {
			if ( in_array( $item->get_type(), $types ) ) {
				$with_only[] = $item;
			}
		}

		return new self( $with_only, $this->repository );
	}

	/**
	 * Clone this collection with only items that are an instance of a given class or interface.
	 *
	 * @since 2.0.0
	 *
	 * @param string $class
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function with_only_instances_of( $class ) {

		$only = array();

		foreach ( $this->items as $item ) {
			if ( $item instanceof $class ) {
				$only[] = $item;
			}
		}

		return new self( $only, $this->repository );
	}

	/**
	 * Return a collection with only summary line items.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function summary_only() {

		$items = array();

		foreach ( $this->items as $item ) {
			if ( $item->is_summary_only() ) {
				$items[] = $item;
			}
		}

		return new self( $items, $this->repository );
	}

	/**
	 * Return a collection with non summary only line items.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function non_summary_only() {

		$items = array();

		foreach ( $this->items as $item ) {
			if ( ! $item->is_summary_only() ) {
				$items[] = $item;
			}
		}

		return new self( $items, $this->repository );
	}

	/**
	 * Return a new collection with only the taxable line items.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function taxable() {
		$taxable = array();

		foreach ( $this->items as $item ) {
			if ( $item instanceof ITE_Taxable_Line_Item ) {
				$taxable[] = $item;
			}
		}

		return new self( $taxable, $this->repository );
	}

	/**
	 * Return a new collection with only the discountable line items.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function discountable() {

		$discountable = array();

		foreach ( $this->items as $item ) {
			if ( $item instanceof ITE_Discountable_Line_Item ) {
				$discountable[] = $item;
			}
		}

		return new self( $discountable, $this->repository );
	}

	/**
	 * Get all items having a given parameter.
	 *
	 * @since 2.0.0
	 *
	 * @param array|string $param,... Param to check for. If multiple, only one must match.
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function having_param( $param ) {

		if ( is_string( $param ) ) {
			$params = func_get_args();
		} else {
			$params = $param;
		}

		return $this->filter( function ( ITE_Line_Item $item ) use ( $params ) {

			foreach ( $params as $param ) {
				if ( $item->has_param( $param ) ) {
					return true;
				}
			}

			return false;
		} );
	}

	/**
	 * Get all items not having a given parameter.
	 *
	 * @since 2.0.0
	 *
	 * @param array|string $param,... Param to check for. If multiple, only one must not match.
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function not_having_param( $param ) {

		if ( is_string( $param ) ) {
			$params = func_get_args();
		} else {
			$params = $param;
		}

		return $this->filter( function ( ITE_Line_Item $item ) use ( $params ) {

			foreach ( $params as $param ) {
				if ( ! $item->has_param( $param ) ) {
					return true;
				}
			}

			return false;
		} );
	}

	/**
	 * Clone this collection with only items that pass the given callback.
	 *
	 * @since 2.0.0
	 *
	 * @param callable $callback
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function filter( $callback ) {
		return new self( array_filter( $this->items, $callback ), $this->repository );
	}

	/**
	 * Return a unique collection of items.
	 *
	 * If no callback is given, uniques will be detected by the ID and type.
	 *
	 * @since 2.0.0
	 *
	 * @param callable $callback
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function unique( $callback = null ) {

		$items = array();

		foreach ( $this->items as $item ) {

			if ( $callback ) {
				$key = $callback( $item );
			} else {
				$key = $item->get_type() . $item->get_id();
			}

			$items[ $key ] = $item;
		}

		return new self( array_values( $items ), $this->repository );
	}

	/**
	 * Segment this collection into multiple collections based on a callaback function.
	 *
	 * @since 2.0.0
	 *
	 * @param callable $callback Receives a Line Item, should return a string to identify the bucket.
	 *                           If none given, will segment by type.
	 *
	 * @return ITE_Line_Item_Collection[]
	 */
	public function segment( $callback = null ) {

		$segmented = array();

		foreach ( $this->items as $item ) {
			if ( $callback ) {
				$key = $callback( $item );
			} else {
				$key = $item->get_type();
			}

			if ( isset( $segmented[ $key ] ) ) {
				$segmented[ $key ]->add( $item );
			} else {
				$segmented[ $key ] = new self( array( $item ), $this->repository );
			}
		}

		return $segmented;
	}

	/**
	 * Flatten the collection.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function flatten() {

		$items = array();

		foreach ( $this->items as $item ) {
			if ( $item instanceof ITE_Aggregate_Line_Item ) {
				$items = array_merge( $items, $this->unravel( $item ) );
			}

			$items[] = $item;
		}

		return new self( $items, $this->repository );
	}

	/**
	 * Set the cart on all cart aware line items.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return \ITE_Line_Item_Collection
	 */
	public function set_cart( \ITE_Cart $cart ) {
		$items = $this->flatten();

		foreach ( $items as $item ) {
			if ( $item instanceof ITE_Cart_Aware ) {
				$item->set_cart( $cart );
			}
		}

		return $this;
	}

	/**
	 * Unravel an aggregate line item.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Aggregate_Line_Item $item
	 *
	 * @return \ITE_Line_Item[]
	 */
	protected final function unravel( ITE_Aggregate_Line_Item $item ) {
		$nested = array();

		foreach ( $item->get_line_items() as $child ) {
			if ( $child instanceof ITE_Aggregate_Line_Item ) {
				$nested = array_merge( $nested, $this->unravel( $child ) );
			}

			$nested[] = $child;
		}

		return $nested;
	}

	/**
	 * Calculate the total of all items in this collection.
	 *
	 * @since 2.0.0
	 *
	 * @return float
	 */
	public function total() {
		$total = 0.00;

		foreach ( $this->items as $item ) {
			$total += $item->get_total();
		}

		return $total;
	}

	/**
	 * Freeze all of the items in this collection.
	 *
	 * @since 2.0.0
	 */
	public function freeze() {
		foreach ( $this->items as $item ) {
			$item->freeze();
		}

		$this->save();
	}

	/**
	 * Save all of the line items in the collection.
	 *
	 * @since 2.0.0
	 */
	public function save() {
		$this->repository->save_many( $this->items );
	}

	/**
	 * Remove all of the items in this collection.
	 *
	 * @since 2.0.0
	 */
	public function delete() {
		foreach ( $this->items as $item ) {

			if ( $item instanceof ITE_Aggregatable_Line_Item && $item->get_aggregate() ) {
				$item->get_aggregate()->remove_item( $item->get_type(), $item->get_id() );
			}

			$this->repository->delete( $item );
		}
	}

	/**
	 * Get a single line item from the collection.
	 *
	 * @since 2.0.0
	 *
	 * @param string     $type
	 * @param string|int $id
	 *
	 * @return \ITE_Line_Item|null
	 */
	public function get( $type, $id ) {
		foreach ( $this->items as $item ) {
			if ( $item->get_type() === $type && $item->get_id() == $id ) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Get the first item of this collection.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item
	 */
	public function first() {
		return reset( $this->items );
	}

	/**
	 * Get the last item of this collection.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item
	 */
	public function last() {
		return end( $this->items );
	}

	/**
	 * Add a line item to the collection.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Line_Item $item
	 *
	 * @return $this
	 */
	public function add( ITE_Line_Item $item ) {
		$this->items[] = $item;

		return $this;
	}

	/**
	 * Remove a line item from the collection.
	 *
	 * @since 2.0.0
	 *
	 * @param string     $type
	 * @param string|int $id
	 *
	 * @return \ITE_Line_Item|null
	 */
	public function remove( $type, $id ) {
		foreach ( $this->items as $i => $item ) {
			if ( $item->get_type() === $type && $item->get_id() == $id ) {
				unset( $this->items[ $i ] );

				return $item;
			}
		}

		return null;
	}

	/**
	 * Merge a collection with this one.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Line_Item_Collection $collection
	 *
	 * @return ITE_Line_Item_Collection Returns a fresh collection object.
	 */
	public function merge( ITE_Line_Item_Collection $collection ) {
		return new ITE_Line_Item_Collection( array_merge( $this->items, $collection->items ), $this->repository );
	}

	/**
	 * Convert the collection to an array.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item[]
	 */
	public function to_array() {
		return $this->items;
	}

	/**
	 * @return ArrayIterator|ITE_Line_Item[]
	 */
	public function getIterator() {
		return new ArrayIterator( $this->items );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $offset ) {
		return isset( $this->items[ $offset ] ) || array_key_exists( $offset, $this->items );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $offset ) {
		return isset( $this->items[ $offset ] ) ? $this->items[ $offset ] : null;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $offset, $value ) {
		$this->items[ $offset ] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset( $offset ) {
		unset( $this->items[ $offset ] );
	}

	/**
	 * @inheritDoc
	 */
	public function count() {
		return count( $this->items );
	}
}
