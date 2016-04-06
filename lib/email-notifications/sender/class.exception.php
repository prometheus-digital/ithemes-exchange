<?php
/**
 * Contains the delivery exception class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Delivery_Exception
 */
class IT_Exchange_Email_Delivery_Exception extends Exception {

	/**
	 * @var IT_Exchange_Sendable
	 */
	private $email;

	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 * @link  http://php.net/manual/en/exception.construct.php
	 *
	 * @param string               $message  [optional] The Exception message to throw.
	 * @param IT_Exchange_Sendable $email
	 * @param int                  $code     [optional] The Exception code.
	 * @param Exception            $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
	 *
	 * @since 5.1.0
	 */
	public function __construct( $message = '', IT_Exchange_Sendable $email = null, $code = 0, Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );

		$this->email = $email;
	}

	/**
	 * Get the email object that failed to send.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Sendable
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * String representation of the exception
	 * @link  http://php.net/manual/en/exception.tostring.php
	 * @return string the string representation of the exception.
	 * @since 5.1.0
	 */
	public function __toString() {

		$email   = $this->get_email();
		$message = $this->message;

		if ( $email ) {

			if ( $email instanceof IT_Exchange_Sendable_Mutable_Wrapper ) {
				$email = $email->get_original();
			}

			if ( $email instanceof IT_Exchange_Email ) {
				$identifier = $email->get_notification()->get_slug();
			} else {
				$identifier = $email->get_subject();
			}

			$message .= sprintf( ' Email %s sent to %s.', $identifier, $email->get_recipient()->get_email() );
		}

		$message = __CLASS__ . ": {$message}";

		if ( $this->code ) {
			$message .= " [{$this->code}]";
		}

		return $message;
	}
}