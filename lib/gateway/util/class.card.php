<?php
/**
 * Credit Cart Util.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Gateway_Card
 */
class ITE_Gateway_Card implements ITE_Gateway_Payment_Source {

	/** @var string */
	private $holder_name;

	/** @var string */
	private $number;

	/** @var int */
	private $expiration_month;

	/** @var int */
	private $expiration_year;

	/** @var string */
	private $cvc;

	/**
	 * ITE_Gateway_Card constructor.
	 *
	 * @param string $number
	 * @param int    $expiration_year
	 * @param int    $expiration_month
	 * @param string $cvc
	 * @param string $holder_name
	 */
	public function __construct( $number, $expiration_year, $expiration_month, $cvc, $holder_name = '' ) {

		if ( $expiration_year < 100 ) {
			$expiration_year += 2000;
		}

		$this->holder_name      = $holder_name;
		$this->number           = $number;
		$this->expiration_month = $expiration_month;
		$this->expiration_year  = $expiration_year;
		$this->cvc              = $cvc;
	}

	/**
	 * Get the card holder's name.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_holder_name() {
		return $this->holder_name;
	}

	/**
	 * Get the card number.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_number() {
		return $this->number;
	}

	/**
	 * Get the redacted card number.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_redacted_number() {
		$n = $this->get_number();

		if ( strlen( $n ) <= 4 ) {
			return $n;
		}

		return substr( $n, - 4 );
	}

	/**
	 * Get the card expiration month as two digits.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_expiration_month() {
		return $this->expiration_month;
	}

	/**
	 * Get the card expiration year as four digits.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_expiration_year() {
		return $this->expiration_year;
	}

	/**
	 * Get the card's cvc.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_cvc() {
		return $this->cvc;
	}

	/**
	 * @inheritDoc
	 */
	public function get_label() {
		return sprintf(
			__( 'Card ending in %s %s/%s', 'it-l10n-ithemes-exchange' ),
			$this->get_redacted_number(),
			$this->get_expiration_month(),
			$this->get_expiration_year()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_identifier() {
		return md5(
			substr( $this->get_number(), - 4 ) .
			$this->get_expiration_month() .
			$this->get_expiration_year() .
			$this->get_holder_name()
		);
	}

	/**
	 * This obscures the card number and cvc on PHP 5.6 environments.
	 *
	 * @inheritDoc
	 */
	public function __debugInfo() {
		return array(
			'holder_name'      => $this->holder_name,
			'expiration_month' => $this->expiration_month,
			'expiration_year'  => $this->expiration_year,
			'number'           => $this->get_redacted_number(),
		);
	}
}
