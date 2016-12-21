<?php
/**
 * Update Token interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Update_Payment_Token_Handler
 */
interface ITE_Update_Payment_Token_Handler {

	/**
	 * Update a payment token.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Payment_Token $token
	 * @param array             $update
	 *
	 * @return ITE_Payment_Token|null
	 */
	public function update_token( ITE_Payment_Token $token, array $update );
}