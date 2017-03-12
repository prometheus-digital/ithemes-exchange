<?php
/**
 * Guest Customer validator.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Guest_Customer_Purchase_Validator
 */
class ITE_Guest_Customer_Purchase_Validator implements ITE_Line_Item_Validator {

	/**
	 * @inheritDoc
	 */
	public static function get_name() { return 'guest-customer-purchase'; }

	/**
	 * @inheritDoc
	 */
	public function validate( ITE_Line_Item $item, ITE_Cart $cart, ITE_Cart_Feedback $feedback = null ) {

		if ( $cart->is_guest() && ! it_exchange_can_line_item_be_purchased_by_guest( $item, $cart ) ) {

			if ( $feedback ) {
				$feedback->add_error( __( 'This cart cannot be purchased by a guest customer.', 'it-l10n-ithemes-exchange' ) );
			}

			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function coerce( ITE_Line_Item $item, ITE_Cart $cart, ITE_Cart_Feedback $feedback = null ) {

		if ( $this->validate( $item, $cart ) ) {
			return false;
		}

		$cart->remove_item( $item );

		if ( $feedback ) {
			$feedback->add_error(
				sprintf( __( '%s cannot be purchased by a guest customer.', 'it-l10n-ithemes-exchange' ), $item->get_name() ),
				$item
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function accepts( $type ) { return true; }
}