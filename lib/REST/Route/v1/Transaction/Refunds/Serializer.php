<?php
/**
 * Refund Serializer.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Transaction\Refunds;

use iThemes\Exchange\REST\Manager;
use iThemes\Exchange\REST as r;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Route\v1\Transaction\Refunds
 */
class Serializer {

	/**
	 * Serialize a refund.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Refund           $refund
	 * @param \IT_Exchange_Customer $user
	 *
	 * @return array
	 */
	public function serialize( \ITE_Refund $refund, \IT_Exchange_Customer $user ) {

		$data = array(
			'id'         => $refund->ID,
			'amount'     => $refund->amount,
			'reason'     => $refund->reason,
			'issued_by'  => $refund->issued_by ? $refund->issued_by->ID : 0,
			'created_at' => \iThemes\Exchange\REST\format_rfc339( $refund->created_at ),
			'gateway_id' => $refund->gateway_id,
		);

		return $data;
	}

	/**
	 * Generate links.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Refund                    $refund
	 * @param \iThemes\Exchange\REST\Manager $manager
	 *
	 * @return array
	 */
	public function generate_links( \ITE_Refund $refund, Manager $manager ) {

		$links = array();

		if ( $refund->issued_by ) {
			$route               = $manager->get_first_route( 'iThemes\Exchange\REST\Route\v1\Customer\Customer' );
			$links['customer'][] = array(
				'href'       => r\get_rest_url( $route, array( 'customer_id' => $refund->issued_by->ID ) ),
				'embeddable' => true
			);
		}

		return $links;
	}

	/**
	 * Get the refund schema.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'refund',
			'type'       => 'object',
			'properties' => array(
				'id'         => array(
					'description' => __( 'The unique id for this refund.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'amount'     => array(
					'description' => __( 'The total amount refunded.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
					'minimum'     => 0.00,
				),
				'reason'     => array(
					'description' => __( 'The user-provided reason for this refund.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'gateway_id' => array(
					'description' => __( 'The gateway ID that implemented the refund.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'readonly'    => true,
				),
				'issued_by'  => array(
					'description' => __( 'The user who issued this refund.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'created_at' => array(
					'description' => __( 'The refund date, as GMT', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
			)
		);
	}

}
