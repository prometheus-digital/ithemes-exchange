<?php
/**
 * Reusable Page Query Arg definition.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Fields;

use Doctrine\Common\Collections\Criteria;
use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Class PageQueryArg
 *
 * @package iThemes\Exchange\REST\Fields
 */
class PageQueryArg implements QueryArg {

	/**
	 * @inheritDoc
	 */
	public function get_attribute() { return 'page'; }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return array(
			'description' => __( 'Current page of the collection.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'integer',
			'default'     => 1,
			'minimum'     => 1,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function scope_can_use( AuthScope $scope, $value = '' ) {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function is_valid( $value ) {
		return true; // handled by schema.
	}

	/**
	 * @inheritDoc
	 */
	public function add_criteria( Criteria $criteria, $value, array $all_query_args ) {
		$per_page = isset( $all_query_args['per_page'] ) ? $all_query_args['per_page'] : get_option( 'posts_per_page' );

		$criteria->setFirstResult( $value * $per_page );
	}
}