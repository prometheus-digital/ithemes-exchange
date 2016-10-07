<?php
/**
 * Bank Account Payment Token.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Payment_Token_Bank_Account
 */
class ITE_Payment_Token_Bank_Account extends ITE_Payment_Token {

	const INDIVIDUAL = 'individual';
	const COMPANY = 'company';

	protected static $token_type = 'bank';

	/**
	 * @inheritDoc
	 */
	public function get_label() {
		return parent::get_label() ?: sprintf( __( 'Bank account ending in %s', 'it-l10n-ithemes-exchange' ), $this->redacted );
	}

	/**
	 * Get the Bank Account's institution name.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_bank_name() { return $this->get_meta( 'bank_name', true ); }

	/**
	 * Set the Bank Account's institution name.
	 *
	 * @since 1.36.0
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function set_bank_name( $name ) { return (bool) $this->update_meta( 'bank_name', $name ); }

	/**
	 * Get the Bank Account's type.
	 *
	 * Either 'individual' or 'company'.
	 *
	 * @since 1.36.0
	 *
	 * @return string
	 */
	public function get_account_type() { return $this->get_meta( 'account_type', true ); }

	/**
	 * Set the Bank Account's type.
	 *
	 * Either 'individual' or 'company'.
	 *
	 * @since 1.36.0
	 *
	 * @param string $type
	 *
	 * @return bool
	 *
	 * @throws \InvalidArgumentException
	 */
	public function set_account_type( $type ) {

		if ( ! in_array( $type, array( self::INDIVIDUAL, self::COMPANY ), true ) ) {
			throw new InvalidArgumentException( 'Invalid account type.' );
		}

		return (bool) $this->update_meta( 'account_type', $type );
	}
}