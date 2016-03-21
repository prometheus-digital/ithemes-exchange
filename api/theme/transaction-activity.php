<?php
/**
 * Contains the transaction activity theme API.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Theme_API_Transaction_Activity
 */
class IT_Theme_API_Transaction_Activity implements IT_Theme_API {

	/**
	 * @var IT_Exchange_Txn_Activity
	 */
	private $activity;

	/**
	 * IT_Theme_API_Transaction_Activity constructor.
	 */
	public function __construct() {
		if ( isset( $GLOBALS['it_exchange']['transaction-activity'] ) ) {
			$this->activity = $GLOBALS['it_exchange']['transaction-activity'];
		} else {
			$this->activity = null;
		}
	}

	/**
	 * @var array
	 */
	public $_tag_map = array(
		'description' => 'description',
		'actor'       => 'actor'
	);

	/**
	 * Get the API context.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	function get_api_context() {
		return 'transaction-activity';
	}

	/**
	 * Get the transaction activity item description.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function description( $options = array() ) {

		if ( ! $this->activity ) {
			return false;
		}

		if ( ! empty( $options['has'] ) ) {
			return trim( $this->activity->get_description() ) !== '';
		}

		return wpautop( $this->activity->get_description() );
	}

	/**
	 * Get the activity actor's name.
	 *
	 * @since 1.36
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function actor( $options = array() ) {

		if ( ! $this->activity ) {
			return false;
		}

		if ( ! empty( $options['has'] ) ) {
			return $this->activity->has_actor();
		}

		return $this->activity->get_actor()->get_name();
	}
}