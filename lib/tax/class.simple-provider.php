<?php
/**
 * Tax Provider.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Simple_Taxes_Provider
 */
class ITE_Simple_Taxes_Provider extends ITE_Tax_Provider {

	/**
	 * @inheritDoc
	 */
	public function is_product_tax_exempt( IT_Exchange_Product $product ) {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_item_class() {
		return 'ITE_Simple_Tax_Line_Item';
	}

	/**
	 * @inheritDoc
	 */
	public function add_taxes_to( ITE_Taxable_Line_Item $item, ITE_Cart $cart ) {

		$options  = it_exchange_get_option( 'addon_taxes_simple' );
		$tax_rate = empty( $options['default-tax-rate'] ) ? 0 : (float) $options['default-tax-rate'];

		$tax = ITE_Simple_Tax_Line_Item::create( $tax_rate, array(), $item );

		if ( $tax->applies_to( $item ) ) {
			$item->add_tax( $tax );
			$cart->get_repository()->save( $item );
		}
	}
}
