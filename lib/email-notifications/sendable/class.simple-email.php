<?php
/**
 * Contains a simple email class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Simple_Email
 */
class IT_Exchange_Simple_Email implements IT_Exchange_Sendable {

	/**
	 * @var string
	 */
	private $subject;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var IT_Exchange_Email_Recipient
	 */
	private $recipient;

	/**
	 * @var array
	 */
	private $context = array();

	/**
	 * @var IT_Exchange_Email_Recipient[]
	 */
	private $ccs = array();

	/**
	 * @var IT_Exchange_Email_Recipient[]
	 */
	private $bccs = array();

	/**
	 * IT_Exchange_Simple_Email constructor.
	 *
	 * @param string                      $subject
	 * @param string                      $message
	 * @param IT_Exchange_Email_Recipient $recipient
	 * @param array                       $context
	 * @param array                       $args May contain 'cc' or 'bcc' values which should each be an array of recipient.
	 */
	public function __construct( $subject, $message, IT_Exchange_Email_Recipient $recipient, $context = array(), $args = array() ) {
		$this->subject   = $subject;
		$this->message   = $message;
		$this->recipient = $recipient;
		$this->context   = $context;

		if ( ! empty( $args['cc'] ) ) {
			if ( ! is_array( $args['cc'] ) ) {
				$args['cc'] = array( $args['cc'] );
			}

			foreach ( $args['cc'] as $cc ) {
				if ( $cc instanceof IT_Exchange_Email_Recipient ) {
					$this->ccs[] = $cc;
				}
			}
		}

		if ( ! empty( $args['bcc'] ) ) {
			if ( ! is_array( $args['bcc'] ) ) {
				$args['bcc'] = array( $args['bcc'] );
			}

			foreach ( $args['bcc'] as $bcc ) {
				if ( $bcc instanceof IT_Exchange_Email_Recipient ) {
					$this->bccs[] = $bcc;
				}
			}
		}
	}

	/**
	 * Get the subject line.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_subject() {
		return $this->subject;
	}

	/**
	 * Get the body.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_body() {
		return $this->message;
	}

	/**
	 * Get the email template.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Email_Template
	 */
	public function get_template() {
		return new IT_Exchange_Email_Template( null );
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
	 * Get the context for this email.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * String representation of object
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize() {

		$data = array(
			'subject'   => $this->get_subject(),
			'body'      => $this->get_body(),
			'ccs'       => $this->get_ccs(),
			'bccs'      => $this->get_bccs(),
			'recipient' => $this->get_recipient(),
		);

		return serialize( $data );
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

		$this->subject   = $data['subject'];
		$this->message   = $data['body'];
		$this->ccs       = $data['ccs'];
		$this->bccs      = $data['bccs'];
		$this->recipient = $data['recipient'];
	}
}