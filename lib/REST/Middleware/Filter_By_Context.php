<?php
/**
 * Filter the response by context.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;

use iThemes\Exchange\REST\Helpers\ContextFilterer;
use iThemes\Exchange\REST\Request;

/**
 * Class Filter_By_Context
 *
 * @package iThemes\Exchange\REST\Middleware
 */
class Filter_By_Context implements Middleware {

	/** @var ContextFilterer */
	private $context_filterer;

	/**
	 * Filter_By_Context constructor.
	 *
	 * @param ContextFilterer $context_filterer
	 */
	public function __construct( ContextFilterer $context_filterer ) { $this->context_filterer = $context_filterer; }

	/**
	 * @inheritDoc
	 */
	public function handle( Request $request, Delegate $next ) {

		$response = $next->next( $request );

		if ( is_wp_error( $response ) || ! $response->get_data() ) {
			return $response;
		}

		$data  = $response->get_data();
		$route = $request->get_matched_route_controller();

		$schema  = $route->get_schema();
		$context = $request['context'] ?: 'view';

		if ( is_array( $data ) && \ITUtility::is_associative_array( $data ) ) {

			$data = $this->context_filterer->filter( $data, $context, $schema );

			$response->set_data( $data );
		} elseif ( is_array( $data ) ) {

			$filtered = array();

			foreach ( $data as $i => $item ) {
				$filtered[ $i ] = $this->context_filterer->filter( $item, $context, $schema );
			}

			$response->set_data( $filtered );
		}

		return $response;
	}
}
