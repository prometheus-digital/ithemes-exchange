<?php
/**
 * Activity actor factory.
 *
 * @since   1.34
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Activity_Actor_Factory
 */
class IT_Exchange_Txn_Activity_Actor_Factory {

	/**
	 * @var array
	 */
	private $types = array();

	/**
	 * Register an activity actor type.
	 *
	 * @since 1.34
	 *
	 * @param string   $type
	 * @param callable $function Function called to make the actor object. Passed activity ID.
	 *                           Or class name. If given class, class will be instantiated without any parameters.
	 *
	 * @return $this
	 */
	public function register( $type, $function ) {

		if ( ! is_callable( $function, false ) && ! class_exists( $function ) ) {
			throw new InvalidArgumentException( "Function for actor '{$type}' type is not callable." );
		}

		$this->types[ $type ] = $function;

		return $this;
	}

	/**
	 * Make an activity actor object.
	 *
	 * @since 1.34
	 *
	 * @param int $activity_post_id
	 *
	 * @return IT_Exchange_Txn_Activity_Actor|null
	 */
	public function make( $activity_post_id ) {

		$type = get_post_meta( $activity_post_id, '_actor_type', true );

		if ( ! $type || ! isset( $this->types[ $type ] ) ) {
			return null;
		}

		$function = $this->types[ $type ];

		if ( is_string( $function ) && class_exists( $function ) ) {
			$actor = new $function();
		} else {
			$actor = call_user_func( $function, $activity_post_id );
		}

		if ( $actor && ! $actor instanceof IT_Exchange_Txn_Activity_Actor ) {
			throw new UnexpectedValueException( "Actor for activity with ID '{$activity_post_id}', is not valid." );
		}

		return $actor;
	}
}