<?php
/**
 * Contains the base tag replacer class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Tag_Replacer_Base
 */
abstract class IT_Exchange_Email_Tag_Replacer_Base implements IT_Exchange_Email_Tag_Replacer {

	/**
	 * @var array
	 */
	private $tags = array();

	/**
	 * Add a tag to be replaced.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Tag $tag
	 *
	 * @return self
	 */
	public function add_tag( IT_Exchange_Email_Tag $tag ) {
		$this->tags[] = $tag;

		return $this;
	}

	/**
	 * Get all registered tags.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Tag[]
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Get all tags for a given notification.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Notification $notification
	 *
	 * @return IT_Exchange_Email_Tag[]
	 */
	public function get_tags_for( IT_Exchange_Email_Notification $notification ) {

		$tags = array();

		foreach ( $this->get_tags() as $tag ) {
			if ( $tag->is_available_for( $notification ) ) {
				$tags[] = $tag;
			}
		}

		return $tags;
	}
}