<?php
/**
 * Object Type interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Object_Type
 */
interface ITE_Object_Type {

	/**
	 * Get the slug of this object type.
	 *
	 * MUST be globally unique. Ex: 'transaction' or 'customer'.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get the label for this object type.
	 *
	 * Ex: Transaction or Customer.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Create an object from a set of attributes.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes
	 *
	 * @return ITE_Object
	 */
	public function create_object( array $attributes );

	/**
	 * Retrieve an object by its ID.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id
	 *
	 * @return ITE_Object|null Object or null if not found.
	 */
	public function get_object_by_id( $id );

	/**
	 * Retrieve objects matching the given criteria.
	 *
	 * @since 2.0.0
	 *
	 * @param array|\Doctrine\Common\Collections\Criteria $criteria
	 *
	 * @return ITE_Object[]
	 */
	public function get_objects( \Doctrine\Common\Collections\Criteria $criteria = null );

	/**
	 * Delete an object by its ID.
	 *
	 * @since 2.0.0
	 *
	 * @param string $id
	 *
	 * @return bool True if deleted or already deleted, false if not able to delete.
	 */
	public function delete_object_by_id( $id );

	/**
	 * Does this object type support meta.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function supports_meta();

	/**
	 * Is this object type RESTful.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_restful();
}
