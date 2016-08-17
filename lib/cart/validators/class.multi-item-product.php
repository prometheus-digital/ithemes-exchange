<?php
/**
 * Multi-item Product Validator.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Multi_Item_Product_Validator
 */
class ITE_Multi_Item_Product_Validator implements ITE_Line_Item_Validator {

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return 'multi-item-product';
	}

	/**
	 * @inheritDoc
	 */
	public function accepts( $type ) {
		return $type === 'product';
	}

	/**
	 * @inheritDoc
	 */
	public function validate( ITE_Line_Item $item, ITE_Cart $cart, ITE_Cart_Feedback $feedback = null ) {

		if ( it_exchange_is_multi_item_product_allowed( $item->get_product()->ID ) ) {
			return true;
		}

		if ( $item->get_quantity() === 1 ) {
			return true;
		}

		if ( $feedback ) {
			$feedback->add_error(
				sprintf( __( 'Only one %s may be purchased at a time.', 'it-l10n-ithemes-exchange' ), $item->get_name() ),
				$item
			);
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function coerce( ITE_Line_Item $item, ITE_Cart $cart, ITE_Cart_Feedback $feedback = null ) {

		if ( $this->validate( $item, $cart ) ) {
			return false;
		}

		$item->set_quantity( 1 );

		if ( ! $cart->get_repository()->save( $item ) ) {
			throw new ITE_Line_Item_Coercion_Failed_Exception(
				__( 'Failed to set quantity to 1.', 'it-l10n-ithemes-exchange' ), $item, $this
			);
		}

		if ( $feedback ) {
			$feedback->add_notice(
				sprintf(
					__( 'Only one %s may be purchased at a time, the quantity has been automatically adjusted.',
						'it-l10n-ithemes-exchange' ),
					$item->get_name()
				),
				$item
			);
		}

		return true;
	}
}