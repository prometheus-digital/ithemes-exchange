<?php
/**
 * Cart Product line item.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Cart_Product
 */
class ITE_Cart_Product extends ITE_Line_Item implements ITE_Taxable_Line_Item, ITE_Discountable_Line_Item,
	ITE_Quantity_Modifiable_Item, ITE_Line_Item_Repository_Aware, ITE_Requires_Optionally_Supported_Features {

	/** @var ITE_Aggregatable_Line_Item[] */
	private $aggregatables = array();

	/** @var IT_Exchange_Product */
	private $product;

	/** @var ITE_Line_Item_Repository */
	private $repository;

	/**
	 * ITE_Cart_Product constructor.
	 *
	 * @param string             $id
	 * @param \ITE_Parameter_Bag $bag
	 * @param \ITE_Parameter_Bag $frozen
	 */
	public function __construct( $id, ITE_Parameter_Bag $bag, ITE_Parameter_Bag $frozen ) {
		parent::__construct( $id, $bag, $frozen );
		$this->set_id( $id );
		$this->product = it_exchange_get_product( $this->get_param( 'product_id' ) );

		if ( $this->has_param( 'itemized_data' ) ) {
			$this->set_param( 'itemized_data', maybe_unserialize( $this->get_param( 'itemized_data' ) ) );
		}

		if ( $this->has_param( 'additional_data' ) ) {
			$this->set_param( 'additional_data', maybe_unserialize( $this->get_param( 'additional_data' ) ) );
		}
	}

	/**
	 * Create a product line item.
	 *
	 * @since 2.0.0
	 *
	 * @param \IT_Exchange_Product $product
	 * @param int                  $quantity
	 *
	 * @return self
	 */
	public static function create( IT_Exchange_Product $product, $quantity = 1 ) {

		$bag = new ITE_Array_Parameter_Bag();
		$bag->set_param( 'product_id', $product->ID );
		$bag->set_param( 'product_name', get_the_title( $product->ID ) );

		$self = new self( '', $bag, new ITE_Array_Parameter_Bag() );
		self::generate_cart_product_id( $self );
		$self->set_quantity( $quantity );

		return $self;
	}

	/**
	 * @inheritDoc
	 */
	public function optional_features_required() {

		$product  = $this->get_product();
		$required = array();

		foreach ( ITE_Product_Feature_Registry::optional() as $feature ) {
			if ( $product->has_feature( $feature->get_feature_slug() ) ) {
				$required[] = new ITE_Optionally_Supported_Feature_Requirement( $feature, $feature->get_details_for_product( $product ) );
			}
		}

		return $required;
	}

	/**
	 * Update the line item from a change in the cart object.
	 *
	 * This is only here for backwards compatibility purposes.
	 *
	 * @since 2.0.0
	 *
	 * @param array $cart_product
	 *
	 * @return bool
	 */
	public function update_from_cart_object( array $cart_product ) {

		$updated = false;

		if ( $this->get_name() !== $cart_product['product_name'] ) {
			$this->frozen->set_param( 'name', $cart_product['product_name'] );
			$this->set_param( 'product_name', $cart_product['product_name'] );

			$updated = true;
		}

		if ( $this->get_amount() !== $cart_product['product_base_price'] ) {
			$this->set_param( 'product_base_price', $cart_product['product_base_price'] );

			$updated = true;
		}

		if ( $this->get_total() !== $cart_product['product_subtotal'] ) {
			$difference = $this->get_total() - $cart_product['product_subtotal'];

			$this->frozen->set_param( 'total', $this->frozen->get_param( 'total' ) + $difference );

			$updated = true;
		}

		return $updated;
	}

	/**
	 * Check if certain itemized data exists.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has_itemized_data( $key ) { return array_key_exists( $key, $this->get_itemized_data() ); }

	/**
	 * Get itemized data.
	 *
	 * Itemized data affects the cart ID. Itemized data can be used to differentiate two different products,
	 * with the same product ID in the cart.
	 *
	 * For example, Variants uses this to store the variant hash so more than one variant product can be purchased in
	 * the same transaction.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 *
	 * @throws OutOfBoundsException If no itemized data exists by the given key.
	 */
	public function get_itemized_data( $key = '' ) {

		$data = $this->has_param( 'itemized_data' ) ? $this->get_param( 'itemized_data' ) : array();

		if ( ! $key ) {
			return $data;
		}

		if ( ! $this->has_itemized_data( $key ) ) {
			throw new OutOfBoundsException( "No itemized data found for key '$key'." );
		}

		return $data[ $key ];
	}

	/**
	 * Set itemized data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function set_itemized_data( $key, $value ) {

		$data         = $this->get_itemized_data();
		$data[ $key ] = $value;
		$success      = $this->set_param( 'itemized_data', $data );

		if ( $success ) {
			self::generate_cart_product_id( $this );
		}

		return $success;
	}

	/**
	 * Remove itemized data.
	 *
	 * Will not error if the given key does not exist.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function remove_itemized_data( $key ) {

		if ( $this->has_itemized_data( $key ) ) {
			$data = $this->get_itemized_data();
			unset( $data[ $key ] );
			$success = $this->set_param( 'itemized_data', $data );

			if ( $success ) {
				self::generate_cart_product_id( $this );
			}

			return $success;
		}

		return false;
	}

	/**
	 * Check if certain additional data exists.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has_additional_data( $key ) { return array_key_exists( $key, $this->get_additional_data() ); }

	/**
	 * Get additional data.
	 *
	 * Additional data does not affect the cart product ID.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 *
	 * @throws OutOfBoundsException If no itemized data exists by the given key.
	 */
	public function get_additional_data( $key = '' ) {

		$data = $this->has_param( 'additional_data' ) ? $this->get_param( 'additional_data' ) : array();

		if ( ! $key ) {
			return $data;
		}

		if ( ! $this->has_additional_data( $key ) ) {
			throw new OutOfBoundsException( "No additional data found for key '$key'." );
		}

		return $data[ $key ];
	}

	/**
	 * Set additional data.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function set_additional_data( $key, $value ) {
		$data         = $this->get_additional_data();
		$data[ $key ] = $value;

		return $this->set_param( 'additional_data', $data );
	}

	/**
	 * Remove additional data.
	 *
	 * Will not error if the given key does not exist.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function remove_additional_data( $key ) {

		if ( $this->has_itemized_data( $key ) ) {
			$data = $this->get_additional_data();
			unset( $data[ $key ] );

			return $this->set_param( 'additional_data', $data );
		}

		return false;
	}

	/**
	 * Retrieve the product being purchased.
	 *
	 * @since 2.0.0
	 *
	 * @return \IT_Exchange_Product
	 */
	public function get_product() { return $this->product; }

	/**
	 * Set the product quantity.
	 *
	 * @since 2.0.0
	 *
	 * @param int $quantity
	 */
	public function set_quantity( $quantity ) {

		$quantity = max( 1, $quantity );
		$max      = $this->get_max_quantity_available();

		if ( $max !== '' && $quantity > $max ) {
			$quantity = $max;
		}

		$this->set_param( 'count', $quantity );
	}

	/**
	 * @inheritDoc
	 */
	public function is_quantity_modifiable() {

		if ( ! $this->get_product() ) {
			return true;
		}

		return $this->get_product()->supports_feature( 'purchase-quantity' ) &&
		       it_exchange_is_multi_item_product_allowed( $this->get_product()->ID );
	}

	/**
	 * @inheritdoc
	 */
	public function get_max_quantity_available() {
		return it_exchange_get_max_product_quantity_allowed( $this->get_product(), $this->get_id() );
	}

	/**
	 * @inheritDoc
	 */
	public function get_amount_to_discount() {

		$base = $this->get_base_amount();

		foreach ( $this->aggregatables as $item ) {
			if ( $item instanceof ITE_Discountable_Line_Item ) {
				$base += $item->get_amount_to_discount();
			}
		}

		return $base;
	}

	/**
	 * @inheritDoc
	 */
	public function is_tax_exempt( ITE_Tax_Provider $for ) { return $for->is_product_tax_exempt( $this->get_product() ); }

	/**
	 * @inheritDoc
	 */
	public function get_tax_code( ITE_Tax_Provider $for ) { return $for->get_tax_code_for_product( $this->get_product() ); }

	/**
	 * @inheritDoc
	 */
	public function get_taxable_amount() { return $this->get_base_amount(); }

	/**
	 * @inheritDoc
	 */
	public function get_taxes() {
		return $this->get_line_items()->with_only_instances_of( 'ITE_Tax_Line_Item' );
	}

	/**
	 * @inheritDoc
	 */
	public function add_tax( ITE_Tax_Line_Item $tax ) {
		$this->add_item( $tax );

		/** @var ITE_Taxable_Line_Item $item */
		foreach ( $this->get_line_items()->taxable() as $item ) {
			if ( $tax->applies_to( $item ) ) {
				$item->add_tax( $tax->create_scoped_for_taxable( $item ) );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function remove_tax( $id ) {
		return $this->remove_item( 'tax', $id );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_all_taxes() {
		foreach ( $this->get_taxes() as $tax ) {
			$this->remove_tax( $tax->get_id() );
		}
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

		return new ITE_Line_Item_Collection( $this->aggregatables, $this->repository );
	}

	/**
	 * @inheritDoc
	 */
	public function add_item( ITE_Aggregatable_Line_Item $item ) {

		$item->set_aggregate( $this );

		$this->aggregatables[] = $item;

		if ( $item instanceof ITE_Taxable_Line_Item ) {
			foreach ( $this->get_taxes() as $tax ) {
				if ( $tax->applies_to( $item ) ) {
					$item->add_tax( $tax->create_scoped_for_taxable( $item ) );
				}
			}
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function remove_item( $type, $id ) {

		$found = false;

		foreach ( $this->aggregatables as $i => $item ) {
			if ( $item->get_type() === $type && $item->get_id() === $id ) {
				unset( $this->aggregatables[ $i ] );
				$found = true;

				break;
			}
		}

		reset( $this->aggregatables );

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
		if ( $this->frozen->has_param( 'name' ) ) {
			return $this->frozen->get_param( 'name' );
		} else {
			$title = $this->get_param( 'product_name' );

			return apply_filters( 'it_exchange_get_cart_product_title', $title, $this->bc(), $this );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return $this->frozen->has_param( 'description' ) ? $this->frozen->get_param( 'description' ) : '';
	}

	/**
	 * @inheritDoc
	 */
	public function get_quantity() {
		if ( $this->frozen->has_param( 'quantity' ) ) {
			return $this->frozen->get_param( 'quantity' );
		} else {
			return apply_filters( 'it_exchange_get_cart_product_quantity', $this->get_param( 'count' ), $this->bc() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_amount() {
		return (float) $this->get_base_amount();
	}

	/**
	 * Get the product's subtotal.
	 *
	 * @since 2.0.0
	 *
	 * @return float
	 */
	protected function get_subtotal() {
		$subtotal = $this->get_amount() * $this->get_quantity();

		return (float) apply_filters( 'it_exchange_get_cart_product_subtotal', $subtotal, $this->bc() );
	}

	/**
	 * @inheritDoc
	 */
	public function get_total() {

		if ( $this->frozen->has_param( 'total' ) ) {
			return parent::get_total();
		}

		$subtotal = $this->get_subtotal();

		foreach ( $this->get_line_items()->non_summary_only() as $item ) {
			$subtotal += $item->get_total();
		}

		return $subtotal;
	}

	/**
	 * @inheritDoc
	 */
	public function get_object_id() { return $this->get_param( 'product_id' ); }

	/**
	 * @inheritDoc
	 */
	public function freeze() {
		parent::freeze();

		$this->frozen->remove_param( 'total' );
		$this->frozen->set_param( 'total', $this->get_subtotal() );
	}

	/**
	 * Get the base amount, before any aggregatables are applied.
	 *
	 * @since 2.0.0
	 *
	 * @return float
	 */
	protected function get_base_amount() {

		if ( $this->frozen->has_param( 'amount' ) ) {
			return $this->frozen->get_param( 'amount' );
		}

		$base = $this->get_product()->get_feature( 'base-price' );

		/**
		 * Filter the base cart product price.
		 *
		 * @since 1.0.0
		 *
		 * @param float            $base
		 * @param array            $bc
		 * @param bool             $format
		 * @param ITE_Cart_Product $this
		 */
		return apply_filters( 'it_exchange_get_cart_product_base_price', $base, $this->bc(), false, $this );
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
		return $this->frozen->has_param( 'summary_only' ) ? $this->frozen->get_param( 'summary_only' ) : false;
	}

	/**
	 * Generate the cart product ID.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart_Product $product
	 */
	public static function generate_cart_product_id( ITE_Cart_Product $product ) {
		$product->set_id( $product->get_param( 'product_id' ) . '-' . md5( serialize( $product->get_itemized_data() ) ) );
	}

	/**
	 * Back-compat representation of a cart-product.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function bc() {

		$data = wp_parse_args( $this->get_params(), array(
			'itemized_data'   => array(),
			'additional_data' => array()
		) );

		$data['itemized_data']   = serialize( $data['itemized_data'] );
		$data['additional_data'] = serialize( $data['additional_data'] );
		$data['itemized_hash']   = md5( $data['itemized_data'] );

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function set_line_item_repository( ITE_Line_Item_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * @inheritDoc
	 */
	public function __destruct() {
		unset( $this->aggregatables, $this->repository );
	}
}
