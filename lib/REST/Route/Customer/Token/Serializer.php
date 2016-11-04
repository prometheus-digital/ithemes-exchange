<?php
/**
 * Token Serializer.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Customer\Token;

/**
 * Class Serializer
 *
 * @package iThemes\Exchange\REST\Route\Customer\Token
 */
class Serializer {

	/**
	 * Serialize the payment token.
	 *
	 * @since 1.36.0
	 *
	 * @param \ITE_Payment_Token $token
	 *
	 * @return array
	 */
	public function serialize( \ITE_Payment_Token $token ) {
		return array(
			'id'       => $token->get_pk(),
			'gateway'  => $token->get_raw_attribute( 'gateway' ),
			'label'    => array(
				'raw'      => $token->label,
				'rendered' => $token->get_label()
			),
			'redacted' => $token->redacted,
			'primary'  => (bool) $token->primary,
		);
	}

	/**
	 * Get the schema for Payment Tokens.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'payment-token',
			'type'       => 'object',
			'properties' => array(
				'id'       => array(
					'description' => __( 'The unique id for this token.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'gateway'  => array(
					'description' => __( 'The gateway slug for this token.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'enum'        => array_map( function ( $gateway ) { return $gateway->get_slug(); }, \ITE_Gateways::handles( 'tokenize' ) ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'writeonly'   => true,
					'required'    => true,
				),
				'label'    => array(
					'description' => __( 'The user-provided label for this token.', 'it-l10n-ithemes-exchange' ),
					'oneOf'       => array(
						array(
							'type'       => 'object',
							'context'    => array( 'view', 'edit', 'embed' ),
							'properties' => array(
								'raw'      => array(
									'type'        => 'string',
									'context'     => array( 'edit' ),
									'description' => __( 'The raw user provided label for the payment token.', 'it-l10n-ithemes-exchange' ),
								),
								'rendered' => array(
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),
									'description' => __( 'The label for the payment token.', 'it-l10n-ithemes-exchange' ),
								),
							),
						),
						array( 'type' => 'string' )
					)
				),
				'redacted' => array(
					'description' => __( 'The redacted form of the underlying payment source.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'primary'  => array(
					'description' => __( 'Whether this is the primary payment token for this customer.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => false,
				),
			)
		);
	}
}