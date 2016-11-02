<?php
/**
 * Transactions Endpoint.
 *
 * @since   1.36.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\Transaction;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Transactions
 *
 * @package iThemes\Exchange\REST\Route\Transaction
 */
class Transactions extends Base implements Getable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Transaction constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\Transaction\Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$page     = $request['page'];
		$per_page = $request['per_page'];

		$transactions = it_exchange_get_transactions( array(
			'customer_id'    => $request['customer'],
			'posts_per_page' => $per_page,
			'paged'          => $page,
		), $total );

		$user = it_exchange_get_current_customer();
		$data = array();

		foreach ( $transactions as $transaction ) {
			$serialized = $this->serializer->serialize( $transaction, $user );
			$links      = $this->serializer->generate_links( $transaction, $this->get_manager(), $user );

			foreach ( $links as $rel => $rel_links ) {
				$serialized['_links'][ $rel ] = array();

				foreach ( $rel_links as $link ) {
					$serialized['_links'][ $rel ][] = $link;
				}
			}

			$data[] = $serialized;
		}


		$total_pages         = ceil( $total / $per_page );
		$base_pagination_url = add_query_arg(
			$request->get_query_params(),
			\iThemes\Exchange\REST\get_rest_url( $this, array( $request->get_url_params() ) )
		);

		$response = new \WP_REST_Response( $data );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / $total_pages ) );

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
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		if ( ! $user ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( 'Sorry, you must be logged-in to view transactions.', 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::UNAUTHORIZED )
			);
		}

		if ( ! $request['transaction_id'] && $request['customer'] !== $user->ID && ! user_can( $user->wp_user, 'list_it_transactions' ) ) {
			return new \WP_Error(
				'it_exchange_rest_forbidden_context',
				__( "Sorry, you are not allowed to list other customer's transactions.", 'it-l10n-ithemes-exchange' ),
				array( 'status' => \WP_Http::UNAUTHORIZED )
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
	public function get_path() { return 'transactions/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		return array(
			'page'     => array(
				'description'       => __( 'Current page of the collection.', 'it-l10n-ithemes-exchange' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
			),
			'per_page' => array(
				'description'       => __( 'Maximum number of items to be returned in result set.', 'it-l10n-ithemes-exchange' ),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'customer' => array(
				'description'       => __( 'The customer whose transactions should be retrieved.', 'it-l10n-ithemes-exchange' ),
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'rest_sanitize_request_arg',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}