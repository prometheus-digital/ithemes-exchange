<?php
/**
 * Token Serializer.
 *
 * @since   2.0.0
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
	 * @since 2.0.0
	 *
	 * @param \ITE_Payment_Token $token
	 *
	 * @return array
	 */
	public function serialize( \ITE_Payment_Token $token ) {
		$data = array(
			'id'       => $token->get_pk(),
			'gateway'  => array(
				'slug'  => $token->get_raw_attribute( 'gateway' ),
				'label' => $token->gateway ? $token->gateway->get_name() : $token->get_raw_attribute( 'gateway' ),
			),
			'label'    => array(
				'raw'      => $token->label,
				'rendered' => $token->get_label()
			),
			'primary'  => (bool) $token->primary,
			'type'     => array(
				'slug'  => $token->type,
				'label' => $token::get_token_type_label( $token->type ),
			),
			'redacted' => $token->redacted,
		);

		if ( $token instanceof \ITE_Payment_Token_Card ) {
			$data['issuer'] = $token->get_brand();

			if ( (int) $token->get_expiration_month() ) {
				$data['expiration'] = array(
					'month'    => (int) $token->get_expiration_month(),
					'year'     => (int) $token->get_expiration_year(),
					'editable' => false,
				);

				if ( $token->gateway && ( $h = $token->gateway->get_handler_by_request_name( 'tokenize' ) ) ) {
					$data['expiration']['editable'] = $h instanceof \ITE_Update_Payment_Token_Handler;
				}
			}
		} elseif ( $token instanceof \ITE_Payment_Token_Bank_Account ) {
			$data['issuer'] = $token->get_bank_name();
		}

		return $data;
	}

	/**
	 * Get the schema for Payment Tokens.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'payment-token',
			'type'       => 'object',
			'properties' => array(
				'id'         => array(
					'description' => __( 'The unique id for this token.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'gateway'    => array(
					'description' => __( 'The gateway slug for this token.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'required'    => true,
					'writeonly'   => true,
					'oneOf'       => array(
						array(
							'type' => 'string',
							'enum' => array_map( function ( $gateway ) { return $gateway->get_slug(); }, \ITE_Gateways::handles( 'tokenize' ) ),
						),
						array(
							'type'       => 'object',
							'properties' => array(
								'slug'  => array(
									'description' => __( 'The gateway slug.', 'it-l10n-ithemes-exchange' ),
									'type'        => 'string',
									'enum'        => array_map( function ( $gateway ) { return $gateway->get_slug(); }, \ITE_Gateways::handles( 'tokenize' ) ),
								),
								'label' => array(
									'description' => __( 'The gateway label.', 'it-l10n-ithemes-exchange' ),
									'type'        => 'string',
								),
							),
						),
					),
				),
				'label'      => array(
					'description' => __( 'The user-provided label for this token.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit', 'embed' ),
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
				'primary'    => array(
					'description' => __( 'Whether this is the primary payment token for this customer.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit', 'embed' ),
					'default'     => false,
				),
				'type'       => array(
					'description' => __( 'The payment token type.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'slug'  => array(
							'description' => __( 'The token type slug.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'label' => array(
							'description' => __( 'The token type label.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					)
				),
				'redacted'   => array(
					'description' => __( 'The redacted form of the underlying payment source.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'issuer'     => array(
					'description' => __( 'The card issuer or bank name.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'expiration' => array(
					'description' => __( 'The card expiration date.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit', 'embed' ),
					'type'        => 'object',
					'properties'  => array(
						'year'     => array(
							'type'        => 'integer',
							'description' => __( 'Card expiration year', 'it-l10n-ithemes-exchange' ),
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'month'    => array(
							'type'        => 'integer',
							'description' => __( 'Card expiration month', 'it-l10n-ithemes-exchange' ),
							'min'         => 1,
							'max'         => 12,
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'editable' => array(
							'type'        => 'boolean',
							'description' => __( 'Is the expiration editable.', 'it-l10n-ithemes-exchange' ),
							'default'     => false,
							'readonly'    => true,
							'context'     => array( 'edit' ),
						)
					),
				),
			)
		);
	}
}
