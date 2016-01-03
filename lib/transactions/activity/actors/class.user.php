<?php
/**
 * Contains user actor class.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Activity_User_Actor
 */
class IT_Exchange_Txn_Activity_User_Actor implements IT_Exchange_Txn_Activity_Actor {

	/**
	 * @var WP_User
	 */
	private $user;

	/**
	 * IT_Exchange_Txn_Activity_User_Actor constructor.
	 *
	 * @param WP_User $user
	 */
	public function __construct( WP_User $user ) {
		$this->user = $user;
	}

	/**
	 * Get the actor's name.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->user->display_name;
	}

	/**
	 * Get the URL to the icon representing this actor.
	 *
	 * @since 1.34
	 *
	 * @param int $size Suggested size. Do not rely on this value.
	 *
	 * @return string
	 */
	public function get_icon_url( $size ) {
		return get_avatar_url( $this->user->ID, array(
			'size' => $size
		) );
	}

	/**
	 * Get the URL to view details about this actor.
	 *
	 * This could be a user's profile, for example.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_detail_url() {
		return get_edit_user_link( $this->user->ID );
	}

	/**
	 * Get the type of this actor.
	 *
	 * Ex: 'user', 'customer'.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_type() {
		return 'user';
	}

	/**
	 * Attach this actor to an activity item.
	 *
	 * @since 1.34
	 *
	 * @param IT_Exchange_Txn_Activity $activity
	 *
	 * @return self
	 */
	public function attach( IT_Exchange_Txn_Activity $activity ) {
		update_post_meta( $activity->get_ID(), '_actor_user_id', $this->user->ID );

		return $this;
	}
}