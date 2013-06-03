<?php
/**
 * Creates a widget for Cart Summary
 * @package IT_Exchange
 * @since 0.4.0
*/

class IT_Exchange_Cart_Summary extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'it_exchange_cart_summary',
			__( 'Exchange Cart Summary', 'LION' ),
			array(
				'description' => __( 'Displays the iThemes Exchange cart summary.', 'LION' ),
			)
		);
		if ( ! is_admin() )
			add_filter( 'it_exchange_possible_template_paths', array( $this, 'register_template_directory' ), 10, 2 );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = $instance['title'];

		echo $before_widget;

		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;

		it_exchange_get_template_part( 'cart-summary-widget' );

		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'Featured Product', 'LION' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'LION' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

	/**
	 * Register template directory so that exchange can locate the default template part
	 *
	 * @since 0.4.0
	 *
	 * @param array $existing existing locations
	 * @return array
	*/
	function register_template_directory( $existing, $template_names=array() ) {
		if ( in_array( 'cart-summary-widget', $template_names ) ) {
			$directory = dirname( __FILE__ ) . '/templates';
			$existing[] = $directory;
		}
		return $existing;
	}
}
add_action( 'widgets_init', create_function( '', 'register_widget( "it_exchange_cart_summary" );' ) );
