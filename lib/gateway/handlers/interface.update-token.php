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
interface ITE_Update_Payment_Token_Handler extends ITE_Gateway_Request_Handler {

	/**
	 * Can this handler update a given field.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field
	 *
	 * @return bool
	 */
	public function can_update_field( $field );
}