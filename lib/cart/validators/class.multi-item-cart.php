<?php

/**
 * Multi-item Cart Validator.
 *
 * @since   1.36
 * @license GPLv2
 */
class ITE_Multi_Item_Cart_Validator implements ITE_Cart_Validator {

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return 'multi-item-cart';
	}

	/**
	 * @inheritDoc
	 */
	public function validate( ITE_Cart $cart ) {
		if ( it_exchange_is_multi_item_cart_allowed( $cart ) ) {
			return true;
		}

		return count( $cart->get_items( 'product' ) ) === 1;
	}

	/**
	 * @inheritDoc
	 */
	public function coerce( ITE_Cart $cart, \ITE_Line_Item $new_item = null ) {

		if ( ! $this->validate( $cart ) ) {

			if ( $new_item ) {
				foreach ( $cart->get_items( 'product' ) as $item ) {
					if ( $item->get_id() !== $new_item->get_id() ) {
						$cart->remove_item( 'product', $item->get_id() );
					}
				}
			} else {
				$cart->remove_all( 'product' );
			}
		}

		return true;
	}
}