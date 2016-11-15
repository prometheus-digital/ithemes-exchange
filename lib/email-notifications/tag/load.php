<?php
/**
 * Load the tag replacements.
 *
 * @since   2.0.0
 * @license GPLv2
 */

IT_Exchange_Email_Register_Default_Tags::get_instance();

/**
 * Class IT_Exchange_Email_Register_Default_Tags
 */
class IT_Exchange_Email_Register_Default_Tags {

	/**
	 * @var IT_Exchange_Email_Register_Default_Tags
	 */
	private static $instance = null;

	/**
	 * @return IT_Exchange_Email_Register_Default_Tags
	 */
	public static function get_instance() {
		
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	/**
	 * IT_Exchange_Email_Register_Default_Tags constructor.
	 */
	public function __construct() {
		add_action( 'it_exchange_email_notifications_register_tags', array( $this, 'register' ) );
	}

	/**
	 * Register the default tags.
	 *
	 * @since 2.0.0
	 *
	 * @param IT_Exchange_Email_Tag_Replacer $replacer
	 */
	public function register( IT_Exchange_Email_Tag_Replacer $replacer ) {

		$tags = array(
			'download_list'       => array(
				'name'      => __( 'Download List', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'A list of download links for each download purchased.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'transaction' ),
				'available' => array( 'admin-order' )
			),
			'order_table'         => array(
				'name'      => __( 'Order Table', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'A table of the order details. Accept "purchase_message" option.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'transaction' ),
				'available' => array( 'admin-order' )
			),
			'purchase_date'       => array(
				'name'      => __( 'Purchase Date', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'The date of the purchase.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'transaction' ),
				'available' => array( 'admin-order', 'receipt' )
			),
			'total'               => array(
				'name'      => __( 'Total', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'The total price of the purchase.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'transaction' ),
				'available' => array( 'admin-order', 'receipt' )
			),
			'payment_id'          => array(
				'name'      => __( 'Method ID', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'The payment method ID for this purchase.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'transaction' ),
				'available' => array( 'admin-order', 'receipt' )
			),
			'receipt_id'          => array(
				'name'      => __( 'Receipt ID', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'The unique ID number for this purchase.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'transaction' ),
				'available' => array( 'admin-order' )
			),
			'payment_method'      => array(
				'name'      => __( 'Payment Method', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'The method of payment used for this purchase.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'transaction' ),
				'available' => array( 'admin-order' )
			),
			'shipping_address'    => array(
				'name'      => __( 'Shipping Address', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'The shipping address for this purchase. Blank if shipping is not required.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'transaction' ),
				'available' => array( 'admin-order' )
			),
			'billing_address'     => array(
				'name'      => __( 'Billing Address', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'The billing address for this purchase. Blank if billing is not required.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'transaction' ),
				'available' => array( 'admin-order' )
			),
			'receipt_link'        => array(
				'name'      => __( 'Receipt URL', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'Adds a link so users can view their receipt directly on your website.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'transaction' ),
				'available' => array( 'admin-order', 'receipt' )
			),
			'first_name'          => array(
				'name'      => __( 'First Name', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( "The recipient's first name, or display name if none given.", 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'recipient' ),
				'available' => array()
			),
			'last_name'           => array(
				'name'      => __( 'Last Name', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( "The recipient's last name, or display name if none given.", 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'recipient' ),
				'available' => array()
			),
			'fullname'            => array(
				'name'      => __( 'Full Name', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( "The recipient's full name.", 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'recipient' ),
				'available' => array()
			),
			'username'            => array(
				'name'      => __( 'Username', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( "The recipient's username on the site, if any.", 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'recipient' ),
				'available' => array()
			),
			'email'               => array(
				'name'      => __( 'Email', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( "The recipient's email address.", 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'recipient' ),
				'available' => array()
			),
			'customer_first_name' => array(
				'name'      => __( 'First Name', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( "The customer's first name, or display name if empty.", 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'recipient' ),
				'available' => array( 'admin-order', 'receipt', 'customer-order-note' )
			),
			'customer_last_name'  => array(
				'name'      => __( 'Last Name', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( "The customer's last name, or display name if empty.", 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'recipient' ),
				'available' => array( 'admin-order', 'receipt', 'customer-order-note' )
			),
			'customer_fullname'   => array(
				'name'      => __( 'Full Name', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( "The customer's full name.", 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'recipient' ),
				'available' => array( 'admin-order', 'receipt', 'customer-order-note' )
			),
			'customer_username'   => array(
				'name'      => __( 'Username', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( "The customer's username on the site, if they registered an account.", 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'customer' ),
				'available' => array( 'admin-order', 'receipt', 'customer-order-note' )
			),
			'customer_email'      => array(
				'name'      => __( 'Email', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( "The customer's email address.", 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'customer' ),
				'available' => array( 'admin-order', 'receipt', 'customer-order-note' )
			),
			'login_link'          => array(
				'name'      => __( 'Login URL', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'Adds a link to the login page on your website.', 'it-l10n-ithemes-exchange' ),
				'context'   => array(),
				'available' => array()
			),
			'account_link'        => array(
				'name'      => __( 'Account URL', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'Adds a link to the customer\'s account page on your website.', 'it-l10n-ithemes-exchange' ),
				'context'   => array(),
				'available' => array()
			),
			'profile_link'        => array(
				'name'      => __( 'Profile URL', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'Adds a link to the customer\'s profile page on your website.', 'it-l10n-ithemes-exchange' ),
				'context'   => array(),
				'available' => array()
			),
			'company_name'        => array(
				'name'      => __( 'Company Name', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'The name of your company specified in the general settings.' ),
				'context'   => array(),
				'available' => array()
			)
		);

		foreach ( $tags as $tag => $config ) {

			$obj = new IT_Exchange_Email_Tag_Base( $tag, $config['name'], $config['desc'], array( $this, $tag ) );

			foreach ( $config['context'] as $context ) {
				$obj->add_required_context( $context );
			}

			foreach ( $config['available'] as $notification ) {
				$obj->add_available_for( $notification );
			}

			$replacer->add_tag( $obj );
		}
	}

	/**
	 * Replace the download list tag.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 *
	 * @return string
	 */
	public function download_list( $context = array() ) {

		if ( empty( $context['transaction'] ) ) {
			return '';
		}

		$transaction = $context['transaction'];

		$notice = '';
		ob_start();

		$transaction_products = it_exchange_get_transaction_products( $transaction );
		$hashes               = it_exchange_get_transaction_download_hash_index( $transaction );

		if ( ! empty( $hashes ) ) : ?>
			<div style="border-top: 1px solid #EEE">
				<?php foreach ( $transaction_products as $transaction_product ) :

					if ( ! $product_downloads = it_exchange_get_product_feature( $transaction_product['product_id'], 'downloads' ) ) :
						continue;
					endif;

					$downloads_exist_for_transaction = true;

					if ( ! it_exchange_transaction_is_cleared_for_delivery( $transaction ) ) :

						/* Status notice is blank by default and printed here, in the email if downloads are available.
						 * If downloads are not available for this transaction (tested in loop below), this echo of the status notice won't be printed.
						 * But we know that downloads will be available if the status changes so we set print the message instead of the files.
						 * If no files exist for the transaction, then there is no need to print this message even if status is pending
						 * Clear as mud.
						*/
						$notice = __( 'The status for this transaction does not grant access to downloadable files.', 'it-l10n-ithemes-exchange' ) . ' ' .
						          __( 'Once the transaction is updated to an approved status, you will receive a follow-up email with your download links.', 'it-l10n-ithemes-exchange' );

						$notice = "<p>$notice</p>";
						$notice = '<h3>' . __( 'Available Downloads', 'it-l10n-ithemes-exchange' ) . '</h3>' . $notice;

					else : ?>
						<h4><?php esc_attr_e( it_exchange_get_transaction_product_feature( $transaction_product, 'title' ) ); ?></h4>

						<?php $count = it_exchange_get_transaction_product_feature( $transaction_product, 'count' );

						if ( $count && apply_filters( 'it_exchange_print_downlods_page_link_in_email', true, $transaction ) ) :
							$downloads_url = it_exchange_get_page_url( 'downloads' ); ?>
							<p>
								<?php printf(
									__( 'You have purchased %d unique download link(s) for each file available with this product.', 'it-l10n-ithemes-exchange' ) . ' ' .
									__( '%s%sEach link has its own download limits and you can view the details on your %sdownloads%s page.', 'it-l10n-ithemes-exchange' ),
									$count, '<br />', '<br />', '<a href="' . esc_url( $downloads_url ) . '">', '</a>'
								); ?>
							</p>
						<?php endif;

						foreach ( $product_downloads as $download_id => $download_data ) :

							$hashes_for_product_transaction = it_exchange_get_download_hashes_for_transaction_product( $transaction, $transaction_product, $download_id );

							$hashes_found = ! empty( $hashes_found ) || ! empty( $hashes_for_product_transaction ); ?>

							<h5><?php esc_attr_e( get_the_title( $download_id ) ); ?></h5>

							<ul class="download-hashes">
								<?php foreach ( (array) $hashes_for_product_transaction as $hash ) : ?>
									<li>
										<a href="<?php echo esc_url( add_query_arg( 'it-exchange-download', $hash, get_home_url() ) ); ?>">
											<?php _e( 'Download link', 'it-l10n-ithemes-exchange' ); ?>
										</a>
										<span style="font-family: Monaco, monospace;font-size:12px;color:#AAA;">
											(<?php esc_attr_e( $hash ); ?>)
										</span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif;

		if ( empty( $downloads_exist_for_transaction ) || empty( $hashes_found ) ) {
			echo $notice;

			return ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}

	/**
	 * Replace the first name tag.
	 *
	 * @since 1.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function first_name( $context ) {
		return $context['recipient']->get_first_name();
	}

	/**
	 * Replace the last name tag.
	 *
	 * @since 1.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function last_name( $context ) {
		return $context['recipient']->get_last_name();
	}

	/**
	 * Replace the email tag.
	 *
	 * @since 1.14.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function email( $context ) {
		return $context['recipient']->get_email();
	}

	/**
	 * Replace the fullname tag.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function fullname( $context ) {
		return $context['recipient']->get_full_name();
	}

	/**
	 * Replace the username tag.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function username( $context ) {
		return $context['recipient']->get_username();
	}

	/**
	 * Replace the first name tag.
	 *
	 * @since 1.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function customer_first_name( $context ) {

		$name = '';

		if ( ! empty( $context['customer'] ) ) {
			$customer = $context['customer'];

			if ( ! empty( $customer->wp_user->first_name ) ) {
				$name = $customer->wp_user->first_name;
			} elseif ( ! empty( $customer->wp_user->display_name ) ) {
				$name = $customer->wp_user->display_name;
			}
		} elseif ( is_email( it_exchange_get_transaction_customer_id( $context['transaction'] ) ) ) {
			$name = it_exchange_get_transaction_customer_id( $context['transaction'] );
		}

		return $name;
	}

	/**
	 * Replace the last name tag.
	 *
	 * @since 1.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function customer_last_name( $context ) {

		$name = '';

		if ( ! empty( $context['customer'] ) ) {
			$customer = $context['customer'];

			if ( ! empty( $customer->wp_user->last_name ) ) {
				$name = $customer->wp_user->last_name;
			} elseif ( ! empty( $customer->wp_user->display_name ) ) {
				$name = $customer->wp_user->display_name;
			}
		} elseif ( is_email( it_exchange_get_transaction_customer_id( $context['transaction'] ) ) ) {
			$name = it_exchange_get_transaction_customer_id( $context['transaction'] );
		}

		return $name;
	}

	/**
	 * Replace the email tag.
	 *
	 * @since 1.14.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function customer_email( $context ) {

		$email = '';

		if ( ! empty( $context['customer']->data->user_email ) ) {
			$email = $context['customer']->data->user_email;
		} elseif ( ! empty( $context['transaction'] ) ) {
			$email = it_exchange_get_transaction_customer_email( $context['transaction'] );
		}

		return $email;
	}

	/**
	 * Replace the fullname tag.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function customer_fullname( $context ) {

		$fullname = '';

		if ( ! empty( $context['customer']->data->first_name ) && ! empty( $context['customer']->data->last_name ) ) {
			$fullname = $context['customer']->data->first_name . ' ' . $context['customer']->data->last_name;
		} else if ( ! empty( $context['customer']->data->display_name ) ) {
			$fullname = $context['customer']->data->display_name;
		}

		return $fullname;
	}

	/**
	 * Replace the username tag.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function customer_username( $context ) {
		return empty( $context['customer']->data->user_login ) ? '' : $context['customer']->data->user_login;
	}

	/**
	 * Replace the order table tag.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 * @param array $options
	 *
	 * @return string Replaced value
	 */
	public function order_table( $context, $options = null ) {

		$purchase_messages_heading = '<h3>' . __( 'Important Information', 'it-l10n-ithemes-exchange' ) . '</h3>';
		$purchase_messages         = '';
		$purchase_message_on       = false;

		if ( in_array( 'purchase_message', $options ) ) {
			$purchase_message_on = true;
		}

		ob_start();
		?>
		<table style="text-align: left; background: #FBFBFB; margin-bottom: 1.5em;border:1px solid #DDD;border-collapse: collapse;color:#1f1f1f;">
			<thead style="background:#F3F3F3;">
			<tr>
				<th style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Product', 'it-l10n-ithemes-exchange' ); ?></th>
				<th style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Quantity', 'it-l10n-ithemes-exchange' ); ?></th>
				<th style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Total Price', 'it-l10n-ithemes-exchange' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php if ( $products = it_exchange_get_transaction_products( $context['transaction'] ) ) : ?>
				<?php foreach ( $products as $product ) : ?>
					<tr>
						<td style="padding: 10px;border:1px solid #DDD;">
							<?php $name = it_exchange_get_transaction_product_feature( $product, 'product_name' ); ?>
							<?php echo apply_filters( 'it_exchange_email_notification_order_table_product_name', $name, $product ); ?>
						</td>
						<td style="padding: 10px;border:1px solid #DDD;">
							<?php $count = it_exchange_get_transaction_product_feature( $product, 'count' ); ?>
							<?php echo apply_filters( 'it_exchange_email_notification_order_table_product_count', $count, $product ); ?>
						</td>
						<td style="padding: 10px;border:1px solid #DDD;">
							<?php $subtotal = it_exchange_format_price( it_exchange_get_transaction_product_feature( $product, 'product_subtotal' ) ); ?>
							<?php echo apply_filters( 'it_exchange_email_notification_order_table_product_subtotal', $subtotal, $product ); ?>
						</td>
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
			<?php do_action( 'it_exchange_replace_order_table_tag_before_total_row', it_exchange_email_notifications(), $options ); ?>
			<tr>
				<td colspan="2" style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Total', 'it-l10n-ithemes-exchange' ); ?></td>
				<td style="padding: 10px;border:1px solid #DDD;"><?php echo it_exchange_get_transaction_total( $context['transaction'], true ) ?></td>
			</tr>
			<?php do_action( 'it_exchange_replace_order_table_tag_after_total_row', it_exchange_email_notifications(), $options ); ?>
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
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function purchase_date( $context ) {
		return it_exchange_get_transaction_date( $context['transaction'] );
	}

	/**
	 * Replace the total tag.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function total( $context ) {
		return it_exchange_get_transaction_total( $context['transaction'], true );
	}

	/**
	 * Replace the method ID.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function payment_id( $context ) {
		return it_exchange_get_transaction_method_id( $context['transaction'] );
	}

	/**
	 * Replace the transaction order number.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function receipt_id( $context ) {
		return it_exchange_get_transaction_order_number( $context['transaction'] );
	}

	/**
	 * Replace the payment method tag.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function payment_method( $context ) {
		return it_exchange_get_transaction_method_name( $context['transaction'] );
	}

	/**
	 * Replace the sitename tag.
	 *
	 * @since 1.0.0
	 *
	 * @return string Replaced value
	 */
	public function sitename() {
		return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	}

	/**
	 * Replace the confirmation url.
	 *
	 * @since 1.0.0
	 *
	 * @param array $context
	 *
	 * @return string Replaced value
	 */
	public function receipt_link( $context ) {
		return it_exchange_get_transaction_confirmation_url( $context['transaction'] );
	}

	/**
	 * Replace the login url.
	 *
	 * @since 1.0.2
	 *
	 * @return string Replaced value
	 */
	public function login_link() {
		return it_exchange_get_page_url( 'login' );
	}

	/**
	 * Replace the account link tag.
	 *
	 * @since 1.4.0
	 *
	 * @return string Replaced value
	 */
	public function account_link() {
		return it_exchange_get_page_url( 'account' );
	}

	/**
	 * Replace the profile link tag.
	 *
	 * @since 2.0.0
	 *
	 * @return string Replaced value
	 */
	public function profile_link() {
		return it_exchange_get_page_url( 'profile' );
	}

	/**
	 * Replace the company name tag.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function company_name() {

		$settings = it_exchange_get_option( 'settings_general' );

		return $settings['company-name'];
	}

	/**
	 * Replacement Shipping Address Tag
	 *
	 * @since 1.10.0
	 *
	 * @param array $context
	 * @param array $options
	 *
	 * @return string Shipping Address
	 */
	public function shipping_address( $context, $options = null ) {

		if ( it_exchange_transaction_includes_shipping( $context['transaction'] ) ) {

			$address = it_exchange_get_transaction_shipping_address( $context['transaction'] );
			$before  = empty( $options['before'] ) ? '' : $options['before'];
			$after   = empty( $options['after'] ) ? '' : $options['after'];

			return empty( $address ) ? '' : $before . it_exchange_get_formatted_shipping_address( $address ) . $after;
		}

		return '';
	}

	/**
	 * Replacement Billing Address Tag
	 *
	 * @since 1.10.0
	 *
	 * @param array $context
	 * @param array $options
	 *
	 * @return string Billing Address
	 */
	public function billing_address( $context, $options = null ) {

		$address = it_exchange_get_transaction_billing_address( $context['transaction'] );

		if ( empty( $address ) || empty( $address['address1'] ) ) {
			return '';
		}

		$before = empty( $options['before'] ) ? '' : $options['before'];
		$after  = empty( $options['after'] ) ? '' : $options['after'];

		return empty( $address ) ? '' : $before . it_exchange_get_formatted_billing_address( $address ) . $after;
	}
}
