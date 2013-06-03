<?php
/**
 * Creates a widget for Featured Products
 * @package IT_Exchange
 * @since 0.4.0
*/

class IT_Exchange_Featured_Product extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'it_exchange_featured_product',
			__( 'Exchange Featured Product', 'LION' ),
			array(
				'description' => __( 'Displays a product as featured in WordPress sidebars.', 'LION' ),
			)
		);
		if ( ! is_admin() )
			add_filter( 'it_exchange_possible_template_paths', array( $this, 'register_template_directory' ) );
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
		$product = $instance['product'];

		echo $before_widget;

		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;

		$global_product = empty( $GLOBALS['it_exchange']['product'] ) ? false : $GLOBALS['it_exchange']['product'];
		$GLOBALS['it_exchange']['product'] = it_exchange_get_product( $product );

		it_exchange_get_template_part( 'featured-product-widget' );
		if ( $global_product )
			$GLOBALS['it_exchange']['product'] = $global_product;

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

		$product = empty( $new_instance['product'] ) ? false : absint( $new_instance['product'] );
		$instance['product'] = ( $product and it_exchange_get_product( $product ) ) ? $product : false;

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
		$product = empty( $instance['product'] ) ? 0 : $instance['product'];
		$products = it_exchange_get_products( array( 'posts_per_page' => -1 ) );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'LION' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<?php if ( $products ) : ?>
		<label for="<?php echo $this->get_field_id( 'product' ); ?>"><?php _e( 'Featured Product:', 'LION' ); ?></label> 
		<select id="<?php echo $this->get_field_id( 'product' ); ?>" name="<?php echo $this->get_field_name( 'product' ); ?>">
			<?php foreach( $products as $prod ) { ?>
				<option value="<?php esc_attr_e( $prod->ID ); ?>" <?php selected( $product, $prod->ID ); ?>><?php esc_attr_e( $prod->post_title ); ?></option>
			<?php } ?>
		</select>
		<?php else : ?>
			<?php _e( 'No products created yet.', 'LION' ); ?>
		<?php endif; ?>
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
	function register_template_directory( $existing ) { 
		$directory = dirname( __FILE__ ) . '/templates';
		$existing[] = $directory;
		return $existing;
	} 
}
add_action( 'widgets_init', create_function( '', 'register_widget( "it_exchange_featured_product" );' ) );
