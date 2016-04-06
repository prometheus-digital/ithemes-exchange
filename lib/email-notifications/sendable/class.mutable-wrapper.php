<?php
/**
 * Contains the mutable sendable wrapper class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Sendable_Mutable_Wrapper
 */
class IT_Exchange_Sendable_Mutable_Wrapper implements IT_Exchange_Sendable {

	/**
	 * @var IT_Exchange_Sendable
	 */
	protected $sendable;

	/**
	 * @var string|null
	 */
	protected $subject;

	/**
	 * @var string|null
	 */
	protected $body;

	/**
	 * @var IT_Exchange_Email_Recipient[]
	 */
	protected $additional_ccs = array();

	/**
	 * @var IT_Exchange_Email_Recipient[]
	 */
	protected $additional_bccs = array();

	/**
	 * @var array
	 */
	protected $additional_context = array();

	/**
	 * IT_Exchange_Sendable_Mutable_Wrapper constructor.
	 *
	 * @param IT_Exchange_Sendable $sendable
	 */
	public function __construct( IT_Exchange_Sendable $sendable ) {
		$this->sendable = $sendable;
	}

	/**
	 * Get the subject line.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_subject() {

		if ( ! $this->subject ) {
			return $this->sendable->get_subject();
		}

		return $this->subject;
	}

	/**
	 * Override the subject of this email.
	 *
	 * @since 1.36
	 *
	 * @param string $subject
	 *
	 * @return self
	 */
	public function override_subject( $subject ) {
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Get the body.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_body() {

		if ( ! $this->body ) {
			return $this->sendable->get_body();
		}

		return $this->body;
	}

	/**
	 * Override the body of this email.
	 *
	 * @since 1.36
	 *
	 * @param string $body
	 *
	 * @return self
	 */
	public function override_body( $body ) {
		$this->body = $body;

		return $this;
	}

	/**
	 * Get the email template.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Template
	 */
	public function get_template() {
		return $this->sendable->get_template();
	}

	/**
	 * Get the recipient for this email.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Recipient
	 */
	public function get_recipient() {
		return $this->sendable->get_recipient();
	}

	/**
	 * Get the CCs for this email.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Recipient[]
	 */
	public function get_ccs() {
		return array_merge( $this->additional_ccs, $this->sendable->get_ccs() );
	}

	/**
	 * Add a Cc to this email.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 *
	 * @return self
	 */
	public function add_cc( IT_Exchange_Email_Recipient $recipient ) {
		$this->additional_ccs[] = $recipient;

		return $this;
	}

	/**
	 * Get the BCCs for this email.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Recipient[]
	 */
	public function get_bccs() {
		return array_merge( $this->additional_bccs, $this->sendable->get_bccs() );
	}

	/**
	 * Add a Bcc to this email.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 *
	 * @return self
	 */
	public function add_bcc( IT_Exchange_Email_Recipient $recipient ) {
		$this->additional_bccs[] = $recipient;

		return $this;
	}

	/**
	 * Get the context for this email.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	public function get_context() {
		return $this->additional_context + $this->sendable->get_context();
	}

	/**
	 * Add additional context.
	 *
	 * @since 1.36
	 *
	 * @param string $key
	 * @param mixed  $context
	 *
	 * @return self
	 */
	public function add_context( $key, $context ) {

		if ( ! is_string( $key ) || trim( $key ) === '' ) {
			throw new InvalidArgumentException( '$key must be a non-empty string.' );
		}

		if ( ! array_key_exists( $key, $this->get_context() ) ) {
			$this->additional_context[ $key ] = $context;
		}

		return $this;
	}

	/**
	 * Get the original sendable object.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Sendable
	 */
	public function get_original() {
		return $this->sendable;
	}

	/**
	 * String representation of object
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize() {
		return serialize( array(
			'sendable' => $this->sendable,
			'subject'  => $this->subject,
			'body'     => $this->body,
			'ccs'      => $this->additional_ccs,
			'bccs'     => $this->additional_bccs,
		) );
	}

	/**
	 * Constructs the object
	 * @link  http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 *
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize( $serialized ) {

		$data = unserialize( $serialized );

		$this->sendable        = $data['sendable'];
		$this->subject         = $data['subject'];
		$this->body            = $data['body'];
		$this->additional_ccs  = $data['ccs'];
		$this->additional_bccs = $data['bccs'];
	}
}