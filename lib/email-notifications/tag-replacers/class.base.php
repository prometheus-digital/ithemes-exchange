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
		$this->tags[ $tag->get_tag() ] = $tag;

		return $this;
	}

	/**
	 * Get a tag object for a given tag.
	 *
	 * @since 1.36
	 *
	 * @param string $tag
	 *
	 * @return IT_Exchange_Email_Tag|null
	 */
	public function get_tag( $tag ) {
		return isset( $this->tags[ $tag ] ) ? $this->tags[ $tag ] : null;
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

	/**
	 * Handle a sendable object before it has been sent.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Sendable_Mutable_Wrapper $sendable
	 *
	 * @return bool True to continue, false to stop email sending.
	 */
	public function handle( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

		$sendable->override_subject( $this->replace( $sendable->get_subject(), $sendable->get_context() ) );
		$sendable->override_body( $this->replace( $sendable->get_body(), $sendable->get_context() ) );
	}
}