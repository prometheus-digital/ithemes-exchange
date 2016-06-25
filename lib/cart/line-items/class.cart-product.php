<?php
/**
 * Cart Product line item.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Cart_Product
 */
class ITE_Cart_Product implements ITE_Aggregate_Line_Item, ITE_Taxable_Line_Item, ITE_Line_Item_Repository_Aware {

	/**
	 * @var ITE_Aggregatable_Line_Item[]
	 */
	private $aggregate = array();

	/**
	 * @var array
	 */
	private $itemized = array();

	/**
	 * @var array
	 */
	private $additional = array();

	/**
	 * @var ITE_Parameter_Bag
	 */
	private $bag;

	/**
	 * @var IT_Exchange_Product
	 */
	private $product;

	/**
	 * @var ITE_Tax_Line_Item[]
	 */
	private $taxes = array();

	/**
	 * @var ITE_Line_Item_Repository
	 */
	private $repository;

	/**
	 * ITE_Cart_Product constructor.
	 *
	 * @param \IT_Exchange_Product $product
	 * @param int                  $quantity
	 */
	public function __construct( IT_Exchange_Product $product, $quantity = 1 ) {
		$this->bag     = new ITE_Array_Parameter_Bag();
		$this->product = $product;

		$this->set_quantity( $quantity );
		$this->set_param( 'product_id', $product->ID );
	}

	/**
	 * Check if certain itemized data exists.
	 *
	 * @since 1.36
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has_itemized_data( $key ) {
		return array_key_exists( $key, $this->itemized );
	}

	/**
	 * Get itemized data.
	 *
	 * Itemized data affects the cart ID. Itemized data can be used to differentiate two different products,
	 * with the same product ID in the cart.
	 *
	 * For example, Variants uses this to store the variant hash so more than one variant product can be purchased in
	 * the same transaction.
	 *
	 * @since 1.36
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 *
	 * @throws OutOfBoundsException If no itemized data exists by the given key.
	 */
	public function get_itemized_data( $key = '' ) {

		if ( ! $key ) {
			return $this->itemized;
		}

		if ( ! $this->has_itemized_data( $key ) ) {
			throw new OutOfBoundsException( "No itemized data found for key '$key'." );
		}

		return $this->itemized[ $key ];
	}

	/**
	 * Set itemized data.
	 *
	 * @since 1.36
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function set_itemized_data( $key, $value ) {
		$this->itemized[ $key ] = $value;

		self::generate_cart_product_id( $this );

		return true;
	}

	/**
	 * Remove itemized data.
	 *
	 * Will not error if the given key does not exist.
	 *
	 * @since 1.36
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function remove_itemized_data( $key ) {

		if ( $this->has_itemized_data( $key ) ) {
			unset( $this->itemized[ $key ] );

			self::generate_cart_product_id( $this );

			return true;
		}

		return false;
	}

	/**
	 * Check if certain additional data exists.
	 *
	 * @since 1.36
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has_additional_data( $key ) {
		return array_key_exists( $key, $this->additional );
	}

	/**
	 * Get additional data.
	 *
	 * Additional data does not affect the cart product ID.
	 *
	 * @since 1.36
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 *
	 * @throws OutOfBoundsException If no itemized data exists by the given key.
	 */
	public function get_additional_data( $key = '' ) {

		if ( ! $key ) {
			return $this->additional;
		}

		if ( ! $this->has_additional_data( $key ) ) {
			throw new OutOfBoundsException( "No additional data found for key '$key'." );
		}

		return $this->additional[ $key ];
	}

	/**
	 * Set additional data.
	 *
	 * @since 1.36
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function set_additional_data( $key, $value ) {
		$this->additional[ $key ] = $value;

		return true;
	}

	/**
	 * Remove additional data.
	 *
	 * Will not error if the given key does not exist.
	 *
	 * @since 1.36
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function remove_additional_data( $key ) {

		if ( $this->has_itemized_data( $key ) ) {
			unset( $this->additional[ $key ] );

			self::generate_cart_product_id( $this );

			return true;
		}

		return false;
	}

	/**
	 * Retrieve the product being purchased.
	 *
	 * @since 1.36
	 *
	 * @return \IT_Exchange_Product
	 */
	public function get_product() {
		return $this->product;
	}

	/**
	 * Set the product quantity.
	 *
	 * @since 1.36
	 *
	 * @param int $quantity
	 */
	public function set_quantity( $quantity ) {

		$quantity = max( 1, $quantity );
		$max      = it_exchange_get_max_product_quantity_allowed( $this->get_product(), $this->get_id() );

		if ( $max !== '' && $quantity > $max ) {
			$quantity = $max;
		}

		$this->set_param( 'count', $quantity );
	}

	/**
	 * @inheritDoc
	 */
	public function is_tax_exempt() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_tax_code() {
		return 0;
	}

	/**
	 * @inheritDoc
	 */
	public function get_taxable_amount() {

		$base = it_exchange_get_cart_product_base_price( $this->get_data_to_save(), false );

		foreach ( $this->aggregate as $aggregate ) {
			if ( $aggregate instanceof ITE_Taxable_Line_Item ) {
				$base += $aggregate->get_taxable_amount();
			}
		}

		return $base;
	}

	/**
	 * @inheritDoc
	 */
	public function get_taxes() {
		return $this->taxes;
	}

	/**
	 * @inheritDoc
	 */
	public function add_tax( ITE_Tax_Line_Item $tax ) {
		$this->taxes[] = $tax;
		$this->add_item( $tax );

		foreach ( $this->get_line_items() as $item ) {
			if ( $item instanceof ITE_Taxable_Line_Item ) {
				$item->add_tax( $tax );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function remove_tax( $id ) {

		$found = false;

		foreach ( $this->taxes as $i => $tax ) {
			if ( $tax->get_id() === $id ) {
				unset( $this->taxes[ $i ] );
				$found = true;

				break;
			}
		}

		if ( $found ) {
			reset( $this->taxes );
			$this->remove_item( 'tax', $id );
		}

		return $found;
	}

	/**
	 * @inheritDoc
	 */
	public function remove_all_taxes() {
		$this->taxes = array();
	}

	/**
	 * @inheritDoc
	 * @throws \UnexpectedValueException
	 */
	public function get_line_items() {

		if ( ! $this->repository ) {
			throw new UnexpectedValueException( sprintf(
				'Repository service not available. See %s.', __CLASS__ . '::set_line_item_repository'
			) );
		}

		return new ITE_Line_Item_Collection( $this->aggregate, $this->repository );
	}

	/**
	 * @inheritDoc
	 */
	public function has_primary() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_primary() {
		$clone            = clone $this;
		$clone->aggregate = array();

		return $clone;
	}

	/**
	 * @inheritDoc
	 */
	public function add_item( ITE_Aggregatable_Line_Item $item ) {

		if ( $item instanceof ITE_Tax_Line_Item && ! in_array( $item, $this->get_taxes(), true ) ) {
			return $this->add_tax( $item );
		}

		$item->set_aggregate( $this );
		$this->aggregate[] = $item;
	}

	/**
	 * @inheritDoc
	 */
	public function remove_item( $type, $id ) {

		$found = false;

		foreach ( $this->aggregate as $i => $item ) {
			if ( $item->get_type() === $type && $item->get_id() === $id ) {
				unset( $this->aggregate[ $i ] );
				$found = true;

				break;
			}
		}

		reset( $this->aggregate );

		return $found;
	}

	/**
	 * @inheritDoc
	 */
	public function get_id() {
		return $this->has_param( 'product_cart_id' ) ? $this->get_param( 'product_cart_id' ) : '';
	}

	/**
	 * @inheritDoc
	 */
	protected function set_id( $id ) {
		$this->set_param( 'product_cart_id', $id );
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return $this->get_param( 'product_name' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {

	}

	/**
	 * @inheritDoc
	 */
	public function get_quantity() {
		return (int) $this->get_param( 'count' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_amount() {

		$base = $this->get_product()->get_feature( 'base-price' );
		$base = apply_filters( 'it_exchange_get_cart_product_base_price', $base, $this->get_data_to_save(), false );

		foreach ( $this->aggregate as $aggregate ) {
			if ( ! $aggregate->is_summary_only() ) {
				$base += $aggregate->get_amount();
			}
		}

		return $base;
	}

	/**
	 * @inheritDoc
	 */
	public final function get_type( $label = false ) {
		return $label ? __( 'Product', 'it-l10n-ithemes-exchange' ) : 'product';
	}

	/**
	 * @inheritDoc
	 */
	public function is_summary_only() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function persist( ITE_Line_Item_Repository $repository ) {
		$repository->save( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function has_param( $param ) {
		return $this->bag->has_param( $param );
	}

	/**
	 * @inheritDoc
	 */
	public function get_param( $param ) {
		return $this->bag->get_param( $param );
	}

	/**
	 * @inheritDoc
	 */
	public function get_params() {
		return $this->bag->get_params();
	}

	/**
	 * @inheritDoc
	 */
	public function set_param( $param, $value, $deferred = false ) {
		return $this->bag->set_param( $param, $value, $deferred );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_param( $param, $deferred = false ) {
		return $this->bag->remove_param( $param, $deferred );
	}

	/**
	 * @inheritDoc
	 */
	public function persist_deferred_params() {
		$this->bag->persist_deferred_params();
	}

	/**
	 * Generate the cart product ID.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Cart_Product $product
	 */
	public static function generate_cart_product_id( ITE_Cart_Product $product ) {
		$product->set_id( $product->get_param( 'product_id' ) . '-' . md5( serialize( $product->get_itemized_data() ) ) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_to_save( \ITE_Line_Item_Repository $repository = null ) {
		$data                    = $this->get_params();
		$data['itemized_data']   = serialize( $this->get_itemized_data() );
		$data['additional_data'] = serialize( array() ); // TODO: Add proper support for additional_data
		$data['itemized_hash']   = md5( $data['itemized_data'] );

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public static function from_data( $id, array $data, ITE_Line_Item_Repository $repository ) {

		$item = new self( it_exchange_get_product( $data['product_id'] ), $data['count'] );

		$item->itemized = unserialize( $data['itemized_data'] );
		unset( $data['itemized_data'], $data['itemized_hash'] );

		foreach ( $data as $key => $val ) {
			$item->bag->set_param( $key, $val );
		}

		return $item;
	}

	/**
	 * @inheritDoc
	 */
	public function set_line_item_repository( ITE_Line_Item_Repository $repository ) {
		$this->repository = $repository;
	}
}