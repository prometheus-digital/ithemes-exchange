<?php
/**
 * Purchase Request Serializer.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Cart;

/**
 * Class PurchaseSerializer
 *
 * @package iThemes\Exchange\REST\Route\Cart
 */
class PurchaseSerializer {

	/**
	 * Serialize the purchase request handler.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Purchase_Request_Handler           $handler
	 * @param \ITE_Gateway_Purchase_Request $request
	 *
	 * @return array
	 */
	public function serialize( \ITE_Purchase_Request_Handler $handler, \ITE_Gateway_Purchase_Request $request ) {
		return array(
			'id'     => $handler->get_gateway()->get_slug(),
			'name'   => $handler->get_gateway()->get_name(),
			'label'  => $handler->get_payment_button_label(),
			'nonce'  => $handler->get_nonce(),
			'method' => $handler->get_data_for_REST( $request ),
		);
	}

	/**
	 * Get the purchase schema.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cart-purchase',
			'type'       => 'object',
			'properties' => array(
				'id'     => array(
					'type'        => 'string',
					'description' => __( 'The slug of the gateway.', 'it-l10n-ithemes-exchange' ),
					'required'    => true,
				),
				'name'   => array(
					'type'        => 'string',
					'description' => __( 'The name of the gateway.', 'it-l10n-ithemes-exchange' ),
					'example'     => 'Stripe',
				),
				'label'  => array(
					'type'        => 'string',
					'description' => __( 'The label to be used for the purchase button.', 'it-l10n-ithemes-exchange' ),
					'example'     => 'Pay by Card',
				),
				'nonce'  => array(
					'type'        => 'string',
					'description' => __( 'A token unique to this gateway that is required to complete the purchase.', 'it-l10n-ithemes-exchange' )
				),
				'method' => array(
					'type'                 => 'object',
					'description'          => __( 'Additional data specific to this gateway.', 'it-l10n-ithemes-exchange' ),
					'additionalProperties' => true,
					'properties'           => array(
						'accepts' => array(
							'type'  => 'array',
							'anyOf' => array(
								array( 'type' => 'string', 'enum' => array( 'card' ) ),
								array( 'type' => 'string', 'enum' => array( 'token' ) ),
								array( 'type' => 'string', 'enum' => array( 'tokenize' ) ),
							),
						),
						'method'  => array(
							'type'     => 'string',
							'enum'     => array( 'REST', 'dialog', 'iframe', 'redirect' ),
							'required' => true,
						),
					)
				)
			)
		);
	}
}