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
	 * @since    1.36
	 *
	 * @param ITE_Line_Item[] $items
	 *
	 * @return bool
	 */
	abstract public function save_many( array $items );

	/**
	 * Delete a line item.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item $item
	 *
	 * @return bool
	 */
	abstract public function delete( ITE_Line_Item $item );
}