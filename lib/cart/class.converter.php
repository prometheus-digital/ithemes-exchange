<?php
/**
 * Converter class to transform a transaction object to a series of line items.
 *
 * By definition this is a lossy procedure, and is only intended for backwards-compatibility.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Line_Item_Transaction_Object_Converter
 */
class ITE_Line_Item_Transaction_Object_Converter {

	/**
	 * Convert a transaction object to a series of line items.
	 *
	 * @since 1.36.0
	 *
	 * @param \stdClass                $cart_object
	 * @param \IT_Exchange_Transaction $transaction
	 */
	public function convert( stdClass $cart_object, IT_Exchange_Transaction $transaction ) {

		$repository = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction );

		$products = $this->products( $cart_object->products, $repository );

		if ( $cart_object->shipping_method && $cart_object->shipping_method !== 'multiple-methods' ) {
			$this->shipping_single( $cart_object->shipping_method, $cart_object->shipping_total, $repository );
		} elseif ( $cart_object->shipping_method === 'multiple-methods' ) {
			$this->shipping_multi( $cart_object->shipping_method_multi, $cart_object->shipping_total, $products, $repository );
		}

		if ( $cart_object->taxes_raw ) {
			$this->taxes( $cart_object->taxes_raw, $cart_object->sub_total, $products, $repository );
		}

		if ( $cart_object->coupons_total_discount ) {
			$this->coupons( $cart_object->coupons, $cart_object->coupons_total_discount, $products, $repository );
		}
	}

	/**
	 * Convert products to line items.
	 *
	 * @since 1.36.0
	 *
	 * @param array                                 $products
	 * @param \ITE_Line_Item_Transaction_Repository $repository
	 *
	 * @return \ITE_Cart_Product[]
	 */
	protected function products( array $products, ITE_Line_Item_Transaction_Repository $repository ) {

		$items = array();

		foreach ( $products as $id => $product ) {
			$item                     = new ITE_Cart_Product(
				$id,
				new ITE_Array_Parameter_Bag( array(
					'itemized_data'   => unserialize( $product['itemized_data'] ),
					'additional_data' => unserialize( $product['additional_data'] ),
					'product_id'      => $product['product_id'],
					'product_name'    => $product['product_name'],
					'count'           => $product['count'],
					'itemized_hash'   => $product['itemized_hash'],
				) ),
				new ITE_Array_Parameter_Bag( array(
					'name'         => $product['product_name'],
					'description'  => '',
					'amount'       => $product['product_base_price'],
					'quantity'     => $product['count'],
					'total'        => $product['product_subtotal'],
					'summary_only' => false,
				) ) );
			$items[ $item->get_id() ] = $item;
		}

		$repository->save_many( $items );

		return $items;
	}

	/**
	 * Build the line items for taxes.
	 *
	 * @since 1.36.0
	 *
	 * @param float                                 $taxes
	 * @param float                                 $sub_total
	 * @param \ITE_Cart_Product[]                   $products
	 * @param \ITE_Line_Item_Transaction_Repository $repository
	 */
	protected function taxes( $taxes, $sub_total, array $products, ITE_Line_Item_Transaction_Repository $repository ) {

		$rate = ( $taxes / $sub_total ) * 100;

		if ( metadata_exists( 'post', $repository->get_transaction()->ID, '_it_exchange_easy_us_sales_taxes' ) ) {
			if ( class_exists( 'ITE_TaxCloud_Line_Item' ) ) {
				$item = ITE_TaxCloud_Line_Item::create( $rate );
			} else {
				return;
			}
		} else {
			$item = ITE_Simple_Tax_Line_Item::create( $rate );
		}

		foreach ( $products as $product ) {
			$tax = $item->create_scoped_for_taxable( $product );
			$product->add_tax( $tax );
		}

		$repository->save_many( $products );
	}

	/**
	 * Build the line items for coupons.
	 *
	 * @since 1.36.0
	 *
	 * @param array[]                               $coupons
	 * @param float                                 $coupons_total
	 * @param \ITE_Cart_Product[]                   $products
	 * @param \ITE_Line_Item_Transaction_Repository $repository
	 */
	protected function coupons(
		$coupons,
		$coupons_total,
		array $products,
		ITE_Line_Item_Transaction_Repository $repository
	) {

		// This is horrifically inaccurate, but no worse than what we had before
		$coupons_total /= count( $products ) / count( $coupons );

		foreach ( $coupons as $coupon_data ) {
			$coupon = it_exchange_get_coupon( $coupon_data['id'] );

			if ( ! $coupon ) {
				continue;
			}

			$item = ITE_Coupon_Line_Item::create( $coupon );
			$item = new ITE_Coupon_Line_Item(
				$item->get_id(),
				new ITE_Array_Parameter_Bag( $item->get_params() ),
				new ITE_Array_Parameter_Bag( array(
					'name'         => __( 'Savings', 'it-l10n-ithemes-exchange' ),
					'description'  => $coupon_data['code'],
					'amount'       => $coupons_total,
					'quantity'     => 1,
					'total'        => $coupons_total,
					'summary_only' => true,
				) )
			);

			foreach ( $products as $product ) {
				$product->add_item( $item );
			}
		}

		$repository->save_many( $products );
	}

	/**
	 * Build the line item for single shipping.
	 *
	 * @since 1.36.0
	 *
	 * @param string                                $method
	 * @param float                                 $total
	 * @param \ITE_Line_Item_Transaction_Repository $repository
	 *
	 * @return \ITE_Shipping_Line_Item|null
	 */
	protected function shipping_single( $method, $total, ITE_Line_Item_Transaction_Repository $repository ) {

		$options = it_exchange_get_registered_shipping_method_args( $method );

		if ( empty( $options['provider'] ) ) {
			return null;
		}

		$method   = it_exchange_get_registered_shipping_method( $method );
		$provider = it_exchange_get_registered_shipping_provider( $options['provider'] );

		$item = ITE_Base_Shipping_Line_Item::create( $method, $provider, true );
		$item = new ITE_Base_Shipping_Line_Item(
			$item->get_id(),
			new ITE_Array_Parameter_Bag( $item->get_params() ),
			new ITE_Array_Parameter_Bag( array(
				'name'         => $method->label,
				'description'  => '',
				'amount'       => $total,
				'quantity'     => 1,
				'total'        => $total,
				'summary_only' => true
			) )
		);

		$repository->save( $item );

		return $item;
	}

	/**
	 * Build the line items for multiple shipping methods.
	 *
	 * @since 1.36.0
	 *
	 * @param array                                 $multiple
	 * @param float                                 $total
	 * @param \ITE_Cart_Product[]                   $products
	 * @param \ITE_Line_Item_Transaction_Repository $repository
	 *
	 * @return \ITE_Shipping_Line_Item[]
	 */
	protected function shipping_multi( $multiple, $total, $products, ITE_Line_Item_Transaction_Repository $repository ) {

		$total /= count( $multiple );
		$items = array();

		foreach ( $multiple as $cart_product_id => $method ) {
			$options = it_exchange_get_registered_shipping_method_args( $method );

			if ( empty( $options['provider'] ) ) {
				return null;
			}

			$method   = it_exchange_get_registered_shipping_method( $method );
			$provider = it_exchange_get_registered_shipping_provider( $options['provider'] );

			$item = ITE_Base_Shipping_Line_Item::create( $method, $provider, true );
			$item = new ITE_Base_Shipping_Line_Item(
				$item->get_id(),
				new ITE_Array_Parameter_Bag( $item->get_params() ),
				new ITE_Array_Parameter_Bag( array(
					'name'         => $method->label,
					'description'  => '',
					'amount'       => $total,
					'quantity'     => 1,
					'total'        => $total,
					'summary_only' => true
				) )
			);

			$items[] = $item;

			$item = ITE_Base_Shipping_Line_Item::create( $method, $provider, false );
			$item = new ITE_Base_Shipping_Line_Item(
				$item->get_id(),
				new ITE_Array_Parameter_Bag( $item->get_params() ),
				new ITE_Array_Parameter_Bag( array(
					'name'         => $method->label,
					'description'  => '',
					'amount'       => 0.00,
					'quantity'     => 1,
					'total'        => 0.00,
					'summary_only' => true
				) )
			);

			$products[ $cart_product_id ]->add_item( $item );
			$repository->save( $products[ $cart_product_id ] );
		}

		$repository->save_many( $items );

		return $items;
	}

}