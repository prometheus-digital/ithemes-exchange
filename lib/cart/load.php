<?php
/**
 * Load the cart module.
 *
 * @since   1.36
 * @license GPLv2
 */

use IronBound\DB\Extensions\Meta\BaseMetaTable;

require_once dirname( __FILE__ ) . '/deprecated.php';

require_once dirname( __FILE__ ) . '/class.customer-cart.php';
require_once dirname( __FILE__ ) . '/class.shopping-cart.php';
require_once dirname( __FILE__ ) . '/class.feedback.php';
require_once dirname( __FILE__ ) . '/class.feedback-item.php';
require_once dirname( __FILE__ ) . '/class.converter.php';
require_once dirname( __FILE__ ) . '/interface.cart-validator.php';
require_once dirname( __FILE__ ) . '/interface.line-item-validator.php';
require_once dirname( __FILE__ ) . '/class.line-item.php';
require_once dirname( __FILE__ ) . '/interface.cart-aware.php';
require_once dirname( __FILE__ ) . '/class.line-item-type.php';
require_once dirname( __FILE__ ) . '/class.line-item-types.php';

require_once dirname( __FILE__ ) . '/line-items/class.repository-events.php';
require_once dirname( __FILE__ ) . '/line-items/abstract.repository.php';
require_once dirname( __FILE__ ) . '/line-items/class.session-repository.php';
require_once dirname( __FILE__ ) . '/line-items/class.cached-session-repository.php';
require_once dirname( __FILE__ ) . '/line-items/class.transaction-repository.php';
require_once dirname( __FILE__ ) . '/line-items/interface.repository-aware.php';

require_once dirname( __FILE__ ) . '/line-items/transaction/class.model.php';
require_once dirname( __FILE__ ) . '/line-items/transaction/class.table.php';

require_once dirname( __FILE__ ) . '/line-items/interface.aggregatable.php';
require_once dirname( __FILE__ ) . '/line-items/interface.aggregate.php';
require_once dirname( __FILE__ ) . '/line-items/interface.tax.php';
require_once dirname( __FILE__ ) . '/line-items/interface.taxable.php';
require_once dirname( __FILE__ ) . '/line-items/interface.shipping.php';
require_once dirname( __FILE__ ) . '/line-items/interface.discountable.php';
require_once dirname( __FILE__ ) . '/line-items/interface.quantity-modifiable.php';

require_once dirname( __FILE__ ) . '/line-items/class.cart-product.php';
require_once dirname( __FILE__ ) . '/line-items/class.simple-tax.php';
require_once dirname( __FILE__ ) . '/line-items/class.base-shipping.php';
require_once dirname( __FILE__ ) . '/line-items/class.coupon.php';
require_once dirname( __FILE__ ) . '/line-items/class.fee.php';
require_once dirname( __FILE__ ) . '/line-items/class.collection.php';

require_once dirname( __FILE__ ) . '/validators/class.inventory.php';
require_once dirname( __FILE__ ) . '/validators/class.multi-item-cart.php';
require_once dirname( __FILE__ ) . '/validators/class.multi-item-product.php';

require_once dirname( __FILE__ ) . '/exceptions/class.cart-coercion-failed.php';
require_once dirname( __FILE__ ) . '/exceptions/class.line-item-coercion-failed.php';

require_once dirname( __FILE__ ) . '/class.meta.php';
require_once dirname( __FILE__ ) . '/class.meta-registry.php';

\IronBound\DB\Manager::register( new ITE_Transaction_Line_Item_Table(), '', 'ITE_Transaction_Line_Item_Model' );
\IronBound\DB\Manager::register( new BaseMetaTable( new ITE_Transaction_Line_Item_Table(), array(
	'primary_id_column' => 'line_item'
) ) );

\IronBound\DB\Manager::maybe_install_table( \IronBound\DB\Manager::get( 'ite-line-items' ) );
\IronBound\DB\Manager::maybe_install_table( \IronBound\DB\Manager::get( 'ite-line-items-meta' ) );

ITE_Line_Item_Types::register_type( new ITE_Line_Item_Type( 'product', array(
	'label'               => __( 'Product', 'it-l10n-ithemes-exchange' ),
	'show_in_rest'        => true,
	'editable_in_rest'    => true,
	'rest_serializer'     => function ( array $data, ITE_Cart_Product $product, array $schema, ITE_Cart $cart ) {

		if ( isset( $schema['properties']['product'] ) ) {
			$data['product'] = $product->get_product()->ID;
		}

		if ( isset( $schema['properties']['shipping_method'] ) && $product->get_product()->has_feature( 'shipping' ) ) {

			$data['shipping_method'] = array();

			$method = it_exchange_get_multiple_shipping_method_for_cart_product( $product, $cart );
			$method = it_exchange_get_registered_shipping_method( $method );

			if ( $method ) {
				$data['shipping_method'] = array(
					'id'    => $method->slug,
					'label' => $method->label
				);
			}
		}

		return $data;
	},
	'schema'              => array(
		'product'         => array(
			'description' => __( 'The product for this line item. Cannot be edited after being set.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'integer',
			'context'     => array( 'view', 'edit' ),
			'required'    => true,
			'readonly'    => true,
		),
		'shipping_method' => array(
			'description' => __( 'The shipping method selected for this item.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'object',
			'context'     => array( 'view', 'edit' ),
			'required'    => false,
			'readonly'    => false,
			'properties'  => array(
				'id'    => array(
					'description' => __( 'The unique id for this shipping method.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'label' => array(
					'description' => __( 'The label for this shipping method.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			)
		),
	),
	'create_from_request' => function ( WP_REST_Request $request ) {

		$product = it_exchange_get_product( $request['product'] );

		if ( ! $product ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_product',
				__( 'Invalid product.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => 400 )
			);
		}

		$item = \ITE_Cart_Product::create(
			$product,
			$request['quantity']['selected']
		);

		$cart = it_exchange_get_cart( $request['cart_id'] );
		$cart->add_item( $item );

		return $item;
	}
) ) );

ITE_Line_Item_Types::register_type( new ITE_Line_Item_Type( 'fee', array(
	'label'            => __( 'Fee', 'it-l10n-ithemes-exchange' ),
	'show_in_rest'     => true,
	'editable_in_rest' => false,
) ) );
ITE_Line_Item_Types::register_type( new ITE_Line_Item_Type( 'tax', array( 'label' => __( 'Tax', 'it-l10n-ithemes-exchange' ) ) ) );
ITE_Line_Item_Types::register_type( new ITE_Line_Item_Type( 'shipping', array( 'label' => __( 'Shipping', 'it-l10n-ithemes-exchange' ) ) ) );
ITE_Line_Item_Types::register_type( new ITE_Line_Item_Type( 'coupon', array( 'label' => __( 'Coupon', 'it-l10n-ithemes-exchange' ) ) ) );