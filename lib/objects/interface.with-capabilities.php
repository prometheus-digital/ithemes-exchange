<?php
/**
 * Object Types that have capability guards.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Object_Type_With_Capabilities
 */
interface ITE_Object_Type_With_Capabilities extends ITE_Object_Type {

	/**
	 * Get the WordPress capability granting permission to view objects of this type.
	 *
	 * The 'exist' capability can be returned to grant access to any logged-in user.
	 * An empty string can be returned to grant access to the public.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_view_capability();

	/**
	 * Get the WordPress capability granting permission to edit objects of this type.
	 *
	 * The 'exist' capability can be returned to grant access to any logged-in user.
	 * An empty string can be returned to grant access to the public.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_edit_capability();

	/**
	 * Get the WordPress capability granting permission to create objects of this type.
	 *
	 * The 'exist' capability can be returned to grant access to any logged-in user.
	 * An empty string can be returned to grant access to the public.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_create_capability();

	/**
	 * Get the WordPress capability granting permission to list objects of this type.
	 *
	 * The 'exist' capability can be returned to grant access to any logged-in user.
	 * An empty string can be returned to grant access to the public.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_list_capability();

	/**
	 * Get the WordPress capability granting permission to delete objects of this type.
	 *
	 * The 'exist' capability can be returned to grant access to any logged-in user.
	 * An empty string can be returned to grant access to the public.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_delete_capability();
}