<?php
/**
 * Controls the super widget - which can also be output via a shortcode or a PHP functions
 *
 * @since 0.4.0
 * @package IT_Exchange
*/
class IT_Exchange_Super_Widget extends WP_Widget {
	
	/**
	 * @var string $state the current state of the widget
	 * @since 0.4.0
	*/
	var $state;

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

		$this->set_state();
		$this->enqueue_scripts();
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
			return '';

		// Print widget
		echo '<div class="it-exchange-super-widget-' . esc_attr( $this->get_state() ) . '">';
			echo $args['before_widget'];
				it_exchange_get_template_part( 'super-widget', $this->get_state() );
			echo '</div>';
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
	 * Sets the current state of the widget
	 *
	 * @since 0.4.0
	 *
	 * @param string $state optional. requested state. default is false.
	 * @return false;
	*/
	function set_state( $state=false ) {
		$valid_states = array(
			'registration',
			'login',
			'cart',
			'checkout',
			'purchase',
			'confirmation',
		);
		$valid_states = apply_filters( 'it_exchange_super_widget_valid_states', $valid_states );

		// Set state from param if passed and valid
		if ( $state && in_array( $state, $valid_states ) ) {
			$this->state = $state;
			return;
		}

		// State wasn't passed to the method
		$state = $this->determine_state();
		if ( $state && in_array( $state, $valid_states ) )
			$this->state = $state;

		// Return false if no valid state was found
		return false;
	}

	/**
	 * Enqueue scripts if needed
	 *
	 * @since 0.4.0
	*/
	function enqueue_scripts() {
		if ( ! $this->get_state() )
			return;
	}

	/**
	 * Determines the current state of the widget based on the current query_vars
	 *
	 * @since 0.4.0
	 *
	 * @return string state of widget
	*/
	function determine_state() {
		return 'purchase';
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
