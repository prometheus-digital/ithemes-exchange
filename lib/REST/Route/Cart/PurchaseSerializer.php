<?php
/**
 * Purchase Request Serializer.
 *
 * @since 2.0.0
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
	 * @param \ITE_Gateway_Purchase_Request_Interface $request
	 *
	 * @return array
	 */
	public function serialize( \ITE_Purchase_Request_Handler $handler, \ITE_Gateway_Purchase_Request_Interface $request ) {
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
			'properties' => array()
		);
	}
}