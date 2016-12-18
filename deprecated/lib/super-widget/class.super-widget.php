<?php

/**
 * Controls the super widget - which can also be output via a shortcode or a PHP functions
 *
 * @since   0.4.0
 * @package IT_Exchange
 */
class IT_Exchange_Super_Widget extends WP_Widget {

	/**
	 * @var array $pages exchange pages options
	 * @since 0.4.0
	 */
	var $pages;

	/**
	 * @var boolean $using_permalinks are permalinks set in WP settings?
	 * @since 0.4.0
	 */
	var $using_permalinks;

	/**
	 * @var array $valid_states a filterable list of valid super widget states
	 * @since 0.4.0
	 */
	var $valid_states;

	/**
	 * @var string $state the current state of the widget
	 * @since 0.4.0
	 */
	var $state = false;

	/**
	 * @var string $it_exchange_view current view set by class.pages.php
	 * @since 0.4.0
	 */
	var $it_exchange_view;

	/**
	 * @var bool
	 */
	private $in_sidebar = false;

	/**
	 * @var bool
	 */
	private $rendered = false;

	/** @var bool */
	private $did_loop_start = false;

	/**
	 * Constructor: Init
	 *
	 * @since 0.4.0
	 */
	function __construct() {
		$id_base = 'it-exchange-super-widget';
		$name    = __( 'iThemes Exchange Super Widget', 'it-l10n-ithemes-exchange' );
		$options = array(
			'description' => __( 'Handles Buy Now, Add to Cart, Cart Summary, Registration, Log in, and Confirmation views depending on the situation', 'it-l10n-ithemes-exchange' ),
		);
		parent::__construct( $id_base, $name, $options );

		if ( ! is_admin() ) {
			$this->set_pages();
			$this->set_using_permalinks();
			$this->set_valid_states();
			add_action( 'template_redirect', array( $this, 'load_ajax' ), 1 );
			add_action( 'template_redirect', array( $this, 'set_state' ), 11 );
			add_action( 'dynamic_sidebar_before', array( $this, 'maybe_remove_sw_from_sidebar' ) );
			add_action( 'dynamic_sidebar_after', array( $this, 'mark_out_of_sidebar' ) );
			add_action( 'loop_start', array( $this, 'mark_loop_did_start' ) );
		}
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchange_Super_Widget() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Outputs the widget content. This is a required method by the WP_Widget class
	 *
	 * @since 0.4.0
	 *
	 * @param array $args     Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 *
	 * @return void
	 */
	function widget( $args, $instance ) {
		if ( ! $this->get_state() ) {
			return;
		}

		$defaults = array(
			'enqueue_hide_script' => true,
		);
		$args     = wp_parse_args( $args, $defaults );

		// Flag that we're in the superwidget
		$GLOBALS['it_exchange']['in_superwidget'] = $instance;
		if ( get_queried_object() && ! empty( get_queried_object()->ID ) && it_exchange_get_product( get_queried_object()->ID ) ) {
			$product_id = get_queried_object_id();
		} else {
			$product_id = $this->_get_product_id_for_non_product_pages();
		}

		/**
		 * Filter the args used to build the Super Widget.
		 *
		 * @since 1.32
		 *
		 * @param array $args
		 * @param int   $product_id
		 */
		$args = apply_filters( 'it_exchange_super_widget_args', $args, $product_id );

		if ( $this->rendered ) {

			if ( ! $this->in_sidebar ) {
				return;
			}

			$might_remove  = false;
			$remove_others = true;
		} else {
			$remove_others = false;
			$might_remove  = true;
		}

		// Some JS we're going to need
		?>
		<script type="text/javascript">
			var itExchangeSWAjaxURL = '<?php echo esc_js( get_home_url() . '/?it-exchange-sw-ajax=1' );?>';
			var itExchangeSWState = '<?php echo esc_js( $this->get_state() ); ?>';
			var itExchangeSWOnProductPage = '<?php echo esc_js( $product_id ); ?>';
			var itExchangeSWMultiItemCart = '<?php echo esc_js( it_exchange_is_multi_item_cart_allowed() ); ?>';
			var itExchangeIsUserLoggedIn = '<?php echo esc_js( is_user_logged_in() ); ?>';
			var itExchangeCartShippingAddress = <?php echo esc_js( (boolean) it_exchange_get_customer_shipping_address() ? 1 : 0); ?>;
			var itExchangeCartBillingAddress = <?php echo esc_js( (boolean) it_exchange_get_customer_billing_address() ? 1 : 0); ?>;
			jQuery(function () {

				<?php if ( $remove_others ) : ?>
				jQuery('.it-exchange-super-widget[data-might-remove="1"]').remove();
				<?php endif; ?>

				<?php $shipping_addons = it_exchange_get_enabled_addons( array( 'category' => 'shipping' ) ); if ( ! empty( $shipping_addons) ) : ?>
				// Shipping Init country/state fields
				var iteCountryStatesSyncOptions = {
					statesWrapper    : '.it-exchange-state',
					stateFieldID     : '#it-exchange-shipping-address-state',
					templatePart     : 'super-widget-shipping-address/elements/state',
					autoCompleteState: true
				};
				jQuery('#it-exchange-shipping-address-country', '.it-exchange-super-widget').itCountryStatesSync(iteCountryStatesSyncOptions).selectToAutocomplete().trigger('change');
				<?php endif; ?>

				// Billing Init fields
				var iteCountryStatesSyncOptions = {
					statesWrapper    : '.it-exchange-state',
					stateFieldID     : '#it-exchange-billing-address-state',
					templatePart     : 'super-widget-billing-address/elements/state',
					autoCompleteState: true,
				};
				jQuery('#it-exchange-billing-address-country', '.it-exchange-super-widget').itCountryStatesSync(iteCountryStatesSyncOptions).selectToAutocomplete().trigger('change');

				// Init Purchase Dialog
				itExchangeInitSWPurchaseDialogs();
			});
		</script>
		<?php
		// Print widget
		echo $args['before_widget'];
		?>
		<div class="it-exchange-super-widget it-exchange-super-widget-<?php esc_attr_e( $this->get_state() ); ?>" data-might-remove="<?php echo $might_remove; ?>">
			<style type="text/css">
				.it-exchange-super-widget .spinner {
					background: url('<?php echo get_admin_url(); ?>/images/wpspin_light.gif') no-repeat;
				}
			</style>
			<?php it_exchange_get_template_part( 'super-widget', $this->get_state() ); ?>
		</div>
		<?php
		echo $args['after_widget'];

		// Styles if set
		$css_url = ITUtility::get_url_from_file( dirname( __FILE__ ) . '/css/frontend-global.css' );
		if ( ! apply_filters( 'it_exchange_disable_super_widget_stylesheet', false ) ) {
			wp_enqueue_style( 'it-exchange-super-widget-frontend-global', $css_url );
		}

		if ( $args['enqueue_hide_script'] ) {
			$css_url = ITUtility::get_url_from_file( dirname( __FILE__ ) . '/css/single-product-super-widget.css' );
			wp_enqueue_style( 'it-exchange-single-product-super-widget', $css_url );
		}

		// Allow add-ons to enqueue styles for super-widget
		do_action( 'it_exchange_enqueue_super_widget_styles' );

		// JS
		$script_url = ITUtility::get_url_from_file( dirname( __FILE__ ) . '/js/super-widget.js' );
		wp_enqueue_script( 'it-exchange-super-widget', $script_url, array(
			'jquery',
			'jquery-ui-spinner',
			'detect-credit-card-type',
			'it-exchange-event-manager',
			'jquery.payment'
		), false, true );
		wp_localize_script( 'it-exchange-super-widget', 'exchangeSWL10n', array(
				'processingPaymentLabel' => __( 'Processing', 'it-l10n-ithemes-exchange' ),
				'processingAction'       => __( 'Processing... ', 'it-l10n-ithemes-exchange' ),
			)
		);

		// Autocomplete
		wp_enqueue_style( 'it-exchange-autocomplete-style' );

		// Country States sync
		wp_enqueue_script( 'it-exchange-country-states-sync', ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/country-states-sync.js' ), array(
			'jquery',
			'jquery-ui-autocomplete',
			'jquery-select-to-autocomplete',
			'it-exchange-super-widget'
		), false, true );

		// Allow add-ons to enqueue scripts for super-widget
		do_action( 'it_exchange_enqueue_super_widget_scripts' );

		// Remove superwidget flag
		if ( isset( $GLOBALS['it_exchange']['in_superwidget'] ) ) {
			unset( $GLOBALS['it_exchange']['in_superwidget'] );
		}

		if ( $this->in_sidebar || $this->did_loop_start ) {
			$this->rendered = true;
		}
	}

	/**
	 * Update a particular instance.
	 *
	 * This function should check that $new_instance is set correctly.
	 * The newly calculated value of $instance should be returned.
	 * If "false" is returned, the instance won't be saved/updated.
	 *
	 * @since 0.4.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 *
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	/**
	 * Echo the settings update form
	 *
	 * @since 0.4.0
	 *
	 * @param array $instance Current settings
	 *
	 * @return string
	 */
	function form( $instance ) {
		echo '<p class="no-options-widget">' . __( 'There are no options for this widget.' ) . '</p>';

		return 'noform';
	}

	/**
	 * Load the ajax script if requested
	 *
	 * @since 0.4.0
	 *
	 * @return void
	 */
	function load_ajax() {
		if ( ! empty( $_GET['it-exchange-sw-ajax'] ) ) {
			include( dirname( __FILE__ ) . '/ajax.php' );
			die();
		}
	}

	/**
	 * Grabs an array of valid states for the super widget
	 *
	 * @since 0.4.0
	 *
	 * @return array
	 */
	function set_valid_states() {
		$valid_states       = array(
			'registration',
			'login',
			'cart',
			'checkout',
			'product',
			'confirmation',
			'billing-address',
		);
		$valid_states       = apply_filters( 'it_exchange_super_widget_valid_states', $valid_states );
		$this->valid_states = (array) $valid_states;
	}

	/**
	 * Set the page options
	 *
	 * @since 0.4.0
	 *
	 * @return void
	 */
	function set_pages() {
		$this->pages = it_exchange_get_pages();
	}

	/**
	 * Determines if we are using permalinks or not
	 *
	 * @since 0.4.0
	 *
	 * @return void
	 */
	function set_using_permalinks() {
		$this->using_permalinks = (boolean) get_option( 'permalink_structure' );
	}

	/**
	 * Sets the current state of the widget
	 *
	 * @since 0.4.0
	 *
	 * @return false;
	 */
	function set_state() {

		// Get state from REQUEST
		$state                   = empty( $_REQUEST['ite-sw-state'] ) ? false : $_REQUEST['ite-sw-state'];
		$user_logged_in          = is_user_logged_in();
		$multi_item_cart_allowed = it_exchange_is_multi_item_cart_allowed();
		$items_in_cart           = (bool) it_exchange_get_cart_products();
		$it_exchange_view        = get_query_var( 'it_exchange_view' );

		if ( $items_in_cart && empty( $state ) ) {

			if ( 'product' == $it_exchange_view && ! it_exchange_is_current_product_in_cart() ) {
				$state = 'product';
			} else if ( $product = $this->_get_product_id_for_non_product_pages() ) {

				if ( it_exchange_is_product_in_cart( $product ) ) {
					$state = 'cart';
				} else {
					$state = 'product';
				}

				if ( ! $multi_item_cart_allowed && $state == 'cart' ) {
					$state = it_exchange_get_next_purchase_requirement_property( 'sw-template-part' );
				}

			} else if ( $multi_item_cart_allowed ) {
				$state = 'cart';
			} else if ( ! $multi_item_cart_allowed ) {
				$state = it_exchange_get_next_purchase_requirement_property( 'sw-template-part' );
			}
		} else if ( it_exchange_get_the_product_id() ) {
			$state = 'product';
		}

		// Grab the current state from the checkout requirements if trying to checkout
		if ( 'checkout' == $state ) {
			$state = it_exchange_get_next_purchase_requirement_property( 'sw-template-part' );
		}

		if ( empty( $state ) && ( 'product' == $it_exchange_view || $this->_get_product_id_for_non_product_pages() ) ) {
			$state = 'product';
		} else if ( empty( $state ) ) {
			$state = 'cart';
		}

		// Allow initial state to be filtered by 3rd parties
		$state = apply_filters( 'it_exchange_set_inital_sw_state', $state, $this->valid_states );

		// Validate state
		if ( $state && in_array( $state, $this->valid_states ) ) {
			$this->state = $state;
		}
	}

	/**
	 * Gets the value of the state property
	 *
	 * @since 0.4.0
	 *
	 * @return string
	 */
	function get_state() {

		$this->set_state();

		return empty( $this->state ) ? false : $this->state;
	}

	/**
	 * Get the fallback product ID to use on non-product pages.
	 *
	 * @since 1.32
	 *
	 * @return int|bool
	 */
	protected function _get_product_id_for_non_product_pages() {
		return apply_filters( 'it_exchange_super_widget_empty_product_id', false );
	}

	/**
	 * Maybe remove the SW from the sidebar if the SW shortcode is embedded on this page.
	 *
	 * @since 1.32
	 *
	 * @param $index
	 */
	public function maybe_remove_sw_from_sidebar( $index ) {

		$this->in_sidebar = true;

		if ( IT_Exchange_SW_Shortcode::has_shortcode() && ! ( is_archive() || is_home() ) ) {

			global $wp_registered_widgets;

			$widgets = wp_get_sidebars_widgets();

			if ( ! isset( $widgets[ $index ] ) ) {
				return;
			}

			$widgets = $widgets[ $index ];

			foreach ( $widgets as $widget ) {

				if ( strpos( $widget, 'it-exchange-super-widget' ) !== false ) {
					unset( $wp_registered_widgets[ $widget ] );
				}
			}
		}
	}

	/**
	 * Mark that we are no longer in the sidebar.
	 *
	 * @since 1.36.0
	 */
	public function mark_out_of_sidebar() {
		$this->in_sidebar = false;
	}

	/**
	 * Mark that the loop did start.
	 *
	 * @since 1.35.10.2
	 *
	 * @param \WP_Query $query
	 */
	public function mark_loop_did_start( $query ) {
		if ( $query instanceof WP_Query && $query->is_main_query() ) {
			$this->did_loop_start = true;
		}
	}
}

/**
 * Registers the widget with WordPress on the init_widgets action
 *
 * @since 0.4.0
 *
 * @return void
 */
function it_exchange_register_super_widget() {
	register_widget( 'IT_Exchange_Super_Widget' );
}

add_action( 'widgets_init', 'it_exchange_register_super_widget' );

/**
 * Are we in a superwidget instance
 *
 * @since 0.4.0
 * @return boolean
 */
function it_exchange_in_superwidget() {
	return isset( $GLOBALS['it_exchange']['in_superwidget'] );
}
