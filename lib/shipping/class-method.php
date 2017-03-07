<?php

/**
 * Extend this class to create new shipping method to use with your shipping add-on
 */
abstract class IT_Exchange_Shipping_Method {

	/**
	 * @var string $slug the identifying key of the shipping feature
	 */
	public $slug;

	/**
	 * @var string
	 */
	public $label;

	/**
	 * @var object $product the current product object
	 */
	public $product = false;

	/**
	 * @var boolean $enabled is this shipping feature enabled
	 */
	public $enabled = false;

	/**
	 * @var boolean $available is this shipping feature available to the current product
	 */
	public $available = false;

	/**
	 * @var array $settings the fields that will be added to the settings page of the provider
	 */
	public $settings = array();

	/** @var string */
	protected $settings_key = '';

	/**
	 * Class constructor
	 *
	 * @since 1.4.0
	 *
	 * @param int|bool $product_id exchange product id or empty to attempt to pick up the global product
	 */
	public function __construct( $product_id = false ) {

		// Set slug
		$this->set_slug();

		// Set the settings
		$this->set_settings();

		// Set label
		$this->set_label();

		// Set the product
		$this->set_product( $product_id );

		// Set whether this is enabled
		$this->set_enabled();

		// Set the availability of this method to this product
		$this->set_availability();

		// Set the shipping features for this method
		$this->set_features();
	}

	/**
	 * @return mixed
	 */
	abstract public function set_slug();

	/**
	 * Sets the product if one is available
	 *
	 * @since  1.4.0
	 *
	 * @todo   I don't like this. Cory needs to refactor it. ^gta
	 *
	 * @param  int $product exchange product id or empty to attempt to pick up the global product
	 *
	 * @return void
	 */
	public function set_product( $product_id = false ) {
		$product = false;

		// If a product ID is passed, use it
		if ( $product_id ) {
			$product = it_exchange_get_product( $product_id );
		} else {
			// Grab global $post
			global $post;

			// If post is set in REQUEST, use it.
			if ( isset( $_REQUEST['post'] ) ) {
				$post_id = (int) $_REQUEST['post'];
			} elseif ( isset( $_REQUEST['post_ID'] ) ) {
				$post_id = (int) $_REQUEST['post_ID'];
			} else {
				$post_id = empty( $post->ID ) ? 0 : $post->ID;
			}

			// If we have a post ID, get the post object
			if ( $post_id ) {
				$product = it_exchange_get_product( $post_id );
			}
		}

		// Set the property
		if ( $product instanceof IT_Exchange_Product ) {
			$this->product = $product;
		} else {
			$this->product = false;
		}
	}

	/**
	 * Get the settings mixed with the defaults provided by the setting fields.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $break_cache
	 *
	 * @return array
	 */
	public function get_settings( $break_cache = false ) {

		if ( ! $this->settings_key ) {
			return array();
		}

		$settings = it_exchange_get_option( $this->settings_key, $break_cache );

		foreach ( $this->get_settings_fields() as $field ) {
			if ( array_key_exists( $field['slug'], $settings ) ) {
				continue;
			}

			if ( $field['type'] === 'heading' ) {
				continue;
			}

			$settings[ $field['slug'] ] = isset( $field['default'] ) ? $field['default'] : '';
		}

		return $settings;
	}

	/**
	 * Get the settings fields.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_settings_fields() { return $this->settings; }

	/**
	 * Is this method available to this product
	 *
	 * The shipping method is required to extend override this method in the extended class.
	 * It needs to then determine if the shipping method is available
	 * @since 1.4.0
	 *
	 * @return void
	 */
	abstract public function set_availability();

	/**
	 * Sets up the label of this shipping method.
	 *
	 * @since 1.4.0
	 */
	abstract public function set_label();

	/**
	 * Sets up the settings for this shipping method.
	 *
	 * @since 1.4.0
	 */
	abstract public function set_settings();

	/**
	 * Sets up whether this shipping method is currently enabled.
	 *
	 * @since 1.4.0
	 */
	abstract public function set_enabled();

	/**
	 * Sets up the shipping features utilized by this method.
	 *
	 * @since 1.4.0
	 */
	abstract public function set_features();

	/**
	 * Get the total cost for shipping a given product with this method.
	 *
	 * @since 1.4.0
	 * @since 2.0.0 Add $cart parameter.
	 *
	 * @param array     $cart_product
	 * @param \ITE_Cart $cart
	 *
	 * @return float
	 */
	public function get_shipping_cost_for_product( $cart_product, $cart = null ) {
		return 0.00;
	}

	/**
	 * Get any additional costs this method imposes on the cart as a whole, not an individual product.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return float
	 */
	public function get_additional_cost_for_cart( ITE_Cart $cart ) {
		return 0.00;
	}

	/**
	 * Add a setting field to this method.
	 *
	 * @since 1.4.0
	 *
	 * @param array $setting
	 */
	public function add_setting( $setting ) {
		$this->settings[] = $setting;
	}
}
