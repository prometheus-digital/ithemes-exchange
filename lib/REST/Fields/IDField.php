<?php
/**
 * Reusable ID Field.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Fields;

use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Class IDField
 *
 * @package iThemes\Exchange\REST\Fields
 */
class IDField implements Field {

	/**
	 * @inheritDoc
	 */
	public function get_attribute() { return 'id'; }

	/**
	 * @inheritDoc
	 */
	public function get_schema() {
		return array(
			'description' => __( 'The unique id for this object.', 'it-l10n-ithemes-exchange' ),
			'type'        => 'integer',
			'context'     => array( 'view', 'edit', 'embed' ),
			'readonly'    => true,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function available_in_contexts() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function serialize( $object, array $query_args = array() ) {
		return $object->get_ID();
	}

	/**
	 * @inheritDoc
	 */
	public function update( $object, $new_value ) { return false; }

	/**
	 * @inheritDoc
	 */
	public function scope_can_set( AuthScope $scope, $new_value ) { return false; }
}