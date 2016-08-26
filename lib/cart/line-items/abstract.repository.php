<?php
/**
 * Abstract Line Item Repository.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Line_Item_Repository
 */
abstract class ITE_Line_Item_Repository {

	/**
	 * Get an item from the repository.
	 *
	 * @since 1.36
	 *
	 * @param string $type
	 * @param string $id
	 *
	 * @return ITE_Line_Item|null
	 */
	abstract public function get( $type, $id );

	/**
	 * Get all line items.
	 *
	 * @since 1.36
	 *
	 * @param string $type Optionally specify the type of line items to retrieve.
	 *
	 * @return ITE_Line_Item_Collection|ITE_Line_Item[]
	 */
	abstract public function all( $type = '' );

	/**
	 * Save an item.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item $item
	 *
	 * @return bool
	 */
	abstract public function save( ITE_Line_Item $item );

	/**
	 * Save multiple items.
	 *
	 * @since 1.36
	 *
	 * @param ITE_Line_Item[] $items
	 *
	 * @return bool
	 */
	abstract public function save_many( array $items );

	/**
	 * Delete a line item.
	 *
	 * If an aggregatable line item is passed, the repository should remove the line item from the aggregate,
	 * if one exists.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item $item
	 *
	 * @return bool
	 */
	abstract public function delete( ITE_Line_Item $item );

	/**
	 * Get all meta stored on the cart.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	abstract public function get_all_meta();

	/**
	 * Determine if the cart has a given meta key.
	 *
	 * @since 1.36.0
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	abstract public function has_meta( $key );

	/**
	 * Retrieve metadata from the cart.
	 *
	 * @since 1.36.0
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	abstract public function get_meta( $key );

	/**
	 * Set a meta value for the cart.
	 *
	 * @since 1.36.0
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	abstract public function set_meta( $key, $value );

	/**
	 * Remove metadata from the cart.
	 *
	 * @since 1.36.0
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	abstract public function remove_meta( $key );

	/**
	 * Get the customer's shipping address.
	 * 
	 * @since 1.36
	 * 
	 * @return \ITE_Location|null
	 */
	abstract public function get_shipping_address();

	/**
	 * Get the customer's billing address.
	 * 
	 * @since 1.36
	 * 
	 * @return \ITE_Location|null
	 */
	abstract public function get_billing_address();

	/**
	 * Save the billing address for this purchase.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Location|null $location
	 *
	 * @return bool
	 */
	abstract public function set_billing_address( ITE_Location $location = null );

	/**
	 * Save the shipping address for this purchase.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Location|null $location
	 *
	 * @return bool
	 */
	abstract public function set_shipping_address( ITE_Location $location = null );

	/**
	 * Set the repository for a line item if necessary.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item $item
	 */
	protected final function set_repository( ITE_Line_Item $item ) {
		if ( $item instanceof ITE_Line_Item_Repository_Aware ) {
			$item->set_line_item_repository( $this );
		}
	}
}
