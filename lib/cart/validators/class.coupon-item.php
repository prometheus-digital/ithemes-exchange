<?php
/**
 * Validator to ensure a coupon continues to be valid while in the cart.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Coupon_Line_Item_Validator
 */
class ITE_Coupon_Item_Validator implements ITE_Line_Item_Validator {

	/**
	 * @inheritDoc
	 */
	public static function get_name() { return 'coupon-item'; }

	/**
	 * @inheritDoc
	 */
	public function accepts( $type ) { return $type === 'coupon'; }

	/**
	 * @inheritDoc
	 *
	 * @param ITE_Coupon_Line_Item $item
	 */
	public function validate( ITE_Line_Item $item, ITE_Cart $cart, ITE_Cart_Feedback $feedback = null ) {

		try {
			$item->get_coupon()->validate( $cart );
		} catch ( Exception $e ) {
			if ( $feedback ) {
				$feedback->add_error(
					sprintf(
						__( '%s coupon is no longer valid: %s', 'it-l10n-ithemes-exchange' ),
						$item->get_coupon()->get_code(), $e->getMessage()
					),
					$item
				);
			}

			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 * @param ITE_Coupon_Line_Item $item
	 */
	public function coerce( ITE_Line_Item $item, ITE_Cart $cart, ITE_Cart_Feedback $feedback = null ) {

		if ( $this->validate( $item, $cart, $feedback ) ) {
			return false;
		}

		if ( ! $cart->remove_item( $item ) ) {
			throw new ITE_Line_Item_Coercion_Failed_Exception(
				sprintf( __( 'Unable to remove %s coupon from the cart', 'it-l10n-ithemes-exchange' ), $item->get_coupon()->get_code() ),
				$item, $this
			);
		}

		return true;
	}
}