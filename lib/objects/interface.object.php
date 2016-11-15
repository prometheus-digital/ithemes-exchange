<?php
/**
 * Object Interface.
 *
 * @since 1.36.0
 * @license GPLv2
 */

/**
 * Interface ITE_Object
 */
interface ITE_Object {

	/**
	 * Get the object ID.
	 *
	 * @since 1.36.0
	 *
	 * @return int|string
	 */
	public function get_ID();

	/**
	 * Get a string representation of this object.
	 *
	 * Should be a short string.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function __toString();

	/**
	 * Get the object type.
	 *
	 * @since 1.36.0
	 *
	 * @return ITE_Object_Type
	 */
	public static function get_object_type();
}