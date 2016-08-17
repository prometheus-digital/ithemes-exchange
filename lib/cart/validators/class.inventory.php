<?php
/**
 * Inventory validator.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Product_Inventory_Validator
 */
class ITE_Product_Inventory_Validator implements ITE_Line_Item_Validator {

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return 'inventory';
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

		$max = it_exchange_get_max_product_quantity_allowed( $item->get_product()->ID, $item->get_id() );

		if ( $max === '' || $item->get_quantity() <= $max ) {
			return true;
		}

		if ( $feedback ) {
			$feedback->add_error(
				sprintf(
					__( 'Only %d of %s may be purchased at this time.', 'it-l10n-ithemes-exchange' ),
					$max, $item->get_name()
				),
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

		$max = it_exchange_get_max_product_quantity_allowed( $item->get_product()->ID, $item->get_id() );

		$item->set_quantity( $max );

		if ( ! $cart->get_repository()->save( $item ) ) {
			throw new ITE_Line_Item_Coercion_Failed_Exception(
				__( 'Failed to set the product quantity to the max allowed.', 'it-l10n-ithemes-exchange' ),
				$item, $this
			);
		}

		if ( $feedback ) {
			$feedback->add_notice(
				sprintf(
					__( 'Only %d of %s may be purchased at this time, the quantity has been automatically adjusted.',
						'it-l10n-ithemes-exchange' ),
					$max, $item->get_name()
				),
				$item
			);
		}

		return true;
	}
}