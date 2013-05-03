<?php
/**
 * Controls the super widget - which can also be output via a shortcode or a PHP functions
 *
 * @since 0.4.0
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
	 * @var string $it_exchange_view current view set by class.router.php
	 * @since 0.4.0
	*/
	var $it_exchange_view;

	/**
	 * Constructor: Init
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Exchange_Super_Widget() {
		$id_base = 'it-exchange-super-widget';
		$name    = __( 'iThemes Exchange Super Widget', 'LION' );
		$options = array(
			'description' => __( 'Handles Buy Now, Add to Cart, Cart Summary, Registration, Log in, and Confirmation views depending on the situation', 'LION' ),
		);
		parent::__construct( $id_base, $name, $options );

		if ( ! is_admin() ) {
			$this->set_pages();
			$this->set_using_permalinks();
			$this->set_valid_states();
			add_action( 'template_redirect', array( $this, 'load_ajax' ), 1 );
			add_action( 'template_redirect', array( $this, 'set_state' ), 11 );
			add_action( 'template_redirect', array( $this, 'enqueue_scripts' ), 11 );
		}
	}

	/**
	 * Outputs the widget content. This is a required method by the WP_Widget class
	 *
	 * @since 0.4.0
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget 
	 * @return void
	*/
	function widget( $args, $instance ) {
		if ( ! $this->get_state() )
			return false;

		// Some JS we're going to need
		?>
		<script type="text/javascript">
			var itExchangeSWAjaxURL = '<?php echo esc_js( get_home_url() . '?it-exchange-sw-ajax=1' );?>';
		</script>
		<?php
		// Print widget
		echo $args['before_widget'];
			?>
			<p>
				Temp menu for testing states<br />
				<?php foreach( $this->valid_states as $state ) : ?>
					<a class="it-exchange-test-load-state-via-ajax" data-it-exchange-sw-state="<?php esc_attr_e( $state ); ?>" href="?ite-sw-state=<?php esc_attr_e( $state ); ?>"><?php esc_attr_e( $state ); ?></a><br />
				<?php endforeach; ?>
			</p>
			<div class="it-exchange-super-widget it-exchange-super-widget-<?php esc_attr( $this->get_state() ); ?>">
				<?php it_exchange_get_template_part( 'super-widget', $this->get_state() ); ?>
			</div>
			<?php
		echo $args['after_widget'];
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
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update($new_instance, $old_instance) {
		return $new_instance;
	}    

	/**
	 * Echo the settings update form
	 *
	 * @since 0.4.0
	 *
	 * @param array $instance Current settings
	 * @return void
	 */
	function form($instance) {
		echo '<p class="no-options-widget">' . __('There are no options for this widget.') . '</p>';
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
		$valid_states = array(
			'registration',
			'login',
			'cart',
			'checkout',
			'purchase',
			'confirmation',
		);
		$valid_states = apply_filters( 'it_exchange_super_widget_valid_states', $valid_states );
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
		$this->pages = it_exchange_get_option( 'settings_pages' );
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
		$state = empty( $_REQUEST['ite-sw-state'] ) ? false : $_REQUEST['ite-sw-state'];

		// If state is empty and we're on a product page, set state to 'purchase'
		$state = empty( $state ) && 'product' == get_query_var( 'it_exchange_view' ) ? 'purchase' : $state;

		if ( $state && in_array( $state, $this->valid_states ) )
			$this->state = $state;
	}

	/**
	 * Enqueue scripts if needed
	 *
	 * @since 0.4.0
	*/
	function enqueue_scripts() {
		if ( ! $this->get_state() )
			return;

		$script_url = ITUtility::get_url_from_file( dirname( __FILE__ ) . '/js/super-widget.js' );
		wp_enqueue_script( 'it-exchange-super-widget', $script_url, array( 'jquery' ), false, true );
	}

	/**
	 * Gets the value of the state property
	 *
	 * @since 0.4.0
	 *
	 * @return string
	*/
	function get_state() {
		return empty( $this->state) ? false : $this->state;
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
