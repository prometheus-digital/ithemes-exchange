<?php
/**
 * Transactions Endpoint.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Transaction;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Transactions
 *
 * @package iThemes\Exchange\REST\Route\v1\Transaction
 */
class Transactions extends Base implements Getable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Transaction constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\v1\Transaction\Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$page     = $request['page'];
		$per_page = $request['per_page'];

		$args = array(
			'customer_id'    => $request['customer'],
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'parent'         => $request['parent'],
			'orderby'        => 'ID',
		);

		if ( $request['order_number'] ) {
			$args['ID'] = (int) preg_replace( '/\D/', '', $request['order_number'] );
		}

		if ( $request['cleared_for_delivery'] !== null ) {
			$args['cleared'] = $request['cleared_for_delivery'];
		}

		if ( $request['search'] ) {
			$args['s'] = sanitize_text_field( $request['search'] );
		}

		$transactions = it_exchange_get_transactions( $args, $total );
		$data         = array();

		foreach ( $transactions as $transaction ) {
			$serialized = $this->serializer->serialize( $transaction );
			$links      = $this->serializer->generate_links( $transaction, $this->get_manager() );

			foreach ( $links as $rel => $rel_links ) {
				$serialized['_links'][ $rel ] = array();

				foreach ( $rel_links as $link ) {
					$serialized['_links'][ $rel ][] = $link;
				}
			}

			$data[] = $serialized;
		}


		$total_pages         = $total ? ceil( $total / $per_page ) : 0;
		$base_pagination_url = add_query_arg(
			$request->get_query_params(),
			\iThemes\Exchange\REST\get_rest_url( $this, array( $request->get_url_params() ) )
		);

		$response = new \WP_REST_Response( $data );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', $total_pages );

		$first_link = add_query_arg( 'page', 1, $base_pagination_url );
		$response->link_header( 'first', $first_link );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $total_pages ) {
				$prev_page = $total_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base_pagination_url );
			$response->link_header( 'prev', $prev_link );
		}

		if ( $total_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base_pagination_url );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {

		if ( $request['customer'] && ! $scope->can( 'edit_user', $request['customer'] ) ) {
			return Errors::cannot_list(
				__( "Sorry, you are not allowed to list that customer's transactions.", 'it-l10n-ithemes-exchange' )
			);
		}

		if ( ! $request['customer'] && ! $scope->can( 'list_it_transactions' ) ) {
			return Errors::cannot_list(
				__( "Sorry, you are not allowed to list other customer's transactions.", 'it-l10n-ithemes-exchange' )
			);
		}

		if ( $request['context'] === 'edit' && ! $scope->can( 'edit_others_it_transactions' ) ) {
			return Errors::forbidden_context( 'edit' );
		}

		if ( $request['parent'] ) {
			$parent = it_exchange_get_transaction( $request['parent'] );

			if ( $parent && ! $scope->can( 'edit_it_transaction', $parent ) ) {
				return Errors::cannot_use_query_var( 'parent' );
			} elseif ( ! $parent && $scope->can( 'edit_others_it_transactions' ) ) {
				return Errors::invalid_query_var_usage( 'parent' );
			} elseif ( ! $parent ) {
				return Errors::cannot_use_query_var( 'parent' );
			}
		}

		if ( $request['method_id'] && ! $scope->can( 'list_it_transactions' ) ) {
			return Errors::cannot_use_query_var( 'method_id' );
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
	public function get_path() { return 'transactions/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array(
			'page'                 => array(
				'description' => __( 'Current page of the collection.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'per_page'             => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'customer'             => array(
				'description' => __( 'The customer whose transactions should be retrieved.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => 0,
			),
			'parent'               => array(
				'description' => __( 'Retrieve child transactions of a given parent.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'integer',
				'default'     => null,
			),
			'order_number'         => array(
				'description' => __( 'Filter transactions by order number.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
			),
			'cleared_for_delivery' => array(
				'description' => __( 'Only return transactions that have been cleared for delivery.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'boolean',
				'default'     => null,
			),
			'method'               => array(
				'description' => __( 'Filter by transaction method.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
				'enum'        => array_map( function ( $gateway ) { return $gateway->get_slug(); }, \ITE_Gateways::all() )
			),
			'method_id'            => array(
				'description' => __( 'Filter by method id.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
			),
			'search'               => array(
				'description' => __( 'Limit results to those matching a string.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'string',
				'minLength'   => 3,
				'maxLength'   => 300,
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}
