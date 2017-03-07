<?php
/**
 * Shipping Methods API endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Cart;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Shipping_Methods
 *
 * @package iThemes\Exchange\REST\Route\v1\Cart
 */
class Shipping extends Base implements Getable, Putable {

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );
		$data = $this->prepare_cart_for_response( $cart );

		return new \WP_REST_Response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) { return true; }

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );

		$cart_method          = $cart->get_shipping_method();
		$cart_method          = $cart_method ? $cart_method->slug : '';
		$is_eligible          = it_exchange_cart_is_eligible_for_multiple_shipping_methods( $cart );
		$switched_to_multiple = false;

		if ( $is_eligible && is_array( $request['per_item'] ) ) {
			foreach ( $request['per_item'] as $item ) {

				$line_item = $cart->get_item( $item['item']['type'], $item['item']['id'] );

				if ( ! $line_item ) {
					continue;
				}

				$available = it_exchange_get_enabled_shipping_methods_for_product( $line_item->get_product(), 'slug', $cart );
				$current   = $cart->get_shipping_method( $line_item );
				$current   = $current ? $current->slug : '';

				foreach ( $item['methods'] as $method ) {
					if ( $method['selected'] && $method['id'] !== $current ) {

						if ( ! isset( $available[ $method['id'] ] ) ) {
							return new \WP_Error(
								'it_exchange_rest_invalid_shipping_method',
								sprintf(
									__( "The '%s' shipping method is not available for the '%s' product.", 'it-l10n-ithemes-exchange' ),
									$method['id'], $line_item->get_name()
								),
								array( 'status' => 400 )
							);
						}

						if ( ! $switched_to_multiple ) {
							$cart->set_shipping_method( 'multiple-methods' );
							$switched_to_multiple = true;
						}

						$cart->set_shipping_method( $method['id'], $line_item );
						break;
					}
				}
			}
		}

		if ( ! $switched_to_multiple && is_array( $request['cart_wide'] ) ) {

			$available = it_exchange_get_available_shipping_methods_for_cart( true, $cart );

			foreach ( $request['cart_wide'] as $method ) {
				if ( $method['selected'] && $method['id'] !== $cart_method ) {

					if ( ( ! $is_eligible && $method['id'] === 'multiple-methods' ) || ! isset( $available[ $method['id'] ] ) ) {
						return new \WP_Error(
							'it_exchange_rest_invalid_shipping_method',
							sprintf(
								__( "The '%s' shipping method is not available for this cart.", 'it-l10n-ithemes-exchange' ),
								$method['id']
							),
							array( 'status' => 400 )
						);
					}

					$cart->set_shipping_method( $method['id'] );
				}
			}
		}

		$data = $this->prepare_cart_for_response( $cart );

		return new \WP_REST_Response( $data );
	}

	/**
	 * Prepare a cart for response.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return array
	 */
	protected function prepare_cart_for_response( \ITE_Cart $cart ) {

		$data = array(
			'cart_wide' => array(),
			'per_item'  => array(),
		);

		$selected              = $cart->get_shipping_method();
		$selected              = $selected ? $selected->slug : '';
		$cart_methods          = it_exchange_get_available_shipping_methods_for_cart( true, $cart );
		$eligible_for_multiple = it_exchange_cart_is_eligible_for_multiple_shipping_methods( $cart );

		foreach ( $cart_methods as $method ) {
			$data['cart_wide'][] = array(
				'id'       => $method->slug,
				'label'    => $method->label,
				'total'    => it_exchange_get_cart_shipping_cost( $method->slug, false, $cart ),
				'selected' => $method->slug === $selected,
			);
		}

		if ( $eligible_for_multiple ) {
			$data['cart_wide'][] = array(
				'id'       => 'multiple-methods',
				'label'    => __( 'Multiple Methods', 'it-l10n-ithemes-exchange' ),
				'total'    => null,
				'selected' => 'multiple-methods' === $selected
			);
		} else {
			return $data;
		}

		/** @var \ITE_Cart_Product $product */
		foreach ( $cart->get_items( 'product' ) as $product ) {
			if ( ! $product->get_product()->has_feature( 'shipping' ) ) {
				continue;
			}

			$item_data = array(
				'item'    => array(
					'id'   => $product->get_id(),
					'type' => $product->get_type(),
				),
				'methods' => array(),
			);

			$item_selected = $cart->get_shipping_method( $product );
			$item_selected = $item_selected ? $item_selected->slug : '';
			$item_methods  = it_exchange_get_enabled_shipping_methods_for_product( $product->get_product() );

			if ( is_array( $item_methods ) ) {
				foreach ( $item_methods as $method ) {
					$item_data['methods'][] = array(
						'id'       => $method->slug,
						'label'    => $method->label,
						'total'    => it_exchange_get_shipping_method_cost_for_cart_item( $method->slug, $product->bc(), false, $cart ),
						'selected' => $method->slug === $item_selected && $selected === 'multiple-methods',
					);
				}
			}

			$data['per_item'][] = $item_data;
		}

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( Request $request, AuthScope $scope ) { return true; }

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'shipping/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return array(
			'$schema'     => 'http://json-schema.org/draft-04/schema#',
			'definitions' => array(
				'shipping_method' => array(
					'title'      => __( 'Shipping Method', 'it-l10n-ithemes-exchange' ),
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'description' => __( 'The unique id for this shipping method.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'readonly'    => true,
							'context'     => array( 'view', 'edit' )
						),
						'label'    => array(
							'description' => __( 'The label for this shipping method', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'readonly'    => true,
							'context'     => array( 'view', 'edit' )
						),
						'total'    => array(
							'description' => __( 'The total cost of this shipping method.', 'it-l10n-ithemes-exchange' ),
							'readonly'    => true,
							'context'     => array( 'view', 'edit' ),
							'oneOf'       => array(
								array( 'type' => 'number' ),
								array( 'type' => 'null' ),
							),
						),
						'selected' => array(
							'description' => __( 'Whether this is the selected shipping method..', 'it-l10n-ithemes-exchange' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'default'     => false,
						),
					)
				)
			),
			'title'       => 'cart_shipping_methods',
			'type'        => 'object',
			'properties'  => array(
				'cart_wide' => array(
					'description' => __( 'List of shipping methods that can be applied cart-wide.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'items'       => array( '$ref' => '#/definitions/shipping_method' )
				),
				'per_item'  => array(
					'description' => __( 'Lis of items and associated possible shipping methods.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'item'    => array(
								'type'        => 'object',
								'description' => __( 'The line item shipping is applied to.', 'it-l10n-ithemes-exchange' ),
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
								'properties'  => array(
									'id'   => array(
										'type'        => 'string',
										'description' => __( 'The unique id for this line item.', 'it-l10n-ithemes-exchange' ),
										'context'     => array( 'view', 'edit' ),
									),
									'type' => array(
										'type'        => 'string',
										'description' => __( 'The type of this line item.', 'it-l10n-ithemes-exchange' ),
										'context'     => array( 'view', 'edit' ),
									)
								)
							),
							'methods' => array(
								'description' => __( 'List of shipping methods that can be applied to this item.', 'it-l10n-ithemes-exchange' ),
								'type'        => 'array',
								'context'     => array( 'view', 'edit' ),
								'items'       => array( '$ref' => '#/definitions/shipping_method' )
							)
						)
					)
				),
			)
		);
	}
}
