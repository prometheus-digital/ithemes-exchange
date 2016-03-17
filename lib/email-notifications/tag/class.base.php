<?php
/**
 * Contains the base tag class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Tag_Base
 */
class IT_Exchange_Email_Tag_Base implements IT_Exchange_Email_Tag {

	/**
	 * @var string
	 */
	private $tag;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var callable
	 */
	private $render;

	/**
	 * @var array
	 */
	private $required_context = array();

	/**
	 * @var array
	 */
	private $available_for = array();

	/**
	 * IT_Exchange_Email_Tag_Base constructor.
	 *
	 * @param string   $tag
	 * @param string   $name
	 * @param string   $description
	 * @param callable $render
	 */
	public function __construct( $tag, $name, $description, $render ) {

		if ( ! is_string( $tag ) || ! is_string( $name ) || ! is_string( $description ) ) {
			throw new InvalidArgumentException( '$tag, $name, $description must be a string.' );
		}

		$this->tag         = $tag;
		$this->name        = $name;
		$this->description = $description;

		if ( ! is_callable( $render, false ) ) {
			throw new InvalidArgumentException( '$render must be callable.' );
		}

		$this->render = $render;
	}

	/**
	 * Get the email tag.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_tag() {
		return $this->tag;
	}

	/**
	 * Get the name of the tag.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the tag's description.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the required context to render this tag.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	public function get_required_context() {
		return $this->required_context;
	}

	/**
	 * Add an item of required context.
	 *
	 * @since 1.36
	 *
	 * @param string $context
	 *
	 * @return self
	 */
	public function add_required_context( $context ) {

		if ( ! is_string( $context ) ) {
			throw new InvalidArgumentException( '$context must be a string.' );
		}

		if ( ! in_array( $context, $this->get_required_context() ) ) {
			$this->required_context[] = $context;
		}

		return $this;
	}

	/**
	 * Is this email tag available for a given notification.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Notification $notification
	 *
	 * @return bool
	 */
	public function is_available_for( IT_Exchange_Email_Notification $notification ) {

		if ( empty( $this->available_for ) ) {
			return true;
		}

		return in_array( $notification->get_slug(), $this->available_for, true );
	}

	/**
	 * Add a notification this tag is available for.
	 *
	 * @since 1.36
	 *
	 * @param string $notification_slug
	 *
	 * @return self
	 */
	public function add_available_for( $notification_slug ) {

		if ( ! is_string( $notification_slug ) ) {
			throw new InvalidArgumentException( '$notification_slug' );
		}

		if ( ! in_array( $notification_slug, $this->available_for ) ) {
			$this->available_for[] = $notification_slug;
		}

		return $this;
	}

	/**
	 * Render the email tag.
	 *
	 * If not all required context is available,
	 * the render method won't be called.
	 *
	 * @since 1.36
	 *
	 * @param array $context
	 *
	 * @return string
	 */
	public function render( $context ) {
		return call_user_func( $this->render, $context );
	}
}