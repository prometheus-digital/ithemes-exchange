<?php
/**
 * Contains the email tag replacer interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Shortcode_Tag_Replacer
 */
interface IT_Exchange_Email_Tag_Replacer extends IT_Exchange_Email_Middleware {

	/**
	 * Replace the email tags.
	 *
	 * @since 1.36
	 *
	 * @param string $content
	 * @param array  $context
	 *
	 * @return string
	 */
	public function replace( $content, $context );

	/**
	 * Format a tag.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Tag|string $tag
	 *
	 * @return string
	 */
	public function format_tag( $tag );

	/**
	 * Add a tag to be replaced.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Tag $tag
	 *
	 * @return self
	 */
	public function add_tag( IT_Exchange_Email_Tag $tag );

	/**
	 * Get a tag object for a given tag.
	 *
	 * @since 1.36
	 *
	 * @param string $tag
	 *
	 * @return IT_Exchange_Email_Tag|null
	 */
	public function get_tag( $tag );

	/**
	 * Get all registered tags.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Tag[]
	 */
	public function get_tags();

	/**
	 * Get all tags for a given notification.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Notification $notification
	 *
	 * @return IT_Exchange_Email_Tag[]
	 */
	public function get_tags_for( IT_Exchange_Email_Notification $notification );
}