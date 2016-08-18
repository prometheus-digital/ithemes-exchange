<?php
/**
 * Deprecated Meta Handler.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Transaction_Deprecated_Meta
 */
class ITE_Transaction_Deprecated_Meta {

	/** @var bool */
	private $doing_meta_update = false;

	/** @var bool */
	private $doing_attribute_update = false;

	/** @var array */
	private $meta_to_attributes = array();

	/** @var array */
	private $attributes_to_meta = array();

	/** @var array */
	private $updating_cart_object = array();

	/**
	 * ITE_Transaction_Deprecated_Meta constructor.
	 */
	public function __construct() {
		add_action( 'update_post_meta', array( $this, 'detect_cart_object_meta_update' ), 10, 4 );
		add_action( 'updated_post_meta', array( $this, 'on_meta_update' ), 10, 4 );
		IT_Exchange_Transaction::updated( array( $this, 'on_attribute_update' ) );
	}

	/**
	 * Add a deprecated pair of meta key to model attribute.
	 *
	 * @since 1.36.0
	 *
	 * @param string $meta_key
	 * @param string $model_attribute
	 *
	 * @return $this
	 */
	public function add_pair( $meta_key, $model_attribute ) {
		$this->meta_to_attributes[ $meta_key ]        = $model_attribute;
		$this->attributes_to_meta[ $model_attribute ] = $meta_key;

		return $this;
	}

	/**
	 * Detect the cart object being updated.
	 *
	 * This has to be a two step process to detect that a change is about to be detected, and then detect
	 * after the change has been successfully made.
	 *
	 * @since 1.36.0
	 *
	 * @param int    $meta_id
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 */
	public function detect_cart_object_meta_update( $meta_id, $object_id, $meta_key, $meta_value ) {

		if ( get_post_type( $object_id ) !== 'it_exchange_tran' ) {
			return;
		}

		if ( $meta_key !== '_it_exchange_cart_object' ) {
			return;
		}

		$this->updating_cart_object[ $object_id ] = get_post_meta( $object_id, '_it_exchange_cart_object', true );
	}

	/**
	 * When a meta value is updated, update the corresponding model attribute.
	 *
	 * @since 1.36.0
	 *
	 * @param int    $meta_id
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 */
	public function on_meta_update( $meta_id, $object_id, $meta_key, $meta_value ) {

		if ( $this->doing_meta_update ) {
			return;
		}

		if ( get_post_type( $object_id ) !== 'it_exchange_tran' ) {
			return;
		}

		if ( $meta_key === '_it_exchange_cart_object' && isset( $this->updating_cart_object[ $object_id ] ) ) {
			$this->handle_cart_object_update( $object_id, $meta_value, $this->updating_cart_object[ $object_id ] );

			return;
		}

		if ( ! isset( $this->meta_to_attributes[ $meta_key ] ) ) {
			return;
		}

		$this->doing_attribute_update = true;

		$transaction = IT_Exchange_Transaction::get( $object_id );
		$attribute   = $this->meta_to_attributes[ $meta_key ];

		$transaction->$attribute = $meta_value;
		$transaction->save();

		$this->doing_attribute_update = false;
	}

	/**
	 * Handle a cart object being updated.
	 *
	 * @since 1.36.0
	 *
	 * @param int      $ID
	 * @param stdClass $new
	 * @param stdClass $old
	 */
	private function handle_cart_object_update( $ID, $new, $old ) {

		$this->doing_attribute_update = true;

		$transaction = it_exchange_get_transaction( $ID );

		if ( $new->total != $old->total ) {
			$transaction->set_attribute( 'total', $new->total );
		}

		if ( $new->sub_total != $old->sub_total ) {
			$transaction->set_attribute( 'subtotal', $new->sub_total );
		}

		$repo = new ITE_Line_Item_Transaction_Repository( new ITE_Line_Item_Repository_Events(), $transaction );

		if ( count( $new->products ) === count( $old->products ) ) {
			foreach ( $new->products as $product_cart_id => $cart_product ) {
				if ( $cart_product == $old->products[ $product_cart_id ] ) {
					continue;
				}

				$item = $transaction->get_item( 'product', $product_cart_id );

				if ( ! $item instanceof ITE_Cart_Product ) {
					continue;
				}

				$save = $item->update_from_cart_object( $cart_product );

				if ( $save ) {
					_doing_it_wrong(
						'it_exchange_update_transaction_cart_object', "Don't modify cart products via cart object.", '1.36.0'
					);
					$repo->save( $item );
				}
			}
		} elseif ( count( $new->products ) < count( $old->products ) ) {
			foreach ( $old->products as $product_cart_id => $cart_product ) {
				if ( ! isset( $new->products[ $product_cart_id ] ) ) {

					$item = $repo->get( 'product', $product_cart_id );

					if ( $item ) {
						_doing_it_wrong(
							'it_exchange_update_transaction_cart_object', "Don't delete cart products via cart object.", '1.36.0'
						);
						$repo->delete( $item );
					}
				}
			}
		} else {
			foreach ( $new->products as $product_cart_id => $cart_product ) {
				$item = new ITE_Cart_Product(
					$product_cart_id,
					new ITE_Array_Parameter_Bag( array_intersect_key( $cart_product, array_flip( array(
						'itemized_data',
						'additional_data',
						'product_id',
						'product_name',
						'product_cart_id',
						'count',
						'itemized_hash',
					) ) ) ),
					new ITE_Array_Parameter_Bag( array(
						'total'        => $cart_product['product_subtotal'],
						'amount'       => $cart_product['product_base_price'],
						'name'         => $cart_product['product_name'],
						'quantity'     => $cart_product['quantity'],
						'summary_only' => false,
					) )
				);

				_doing_it_wrong(
					'it_exchange_update_transaction_cart_object', "Don't add cart products via cart object.", '1.36.0'
				);

				$repo->save( $item );
			}
		}

		$transaction->save();

		$this->doing_attribute_update = false;
	}

	/**
	 * When a transaction is saved, update any corresponding meta values.
	 *
	 * @since 1.36.0
	 *
	 * @param \IronBound\WPEvents\GenericEvent $event
	 */
	public function on_attribute_update( \IronBound\WPEvents\GenericEvent $event ) {

		if ( $this->doing_attribute_update ) {
			return;
		}

		$this->doing_meta_update = true;

		$changed = $event->get_argument( 'changed' );

		$cart_object = $event->get_subject()->cart_details;
		$update_co   = false;

		if ( isset( $changed['total'] ) ) {
			$cart_object->total = $changed['total'];
			$update_co          = true;
		}

		if ( isset( $changed['subtotal'] ) ) {
			$cart_object->sub_total = $changed['subtotal'];
			$update_co              = true;
		}

		if ( $update_co ) {
			it_exchange_update_transaction_cart_object( $event->get_subject(), $cart_object );
		}

		foreach ( $changed as $field => $new_value ) {

			if ( ! isset( $this->attributes_to_meta[ $field ] ) ) {
				continue;
			}

			update_post_meta( $event->get_subject()->ID, $this->attributes_to_meta[ $field ], $new_value );
		}

		$this->doing_meta_update = false;
	}

}