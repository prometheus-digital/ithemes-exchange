<?php
/**
 * This is a core add-on. It adds the Transaction Status metabox to the New / Edit Product view
 *
 * @since 0.3.3
 * @package IT_Cart_Buddy
*/
class IT_Cart_Buddy_Core_Addon_Transaction_Status_Meta_Box {
	
	/**
	 * Class constructor. Registers hooks
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function IT_Cart_Buddy_Core_Addon_Transaction_Status_Meta_Box() {
		add_action( 'it_cart_buddy_transaction_metabox_callback', array( $this, 'register_transaction_status_meta_box' ) );
		add_action( 'it_cart_buddy_save_transaction', array( $this, 'update_transaction_status' ) );
	}

	/**
	 * Register's the Transaction Status Metabox
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function register_transaction_status_meta_box( $post ) {
		add_meta_box( 'it_cart_buddy_transaction_status', __( 'Transaction Status', 'LION' ), array( $this, 'print_meta_box' ), $post->post_type, 'side' );
	}

	/**
	 * This method prints the contents of the metabox
	 *
	 * @since 0.3.3
	 * @void
	*/
	function print_meta_box( $post ) {
		$transaction = it_cart_buddy_get_transaction( $post );

		$current_status     = it_cart_buddy_get_transaction_status( $transaction );
		$transaction_method = it_cart_buddy_get_transaction_method( $transaction );

		if ( ! it_cart_buddy_add_on_supports( $transaction_method, 'transaction_status' ) )
			return;

		$available_statuses  = it_cart_buddy_get_add_on_support( $transaction_method, 'transaction_status' );
		$available_statuses = empty( $available_statuses['options'] ) ? array() : $available_statuses['options'];

		if ( count( $available_statuses ) < 2 ) {
			echo '<p>' . __( 'The transaction method used for this transaction does not support changing transaction statuses.', 'LION' ) . '</p>';
		} else {
			?><div id="it_cart_buddy_transaction_status_select"><?php
			foreach( $available_statuses as $slug => $name ) {
				?>
				<label for="it_cart_buddy_transaction_status-<?php esc_attr_e( $slug ); ?>">
					<input type="radio" id="it_cart_buddy_transaction_status-<?php esc_attr_e( $slug ); ?>" name="_it_cart_buddy_transaction_status" <?php checked( $slug, $current_status ); ?> value="<?php esc_attr_e( $slug ); ?>" /> <?php esc_attr_e( $name ); ?><br />
				</label>
				<?php
			}
			?></div><?php
		}
	}

	/**
	 * Updates the post_meta that holds the transaction_status 
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function update_transaction_status( $post ) {
		$transaction_status = false;

		// Ensure we're posting or return
		if ( empty( $_POST['_it_cart_buddy_transaction_status'] ) )
			return;

		// Ensure we have a WP post object or return
		$post = empty( $_POST['post_ID'] ) ? false: get_post( $_POST['post_ID'] );
		if ( empty( $post->ID ) )
			return;

		$transaction = it_cart_buddy_get_transaction( $post );
		if ( ! $transaction->ID )
			return;

		// If we have a product_type, update
		if ( $transaction )
			it_cart_buddy_update_transaction_status( $transaction, $_POST['_it_cart_buddy_transaction_status'] );
	}
}
global $pagenow;
if ( is_admin() && 'post.php' == $pagenow )
	new IT_Cart_Buddy_Core_Addon_Transaction_Status_Meta_Box(); 
