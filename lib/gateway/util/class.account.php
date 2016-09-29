<?php
/**
 * Bank Account class.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Gateway_Bank_Account
 */
class ITE_Gateway_Bank_Account {

	const INDIVIDUAL = 'individual';
	const COMPANY = 'company';

	/** @var string */
	private $holder_name;

	/** @var string */
	private $type;

	/** @var string */
	private $account_number;

	/** @var string */
	private $routing_number = '';

	/**
	 * ITE_Gateway_Bank_Account constructor.
	 *
	 * @param string $holder_name
	 * @param string $type
	 * @param string $account_number
	 * @param string $routing_number
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $holder_name, $type, $account_number, $routing_number = '' ) {

		if ( ! in_array( $type, array( self::INDIVIDUAL, self::COMPANY ), true ) ) {
			throw new InvalidArgumentException( sprintf( 'Invalid bank account type. %s', $type ) );
		}

		$this->holder_name    = $holder_name;
		$this->type           = $type;
		$this->account_number = $account_number;
		$this->routing_number = $routing_number;
	}

	/**
	 * Get the bank account holder's name.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_holder_name() {
		return $this->holder_name;
	}

	/**
	 * Get the bank account type.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get the bank account number.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_account_number() {
		return $this->account_number;
	}

	/**
	 * Get the routing number.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_routing_number() {
		return $this->routing_number;
	}

	/**
	 * This obscures the card number and cvc on PHP 5.6 environments.
	 *
	 * @inheritDoc
	 */
	public function __debugInfo() {
		return array(
			'holder_name'    => $this->holder_name,
			'account_number' => substr( $this->account_number, - 4 ),
			'routing_number' => $this->routing_number,
			'type'           => $this->type
		);
	}
}