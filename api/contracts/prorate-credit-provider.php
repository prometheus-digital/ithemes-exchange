<?php
/**
 * Credit provider interface.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Interface ITE_Contract_Prorate_Credit_Provider
 */
interface ITE_Contract_Prorate_Credit_Provider {

	/**
	 * Get the available prorate credit this object provides.
	 *
	 * @since 1.36
	 *
	 * @param IT_Exchange_Product      $for Product we are request credit for.
	 * @param IT_Exchange_Product|null $to  Optionally, provide the product this credit will be applied to.
	 *
	 * @return float Available credit
	 *               
	 * @throws InvalidArgumentException If the `$for` product is invalid.
	 */
	public function get_available_prorate_credit( IT_Exchange_Product $for, IT_Exchange_Product $to = null );
}