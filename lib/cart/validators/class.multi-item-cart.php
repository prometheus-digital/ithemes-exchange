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
	public function validate( ITE_Cart $cart, ITE_Cart_Feedback $feedback = null ) {
		if ( it_exchange_is_multi_item_cart_allowed( $cart ) ) {
			return true;
		}

		if ( $cart->get_items( 'product' )->count() === 1 ) {
			return true;
		}

		if ( $feedback ) {
			$feedback->add_error(
				__( 'Only one product is allowed in the cart at a time.', 'it-l10n-ithemes-exchange' )
			);
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function coerce( ITE_Cart $cart, \ITE_Line_Item $new_item = null, ITE_Cart_Feedback $feedback = null ) {

		if ( $this->validate( $cart ) ) {
			return false;
		}

		if ( $new_item ) {

			$removed = array();

			foreach ( $cart->get_items( 'product' ) as $item ) {
				if ( $item->get_id() !== $new_item->get_id() ) {
					$cart->remove_item( 'product', $item->get_id() );
					$removed[] = $item->get_name();
				}
			}

			if ( $feedback ) {
				$feedback->add_notice(
					sprintf(
						__( '%s can only be purchased individually.', 'it-l10n-ithemes-exchange' ),
						$new_item->get_name()
					)
					. ' ' .
					sprintf(
						__( 'The following products were removed from your cart: %s.', 'it-l10n-ithemes-exchange' ),
						implode( ', ', $removed )
					),
					$new_item
				);
			}
		} else {
			$cart->remove_all( 'product' );

			if ( $feedback ) {
				$feedback->add_notice(
					__( 'Only one product is allowed in the cart at a time. Your cart has been emptied.', 'it-l10n-ithemes-exchange' )
				);
			}
		}

		return true;
	}
}