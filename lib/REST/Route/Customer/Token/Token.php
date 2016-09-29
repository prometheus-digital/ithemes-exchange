<?php
/**
 * Single Token Route.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Customer\Token;

use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;

/**
 * Class Token
 * @package iThemes\Exchange\REST\Route\Customer\Token
 */
class Token implements Getable, Putable, Deletable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Token constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\Customer\Token\Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( \WP_REST_Request $request ) {

		$url_params = $request->get_url_params();
		$token      = \ITE_Payment_Token::get( $url_params['token_id'] );

		return new \WP_REST_Response( $this->serializer->serialize( $token ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permissions_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( \WP_REST_Request $request ) {

		$url_params = $request->get_url_params();
		$token      = \ITE_Payment_Token::get( $url_params['token_id'] );

		$token->label = $request['label'];

		if ( ! $token->primary && $request['primary'] ) {
			$saved = $token->make_primary();
		} elseif ( $token->primary && ! $request['primary'] ) {
			try {
				$saved = $token->make_non_primary();
			}
			catch ( \InvalidArgumentException $e ) {
				return new \WP_Error(
					'it_exchange_rest_cannot_make_token_primary',
					__( 'The token could not be updated to primary.', 'it-l10n-ithemes-exchange' ),
					array( 'status' => \WP_Http::BAD_REQUEST )
				);
			}
		} else {
			$saved = $token->save();
		}

		if ( ! $saved ) {
			return new \WP_Error(
				'it_exchange_rest_cannot_update',
				__( 'The token could not be updated.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		return new \WP_REST_Response( $this->serializer->serialize( $token ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_put( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permissions_check( $request, $user );
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( \WP_REST_Request $request ) {

		$url_params = $request->get_url_params();

		if ( ! \ITE_Payment_Token::get( $url_params['token_id'] )->delete() ) {
			return new \WP_Error(
				'it_exchange_rest_cannot_delete',
				__( 'The token could not be deleted.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		return new \WP_REST_Response( '', \WP_Http::NO_CONTENT );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {
		return $this->permissions_check( $request, $user );
	}

	/**
	 * Perform a permissions check.
	 *
	 * @since 1.36.0
	 *
	 * @param \WP_REST_Request           $request
	 * @param \IT_Exchange_Customer|null $user
	 *
	 * @return bool|\WP_Error
	 */
	protected function permissions_check( \WP_REST_Request $request, \IT_Exchange_Customer $user = null ) {

		$url_params = $request->get_url_params();
		$token      = \ITE_Payment_Token::get( $url_params['token_id'] );

		if ( ! $token ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_payment_token',
				__( 'Invalid payment token.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::NOT_FOUND )
			);
		}

		if ( ! $token->customer || ! $user || $token->customer->ID !== $user->ID ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to access this payment token.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::FORBIDDEN )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return '(?P<token_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
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
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'gateway'  => array(
					'description' => __( 'The gateway slug for this token.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'enum'        => array_map( function ( $gateway ) { return $gateway->get_slug(); }, \ITE_Gateways::handles( 'tokenize' ) ),
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
					'required'    => true,
				),
				'label'    => array(
					'description' => __( 'The user-provided label for this token.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'redacted' => array(
					'description' => __( 'The redacted form of the underlying payment source.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'primary'  => array(
					'description' => __( 'Whether this is the primary payment token for this customer.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'default'     => false,
				),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function has_parent() { return true; }

	/**
	 * @inheritDoc
	 */
	public function get_parent() { return new Tokens( $this->serializer, new \ITE_Gateway_Request_Factory(), $this ); }
}