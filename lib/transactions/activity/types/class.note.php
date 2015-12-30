<?php
/**
 * Contains activity note class.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Note_Activity
 */
class IT_Exchange_Txn_Note_Activity extends IT_Exchange_Txn_AbstractActivity {

	/**
	 * Retrieve a note activity item.
	 *
	 * @since 1.34
	 *
	 * @param int $id
	 *
	 * @return IT_Exchange_Txn_Note_Activity|null
	 */
	public static function get( $id ) {

		$post = get_post( $id );

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		$actor   = null;
		$user_id = get_post_meta( $post->ID, '_actor_user_id', true );

		if ( $user_id ) {

			$user = get_user_by( 'id', $user_id );

			if ( $user instanceof WP_User ) {
				$actor = new IT_Exchange_Txn_Activity_User_Actor( $user );
			}
		} else {
			$transaction = it_exchange_get_transaction( $post->post_parent );

			$customer = it_exchange_get_transaction_customer( $transaction );

			if ( $customer instanceof IT_Exchange_Customer ) {
				$actor = new IT_Exchange_Txn_Activity_Customer_Actor( $customer );
			}
		}

		return new self( $post, $actor );
	}

	/**
	 * Get the type of the activity.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_type() {
		return 'note';
	}

	/**
	 * Is this activity public.
	 *
	 * The customer is notified for public activities.
	 *
	 * @since 1.34
	 *
	 * @return bool
	 */
	public function is_public() {

		if ( $this->has_actor() && $this->get_actor() instanceof IT_Exchange_Txn_Activity_Customer_Actor ) {
			return true;
		}

		return parent::is_public();
	}
}