<?php
/**
 * This is a core add-on. It adds the Transaction Status metabox to the New / Edit Product view
 *
 * @since 0.3.3
 * @package IT_Exchange
*/
class IT_Exchange_Core_Addon_Transaction_Status_Meta_Box {
	
	/**
	 * Class constructor. Registers hooks
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function IT_Exchange_Core_Addon_Transaction_Status_Meta_Box() {
		if ( is_admin() ) {
			add_action( 'it_exchange_transaction_metabox_callback', array( $this, 'register_transaction_status_meta_box' ) );
			add_action( 'it_exchange_save_transaction', array( $this, 'update_transaction_status' ) );
		}
	}

	/**
	 * Register's the Transaction Status Metabox
	 *
	 * @since 0.3.3
	 * @return void
	*/
	function register_transaction_status_meta_box( $post ) {
		add_meta_box( 'it_exchange_transaction_status', __( 'Transaction Status', 'LION' ), array( $this, 'print_meta_box' ), $post->post_type, 'side' );
	}

	/**
	 * This method prints the contents of the metabox
	 *
	 * @since 0.3.3
	 * @void
	*/
	function print_meta_box( $post ) {
		$transaction = it_exchange_get_transaction( $post );
		$transaction_method = it_exchange_get_transaction_method( $transaction );

		if ( ! it_exchange_addon_supports( $transaction_method, 'transaction_status' ) )
			return;
			
		$current_status = it_exchange_get_transaction_status( $transaction );
		$available_statuses = it_exchange_get_addon_support( $transaction_method, 'transaction_status' );
		$available_statuses = empty( $available_statuses['options'] ) ? array() : $available_statuses['options'];

		if ( count( $available_statuses ) < 2 ) {
			esc_attr_e( it_exchange_get_transaction_status_label( $transaction ) );
		} else {
			?><div id="it-exchange-transaction-status-select"><?php
			foreach( $available_statuses as $slug => $name ) {
				?>
				<label for="it-exchange-transaction-status-<?php esc_attr_e( $slug ); ?>">
					<input type="radio" id="it-exchange-transaction-status-<?php esc_attr_e( $slug ); ?>" name="it-exchange-transaction-status" <?php checked( $slug, $current_status ); ?> value="<?php esc_attr_e( $slug ); ?>" /> <?php esc_attr_e( $name ); ?><br />
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
		if ( empty( $_POST['it-exchange-transaction-status'] ) )
			return;

		// Ensure we have a WP post object or return
		$post = empty( $_POST['post_ID'] ) ? false: get_post( $_POST['post_ID'] );
		if ( empty( $post->ID ) )
			return;

		$transaction = it_exchange_get_transaction( $post );
		if ( ! $transaction->ID )
			return;

		// If we have a product_type, update
		if ( $transaction )
			it_exchange_update_transaction_status( $transaction, $_POST['it-exchange-transaction-status'] );
	}
}
global $pagenow;
if ( is_admin() && 'post.php' == $pagenow )
	new IT_Exchange_Core_Addon_Transaction_Status_Meta_Box();
