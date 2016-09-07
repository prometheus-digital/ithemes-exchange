<?php
/**
 * Load the Session model.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Session_Model
 *
 * @property string         $ID
 * @property string         $cart_id
 * @property \WP_User       $customer
 * @property array          $data
 * @property \DateTime      $expires_at
 * @property-read \DateTime $created_at
 * @property-read \DateTime $updated_at
 */
class ITE_Session_Model extends \IronBound\DB\Model {

	protected static $_cache = false;

	public function get_pk() {
		return $this->ID;
	}

	protected function _access_data( $data ) {
		return $data ? unserialize( $data ) : array();
	}

	protected function _mutate_data( $data ) {
		return serialize( $data );
	}

	protected static function get_table() {
		return static::$_db_manager->get( 'ite-sessions' );
	}
}