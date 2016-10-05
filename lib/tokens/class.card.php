<?php
/**
 * CC Payment Token.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Payment_Token_Card
 */
class ITE_Payment_Token_Card extends ITE_Payment_Token {

	const CREDIT = 'credit';
	const DEBIT = 'debit';

	protected static $token_type = 'card';

	/**
	 * Get the Card's brand.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_brand() { return $this->get_meta( 'brand', true ); }

	/**
	 * Set the Card's brand.
	 *
	 * @since 1.36.0
	 *
	 * @param string $brand
	 *
	 * @return bool
	 */
	public function set_brand( $brand ) { return (bool) $this->update_meta( 'brand', $brand ); }

	/**
	 * Get the card's expiration month
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_expiration_month() { return zeroise( $this->get_meta( 'expiration_month', true ), 2 ); }

	/**
	 * Set the Card's expiration month.
	 *
	 * @since 1.36.0
	 *
	 * @param string $month
	 *
	 * @return bool
	 */
	public function set_expiration_month( $month ) { return (bool) $this->update_meta( 'expiration_month', $month ); }

	/**
	 * Get the card's expiration year
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_expiration_year() { return zeroise( $this->get_meta( 'expiration_year', true ), 2 ); }

	/**
	 * Set the Card's expiration year.
	 *
	 * @since 1.36.0
	 *
	 * @param string $year
	 *
	 * @return bool
	 */
	public function set_expiration_year( $year ) { return (bool) $this->update_meta( 'expiration_year', $year ); }

	/**
	 * Get the Card's source of funding.
	 *
	 * Could be either 'credit' or 'debit'.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_funding() { return $this->get_meta( 'funding', true ); }

	/**
	 * Set the Card's source of funding.
	 *
	 * @since 1.36.0
	 *
	 * @param string $funding
	 *
	 * @return bool
	 */
	public function set_funding( $funding ) { return (bool) $this->update_meta( 'funding', $funding ); }
}