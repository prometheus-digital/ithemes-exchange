<?php
/**
 * Contains the email tag replacer class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Tag_Replacer
 */
class IT_Exchange_Email_Tag_Replacer {

	/**
	 * @var array
	 */
	private $context;

	/**
	 * IT_Exchange_Email_Tag_Replacer constructor.
	 */
	public function __construct() {
		add_shortcode( 'it_exchange_email', array( $this, 'shortcode' ) );
	}

	/**
	 * Replace the email tags.
	 *
	 * @since 1.36
	 *
	 * @param string $content
	 * @param array  $context
	 *
	 * @return string
	 */
	public function replace( $content, $context ) {

		$this->context = $context;

		it_exchange_email_notifications()->transaction_id = empty( $context['transaction'] ) ? false : $context['transaction']->ID;
		it_exchange_email_notifications()->customer_id    = empty( $context['customer'] ) ? false : $context['customer']->id;
		it_exchange_email_notifications()->user           = it_exchange_get_customer( it_exchange_email_notifications()->customer_id );

		return do_shortcode( $content );
	}

	/**
	 * Get shortcode functions.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	protected function get_shortcode_functions() {

		$shortcode_functions = array(
			'download_list'    => 'replace_download_list_tag',
			'name'             => 'replace_name_tag',
			'email'            => 'replace_email_tag',
			'fullname'         => 'replace_fullname_tag',
			'username'         => 'replace_username_tag',
			'order_table'      => 'replace_order_table_tag',
			'purchase_date'    => 'replace_purchase_date_tag',
			'total'            => 'replace_total_tag',
			'payment_id'       => 'replace_payment_id_tag',
			'receipt_id'       => 'replace_receipt_id_tag',
			'payment_method'   => 'replace_payment_method_tag',
			'sitename'         => 'replace_sitename_tag',
			'receipt_link'     => 'replace_receipt_link_tag',
			'login_link'       => 'replace_login_link_tag',
			'account_link'     => 'replace_account_link_tag',
			'shipping_address' => 'replace_shipping_address_tag',
			'billing_address'  => 'replace_billing_address_tag',
		);

		/**
		 * Filter the available shortcode functions.
		 *
		 * @since 1.0
		 *
		 * @param array $shortcode_functions
		 */
		return apply_filters( 'it_exchange_email_notification_shortcode_functions', $shortcode_functions, $this->get_data() );
	}

	/**
	 * Shortcode callback.
	 *
	 * @since 1.36
	 *
	 * @param array  $atts
	 * @param string $content
	 *
	 * @return string
	 */
	public function shortcode( $atts, $content = '' ) {

		$data = $this->get_data();

		$supported_pairs = array( 'show' => '', 'options' => '' );

		$atts = shortcode_atts( $supported_pairs, $atts );
		$tag  = $atts['show'];
		$opts = explode( ',', $atts['options'] );

		$functions = $this->get_shortcode_functions();

		$r = false;

		if ( ! empty( $functions[ $tag ] ) ) {
			if ( is_callable( array( $this, $functions[ $tag ] ) ) ) {
				$r = call_user_func( array(
					$this,
					$functions[ $tag ]
				), it_exchange_email_notifications(), $opts, $atts );
			} else if ( is_callable( $functions[ $tag ] ) ) {
				$r = call_user_func( $functions[ $tag ], it_exchange_email_notifications(), $opts, $atts );
			}
		}

		/**
		 * Filter the shortcode response.
		 *
		 * @since 1.0
		 *
		 * @param string $r
		 * @param array  $atts
		 * @param string $content
		 * @param array  $data
		 */
		return apply_filters( "it_exchange_email_notification_shortcode_{$tag}", $r, $atts, $content, $data );
	}

	/**
	 * Replace the download list tag.
	 *
	 * @since 1.0.0
	 *
	 * @param IT_Exchange_Email_Notifications $args
	 * @param array                           $options
	 *
	 * @return string
	 */
	public function replace_download_list_tag( $args, $options = array() ) {

		if ( ! $args || ! $args->transaction_id ) {
			return '';
		}

		$status_notice = '';
		ob_start();
		// Grab products attached to transaction
		$transaction_products = it_exchange_get_transaction_products( $args->transaction_id );

		// Grab all hashes attached to transaction
		$hashes = it_exchange_get_transaction_download_hash_index( $args->transaction_id );
		if ( ! empty( $hashes ) ) {
			?>
			<div style="border-top: 1px solid #EEE">
				<?php foreach ( $transaction_products as $transaction_product ) : ?>
					<?php if ( $product_downloads = it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads' ) ) : $downloads_exist_for_transaction = true; ?>
						<?php if ( ! it_exchange_transaction_is_cleared_for_delivery( $args->transaction_id ) ) : ?>
							<?php
							/* Status notice is blank by default and printed here, in the email if downloads are available.
							 * If downloads are not available for this transaction (tested in loop below), this echo of the status notice won't be printed.
							 * But we know that downloads will be available if the status changes so we set print the message instead of the files.
							 * If no files exist for the transaction, then there is no need to print this message even if status is pending
							 * Clear as mud.
							*/
							$status_notice = '<p>' . __( 'The status for this transaction does not grant access to downloadable files. Once the transaction is updated to an approved status, you will receive a follow-up email with your download links.', 'it-l10n-ithemes-exchange' ) . '</p>';
							$status_notice = '<h3>' . __( 'Available Downloads', 'it-l10n-ithemes-exchange' ) . '</h3>' . $status_notice;
							?>
						<?php else : ?>
							<h4><?php esc_attr_e( it_exchange_get_transaction_product_feature( $transaction_product, 'title' ) ); ?></h4>
							<?php $count = it_exchange_get_transaction_product_feature( $transaction_product, 'count' ); ?>
							<?php if ( $count > 1 && apply_filters( 'it_exchange_print_downlods_page_link_in_email', true, $args->transaction_id ) ) : ?>
								<?php $downloads_url = it_exchange_get_page_url( 'downloads' ); ?>
								<p><?php printf( __( 'You have purchased %d unique download link(s) for each file available with this product.%s%sEach link has its own download limits and you can view the details on your %sdownloads%s page.', 'it-l10n-ithemes-exchange' ), $count, '<br />', '<br />', '<a href="' . esc_url( $downloads_url ) . '">', '</a>' ); ?></p>
							<?php endif; ?>
							<?php foreach ( $product_downloads as $download_id => $download_data ) : ?>
								<?php $hashes_for_product_transaction = it_exchange_get_download_hashes_for_transaction_product( $args->transaction_id, $transaction_product, $download_id ); ?>
								<?php $hashes_found = ( ! empty( $hashes_found ) || ! empty( $hashes_for_product_transaction ) ); // If someone purchases a product prior to downloads existing, they dont' get hashes/downloads ?>
								<h5><?php esc_attr_e( get_the_title( $download_id ) ); ?></h5>
								<ul class="download-hashes">
									<?php foreach ( (array) $hashes_for_product_transaction as $hash ) : ?>
										<li>
											<a href="<?php echo esc_url( add_query_arg( 'it-exchange-download', $hash, get_home_url() ) ); ?>">
												<?php _e( 'Download link', 'it-l10n-ithemes-exchange' ); ?>
											</a>
											<span style="font-family: Monaco, monospace;font-size:12px;color:#AAA;">(<?php esc_attr_e( $hash ); ?>)</span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php
		}

		if ( empty( $downloads_exist_for_transaction ) || empty( $hashes_found ) ) {
			echo $status_notice;

			return ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}

	/**
	 * Replace the name tag.
	 *
	 * @since 1.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	public function replace_name_tag( $args, $options = array() ) {

		$name = '';

		if ( ! empty( $args->user->data->first_name ) ) {
			$name = $args->user->data->first_name;
		} else if ( ! empty( $args->user->data->display_name ) ) {
			$name = $args->user->data->display_name;
		} else if ( ! empty( $GLOBALS['it_exchange']['email-confirmation-data'][0]->customer_id ) && is_email( $GLOBALS['it_exchange']['email-confirmation-data'][0]->customer_id ) ) {
			// Guest Chekcout
			$name = $GLOBALS['it_exchange']['email-confirmation-data'][0]->customer_id;
		}

		return $name;
	}

	/**
	 * Replace the email tag.
	 *
	 * @since 1.14.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	public function replace_email_tag( $args, $options = null ) {

		$email = '';

		if ( ! empty( $args->user->data->user_email ) ) {
			$email = $args->user->data->user_email;
		}

		return $email;
	}

	/**
	 * Replace the fullname tag.
	 *
	 * @since 1.0.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	public function replace_fullname_tag( $args, $options = null ) {
		$fullname = '';

		if ( ! empty( $args->user->data->first_name ) && ! empty( $args->user->data->last_name ) ) {
			$fullname = $args->user->data->first_name . ' ' . $args->user->data->last_name;
		} else if ( ! empty( $args->user->data->display_name ) ) {
			$fullname = $args->user->data->display_name;
		}

		return $fullname;
	}

	/**
	 * Replace the username tag.
	 *
	 * @since 1.0.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	public function replace_username_tag( $args, $options = null ) {
		return empty( $args->user->data->user_login ) ? '' : $args->user->data->user_login;
	}

	/**
	 * Replace the order table tag.
	 *
	 * @since 1.0.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	public function replace_order_table_tag( $args, $options = null ) {

		$purchase_messages_heading = '<h3>' . __( 'Important Information', 'it-l10n-ithemes-exchange' ) . '</h3>';
		$purchase_messages         = '';
		$purchase_message_on       = false;

		if ( in_array( 'purchase_message', $options ) ) {
			$purchase_message_on = true;
		}

		ob_start();
		?>
		<table style="text-align: left; background: #FBFBFB; margin-bottom: 1.5em;border:1px solid #DDD;border-collapse: collapse;">
			<thead style="background:#F3F3F3;">
			<tr>
				<th style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Product', 'it-l10n-ithemes-exchange' ); ?></th>
				<th style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Quantity', 'it-l10n-ithemes-exchange' ); ?></th>
				<th style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Total Price', 'it-l10n-ithemes-exchange' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( $products = it_exchange_get_transaction_products( $args->transaction_id ) ) : ?>
				<?php foreach ( $products as $product ) : ?>
					<tr>
						<td style="padding: 10px;border:1px solid #DDD;">
							<?php echo apply_filters( 'it_exchange_email_notification_order_table_product_name', it_exchange_get_transaction_product_feature( $product, 'product_name' ), $product ); ?>
						</td>
						<td style="padding: 10px;border:1px solid #DDD;"><?php echo apply_filters( 'it_exchange_email_notification_order_table_product_count', it_exchange_get_transaction_product_feature( $product, 'count' ), $product ); ?></td>
						<td style="padding: 10px;border:1px solid #DDD;"><?php echo apply_filters( 'it_exchange_email_notification_order_table_product_subtotal', it_exchange_format_price( it_exchange_get_transaction_product_feature( $product, 'product_subtotal' ), $product ) ); ?></td>
					</tr>

					<?php
					// Generate Purchase Messages
					if ( $purchase_message_on && it_exchange_product_has_feature( $product['product_id'], 'purchase-message' ) ) {
						$purchase_messages .= '<h4>' . esc_attr( it_exchange_get_transaction_product_feature( $product, 'product_name' ) ) . '</h4>';
						$purchase_messages .= '<p>' . it_exchange_get_product_feature( $product['product_id'], 'purchase-message' ) . '</p>';
						$purchase_messages = apply_filters( 'it_exchange_email_notification_order_table_purchase_message', $purchase_messages, $product );
					}
					?>

				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
			<tfoot style="background:#F3F3F3;">
			<?php do_action( 'replace_order_table_tag_before_total_row', $args, $options ); ?>
			<tr>
				<td colspan="2" style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Total', 'it-l10n-ithemes-exchange' ); ?></td>
				<td style="padding: 10px;border:1px solid #DDD;"><?php echo it_exchange_get_transaction_total( $args->transaction_id, true ) ?></td>
			</tr>
			<?php do_action( 'replace_order_table_tag_after_total_row', $args, $options ); ?>
			</tfoot>
		</table>
		<?php

		$table = ob_get_clean();
		$table .= empty( $purchase_messages ) ? '' : $purchase_messages_heading . $purchase_messages;

		return $table;
	}

	/**
	 * Replace the purchase date tag.
	 *
	 * @since 1.0.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	function replace_purchase_date_tag( $args, $options = null ) {
		return it_exchange_get_transaction_date( $args->transaction_id );
	}

	/**
	 * Replace the total tag.
	 *
	 * @since 1.0.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	function replace_total_tag( $args, $options = null ) {
		return it_exchange_get_transaction_total( $args->transaction_id, true );
	}

	/**
	 * Replace the method ID.
	 *
	 * @since 1.0.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	function replace_payment_id_tag( $args, $options = null ) {
		return it_exchange_get_gateway_id_for_transaction( $args->transaction_id );
	}

	/**
	 * Replace the transaction order number.
	 *
	 * @since 1.0.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	function replace_receipt_id_tag( $args, $options = null ) {
		return it_exchange_get_transaction_order_number( $args->transaction_id );
	}

	/**
	 * Replace the payment method tag.
	 *
	 * @since 1.0.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	function replace_payment_method_tag( $args, $options = null ) {
		return it_exchange_get_transaction_method( $args->transaction_id );
	}

	/**
	 * Replace the sitename tag.
	 *
	 * @since 1.0.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	function replace_sitename_tag( $args, $options = null ) {
		return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	}

	/**
	 * Replace the confirmation url.
	 *
	 * @since 1.0.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	function replace_receipt_link_tag( $args, $options = null ) {
		return it_exchange_get_transaction_confirmation_url( $args->transaction_id );
	}

	/**
	 * Replace the login url.
	 *
	 * @since 1.0.2
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	function replace_login_link_tag( $args, $options = null ) {
		return it_exchange_get_page_url( 'login' );
	}

	/**
	 * Replace the account link tag.
	 *
	 * @since 1.4.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 *
	 * @return string Replaced value
	 */
	function replace_account_link_tag( $args, $options = null ) {
		return it_exchange_get_page_url( 'account' );
	}

	/**
	 * Replacement Shipping Address Tag
	 *
	 * @since 1.10.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 * @param array  $atts
	 *
	 * @return string Shipping Address
	 */
	function replace_shipping_address_tag( $args, $options = null, $atts = array() ) {
		if ( it_exchange_transaction_includes_shipping( $args->transaction_id ) ) {
			$address = it_exchange_get_transaction_shipping_address( $args->transaction_id );
			$before  = empty( $atts['before'] ) ? '' : $atts['before'];
			$after   = empty( $atts['after'] ) ? '' : $atts['after'];

			return empty( $address ) ? '' : $before . it_exchange_get_formatted_shipping_address( $address ) . $after;
		}

		return '';
	}

	/**
	 * Replacement Billing Address Tag
	 *
	 * @since 1.10.0
	 *
	 * @param object $args of IT_Exchange_Email_Notifications
	 * @param array  $options
	 * @param array  $atts
	 *
	 * @return string Billing Address
	 */
	function replace_billing_address_tag( $args, $options = null, $atts = array() ) {
		$address = it_exchange_get_transaction_billing_address( $args->transaction_id );
		if ( empty( $address ) ) {
			return '';
		}
		$before = empty( $atts['before'] ) ? '' : $atts['before'];
		$after  = empty( $atts['after'] ) ? '' : $atts['after'];

		return empty( $address ) ? '' : $before . it_exchange_get_formatted_billing_address( $address ) . $after;
	}

	/**
	 * Get the data array. This is mainly for back-compat.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			0 => empty( $this->context['transaction'] ) ? null : it_exchange_get_transaction( $this->context['transaction'] ),
			1 => it_exchange_email_notifications()
		);
	}
}