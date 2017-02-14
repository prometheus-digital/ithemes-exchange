<?php
/**
 * Tax Manager.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Tax_Manager
 */
class ITE_Tax_Manager implements ITE_Cart_Aware {

	/** @var ITE_Tax_Provider[] */
	private $providers = array();

	/** @var bool */
	private $use_shipping = false;

	/** @var ITE_Cart */
	private $cart;

	/** @var ITE_Location */
	private $current_location;

	/**
	 * ITE_Tax_Manager constructor.
	 *
	 * @param \ITE_Cart $cart
	 */
	public function __construct( ITE_Cart $cart ) {
		$this->cart         = $cart;
		$this->use_shipping = $cart->requires_shipping();

		if ( $this->use_shipping ) {
			$address = $this->cart->get_shipping_address();
		} else {
			$address = $this->cart->get_billing_address();
		}

		if ( $address ) {
			$this->current_location = new ITE_In_Memory_Address( $address->to_array() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function set_cart( ITE_Cart $cart ) {
		$this->cart = $cart;
	}

	/**
	 * Register a tax provider.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Tax_Provider $provider Tax provider. Take care to only register it once.
	 * @param bool              $sort     Whether to automatically resort the providers.
	 *                                    If false, the registration should be followed by a call to `sort_providers()`.
	 *
	 * @return $this
	 */
	public function register_provider( ITE_Tax_Provider $provider, $sort = true ) {
		$this->providers[] = $provider;

		if ( $sort ) {
			$this->providers = $this->do_sort_providers( $this->providers );
		}

		return $this;
	}

	/**
	 * Sort all tax providers.
	 *
	 * @since 2.0.0
	 *
	 * @return $this
	 */
	public function sort_providers() {
		$this->providers = $this->do_sort_providers( $this->providers );

		return $this;
	}

	/**
	 * Sort tax providers by their location.
	 *
	 * @since 2.0.0
	 *
	 * @param array $providers
	 *
	 * @return array
	 */
	protected function do_sort_providers( array $providers ) {
		usort( $providers, function ( ITE_Tax_Provider $a, ITE_Tax_Provider $b ) {

			if ( ! $a->is_restricted_to_location() ) {
				return 1;
			}

			if ( ! $b->is_restricted_to_location() ) {
				return - 1;
			}

			$pa = $a->is_restricted_to_location()->get_precision();
			$pb = $b->is_restricted_to_location()->get_precision();

			$priority = array( 'country', 'state', 'zip', 'city' );

			$pai = array_search( $pa, $priority, true );
			$pbi = array_search( $pb, $priority, true );

			return $pbi - $pai;
		} );

		return $providers;
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function hooks() {
		add_action( 'it_exchange_add_line_item_to_cart', array( $this, 'on_add_item' ), 10, 2 );
		add_action( 'it_exchange_remove_line_item_from_cart', array( $this, 'on_remove_item' ), 10, 2 );
		add_action( 'it_exchange_finalize_cart_totals', array( $this, 'finalize_totals' ) );
		add_action( 'it_exchange_set_cart_shipping_address', array( $this, 'shipping_updated' ) );
		add_action( 'it_exchange_set_cart_billing_address', array( $this, 'billing_updated' ) );
		add_action( 'it_exchange_merged_cart', array( $this, 'cart_merged' ), 10, 3 );
	}

	/**
	 * Add taxes when an item is added to the cart.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Line_Item $item
	 * @param \ITE_Cart      $cart
	 */
	public function on_add_item( ITE_Line_Item $item, ITE_Cart $cart ) {

		if ( $cart->get_id() !== $this->cart->get_id() ) {
			return;
		}

		// Check if adding an item to the cart caused the shipping address to be required.
		if ( ! $this->use_shipping && $cart->requires_shipping() ) {
			$this->handle_address_update( $cart->get_shipping_address() );
		}

		if ( ! $item instanceof ITE_Taxable_Line_Item ) {
			return;
		}

		foreach ( $this->providers as $provider ) {

			$zone = $provider->is_restricted_to_location();

			if ( ! $this->current_location ) {
				if ( ! $zone ) {
					$this->add_taxes_to_item( $item, $provider );

					return;
				}

				continue;
			} elseif ( $zone && $zone->contains( $zone->mask( $this->current_location ) ) ) {
				$this->add_taxes_to_item( $item, $provider );

				return;
			} elseif ( ! $zone ) {
				$this->add_taxes_to_item( $item, $provider );

				return;
			}
		}
	}

	/**
	 * Check if an address update needs to processed depending on the item removed.
	 *
	 * @since 2.0.0
	 *
	 * @param ITE_Line_Item $item
	 * @param ITE_Cart      $cart
	 */
	public function on_remove_item( ITE_Line_Item $item, ITE_Cart $cart ) {

		if ( $cart->get_id() !== $this->cart->get_id() ) {
			return;
		}

		// Check if removing an item from the cart caused the shipping address to no longer be required
		if ( $this->use_shipping && ! $cart->requires_shipping() ) {
			$this->handle_address_update( $cart->get_billing_address() );
		}
	}

	/**
	 * Finalize tax totals on the cart.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 */
	public function finalize_totals( ITE_Cart $cart ) {

		foreach ( $this->providers as $provider ) {
			$provider->finalize_taxes( $cart );
		}
	}

	/**
	 * When the shipping address is updated, possibly recalculate taxes.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 */
	public function shipping_updated( ITE_Cart $cart ) {

		if ( $cart->get_id() !== $this->cart->get_id() ) {
			return;
		}

		if ( ! $this->use_shipping ) {
			return;
		}

		$this->handle_address_update( $this->cart->get_shipping_address() );
	}

	/**
	 * When the billing address is updated, possibly recalculate taxes.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 */
	public function billing_updated( ITE_Cart $cart ) {

		if ( $cart->get_id() !== $this->cart->get_id() ) {
			return;
		}

		if ( $this->use_shipping ) {
			return;
		}

		$this->handle_address_update( $this->cart->get_billing_address() );
	}

	/**
	 * Handle carts merging.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 * @param \ITE_Cart $other
	 * @param bool      $coerce
	 */
	public function cart_merged( ITE_Cart $cart, ITE_Cart $other, $coerce ) {

		if ( $cart->get_id() !== $this->cart->get_id() && $other->get_id() !== $this->cart->get_id() ) {
			return;
		}

		if ( $this->use_shipping ) {
			$new = $cart->get_shipping_address();
		} else {
			$new = $cart->get_billing_address();
		}

		// Force address updating when logging a customer in.
		if ( $this->current_location && $new && $new->equals( $this->current_location ) && ! doing_action( 'wp_login' ) ) {
			return;
		}

		$this->handle_address_update( $new );

		if ( $coerce ) {
			$cart->coerce();
		}
	}

	/**
	 * Handle an address being updated.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Location|null $new_address
	 */
	protected function handle_address_update( ITE_Location $new_address = null ) {

		$added = false;

		foreach ( $this->providers as $provider ) {

			$zone = $provider->is_restricted_to_location();

			if ( $new_address === null ) {
				$this->cart->get_items( 'tax', true )->with_only_instances_of( $provider->get_item_class() )->delete();

				continue;
			}

			$masked = $zone ? $zone->mask( $new_address ) : $new_address;

			if ( $this->current_location && $this->current_location->equals( $masked ) ) {
				continue;
			} elseif ( ! $zone || $zone->contains( $masked ) ) {
				$this->cart->get_items( 'tax', true )->with_only_instances_of( $provider->get_item_class() )->delete();

				if ( ! $added ) {
					foreach ( $this->cart->get_items()->taxable() as $item ) {
						$this->add_taxes_to_item( $item, $provider );
					}
				}

				$added = true;
			} else {
				$this->cart->get_items( 'tax', true )->with_only_instances_of( $provider->get_item_class() )->delete();
			}
		}

		$this->current_location = $new_address;
	}

	/**
	 * Add taxes to a taxable item for a provider.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Taxable_Line_Item $item
	 * @param \ITE_Tax_Provider      $provider
	 */
	protected function add_taxes_to_item( ITE_Taxable_Line_Item $item, ITE_Tax_Provider $provider ) {
		$provider->add_taxes_to( $item, $this->cart );
	}
}
