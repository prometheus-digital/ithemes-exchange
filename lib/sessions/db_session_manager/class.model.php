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

	public function get_pk() {
		return $this->ID;
	}

	/**
	 * @inheritDoc
	 */
	protected static function boot() {
		parent::boot();

		static::updated( function ( \IronBound\WPEvents\GenericEvent $event ) {

			/** @var ITE_Session_Model $model */
			$model = $event->get_subject();
			$changed = $event->get_argument('changed');

			if ( ! empty( $changed['cart_id'] ) ) {
				wp_cache_delete( $model->get_pk(), $model::get_cache_group() . '-cart-id' );
			}
		} );
	}


	/**
	 * Retrieve a session by cart ID.
	 *
	 * @since 1.36.0
	 *
	 * @param string $cart_id
	 *
	 * @return \ITE_Session_Model|null
	 */
	public static function from_cart_id( $cart_id ) {

		$id = wp_cache_get( $cart_id, static::get_cache_group() . '-cart-id' );

		if ( ! $id ) {
			$model = self::query()->where( 'cart_id', '=', $cart_id )->first();

			if ( ! $model ) {
				return null;
			}

			wp_cache_set( $cart_id, $model->ID, static::get_cache_group() . '-cart-id' );
		} else {
			$model = static::get( $id );

			if ( ! $model || $model->cart_id !== $cart_id ) {
				wp_cache_delete( $cart_id, static::get_cache_group() . '-cart-id' );

				return static::from_cart_id( $cart_id );
			}
		}

		return $model;
	}

	/**
	 * Find the best model for a customer.
	 *
	 * @since 1.36.0
	 *
	 * @param \IT_Exchange_Customer $customer
	 *
	 * @return ITE_Session_Model|null
	 */
	public static function find_best_for_customer( IT_Exchange_Customer $customer ) {

		$session = ITE_Session_Model::query()
		                            ->and_where( 'customer', '=', $customer->id )
		                            ->order_by( 'cart_id', 'DESC' )
		                            ->order_by( 'updated_at', 'DESC' )
		                            ->take( 1 )
		                            ->first();

		return $session ?: null;
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