<?php
/**
 * Single email notification class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Notification
 */
abstract class IT_Exchange_Email_Notification {

	/**
	 * @var string
	 */
	private $slug;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $subject;

	/**
	 * @var string
	 */
	private $body;

	/**
	 * @var bool
	 */
	private $active = true;

	/**
	 * @var IT_Exchange_Email_Template
	 */
	private $template;

	/**
	 * IT_Exchange_Email_Notification constructor.
	 *
	 * @param string                     $name
	 * @param string                     $slug
	 * @param IT_Exchange_Email_Template $template
	 * @param array                      $defaults
	 */
	public function __construct( $name, $slug, IT_Exchange_Email_Template $template = null, array $defaults = array() ) {
		$this->name     = $name;
		$this->slug     = $slug;
		$this->template = $template ? $template : new IT_Exchange_Email_Template( '' );

		$emails = it_exchange_get_option( 'emails' );

		if ( ! is_array( $emails ) || ! isset( $emails[ $this->get_slug() ] ) ) {
			$data = $defaults;
		} else {
			$data = $emails[ $this->get_slug() ];
		}

		$data = ITUtility::merge_defaults( $data, $defaults );

		$this->setup_properties( $data );
	}

	/**
	 * Setup this object's properties.
	 *
	 * @since 1.36
	 *
	 * @param array $data
	 */
	protected function setup_properties( $data ) {
		$this->set_subject( isset( $data['subject'] ) ? $data['subject'] : '' );
		$this->set_body( isset( $data['body'] ) ? $data['body'] : '' );
		$this->set_active( isset( $data['active'] ) ? (bool) $data['active'] : true );
	}

	/**
	 * Get the email slug.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the email name.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the notification type.
	 *
	 * @since 1.36
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	abstract public function get_type( $label = false );

	/**
	 * Get the subject line of the notification.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_subject() {
		return $this->subject;
	}

	/**
	 * Set the subject line of the notification.
	 *
	 * @since 1.36
	 *
	 * @param string $subject
	 *
	 * @return self
	 */
	public function set_subject( $subject ) {
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Get the notification body.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Set the notification body.
	 *
	 * @since 1.36
	 *
	 * @param string $body
	 *
	 * @return self
	 */
	public function set_body( $body ) {
		$this->body = $body;

		return $this;
	}

	/**
	 * Check if this notification is active.
	 *
	 * @since 1.36
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->active;
	}

	/**
	 * Set the active state of the email notification.
	 *
	 * @since 1.36
	 *
	 * @param boolean $active
	 *
	 * @return self
	 */
	public function set_active( $active ) {

		if ( ! is_bool( $active ) ) {
			throw new InvalidArgumentException( sprintf( '$active must be a boolean, %s, given', gettype( $active ) ) );
		}

		$this->active = $active;

		return $this;
	}

	/**
	 * Get the template for this notification.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Template
	 */
	public function get_template() {
		return $this->template;
	}

	/**
	 * Get the data to save.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	protected function get_data_to_save() {
		return array(
			'subject' => $this->get_subject(),
			'body'    => $this->get_body(),
			'active'  => $this->is_active()
		);
	}

	/**
	 * Save the email notification.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public function save() {

		$emails = it_exchange_get_option( 'emails' );

		$emails[ $this->get_slug() ] = $this->get_data_to_save();

		return it_exchange_save_option( 'emails', $emails );
	}
}