<?php
/**
 * Tokenize Request.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Gateway_Tokenize_Request
 */
class ITE_Gateway_Tokenize_Request implements ITE_Gateway_Request {

	/** @var mixed */
	private $source_to_tokenize;

	/** @var string */
	private $label = '';

	/** @var bool */
	private $set_as_primary = false;

	/** @var IT_Exchange_Customer */
	private $customer;

	/** @var ITE_Location */
	private $address;

	/**
	 * ITE_Gateway_Tokenize_Request constructor.
	 *
	 * @param \IT_Exchange_Customer $customer
	 * @param mixed                 $source_to_tokenize
	 * @param string                $label
	 * @param bool                  $set_as_primary
	 */
	public function __construct( IT_Exchange_Customer $customer, $source_to_tokenize, $label = '', $set_as_primary = false ) {
		$this->customer           = $customer;
		$this->source_to_tokenize = $source_to_tokenize;
		$this->label              = (string) $label;
		$this->set_as_primary     = (bool) $set_as_primary;
	}

	/**
	 * Get the raw payment source that should be tokenized.
	 *
	 * This could be
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Gateway_Card|ITE_Gateway_Bank_Account|mixed
	 */
	public function get_source_to_tokenize() {
		return $this->source_to_tokenize;
	}

	/**
	 * Get the customer label.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Should the resulting token be set as the primary token.
	 *
	 * If no other tokens exist, the token will be set as primary regardless of the value set in the request.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function should_set_as_primary() {
		return $this->set_as_primary;
	}

	/**
	 * Get the address the payment source is associated with.
	 *
	 * Optionally used by some gateways to improve verification.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Location|null
	 */
	public function get_address() {
		return $this->address;
	}

	/**
	 * Set the address the payment source is associated with.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Location $address
	 */
	public function set_address( ITE_Location $address ) {
		$this->address = $address;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_name() { return 'tokenize'; }

	/**
	 * @inheritDoc
	 */
	public function get_customer() { return $this->customer; }
}
