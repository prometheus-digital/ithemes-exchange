<?php
/**
 * Subject Interface.
 *
 * @since 1.36.0
 * @license GPLv2
 */

/**
 * Interface ITE_Activity_Subject
 *
 * Describes something that can have activity generated about it.
 */
interface ITE_Activity_Subject {

	/**
	 * Get this item's ID.
	 *
	 * @since 1.36.0
	 *
	 * @return string|int
	 */
	public function get_ID();

	/**
	 * Get the name of this item.
	 *
	 * @sinec 1.36.0
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the type of subject. This should be globally unique.
	 *
	 * For example: 'transaction' or 'customer'.
	 *
	 * @since 1.36.0
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public static function get_object_type( $label = false );
}