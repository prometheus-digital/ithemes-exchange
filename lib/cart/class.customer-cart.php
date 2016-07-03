<?php
/**
 * Cart class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Cart
 */
class ITE_Cart {

	/** @var \ITE_Line_Item_Repository */
	private $repository;

	/** @var array */
	private $items = array();

	/** @var ITE_Cart_Validator[] */
	private $cart_validators = array();

	/** @var ITE_Line_Item_Validator[] */
	private $item_validators = array();

	/** @var string */
	private $cart_id;

	/** @var IT_Exchange_Customer|null */
	private $customer;

	/** @var bool */
	private $doing_merge = false;

	/**
	 * ITE_Cart constructor.
	 *
	 * @param ITE_Line_Item_Repository $repository
	 * @param string                   $cart_id
	 * @param IT_Exchange_Customer     $customer
	 */
	public function __construct( ITE_Line_Item_Repository $repository, $cart_id, IT_Exchange_Customer $customer = null ) {
		$this->repository = $repository;
		$this->cart_id    = $cart_id;

		$all = $repository->all();

		foreach ( $all as $item ) {
			$this->items[ $item->get_type() ][] = $item;
		}

		if ( ! $customer && $this->is_current() ) {
			$customer = it_exchange_get_current_customer();

			if ( ! $customer instanceof IT_Exchange_Customer ) {
				$customer = null;
			}
		}

		$this->customer = $customer;

		$this->add_cart_validator( new ITE_Multi_Item_Cart_Validator() );
		$this->add_item_validator( new ITE_Multi_Item_Product_Validator() );
		$this->add_item_validator( new ITE_Product_Inventory_Validator() );
	}

	/**
	 * Get the cart ID.
	 *
	 * @since 1.36
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->cart_id;
	}

	/**
	 * Get the customer this cart belongs to.
	 *
	 * @since 1.36
	 *
	 * @return IT_Exchange_Customer|null
	 */
	public function get_customer() {
		return $this->customer;
	}

	/**
	 * Check if the cart is the current active cart.
	 *
	 * @since 1.36
	 *
	 * @return bool
	 */
	public function is_current() {
		return $this->get_id() === it_exchange_get_cart_id();
	}

	/**
	 * Returns true if the cart is undergoing a merge.
	 *
	 * @since 1.36
	 *
	 * @return boolean
	 */
	public function is_doing_merge() {
		return $this->doing_merge;
	}

	/**
	 * Get the customer's shipping address.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	public function get_shipping_address() {
		return $this->get_repository()->get_shipping_address();
	}

	/**
	 * Get the customer's billing address.
	 *
	 * @since 1.36.0
	 *
	 * @return array
	 */
	public function get_billing_address() {
		return $this->get_repository()->get_billing_address();
	}

	/**
	 * Add a line item to the cart.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item $item
	 * @param bool           $coerce
	 *
	 * @return bool
	 */
	public function add_item( ITE_Line_Item $item, $coerce = true ) {

		if ( $item instanceof ITE_Line_Item_Repository_Aware ) {
			$item->set_line_item_repository( $this->get_repository() );
		}

		$_items = $this->items;

		$method = "add_{$item->get_type()}_item";

		if ( ! method_exists( $this, $method ) || $this->{$method}( $item ) !== false ) {
			$this->items[ $item->get_type() ][] = $item;
		}

		if ( ( $coerce && ! $this->coerce( $item ) ) || ( ! $coerce && ! $this->validate() ) ) {
			$this->items = $_items;

			return false;
		}

		$this->get_repository()->save( $item );

		/**
		 * Fires when a line item is added to the cart.
		 *
		 * @since 1.36
		 *
		 * @param \ITE_Line_Item $item
		 * @param \ITE_Cart      $cart
		 */
		do_action( 'it_exchange_add_line_item_to_cart', $item, $this );

		/**
		 * Fires when a line item is added to the cart.
		 *
		 * The dynamic portion of this hook refers to the line item type.
		 *
		 * @since 1.36
		 *
		 * @param \ITE_Line_Item $item
		 * @param \ITE_Cart      $cart
		 */
		do_action( "it_exchange_add_{$item->get_type()}_to_cart", $item, $this );

		return true;
	}

	/**
	 * Get the line items contained in the cart.
	 *
	 * @since 1.36
	 *
	 * @param string $type    If empty, all line items will be returned.
	 * @param bool   $flatten Whether to flatten aggregate line items.
	 *
	 * @return ITE_Line_Item_Collection|ITE_Line_Item[]
	 *
	 * @throws InvalidArgumentException If $type is invalid.
	 */
	public function get_items( $type = '', $flatten = false ) {

		if ( $type ) {
			self::assert_type( $type );
		}

		if ( $flatten ) {
			$items = $this->get_items()->flatten();

			if ( $type ) {
				return $items->with_only( $type );
			} else {
				return $items;
			}
		}

		if ( ! $type ) {
			$items = array();

			foreach ( $this->items as $item ) {
				$items = array_merge( $items, $item );
			}

			return new ITE_Line_Item_Collection( $items, $this->get_repository() );
		}

		if ( isset( $this->items[ $type ] ) ) {
			return new ITE_Line_Item_Collection( $this->items[ $type ], $this->get_repository() );
		} else {
			return new ITE_Line_Item_Collection( array(), $this->get_repository() );
		}
	}

	/**
	 * Retrieve a line item from the cart.
	 *
	 * @since 1.36
	 *
	 * @param string     $type
	 * @param string|int $id
	 *
	 * @return \ITE_Line_Item|null
	 *
	 * @throws InvalidArgumentException If $type is invalid.
	 */
	public function get_item( $type, $id ) {

		$items = $this->get_items( $type );

		foreach ( $items as $item ) {
			if ( $item->get_id() === $id ) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Remove an item from the cart.
	 *
	 * @since 1.36
	 *
	 * @param string     $type
	 * @param string|int $id
	 *
	 * @return bool False if item could not be found.
	 */
	public function remove_item( $type, $id ) {

		if ( ! isset( $this->items[ $type ] ) ) {
			return false;
		}

		foreach ( $this->items[ $type ] as $i => $item ) {
			if ( $item->get_id() === $id ) {
				unset( $this->items[ $type ][ $i ] );
				$this->get_repository()->delete( $item );

				/**
				 * Fires when a line item is removed from the cart.
				 *
				 * @since 1.36
				 *
				 * @param \ITE_Line_Item $item
				 * @param \ITE_Cart      $cart
				 */
				do_action( 'it_exchange_remove_line_item_from_cart', $item, $this );

				/**
				 * Fires when a line item is removed from the cart.
				 *
				 * The dynamic portion of this hook refers to the line item type.
				 *
				 * @since 1.36
				 *
				 * @param \ITE_Line_Item $item
				 * @param \ITE_Cart      $cart
				 */
				do_action( "it_exchange_remove_{$item->get_type()}_from_cart", $item, $this );

				return true;
			}
		}

		return false;
	}

	/**
	 * Remove all line items, or all line items of a given type from the cart.
	 *
	 * @since 1.36
	 *
	 * @param string $type    The item type. Optionally. If unspecified, all item types will be removed.
	 * @param bool   $flatten Whether to remove all items, including aggregates' children.
	 *
	 * @return bool
	 */
	public function remove_all( $type = '', $flatten = false ) {

		foreach ( $this->get_items( $type, $flatten ) as $item ) {
			$this->get_repository()->delete( $item );

			// This hook is documented in lib/cart/class.customer-cart.php
			do_action( 'it_exchange_remove_line_item_from_cart', $item, $this );

			// This hook is documented in lib/cart/class.customer-cart.php
			do_action( "it_exchange_remove_{$item->get_type()}_from_cart", $item, $this );
		}

		if ( $type ) {
			unset( $this->items[ $type ] );
		} else {
			$this->items = array();
		}

		return true;
	}

	/**
	 * Callback to perform custom processing when a cart product line item is added to the cart.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Cart_Product $product
	 *
	 * @return bool
	 */
	protected function add_product_item( ITE_Cart_Product $product ) {
		ITE_Cart_Product::generate_cart_product_id( $product );

		if ( $dupe = $this->get_item( 'product', $product->get_id() ) ) {

			if ( $this->is_doing_merge() ) {
				return false; // Don't combine quantities when doing a merge
			}

			$dupe->set_quantity( $product->get_quantity() + $dupe->get_quantity() );
			$dupe->persist( $this->get_repository() );

			return false;
		}

		return true;
	}

	/**
	 * Callback to perform custom processing when a tax line item is added to the cart.
	 *
	 * @since 1.36
	 *
	 * @param ITE_Tax_Line_Item $tax
	 *
	 * @return bool
	 */
	protected function add_tax_item( ITE_Tax_Line_Item $tax ) {
		foreach ( $this->get_items() as $item ) {
			if ( $item instanceof ITE_Taxable_Line_Item && $tax->applies_to( $item ) ) {
				$item->add_tax( $tax->create_scoped_for_taxable( $item ) );
			}
		}

		return false;
	}

	/**
	 * Calculate the total of all line items or a given line item type.
	 *
	 * This calculation is not cached.
	 *
	 * @since 1.36
	 *
	 * @param string $type
	 * @param bool   $unravel
	 *
	 * @return float
	 */
	public function calculate_total( $type = '', $unravel = false ) {

		$items = $this->get_items( $type, $unravel );
		$total = 0.00;

		foreach ( $items as $item ) {
			$total += $item->get_amount() * $item->get_quantity();
		}

		return $total;
	}

	/**
	 * Validate the current state of the cart.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Cart_Feedback|null $feedback
	 *
	 * @return bool
	 */
	public function validate( ITE_Cart_Feedback $feedback = null ) {

		foreach ( $this->cart_validators as $cart_validator ) {
			if ( ! $cart_validator->validate( $this, $feedback ) ) {
				return false;
			}
		}

		$items = $this->get_items();

		foreach ( $this->item_validators as $item_validator ) {
			foreach ( $items as $item ) {
				if ( $item_validator->accepts( $item->get_type() ) && ! $item_validator->validate( $item, $this, $feedback ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Coerce the cart to a valid state.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item     $new_item
	 * @param \ITE_Cart_Feedback $feedback
	 *
	 * @return bool
	 *
	 * @throws \ITE_Line_Item_Coercion_Failed_Exception
	 * @throws \ITE_Cart_Coercion_Failed_Exception
	 */
	public function coerce( ITE_Line_Item $new_item = null, ITE_Cart_Feedback $feedback = null ) {

		$valid = true;

		foreach ( $this->cart_validators as $cart_validator ) {
			if ( ! $cart_validator->coerce( $this, $new_item, $feedback ) ) {
				$valid = false;
			}
		}

		$items = $this->get_items();

		foreach ( $this->item_validators as $item_validator ) {
			foreach ( $items as $item ) {
				if ( $item_validator->accepts( $item->get_type() ) && ! $item_validator->coerce( $item, $this, $feedback ) ) {
					$valid = false;
				}
			}
		}

		return $valid;
	}

	/**
	 * Empty the cart.
	 *
	 * @since 1.36
	 */
	public function empty_cart() {

		/**
		 * Fires when the cart is about to be emptied.
		 *
		 * @since 1.36
		 *
		 * @param \ITE_Cart $cart
		 */
		do_action( 'it_exchange_empty_cart', $this );

		$items = $this->get_items();
		$this->remove_all();

		/**
		 * Fires when the cart was just emptied.
		 *
		 * @since 1.36
		 *
		 * @param \ITE_Cart                 $cart
		 * @param \ITE_Line_Item_Collection $items Items removed from the cart.
		 */
		do_action( 'it_exchange_emptied_cart', $this, $items );
	}

	/**
	 * Merge another cart into this cart.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Cart $cart
	 * @param bool      $coerce
	 */
	public function merge( ITE_Cart $cart, $coerce = true ) {

		$this->doing_merge = true;
		$cart->doing_merge = true;

		/**
		 * Fires before a cart has been merged into another cart.
		 *
		 * @since 1.36
		 *
		 * @param \ITE_Cart $this The primary cart.
		 * @param \ITE_Cart $cart The cart being merged.
		 * @param bool      $coerce
		 */
		do_action( 'it_exchange_merge_cart', $this, $cart, $coerce );

		foreach ( $cart->get_items() as $item ) {
			$this->add_item( $item, false );
		}

		$cart->remove_all();

		if ( $coerce ) {
			$this->coerce();
		}

		/**
		 * Fires before a cart has been merged into another cart.
		 *
		 * @since 1.36
		 *
		 * @param \ITE_Cart $this The primary cart.
		 * @param \ITE_Cart $cart The cart being merged.
		 * @param bool      $coerce
		 */
		do_action( 'it_exchange_merged_cart', $this, $cart, $coerce );

		$this->doing_merge = false;
		$cart->doing_merge = false;
	}

	/**
	 * Add a cart wide validator.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Cart_Validator $validator
	 *
	 * @return $this
	 */
	public function add_cart_validator( ITE_Cart_Validator $validator ) {
		$this->cart_validators[ $validator->get_name() ] = $validator;

		return $this;
	}

	/**
	 * Remove a cart wide validator.
	 *
	 * @since 1.36
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function remove_cart_validator( $name ) {
		unset( $this->cart_validators[ $name ] );

		return $this;
	}

	/**
	 * Add a line item validator.
	 *
	 * @since 1.36
	 *
	 * @param \ITE_Line_Item_Validator $validator
	 *
	 * @return $this
	 */
	public function add_item_validator( ITE_Line_Item_Validator $validator ) {
		$this->item_validators[ $validator->get_name() ] = $validator;

		return $this;
	}

	/**
	 * Remove a line item validator.
	 *
	 * @since 1.36
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function remove_item_validator( $name ) {
		unset( $this->item_validators[ $name ] );

		return $this;
	}

	/**
	 * Get the repository being used for persistence.
	 *
	 * @since 1.36
	 *
	 * @return \ITE_Line_Item_Repository
	 */
	public function get_repository() {
		return $this->repository;
	}

	/**
	 * Assert that the given type is valid.
	 *
	 * @since 1.36
	 *
	 * @param string $type
	 *
	 * @throws InvalidArgumentException
	 */
	protected static function assert_type( $type ) {
		if ( ! is_string( $type ) || trim( $type ) === '' ) {
			throw new InvalidArgumentException( '$type must be non-zero length string.' );
		}
	}
}