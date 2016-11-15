<?php
/**
 * Credit provider interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Contract_Prorate_Credit_Provider
 */
interface ITE_Contract_Prorate_Credit_Provider {

	/**
	 * Handle a prorate credit request.
	 *
	 * Should set the amount of available credit on the request object.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Prorate_Credit_Request $request    Prorate credit request.
	 * @param ITE_Daily_Price_Calculator $calculator Daily price calculator.
	 *
	 * @throws InvalidArgumentException If the product providing credit is invalid.
	 */
	public static function handle_prorate_credit_request( ITE_Prorate_Credit_Request $request, ITE_Daily_Price_Calculator $calculator );

	/**
	 * Determine if this credit provider is able to accept this type of credit request.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Prorate_Credit_Request $request Prorate credit request.
	 *
	 * @return bool
	 */
	public static function accepts_prorate_credit_request( ITE_Prorate_Credit_Request $request );
}
