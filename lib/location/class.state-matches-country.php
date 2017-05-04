<?php
/**
 * Validate that the selected state is an option for the selected country.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Location_State_Matches_Country_Validator
 */
class ITE_Location_State_Matches_Country_Validator implements ITE_Location_Validator {

	/**
	 * @inheritDoc
	 */
	public static function get_name() { return 'state-matches-country'; }

	/**
	 * @inheritDoc
	 */
	public function validate( ITE_Location $location ) {

		if ( empty( $location['country'] ) ) {
			return true;
		}

		$country = $location['country'];
		$state   = $location['state'];

		$states = it_exchange_get_country_states( array( 'country' => $country ) );

		if ( empty( $states ) ) {
			return true;
		}

		if ( ! isset( $states[ $state ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function validate_for_cart( ITE_Location $location, ITE_Cart $cart ) {

		if ( $this->validate( $location ) ) {
			return true;
		}

		$cart->get_feedback()->add_error(
			__( 'Selected address state does not match selected country.', 'it-l10n-ithemes-exchange' )
		);

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function can_validate() { return null; }
}