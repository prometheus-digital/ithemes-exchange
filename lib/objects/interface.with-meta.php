<?php
/**
 * Object Type with Meta.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Object_Type_With_Meta
 */
interface ITE_Object_Type_With_Meta extends ITE_Object_Type {

	/**
	 * Add metadata to the object.
	 *
	 * @see   add_metadata()
	 *
	 * @since 2.0.0
	 *
	 * @param string $object_id
	 * @param string $key    Metadata key.
	 * @param mixed  $value  Metadata value. Must be serializable if non-scalar. Must be slashed.
	 * @param bool   $unique Whether the specified metadata key should be unique for the object.
	 *
	 * @return bool
	 */
	public function add_meta( $object_id, $key, $value, $unique = false );

	/**
	 * Update metadata on the object. If no value already exists for the given
	 * metadata key, the metadata will be added.
	 *
	 * @see   update_metadata()
	 *
	 * @since 2.0.0
	 *
	 * @param string $object_id
	 * @param string $key        Metadata key.
	 * @param mixed  $value      Metadata value. Must be serializable if non-scalar. Must be slashed.
	 * @param string $prev_value Optional, if specified, only update existing metadata with the specified value.
	 *
	 * @return bool  True on successful update, false on failure.
	 */
	public function update_meta( $object_id, $key, $value, $prev_value = '' );

	/**
	 * Retrieve metadata from the object.
	 *
	 * @see   get_metadata()
	 *
	 * @since 2.0.0
	 *
	 * @param string $object_id
	 * @param string $key    Metadata key. If not specified, retrieve all metadata from the object.
	 * @param bool   $single Optional. If given, return only the first value of the specified meta key.
	 *
	 * @return mixed Single metadata value, or array of values.
	 */
	public function get_meta( $object_id, $key = '', $single = true );

	/**
	 * Delete metadata on the object.
	 *
	 * @see   delete_metadata()
	 *
	 * @since 2.0.0
	 *
	 * @param string $object_id
	 * @param string $key        Metadata key.
	 * @param mixed  $value      Optional. Metadata value. Must be serializable if non-scalar. Must be slashed.
	 *                           If specified, only metadata with the given value will be deleted.
	 * @param bool   $delete_all Optional. If specified, metadata entries for all objects,
	 *                           not just this one, will be deleted.
	 *
	 * @return bool True on successful delete, false on failure.
	 */
	public function delete_meta( $object_id, $key, $value = '', $delete_all = false );
}
