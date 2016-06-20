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
	public function validate( ITE_Line_Item $item, ITE_Cart $cart ) {

		if ( it_exchange_is_multi_item_product_allowed( $item->get_product()->ID ) ) {
			return true;
		}

		return $item->get_quantity() === 1;
	}

	/**
	 * @inheritDoc
	 */
	public function coerce( ITE_Line_Item $item, ITE_Cart $cart ) {

		if ( $this->validate( $item, $cart ) ) {
			return true;
		}

		$item->set_quantity( 1 );

		return $item->persist( $cart->get_repository() );
	}
}