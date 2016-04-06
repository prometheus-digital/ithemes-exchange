<?php
/**
 * Contains the tag interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Email_Tag
 */
interface IT_Exchange_Email_Tag {

	/**
	 * Get the email tag.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_tag();

	/**
	 * Get the name of the tag.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the tag's description.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Get the required context to render this tag.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	public function get_required_context();

	/**
	 * Is this email tag available for a given notification.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Notification $notification
	 *
	 * @return bool
	 */
	public function is_available_for( IT_Exchange_Email_Notification $notification );
	
	/**
	 * Add a notification this tag is available for.
	 *
	 * @since 1.36
	 *
	 * @param string $notification_slug
	 *
	 * @return self
	 */
	public function add_available_for( $notification_slug );

	/**
	 * Add a notification that this tag is NOT available for.
	 *
	 * @since 1.36
	 *
	 * @param string $notification_slug
	 *
	 * @return $this
	 */
	public function add_not_available_for( $notification_slug );

	/**
	 * Render the email tag.
	 *
	 * If not all required context is available,
	 * the render method won't be called.
	 *
	 * @since 1.36
	 *
	 * @param array $context
	 * @param array $options
	 *
	 * @return string
	 */
	public function render( $context, $options = array() );
}