<?php
/**
 * Line Items Collection.
 *
 * @since   1.36
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
	 * @since 1.36
	 *
	 * @param string $type
	 *
	 * @return self
	 */
	public function without( $type ) {

		$types   = func_get_args();
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
	 * @since 1.36
	 *
	 * @param string $type
	 *
	 * @return self
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
	 * @since 1.36
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
	 * Clone this collection with only items that pass the given callback.
	 *
	 * @since 1.36
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
	 * @since 1.36.0
	 *        
	 * @param callable $callback
	 * 
	 * @return \ITE_Line_Item_Collection
	 */
	public function unique( $callback = null ) {

		$items = array();

		foreach ( $this->items as $item ) {
			
			if ( $callback ) {
				$key = $callback($item);
			} else {
				$key = $item->get_type() . $item->get_id();
			}
			
			$items[ $key ] = $item;
		}

		return new self( array_values( $items ), $this->repository );
	}

	/**
	 * Calculate the total of all items in this collection.
	 *
	 * @since 1.36
	 *
	 * @return float
	 */
	public function total() {
		$total = 0.00;

		foreach ( $this->items as $item ) {
			$total += $item->get_amount() * $item->get_quantity();
		}

		return $total;
	}

	/**
	 * Save all of the line items in the collection.
	 *
	 * @since 1.36
	 */
	public function save() {
		foreach ( $this->items as $item ) {
			$item->persist( $this->repository );
		}
	}

	/**
	 * Remove all of the items in this collection.
	 *
	 * @since 1.36
	 */
	public function delete() {
		foreach ( $this->items as $item ) {
			$this->repository->delete( $item );
		}
	}

	/**
	 * Get a single line item from the collection.
	 *
	 * @since 1.36
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
	 * @since 1.36
	 *
	 * @return \ITE_Line_Item
	 */
	public function first() {
		return reset( $this->items );
	}

	/**
	 * Get the last item of this collection.
	 *
	 * @since 1.36
	 *
	 * @return \ITE_Line_Item
	 */
	public function last() {
		return end( $this->items );
	}

	/**
	 * Add a line item to the collection.
	 *
	 * @since 1.36
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
	 * @since 1.36
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
	 * Convert the collection to an array.
	 *
	 * @since 1.36
	 *
	 * @return \ITE_Line_Item[]
	 */
	public function to_array() {
		return $this->items;
	}

	/**
	 * @inheritDoc
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