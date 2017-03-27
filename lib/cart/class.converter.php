<?php
/**
 * Converter class to transform a transaction object to a series of line items.
 *
 * By definition this is a lossy procedure, and is only intended for backwards-compatibility.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Line_Item_Transaction_Object_Converter
 */
class ITE_Line_Item_Transaction_Object_Converter {

	/**
	 * Convert a transaction object to a series of line items.
	 *
	 * @since 2.0.0
	 *
	 * @param \stdClass                $cart_object
	 * @param \IT_Exchange_Transaction $transaction
	 */
	public function convert( stdClass $cart_object, IT_Exchange_Transaction $transaction ) {

		$repository = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction );

		if ( empty( $cart_object->products ) ) {
			return;
		}

		$products = $this->products( $cart_object->products, $repository );

		if ( isset( $cart_object->shipping_total ) ) {
			$shipping_total = it_exchange_convert_from_database_number( $cart_object->shipping_total );
		} else {
			$shipping_total = 0;
		}

		if ( $shipping_total ) {
			if ( $cart_object->shipping_method && $cart_object->shipping_method !== 'multiple-methods' ) {
				$this->shipping_single( $cart_object->shipping_method, $shipping_total, $products, $repository );
			} elseif ( $cart_object->shipping_method === 'multiple-methods' ) {
				$this->shipping_multi( $cart_object->shipping_method_multi, $shipping_total, $products, $repository );
			}
		}

		if ( ! empty( $cart_object->taxes_raw ) ) {
			$res = $this->taxes( $cart_object->taxes_raw, $cart_object->sub_total, $products, $repository );

			if ( ! $res ) {
				$transaction->add_meta( 'failed_tax_upgrade', true );
			}
		}

		if ( ! empty( $cart_object->coupons_total_discount ) ) {
			$this->coupons( $cart_object->coupons, $cart_object->coupons_total_discount, $products, $repository );
		}
	}

	/**
	 * Convert products to line items.
	 *
	 * @since 2.0.0
	 *
	 * @param array                                 $products
	 * @param \ITE_Line_Item_Transaction_Repository $repository
	 *
	 * @return \ITE_Cart_Product[]
	 */
	protected function products( array $products, ITE_Line_Item_Transaction_Repository $repository ) {

		$items = array();

		foreach ( $products as $id => $product ) {

			$product = ITUtility::merge_defaults( $product, array(
				'itemized_data'      => '',
				'additional_data'    => '',
				'product_id'         => 0,
				'product_name'       => '',
				'count'              => 1,
				'itemized_hash'      => '',
				'product_base_price' => 0.00,
				'product_subtotal'   => 0,
			) );

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
			$item->set_line_item_repository( $repository );
		}

		$repository->save_many( $items );

		return $items;
	}

	/**
	 * Build the line items for taxes.
	 *
	 * @since 2.0.0
	 *
	 * @param float                                 $taxes
	 * @param float                                 $sub_total
	 * @param \ITE_Cart_Product[]                   $products
	 * @param \ITE_Line_Item_Transaction_Repository $repository
	 *
	 * @return bool
	 */
	protected function taxes( $taxes, $sub_total, array $products, ITE_Line_Item_Transaction_Repository $repository ) {

		$tid = $repository->get_transaction()->ID;

		$rate = ( $taxes / $sub_total ) * 100;

		if ( metadata_exists( 'post', $tid, '_it_exchange_easy_us_sales_taxes' ) ) {
			if ( class_exists( 'ITE_TaxCloud_Line_Item' ) ) {
				$item = ITE_TaxCloud_Line_Item::create( $rate );
			} else {
				return false;
			}
		} elseif ( metadata_exists( 'post', $tid, '_it_exchange_easy_canadian_sales_taxes' ) ) {

			if ( ! class_exists( 'ITE_Canadian_Tax_Item' ) ) {
				return false;
			}

			$data = get_post_meta( $tid, '_it_exchange_easy_canadian_sales_taxes', true );

			if ( ! is_array( $data ) ) {
				return false;
			}

			if ( $repository->get_transaction()->get_shipping_address() ) {
				$state = $repository->get_shipping_address()->offsetGet( 'state' );
			} elseif ( $repository->get_billing_address() ) {
				$state = $repository->get_billing_address()->offsetGet( 'state' );
			} else {
				return false;
			}

			$settings = it_exchange_get_option( 'addon_easy_canadian_sales_taxes', true, false );

			if ( empty( $settings['tax-rates'] ) ) {
				$settings = it_exchange_get_option( 'addon_easy_canadian_sales_taxes', true );
			}

			foreach ( $data as $tax_type ) {
				$code = '';

				if ( ! isset( $settings['tax-rates'][ $state ] ) ) {
					return false;
				}

				foreach ( $settings['tax-rates'][ $state ] as $index => $rate_data ) {
					if ( $rate_data['type'] == $tax_type['type'] ) {
						$code = "$state:$index";
						break;
					}
				}

				$total_tax_amount = 0;

				foreach ( $products as $product ) {

					$product_price = $product->get_amount() * $product->get_quantity();
					$tax_amount    = $product_price * ( $tax_type['rate'] / 100 );

					$item = new ITE_Canadian_Tax_Item(
						md5( uniqid( 'CANADIAN', true ) . $tax_type['type'] ),
						new ITE_Array_Parameter_Bag( array(
							'code'                => $code,
							'rate'                => $tax_type['rate'],
							'applies_to_shipping' => $tax_type['shipping']
						) ),
						new ITE_Array_Parameter_Bag( array(
							'quantity' => 1,
							'name'     => $tax_type['type'],
							'amount'   => $tax_amount,
							'total'    => $tax_amount,
						) )
					);

					$item->set_aggregate( $product );
					$product->add_tax( $item );

					$total_tax_amount += $tax_amount;
				}

				$repository->save_many( $products );

				if ( $total_tax_amount < $tax_type['total'] ) {
					$repository->save( new ITE_Canadian_Tax_Item(
						md5( uniqid( 'CANADIAN', true ) . $tax_type['type'] ),
						new ITE_Array_Parameter_Bag( array(
							'code'                => $code,
							'rate'                => $tax_type['rate'],
							'applies_to_shipping' => $tax_type['shipping']
						) ),
						new ITE_Array_Parameter_Bag( array(
							'quantity' => 1,
							'name'     => $tax_type['type'],
							'amount'   => $tax_type['total'] - $total_tax_amount,
							'total'    => $tax_type['total'] - $total_tax_amount,
						) )
					) );
				}
			}

			return true;
		} elseif ( metadata_exists( 'post', $tid, '_it_exchange_easy_eu_value_added_taxes_taxes_total' ) ) {

			if ( ! class_exists( 'ITE_EU_VAT_Line_Item' ) ) {
				return false;
			}

			$settings = it_exchange_get_option( 'addon_easy_eu_value_added_taxes' );

			$regular_taxes = get_post_meta( $tid, '_it_exchange_easy_eu_value_added_taxes', true );
			$moss_taxes    = get_post_meta( $tid, '_it_exchange_easy_eu_value_added_vat_moss_taxes', true );

			if ( is_array( $regular_taxes ) ) {
				foreach ( $regular_taxes as $regular_tax ) {

					if ( empty( $regular_tax['total'] ) ) {
						continue;
					}

					$code = '';

					foreach ( $settings['tax-rates'] as $index => $rate_data ) {
						if ( $rate_data['rate'] == $regular_tax['tax-rate']['rate'] ) {
							$code = "vat:$index";
							break;
						}
					}

					$rate_data = $regular_tax['tax-rate'];
					$rate      = $rate_data['rate'];
					$shipping  = ! empty( $rate_data['shipping'] )
					             && in_array( $rate_data['shipping'], array( 'on', true ), true );

					$total_tax_amount = 0;

					foreach ( $products as $product ) {

						$product_price = $product->get_amount() * $product->get_quantity();
						$tax_amount    = $product_price * ( $rate / 100 );

						$item = new ITE_EU_VAT_Line_Item(
							md5( uniqid( 'VAT', true ) . $rate ),
							new ITE_Array_Parameter_Bag( array(
								'code'                => $code,
								'rate'                => $rate,
								'applies_to_shipping' => $shipping
							) ),
							new ITE_Array_Parameter_Bag( array(
								'name'   => $rate_data['label'] ?: __( 'VAT', 'it-l10n-ithemes-exchange' ),
								'amount' => $tax_amount,
								'total'  => $tax_amount,
							) )
						);

						$item->set_aggregate( $product );
						$product->add_tax( $item );

						$total_tax_amount += $tax_amount;
					}

					$repository->save_many( $products );

					if ( $total_tax_amount < $regular_tax['total'] ) {
						$repository->save( new ITE_EU_VAT_Line_Item(
							md5( uniqid( 'VAT', true ) . $rate ),
							new ITE_Array_Parameter_Bag( array(
								'code'                => $code,
								'rate'                => $rate,
								'applies_to_shipping' => $shipping,
							) ),
							new ITE_Array_Parameter_Bag( array(
								'name'   => $rate_data['label'] ?: __( 'VAT', 'it-l10n-ithemes-exchange' ),
								'amount' => $regular_tax['total'] - $total_tax_amount,
								'total'  => $regular_tax['total'] - $total_tax_amount,
							) )
						) );
					}
				}
			}

			if ( is_array( $moss_taxes ) ) {

				if ( $repository->get_transaction()->get_shipping_address() ) {
					$country = $repository->get_shipping_address()->offsetGet( 'country' );
				} elseif ( $repository->get_billing_address() ) {
					$country = $repository->get_billing_address()->offsetGet( 'country' );
				} else {
					return false;
				}

				foreach ( $moss_taxes as $moss_tax ) {

					if ( empty( $moss_tax['total'] ) ) {
						continue;
					}

					$code = '';

					foreach ( $settings['vat-moss-tax-rates'][ $country ] as $index => $rate_data ) {
						if ( $rate_data['rate'] == $moss_tax['tax-rate']['rate'] ) {
							$code = "moss:$country:$index";
							break;
						}
					}

					$rate_data = $moss_tax['tax-rate'];
					$rate      = $rate_data['rate'];
					$shipping  = ! empty( $rate_data['shipping'] )
					             && in_array( $rate_data['shipping'], array( 'on', true ), true );

					$total_tax_amount = 0;

					foreach ( $products as $product ) {

						$product_price = $product->get_amount() * $product->get_quantity();
						$tax_amount    = $product_price * ( $rate / 100 );

						$item = new ITE_EU_VAT_Line_Item(
							md5( uniqid( 'VAT', true ) . $rate ),
							new ITE_Array_Parameter_Bag( array(
								'code'                => $code,
								'rate'                => $rate,
								'applies_to_shipping' => $shipping
							) ),
							new ITE_Array_Parameter_Bag( array(
								'name'   => $rate_data['label'] ?: __( 'VAT', 'it-l10n-ithemes-exchange' ),
								'amount' => $tax_amount,
								'total'  => $tax_amount,
							) )
						);

						$item->set_aggregate( $product );
						$product->add_tax( $item );

						$total_tax_amount += $tax_amount;
					}

					$repository->save_many( $products );

					if ( $total_tax_amount < $moss_tax['total'] ) {
						$repository->save( new ITE_EU_VAT_Line_Item(
							md5( uniqid( 'VAT', true ) . $rate ),
							new ITE_Array_Parameter_Bag( array(
								'code'                => $code,
								'rate'                => $rate,
								'applies_to_shipping' => $shipping,
							) ),
							new ITE_Array_Parameter_Bag( array(
								'name'   => $rate_data['label'] ?: __( 'VAT', 'it-l10n-ithemes-exchange' ),
								'amount' => $moss_tax['total'] - $total_tax_amount,
								'total'  => $moss_tax['total'] - $total_tax_amount,
							) )
						) );
					}
				}
			}

			return true;
		} else {
			$item = ITE_Simple_Tax_Line_Item::create( $rate );
		}

		foreach ( $products as $product ) {
			$tax = $item->create_scoped_for_taxable( $product );
			$product->add_tax( $tax );
		}

		$repository->save_many( $products );

		return true;
	}

	/**
	 * Build the line items for coupons.
	 *
	 * @since 2.0.0
	 *
	 * @param array[]                               $coupon_types
	 * @param float                                 $total
	 * @param \ITE_Cart_Product[]                   $products
	 * @param \ITE_Line_Item_Transaction_Repository $repository
	 */
	protected function coupons(
		$coupon_types,
		$total,
		array $products,
		ITE_Line_Item_Transaction_Repository $repository
	) {

		$total_coupons = 0;

		foreach ( $coupon_types as $coupons ) {
			$total_coupons += count( $coupons );
		}

		$distribute_over = array();

		foreach ( $products as $product ) {
			$distribute_over[ $product->get_id() ] = $product->get_total();
		}

		$distributed_over = it_exchange_proportionally_distribute_cost( $total / $total_coupons, $distribute_over );

		$per_products = array();

		foreach ( $coupon_types as $coupon_type => $coupons ) {

			foreach ( $coupons as $coupon_data ) {

				if ( empty( $coupon_data['id'] ) ) {
					continue;
				}

				$global = new ITE_Coupon_Line_Item(
					md5( $coupon_data['code'] ),
					new ITE_Array_Parameter_Bag( array_merge( array( 'type' => $coupon_type, ), $coupon_data ) ),
					new ITE_Array_Parameter_Bag( array(
						'name'         => __( 'Savings', 'it-l10n-ithemes-exchange' ),
						'description'  => $coupon_data['code'],
						'amount'       => 0,
						'quantity'     => 1,
						'total'        => 0,
						'summary_only' => true,
					) )
				);

				$global->set_line_item_repository( $repository );
				$repository->save( $global );

				foreach ( $products as $product ) {

					$per_product_amount = $distributed_over[ $product->get_id() ];
					$per_product_amount -= $product->get_total();
					$per_product_amount *= - 1;

					$props = array_merge( array( 'type' => $coupon_type ), $coupon_data );

					$scoped = new ITE_Coupon_Line_Item(
						md5( $coupon_data['code'] . '-' . $product->get_id() ),
						new ITE_Array_Parameter_Bag(),
						new ITE_Array_Parameter_Bag( array(
							'name'         => __( 'Savings', 'it-l10n-ithemes-exchange' ),
							'description'  => $coupon_data['code'],
							'amount'       => $per_product_amount,
							'quantity'     => 1,
							'total'        => $per_product_amount,
							'summary_only' => true,
						) )
					);
					$scoped->set_scoped_from( $global );
					$scoped->set_aggregate( $product );
					$scoped->set_line_item_repository( $repository );

					foreach ( $props as $k => $v ) {
						$scoped->set_param( $k, $v );
					}

					$product->add_item( $scoped );

					$per_products[] = $scoped;
				}
			}
		}

		$repository->save_many( $per_products );
	}

	/**
	 * Build the line item for single shipping.
	 *
	 * @since 2.0.0
	 *
	 * @param string                                $method_slug
	 * @param float                                 $total
	 * @param \ITE_Cart_Product[]                   $products
	 * @param \ITE_Line_Item_Transaction_Repository $repository
	 *
	 * @return \ITE_Shipping_Line_Item|null
	 */
	protected function shipping_single( $method_slug, $total, $products, ITE_Line_Item_Transaction_Repository $repository ) {

		$method  = it_exchange_get_registered_shipping_method( $method_slug );
		$options = it_exchange_get_registered_shipping_method_args( $method_slug );

		if ( $method ) {
			$method_label = $method->label;
		} elseif ( is_int( $method ) ) {
			$method_label = "Table Rate Shipping #{$method} (deleted)";
		} else {
			$method_label = ucwords( str_replace( array( '-', '_' ), ' ', $method ) );
		}

		$shippable    = array();
		$per_products = array();

		foreach ( $products as $product ) {
			if ( $product->get_product() && $product->get_product()->has_feature( 'shipping' ) ) {
				$shippable[] = $product;
			}
		}

		if ( ! $shippable ) {
			$shippable = $products;
		}

		$distributed_over = array();

		if ( $total != 0 ) {

			$distribute_over = array();

			foreach ( $shippable as $product ) {
				$distribute_over[ $product->get_id() ] = $product->get_total();
			}

			$distributed_over = it_exchange_proportionally_distribute_cost( $total, $distribute_over );
		}

		$global = new ITE_Base_Shipping_Line_Item(
			md5( $method_slug . '-true-' . microtime() ),
			new ITE_Array_Parameter_Bag( array(
				'method'    => $method_slug,
				'provider'  => isset( $options['provider'] ) ? $options['provider'] : '',
				'cart_wide' => true,
			) ),
			new ITE_Array_Parameter_Bag( array(
				'name'         => $method_label,
				'description'  => '',
				'amount'       => 0,
				'quantity'     => 1,
				'total'        => 0,
				'summary_only' => true
			) )
		);
		$global->set_line_item_repository( $repository );

		foreach ( $shippable as $product ) {

			if ( $distributed_over ) {
				$per_item_cost = $distributed_over[ $product->get_id() ] - $product->get_total();
			} else {
				$per_item_cost = 0;
			}

			$per_product = new ITE_Base_Shipping_Line_Item(
				md5( $method_slug . '-false-' . microtime() ),
				new ITE_Array_Parameter_Bag( array(	'cart_wide' => false, ) ),
				new ITE_Array_Parameter_Bag( array(
					'name'         => $method_label,
					'description'  => '',
					'amount'       => $per_item_cost,
					'quantity'     => 1,
					'total'        => $per_item_cost,
					'summary_only' => true
				) )
			);
			$per_product->set_line_item_repository( $repository );
			$per_product->set_aggregate( $product );
			$per_product->set_scoped_from( $global );
			$per_products[] = $per_product;
		}

		$repository->save_many( $per_products );
		$repository->save( $global );

		return $global;
	}

	/**
	 * Build the line items for multiple shipping methods.
	 *
	 * @since 2.0.0
	 *
	 * @param array                                 $multiple
	 * @param float                                 $total
	 * @param \ITE_Cart_Product[]                   $products
	 * @param \ITE_Line_Item_Transaction_Repository $repository
	 *
	 * @return \ITE_Shipping_Line_Item[]
	 */
	protected function shipping_multi( $multiple, $total, $products, ITE_Line_Item_Transaction_Repository $repository ) {

		$per_products = array();
		$globals      = array();

		$distribute_over = array();

		foreach ( $multiple as $cart_product_id => $method_slug ) {
			if ( ! isset( $products[ $cart_product_id ] ) ) {
				continue; // Sanity check
			}

			if ( $method_slug === 'exchange-free-shipping' ) {
				continue;
			}

			$distribute_over[ $cart_product_id ] = $products[ $cart_product_id ]->get_total();
		}

		$distributed_over = it_exchange_proportionally_distribute_cost( $total, $distribute_over );

		foreach ( $multiple as $cart_product_id => $method_slug ) {

			if ( ! isset( $products[ $cart_product_id ] ) ) {
				continue;
			}

			$product = $products[ $cart_product_id ];

			$method  = it_exchange_get_registered_shipping_method( $method_slug );
			$options = it_exchange_get_registered_shipping_method_args( $method_slug );

			if ( $method ) {
				$method_label = $method->label;
			} elseif ( is_int( $method ) ) {
				$method_label = "Table Rate Shipping #{$method} (deleted)";
			} else {
				$method_label = ucwords( str_replace( array( '-', '_' ), ' ', $method ) );
			}

			if ( isset( $distributed_over[ $cart_product_id ] ) ) {
				$per_item_cost = $distributed_over[ $cart_product_id ] - $product->get_total();
			} else {
				$per_item_cost = 0;
			}

			$global = new ITE_Base_Shipping_Line_Item(
				md5( $method_slug . '-true-' . microtime() ),
				new ITE_Array_Parameter_Bag( array(
					'method'    => $method_slug,
					'provider'  => isset( $options['provider'] ) ? $options['provider'] : '',
					'cart_wide' => true,
				) ),
				new ITE_Array_Parameter_Bag( array(
					'name'         => $method_label,
					'description'  => '',
					'amount'       => 0,
					'quantity'     => 1,
					'total'        => 0,
					'summary_only' => true
				) )
			);
			$global->set_line_item_repository( $repository );

			$globals[] = $global;

			$per_product = new ITE_Base_Shipping_Line_Item(
				md5( $method_slug . '-false-' . microtime() ),
				new ITE_Array_Parameter_Bag( array( 'cart_wide' => false, ) ),
				new ITE_Array_Parameter_Bag( array(
					'name'         => $method_label,
					'description'  => '',
					'amount'       => $per_item_cost,
					'quantity'     => 1,
					'total'        => $per_item_cost,
					'summary_only' => true
				) )
			);
			$per_product->set_line_item_repository( $repository );
			$per_product->set_aggregate( $product );
			$per_product->set_scoped_from( $global );
			$per_products[] = $per_product;
		}

		$repository->save_many( $per_products );
		$repository->save_many( $globals );

		return $per_products;
	}

}
