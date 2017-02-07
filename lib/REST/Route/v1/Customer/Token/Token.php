<?php
/**
 * Single Token Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Customer\Token;

use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Putable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\RouteObjectExpandable;

/**
 * Class Token
 *
 * @package iThemes\Exchange\REST\Route\v1\Customer\Token
 */
class Token extends Base implements Getable, Putable, Deletable, RouteObjectExpandable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Token constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\v1\Customer\Token\Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$token = $request->get_route_object( 'token_id' );

		return new \WP_REST_Response( $this->serializer->serialize( $token ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {
		if ( ( $r = $this->permissions_check( $request, $user ) ) !== true ) {
			return $r;
		}

		if ( ! user_can( $user->wp_user, 'it_read_payment_token', $request->get_route_object( 'token_id' ) ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to view this payment token.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_put( Request $request ) {

		/** @var \ITE_Payment_Token_Card|\ITE_Payment_Token_Bank_Account $token */
		$token = $request->get_route_object( 'token_id' );

		if ( $token instanceof \ITE_Payment_Token_Card && $request['expiration'] ) {

			$handler = $token->gateway->get_handler_by_request_name( 'tokenize' );

			if ( $handler instanceof \ITE_Update_Payment_Token_Handler ) {
				$update = array();

				if ( (int) $token->get_expiration_month() != $request['expiration']['month'] ) {
					$update['expiration_month'] = $request['expiration']['month'];
				}

				if ( (int) $token->get_expiration_year() != $request['expiration']['year'] ) {
					$update['expiration_year'] = $request['expiration']['year'];
				}

				if ( $update ) {
					$token = $handler->update_token( $token, $update );

					if ( ! $token ) {
						return new \WP_Error(
							'it_exchange_rest_cannot_update',
							__( 'The token could not be updated.', 'it-l10n-ithemes-exchange' ),
							array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
						);
					}
				}
			}
		}

		$label = is_array( $request['label'] ) ? $request['label']['raw'] : $request['label'];

		if ( $label ) {
			$token->label = $label;
		}

		if ( ! $token->primary && $request['primary'] ) {
			$saved = $token->make_primary();
		} elseif ( $token->primary && ! $request['primary'] ) {
			try {
				$saved = $token->make_non_primary();
			} catch ( \InvalidArgumentException $e ) {
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
	public function user_can_put( Request $request, \IT_Exchange_Customer $user = null ) {
		if ( ( $r = $this->permissions_check( $request, $user ) ) !== true ) {
			return $r;
		}

		if ( ! user_can( $user->wp_user, 'it_edit_payment_token', $request->get_route_object( 'token_id' ) ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to edit this payment token.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {

		\ITE_Payment_Token::get( $request->get_param( 'token_id', 'URL' ) )->delete();

		return new \WP_REST_Response( '', \WP_Http::NO_CONTENT );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, \IT_Exchange_Customer $user = null ) {
		if ( ( $r = $this->permissions_check( $request, $user ) ) !== true ) {
			return $r;
		}

		if ( ! user_can( $user->wp_user, 'it_delete_payment_token', $request->get_route_object( 'token_id' ) ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete this payment token.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Perform a permissions check.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 * @param \IT_Exchange_Customer|null     $user
	 *
	 * @return bool|\WP_Error
	 */
	protected function permissions_check( Request $request, \IT_Exchange_Customer $user = null ) {

		$token = $request->get_route_object( 'token_id' );

		if ( ! $token ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_payment_token',
				__( 'Invalid payment token.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::NOT_FOUND )
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
	public function get_route_object_map() { return array( 'token_id' => 'ITE_Payment_Token::get' ); }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}
