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
	 * @var WP_User|null
	 */
	private $user;

	/**
	 * IT_Exchange_Txn_Activity_User_Actor constructor.
	 *
	 * @param WP_User|null $user Pass null if user is deleted.
	 */
	public function __construct( WP_User $user = null ) {
		$this->user = $user;
	}

	/**
	 * Make a user actor from an activity ID.
	 *
	 * This is used as a callback from the actor factory, it should not be called directly.
	 *
	 * @internal
	 *
	 * @param int $activity_id
	 *
	 * @return IT_Exchange_Txn_Activity_User_Actor
	 */
	public static function make( $activity_id ) {

		$user_id = get_post_meta( $activity_id, '_actor_user_id', true );

		if ( ! is_numeric( $user_id ) ) {
			return null;
		}

		$user = get_user_by( 'id', $user_id );

		if ( ! $user instanceof WP_User ) {
			$user = null;
		}

		return new self( $user );
	}

	/**
	 * Get the actor's name.
	 *
	 * @since 1.34
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->user ? $this->user->display_name : __( 'Deleted User', 'it-l10n-ithemes-exchange' );
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

		if ( ! $this->user ) {
			return '';
		}

		if ( ! function_exists( 'get_avatar_url' ) ) {
			return '';
		}

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
		return $this->user ? get_edit_user_link( $this->user->ID ) : '';
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

	/**
	 * Convert the actor to an array of data.
	 *
	 * Substitute for jsonSerialize because 5.2 ;(
	 *
	 * @since 1.34
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'name' => $this->get_name(),
			'icon' => $this->get_icon_url( 48 ),
			'url'  => $this->get_detail_url()
		);
	}
}