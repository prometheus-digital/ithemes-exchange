<?php
/**
 * Contains the email class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email
 */
class IT_Exchange_Email implements IT_Exchange_Sendable {

	/**
	 * @var IT_Exchange_Email_Recipient
	 */
	private $recipient;

	/**
	 * @var IT_Exchange_Email_Recipient[]
	 */
	private $ccs = array();

	/**
	 * @var IT_Exchange_Email_Recipient[]
	 */
	private $bccs = array();

	/**
	 * @var IT_Exchange_Email_Notification
	 */
	private $notification;

	/**
	 * @var array
	 */
	private $context = array();

	/**
	 * IT_Exchange_Email constructor.
	 *
	 * @param IT_Exchange_Email_Recipient    $recipient
	 * @param IT_Exchange_Email_Notification $notification
	 * @param array                          $context
	 */
	public function __construct( IT_Exchange_Email_Recipient $recipient, IT_Exchange_Email_Notification $notification, array $context = array() ) {
		$this->recipient    = $recipient;
		$this->notification = $notification;

		foreach ( $context as $key => $val ) {
			$this->add_context( $val, $key );
		}
	}

	/**
	 * Add a CC to the email.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 *
	 * @return self
	 */
	public function add_cc( IT_Exchange_Email_Recipient $recipient ) {
		$this->ccs[] = $recipient;

		return $this;
	}

	/**
	 * Add a BCC to the email.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 *
	 * @return self
	 */
	public function add_bcc( IT_Exchange_Email_Recipient $recipient ) {
		$this->bccs[] = $recipient;

		return $this;
	}

	/**
	 * Add context to the email.
	 *
	 * @since 1.36
	 *
	 * @param mixed|stdClass|Serializable $context
	 * @param string                      $key
	 *
	 * @return self
	 */
	public function add_context( $context, $key ) {

		if ( ! is_string( $key ) || trim( $key ) === '' ) {
			throw new InvalidArgumentException( '$key must be a non-empty string.' );
		}

		$this->context[ $key ] = $context;

		return $this;
	}

	/**
	 * Get the subject line.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_subject() {

		$subject = $this->get_notification()->get_subject();

		return apply_filters( "it_exchange_email_{$this->get_notification()->get_slug()}_subject", $subject, $this );
	}

	/**
	 * Get the body.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_body() {

		$body = $this->get_notification()->get_body();

		return apply_filters( "it_exchange_email_{$this->get_notification()->get_slug()}_body", $body, $this );
	}

	/**
	 * Get the email template.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Template
	 */
	public function get_template() {
		return $this->get_notification()->get_template();
	}

	/**
	 * Get the recipient for this email.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Recipient
	 */
	public function get_recipient() {
		return $this->recipient;
	}

	/**
	 * Get the CCs for this email.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Recipient[]
	 */
	public function get_ccs() {
		return $this->ccs;
	}

	/**
	 * Get the BCCs for this email.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Recipient[]
	 */
	public function get_bccs() {
		return $this->bccs;
	}

	/**
	 * Get the notification this email is based on.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Notification
	 */
	public function get_notification() {
		return $this->notification;
	}

	/**
	 * Get the context for this email.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	public function get_context() {
		return $this->context;
	}
}