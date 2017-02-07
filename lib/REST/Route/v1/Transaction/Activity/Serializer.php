<?php
/**
 * Transaction Activity Serializer.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Transaction\Activity;

class Serializer {

	/**
	 * Serialize the transaction activity item.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Txn_Activity $activity
	 * @param \WP_REST_Request          $request
	 *
	 * @return array
	 */
	public function serialize( \IT_Exchange_Txn_Activity $activity, \WP_REST_Request $request ) {

		$factory = it_exchange_get_txn_activity_factory();
		$types   = $factory->get_types();

		if ( ! isset( $types[ $activity->get_type() ] ) ) {
			$label = ucfirst( $activity->get_type() );
		} else {
			$label = $types[ $activity->get_type() ]['label'];
		}

		$data = array(
			'id'          => $activity->get_ID(),
			'description' => $activity->get_description(),
			'type'        => array( 'slug' => $activity->get_type(), 'label' => $label ),
			'time'        => \iThemes\Exchange\REST\format_rfc339( $activity->get_time()->format( 'Y-m-d H:i:s' ) ),
			'is_public'   => $activity->is_public(),
			'actor'       => null,
		);

		if ( $activity->has_actor() ) {
			$data['actor'] = array(
				'name'       => $activity->get_actor()->get_name(),
				'type'       => $activity->get_actor()->get_type(),
				'icon_url'   => $activity->get_actor()->get_icon_url( $request['icon_size'] ),
				'detail_url' => $activity->get_actor()->get_detail_url(),
			);
		}

		return $data;
	}

	/**
	 * Get the activity item schema.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'transaction-activity',
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'The unique id for this activity item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'description' => array(
					'description' => __( 'The text of this activity item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'type'        => array(
					'description' => __( 'The activity item type.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
					'properties'  => array(
						'slug'  => array(
							'description' => __( 'The activity item type slug.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'enum'        => array_keys( it_exchange_get_txn_activity_factory()->get_types() ),
							'context'     => array( 'view', 'edit' ),
						),
						'label' => array(
							'description' => __( 'The activity item type label.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'enum'        => wp_list_pluck( it_exchange_get_txn_activity_factory()->get_types(), 'label' ),
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					)
				),
				'time'        => array(
					'description' => __( 'The time the activity occurred, as GMT.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'is_public'   => array(
					'description' => __( 'Whether the activity item is public to the customer..', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'actor'       => array(
					'description' => __( 'The activity item actor, if any.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
					'properties'  => array(
						'name'     => array(
							'description' => __( 'The name of the actor.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'type'     => array(
							'description' => __( 'The type of the actor.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'enum'        => it_exchange_get_txn_activity_actor_factory()->get_types(),
							'context'     => array( 'view', 'edit' ),
						),
						'icon_url' => array(
							'description' => __( 'The icon representing the the actor.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'format'      => 'url',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
						'detail_url' => array(
							'description' => __( 'An admin URL containing more information about the actor.', 'it-l10n-ithemes-exchange' ),
							'type'        => 'string',
							'format'      => 'url',
							'context'     => array( 'view', 'edit', 'embed' ),
						),
					)
				),
			)
		);
	}
}
