<?php
/**
 * Contains the class or the email notifications object
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * The IT_Exchange_Email_Notifications class is for sending out email notification using wp_mail()
 *
 * @since 0.4.0
*/
class IT_Exchange_Email_Notifications {
	
	private $transaction_id;
	private $customer_id;
	private $user;

	/**
	 * Constructor. Sets up the class
	 *
	 * @since 0.4.0
	*/
	function IT_Exchange_Email_Notifications() {
		// Send emails on successfull transaction
		add_action( 'it_exchange_add_transaction_success', array( $this, 'send_purchase_emails' ), 20 );

		// Send emails when admin requests a resend
		add_action( 'admin_init', array( $this, 'handle_resend_confirmation_email_requests' ) );
		
		// Resends email notifications when status is changed from one that's not cleared for delivery to one that is cleared
		add_action( 'it_exchange_update_transaction_status', array( $this, 'resend_if_transaction_status_gets_cleared_for_delivery' ), 10, 3 );

		add_shortcode( 'it_exchange_email', array( $this, 'ithemes_exchange_email_notification_shortcode' ) );

	}
	
	/**
	 * Listens for the resend email request and passes along to send_purchase_emails
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function handle_resend_confirmation_email_requests() {
		// Abort if not requested
		if ( empty( $_GET[ 'it-exchange-customer-transaction-action' ] ) || $_GET[ 'it-exchange-customer-transaction-action' ] != 'resend' )
			return;

		// Abort if no transaction or invalid transaction was passed
		$transaction = it_exchange_get_transaction( $_GET['id'] );
		if ( empty( $transaction->ID ) ) {
			it_exchange_add_message( 'error', __( 'Invalid transaction. Confirmation email not sent.', 'LION' ) );
			$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
			wp_redirect( $url );
			die();
		}

		// Abort if nonce is bad
		$nonce = empty( $_GET['_wpnonce'] ) ? false : $_GET['_wpnonce'];
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'it-exchange-resend-confirmation-' . $transaction->ID ) ) {
			it_exchange_add_message( 'error', __( 'Confirmation Email not sent. Please try again.', 'LION' ) );
			$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
			wp_redirect( $url );
			die();
		}

		// Abort if user doesn't have permission
		if ( ! current_user_can( 'administrator' ) ) {
			it_exchange_add_message( 'error', __( 'You do not have permission to resend confirmation emails.', 'LION' ) );
			$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
			wp_redirect( $url );
			die();
		}

		// Resend w/o admin notification
		$this->send_purchase_emails( $transaction, false );
		it_exchange_add_message( 'notice', __( 'Confirmation email resent', 'LION' ) );
		$url = remove_query_arg( array( 'it-exchange-customer-transaction-action', '_wpnonce' ) );
		wp_redirect( $url );
		die();
	}

	/**
	 * Process the transaction and send appropriate emails
	 *
	 * @since 0.4.0
	 *
	 * @param mixed $transaction ID or object
	 * @param int $customer_id The customer ID
	 * @return void
	*/
	function send_purchase_emails( $transaction, $send_admin_email=true ) {

		$transaction = it_exchange_get_transaction( $transaction );
		if ( empty( $transaction->ID ) )
			return;
		
		$this->transaction_id 	= $transaction->ID;
		$this->customer_id 		= it_exchange_get_transaction_customer_id( $this->transaction_id );
		$this->user 			= get_userdata( $this->customer_id );
		
		$settings = it_exchange_get_option( 'settings_email' );	

		// Edge case where sale is made before admin visits email settings.
		if ( empty( $settings['receipt-email-name'] ) && ! isset( $IT_Exchange_Admin ) ) {
			global $IT_Exchange;
			include_once( dirname( dirname( __FILE__ ) ) . '/admin/class.admin.php' );
		//	$IT_Exchange_Admin = new IT_Exchange_Admin( $IT_Exchange );
			add_filter( 'it_storage_get_defaults_exchange_settings_email', array( 'IT_Exchange_Admin', 'set_email_settings_defaults' ) );
			$settings = it_exchange_get_option( 'settings_email', true );	
		}
		
		$headers[] = 'From: ' . $settings['receipt-email-name'] . ' <' . $settings['receipt-email-address'] . '>';
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-Type: text/html';
		$headers[] = 'charset=utf-8';
		
		$subject = do_shortcode( $settings['receipt-email-subject'] );
		$body    = apply_filters( 'send_purchase_emails_body', $settings['receipt-email-template'] );
		$body    = apply_filters( 'send_purchase_emails_body_' . it_exchange_get_transaction_method( $transaction->ID ), $body );
		$body    = $this->body_header() . wpautop( do_shortcode( $body ) ) . $this->body_footer();
		
		wp_mail( $this->user->user_email, strip_tags( $subject ), $body, $headers );
		
		// Send admin notification if param is true and email is provided in settings
		if ( $send_admin_email && ! empty( $settings['notification-email-address'] ) ) {
			
			
			$subject = do_shortcode( $settings['admin-email-subject'] );
			$body    = apply_filters( 'send_admin_emails_body', $settings['admin-email-template'] );
			$body    = apply_filters( 'send_admin_emails_body_' . it_exchange_get_transaction_method( $transaction->ID ), $body );
			$body    = $this->body_header() . wpautop( do_shortcode( $body ) ) . $this->body_footer();
			
			$emails = explode( ',', $settings['notification-email-address'] );
			
			foreach ( $emails as $email ) {
				
				wp_mail( trim( $email ), strip_tags( $subject ), $body, $headers );
			
			}
		
		}

	}
	
	/**
	 * Returns Email HTML header
	 *
	 * @since 0.4.0
	 *
	 * @return string HTML header
	*/
	function body_header() {
		
		$output  = '<html>';
		$output .= '<head>';
		$output .= '	<style type="text/css">#outlook a { padding: 0; }</style>';
		$output .= '</head>';
		$output .= '<body>';
		
		return apply_filters( 'it_exchange_email_notification_body_header', $output );
		
	}
	
	/**
	 * Returns Email HTML footer
	 *
	 * @since 0.4.0
	 *
	 * @return string HTML footer
	*/
	function body_footer() {
		
		$output  = '</body>';
		$output .= '</html>';
		
		return apply_filters( 'it_exchange_email_notification_body_footer', $output );
		
	}
	
	/**
	 * Get available template tags
	 * Array of tags (key) and callback functions (value)
	 *
	 * @since 0.4.0
	 *
	 * @return array available replacement template tags
	*/
	function get_shortcode_functions() {
	
		//Key = replacement tag
		//Value = callback function
		$shortcode_functions = array(
			'download_list'  => 'it_exchange_replace_download_list_tag',
			'name'           => 'it_exchange_replace_name_tag',
			'fullname'       => 'it_exchange_replace_fullname_tag',
			'username'       => 'it_exchange_replace_username_tag',
			'order_table'    => 'it_exchange_replace_order_table_tag',
			'purchase_date'  => 'it_exchange_replace_purchase_date_tag',
			'total'          => 'it_exchange_replace_total_tag',
			'payment_id'     => 'it_exchange_replace_payment_id_tag',
			'receipt_id'     => 'it_exchange_replace_receipt_id_tag',
			'payment_method' => 'it_exchange_replace_payment_method_tag',
			'sitename'       => 'it_exchange_replace_sitename_tag',
			'receipt_link'   => 'it_exchange_replace_receipt_link_tag',
		);
		
		return apply_filters( 'it_exchange_email_notification_shortcode_functions', $shortcode_functions );
		
	}
	
	/**
	 * This shortcode is intended to print an email arguments for email templates
	 *
	 * @since 0.4.0
	 * @param array $atts attributess passed from WP Shortcode API
	 * @param string $content data passed from WP Shortcode API
	 * @return string html for the 'Add to Shopping Cart' HTML
	*/
	function ithemes_exchange_email_notification_shortcode( $atts, $content='' ) {
		
		$supported_pairs = array(
			'show'    => '',
			'options' => '',
		);
		// Merge supported_pairs with passed attributes
		extract( shortcode_atts( $supported_pairs, $atts ) );
		
		$shortcode_functions = $this->get_shortcode_functions();
			
		if ( !empty( $shortcode_functions[$show] ) )
			return call_user_func( array( $this, $shortcode_functions[$show] ), $this, explode( ',', $options ) );
		else
			return;
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 * @todo better way to get this URL????
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_download_list_tag( $args, $options = NULL ) {
		ob_start();
		// Grab products attached to transaction
		$transaction_products = it_exchange_get_transaction_products( $args->transaction_id );

		// Grab all hashes attached to transaction
		$hashes   = it_exchange_get_transaction_download_hash_index( $args->transaction_id );
		?>
		<?php foreach ( $transaction_products as $transaction_product ) : ?>
			<?php
				$product_id = $transaction_product['product_id'];
				$db_product = it_exchange_get_product( $transaction_product );
			?>
			<h3><?php esc_attr_e( it_exchange_get_transaction_product_feature( $transaction_product, 'title' ) ); ?></h3>
				<?php if ( $product_downloads = it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads' ) ) : ?>
					<?php $count = it_exchange_get_transaction_product_feature( $transaction_product, 'count' ); ?>
					<?php if ( $count > 1 ) : ?>
						<?php $downloads_url = it_exchange_get_page_url( 'downloads' ); ?>
						<p><?php printf( __( 'You have purchased %d unique download link(s) for each file available with this product.%sEach link has its own download limits.%sYou can view the details on you %sdownloads%s page.', 'LION' ), $count, '<br />', '<br />', '<a href="' . $downloads_url . '">', '</a>' ); ?></p>
					<?php endif; ?>
					<?php if ( ! it_exchange_transaction_is_cleared_for_delivery( $args->transaction_id ) ) : ?>
						<p><?php _e( 'The status for this transaction does not grant access to downlodable files. Once the transaction is updated to an appoved status, you will receive a follup email with your download links.', 'LION' ); ?></p>
					<?php endif; ?>
					<?php foreach( $product_downloads as $download_id => $download_data ) : ?>
						<?php $hashes_for_product_transaction = it_exchange_get_download_hashes_for_transaction_product( $args->transaction_id, $transaction_product, $download_id ); ?>
						<h4><?php esc_attr_e( get_the_title( $download_id ) ); ?></h4>
						<?php if ( it_exchange_transaction_is_cleared_for_delivery( $args->transaction_id ) ) : ?>
							<ul class="download-hashes">
								<?php foreach( (array) $hashes_for_product_transaction as $hash ) : ?>
									<?php
									$hash_data      = it_exchange_get_download_data_from_hash( $hash );
									$download_limit = ( 'unlimited' == $hash_data['download_limit'] ) ? __( 'Unlimited', 'LION' ) : $hash_data['download_limit'];
									$downloads      = empty( $hash_data['downloads'] ) ? (int) 0 : absint( $hash_data['downloads'] );
									?>
									<li>
										<?php esc_attr_e( $hash ); ?> : <a href="<?php echo site_url() . '?it-exchange-download=' . $hash; ?>"><?php _e( 'Download link', 'LION' ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
		<?php endforeach; ?>
		<?php	
		return ob_get_clean();
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_name_tag( $args, $options = NULL ) {
		if ( !empty( $this->user->first_name ) ) {
			$name = $this->user->first_name;
		} else {
			$name = $this->user->display_name;
		}
		
		return $name;
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_fullname_tag( $args, $options = NULL ) {
		if ( !empty( $this->user->first_name ) ) {
			$fullname = $this->user->first_name . ' ' . $this->user->last_name;
		} else {
			$fullname = $this->user->display_name;
		}
		
		return $fullname;
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_username_tag( $args, $options = NULL ) {
		return $this->user->user_login;
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_order_table_tag( $args, $options = NULL ) {
		
		$purchase_message_on = false;
		
		if ( in_array( 'purchase_message', $options ) )
			$purchase_message_on = true;
		
		$table  = '<table>';
		
		$table .= ' <thead>';
		$table .= '  <tr>';
		$table .= '    <th>' . __( 'Product', 'LION' ) . '</th>';
		$table .= '    <th>' . __( 'Quantity', 'LION' ) . '</th>';
		$table .= '    <th>' . __( 'Total Price', 'LION' ) . '</th>';
		$table .= '  <tr>';
		$table .= ' </thead>';
		
		$table .= ' <tbody>';
		if ( $products = it_exchange_get_transaction_products( $this->transaction_id ) ) {
			foreach ( $products as $product ) {
				$table .= '  <tr>';
				$table .= '    <td>';
				$table .= esc_attr( it_exchange_get_transaction_product_feature( $product, 'product_name' ) );
				
				if ( $purchase_message_on )
					$table .= '<p>' . esc_attr( it_exchange_get_product_feature( $product['product_id'], 'purchase-message' ) ) . '</p>'; 
				
				$table .= '</td>';
				$table .= '    <td>' . esc_attr( it_exchange_get_transaction_product_feature( $product, 'count' ) ) . '</td>';
				$table .= '    <td>' . esc_attr( it_exchange_format_price( it_exchange_get_transaction_product_feature( $product, 'product_subtotal' ) ) )  . '</td>';
				$table .= '  <tr>';
			}
		}
		$table .= ' </tbody>';
		
		$table .= ' <tfoot>';
		$table .= '  <tr>';
		$table .= '    <td colspan="2">' . __( 'Total', 'LION' ) . '</td>';
		$table .= '    <td>' . it_exchange_get_transaction_total( $this->transaction_id, true ) . '</td>';
		$table .= '  <tr>';
		$table .= ' </tfoot>';
		
		$table .= '</table>';
		
		return $table;
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_purchase_date_tag( $args, $options = NULL ) {
		return it_exchange_get_transaction_date( $this->transaction_id );
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_total_tag( $args, $options = NULL ) {
		return it_exchange_get_transaction_total( $this->transaction_id, true );	
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_payment_id_tag( $args, $options = NULL ) {
		return it_exchange_get_gateway_id_for_transaction( $this->transaction_id ); 
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_receipt_id_tag( $args, $options = NULL ) {
		return it_exchange_get_transaction_order_number( $this->transaction_id );		
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_payment_method_tag( $args, $options = NULL ) {
		return it_exchange_get_transaction_method( $this->transaction_id );
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_sitename_tag( $args, $options = NULL ) {
		return get_bloginfo( 'name' );
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @return string Replaced value
	*/
	function it_exchange_replace_receipt_link_tag( $args, $options = NULL ) {
		return it_exchange_get_transaction_confirmation_url( $this->transaction_id );
	}

	/**
	 * Resends the email to the customer if the transaction status was changed from not cleared for delivery to cleared.
	 *
	 * @since 0.4.11
	 *
	 * @param object $transaction the transaction object
	 * @param string $old_status the status it was just changed from
	 * @param boolean $old_status_cleared was the old status cleared for delivery?
	 * @return void
	*/
	function resend_if_transaction_status_gets_cleared_for_delivery( $transaction, $old_status, $old_status_cleared ) {
		// Using ->ID here so that get_transaction forces a reload and doesn't use the old object with the old status
		$new_status = it_exchange_get_transaction_status( $transaction->ID );
		$new_status_cleared = it_exchange_transaction_is_cleared_for_delivery( $transaction->ID );

		if ( ( $new_status != $old_status ) && ! $old_status_cleared && $new_status_cleared )
			$this->send_purchase_emails( $transaction, false );
	}
}
$IT_Exchange_Email_Notifications = new IT_Exchange_Email_Notifications();
