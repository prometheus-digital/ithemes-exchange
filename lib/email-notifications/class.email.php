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
class IT_Exchange_Email {

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
	 * IT_Exchange_Email constructor.
	 *
	 * @param IT_Exchange_Email_Recipient    $recipient
	 * @param IT_Exchange_Email_Notification $notification
	 */
	public function __construct( IT_Exchange_Email_Recipient $recipient, IT_Exchange_Email_Notification $notification ) {
		$this->recipient    = $recipient;
		$this->notification = $notification;
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


}