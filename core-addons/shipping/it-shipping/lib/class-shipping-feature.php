<?php
/**
 * Extend this class to create new shipping features to use with your shipping add-on
*/

class IT_Exchange_Shipping_Feature {
	
	var $slug;
	var $product;
	var $values;

	/**
	 * Class constructor
	 *
	 * @since CHANGEME
	 *
	 * @param  mixed $product exchange product id, object, or empty to attempt to pick up the global product
	 * @param  array $options options for the object
	 * @return void
	*/
	function __construct( $product=false, $options=array() ) {

		// Set the product
		$this->set_product( $product );

		// Set the shipping feature values for the current product
		$this->set_values();
	}

	/**
	 * Sets the product
	 *
	 * @since CHANGEME
	 *
	 * @param  mixed $product exchange product id, object, or empty to attempt to pick up the global product
	 * @return void
	*/
	function set_product( $product=false ) {
		if ( $product  ) {
			$product = it_exchange_get_product( $product );
		} else {

			// Grab global $post
			global $post;

			// If post is set in REQUEST, use it.
			if ( isset( $_REQUEST['post'] ) ) 
				$post_id = (int) $_REQUEST['post'];
			elseif ( isset( $_REQUEST['post_ID'] ) ) 
				$post_id = (int) $_REQUEST['post_ID'];
			else
				$post_id = empty( $post->ID ) ? 0 : $post->ID;

			if ( $post_id )
				$post = get_post( $post_id );

			if ( isset( $post ) && ! empty( $post ) ) 
				$product = it_exchange_get_product( $post );
		}
		$this->product = $product;
	}

	function print_add_edit_feature_box() {
		?>
		<div class="shipping-feature <?php esc_attr_e( $this->slug ); ?>">
			<?php $this->print_add_edit_feature_box_interior(); ?>
		</div>
		<?php
	}
}
