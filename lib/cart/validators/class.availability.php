<?php
/**
 * Validate the availability settings for a product.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Product_Availability_Validator
 */
class ITE_Product_Availability_Validator implements ITE_Line_Item_Validator {

	/**
	 * @inheritDoc
	 */
	public static function get_name() { return 'product-availability'; }

	/**
	 * @inheritDoc
	 */
	public function accepts( $type ) { return $type === 'product'; }

	/**
	 * @inheritDoc
	 */
	public function validate( ITE_Line_Item $item, ITE_Cart $cart, ITE_Cart_Feedback $feedback = null ) {
		return $item instanceof ITE_Cart_Product && it_exchange_is_product_available( $item->get_product() );
	}

	/**
	 * @inheritDoc
	 */
	public function coerce( ITE_Line_Item $item, ITE_Cart $cart, ITE_Cart_Feedback $feedback = null ) {

		if ( $this->validate( $item, $cart ) ) {
			return false;
		}

		if ( ! $cart->remove_item( $item->get_type(), $item->get_id() ) ) {
			throw new ITE_Line_Item_Coercion_Failed_Exception(
				__( 'Failed to remove the unavailable item.', 'it-l10n-ithemes-exchange' ),
				$item,
				$this
			);
		}

		if ( $feedback ) {
			$feedback->add_error(
				sprintf( __( 'The %s product is not currently available.', 'it-l10n-ithemes-exchange' ), $item->get_name() ),
				$item
			);
		}

		return true;
	}
}