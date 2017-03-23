<?php

$txn = it_exchange_get_transaction( $post );

if ( isset( $_GET['convert'] ) ) {
	$txn->convert_cart_object();
}

do_action( 'it_exchange_before_payment_details', $post );
$settings = it_exchange_get_option( 'settings_general' );
$currency = it_exchange_get_currency_symbol( $txn->get_currency() );
$dtf      = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
?>
	<div class="postbox" id="it-exchange-transaction-details">
	<div class="inside">
	<div class="transaction-stamp hidden <?php esc_attr_e( strtolower( $txn->get_status( true ) ) ); ?>">
		<?php esc_attr_e( $txn->get_status( true ) ); ?>
	</div>

	<?php if ( $txn->is_sandbox_purchase() ): ?>
		<div class="ribbon"><?php _e( 'Sandbox', 'it-l10n-ithemes-exchange' ); ?></div>
	<?php endif; ?>

	<?php if ( $txn->parent ): ?>
		<div class="spacing-wrapper parent-txn-link bottom-border">
			<span class="dashicons dashicons-arrow-left-alt2"></span>
			<a href="<?php echo esc_url( get_edit_post_link( $txn->parent->ID ) ); ?>">
				<?php printf(
					__( 'View Parent Subscription Payment %s', 'it-l10n-ithemes-exchange' ),
					$txn->parent->get_order_number()
				); ?>
			</a>
		</div>
	<?php endif; ?>

	<?php do_action( 'it_exchange_transaction_details_before_customer_data', $post ); ?>

	<div class="customer-data spacing-wrapper">
		<div class="customer-avatar left">
			<?php echo get_avatar( $txn->get_customer() ? $txn->get_customer()->ID : 0, 80, '', '', array( 'force_display' => true ) ); ?>
		</div>
		<div class="transaction-data right">
			<div class="transaction-order-number">
				<?php esc_attr_e( $txn->get_order_number() ); ?>
			</div>
			<div class="transaction-date">
				<?php esc_attr_e( it_exchange_get_transaction_date( $txn ) ); ?>
			</div>
			<div class="transaction-status <?php esc_attr_e( strtolower( $txn->get_status( true ) ) ); ?>">
				<?php esc_attr_e( $txn->get_status( true ) ); ?>
			</div>
		</div>
		<div class="customer-info">
			<h2 class="customer-display-name">
				<?php esc_attr_e( it_exchange_get_transaction_customer_display_name( $txn ) ); ?>
			</h2>
			<div class="customer-email">
				<?php esc_attr_e( $txn->get_customer_email() ); ?>
			</div>

			<?php if ( ! $post->post_parent ) : ?>
				<div class="customer-ip-address">
					<?php esc_attr_e( $txn->get_customer_ip() ); ?>
				</div>
			<?php endif; ?>

			<?php if ( apply_filters( 'it_exchange_transaction_detail_has_customer_profile', true, $post ) ) : ?>
				<div class="customer-profile">
					<a href="<?php esc_attr_e( it_exchange_get_transaction_customer_admin_profile_url( $post ) ); ?>">
						<?php _e( 'View Customer Data', 'it-l10n-ithemes-exchange' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php do_action( 'it_exchange_transaction_details_after_customer_data', $post ); ?>
	<?php do_action( 'it_exchange_transaction_details_before_shipping_and_billing', $post ); ?>

	<?php
	$billing_address  = $txn->get_billing_address();
	$shipping_address = $txn->get_shipping_address();

	if ( $shipping_address || $billing_address ) : ?>
		<div class="billing-shipping-wrapper columns-wrapper">

			<?php if ( $shipping_address ) : ?>
				<div class="shipping-address column">
					<div class="column-inner">
						<div class="shipping-address-label address-label"><?php _e( 'Shipping Address', 'it-l10n-ithemes-exchange' ); ?></div>
						<p><?php echo it_exchange_get_formatted_shipping_address( $shipping_address ); ?></p>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $billing_address ) : ?>
				<div class="billing-address column">
					<div class="column-inner">
						<div class="billing-address-label address-label"><?php _e( 'Billing Address', 'it-l10n-ithemes-exchange' ); ?></div>
						<p><?php echo it_exchange_get_formatted_billing_address( $billing_address ); ?></p>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php do_action( 'it_exchange_transaction_details_after_shipping_and_bililng', $post ); ?>
	<?php do_action( 'it_exchange_transaction_details_before_products', $post ); ?>

	<div class="products cart-items bottom-border">
		<div class="products-header spacing-wrapper bottom-border">
			<span><?php _e( 'Cart Items', 'it-l10n-ithemes-exchange' ); ?></span>
			<span class="right"><?php _e( 'Amount', 'it-l10n-ithemes-exchange' ); ?></span>
		</div>
		<?php

		$items = $txn->get_items()->non_summary_only();

		if ( ! $items->count() && $txn->has_parent() ) {
			$items = $txn->get_parent()->get_items();
		}

		$product_items = $items->with_only( 'product' );
		$other_items   = $items->without( 'product' );

		$download_index = it_exchange_get_transaction_download_hash_index( $txn );
		?>

		<?php foreach ( $product_items as $product_item ) : /** @var \ITE_Cart_Product $product_item */ ?>
			<div class="item product spacing-wrapper">
				<div class="product-header item-header clearfix">
					<?php do_action( 'it_exchange_transaction_details_begin_product_header', $post, $product_item->bc() ); ?>
					<div class="product-title item-title left">
						<?php do_action( 'it_exchange_transaction_print_metabox_before_product_feature_title', $post, $product_item->bc() ); ?>
						<?php echo $product_item->get_name(); ?> (<?php echo $product_item->get_quantity(); ?>)
						<?php do_action( 'it_exchange_transaction_print_metabox_after_product_feature_title', $post, $product_item->bc() ); ?>
					</div>
					<div class="product-subtotal item-subtotal right">
						<?php do_action( 'it_exchange_transaction_print_metabox_before_product_feature_subtotal', $post, $product_item->bc() ); ?>
						<?php
							$total = $product_item->get_total();
							$total_negative = $product_item->get_line_items()->filter( function ( ITE_Line_Item $item ) {
								return ! $item->is_summary_only() && $item->get_total() < 0;
							} )->total();
							$total += $total_negative * -1;
						?>
						<?php esc_attr_e( it_exchange_format_price( $total ) ); ?>
						<?php do_action( 'it_exchange_transaction_print_metabox_after_product_feature_subtotal', $post, $product_item->bc() ); ?>
					</div>
					<?php do_action( 'it_exchange_transaction_details_end_product_header', $post, $product_item->bc() ); ?>
				</div>
				<div class="product-details">
					<?php do_action( 'it_exchange_transaction_details_begin_product_details', $post, $product_item->bc() ); ?>

					<?php if ( it_exchange_transaction_includes_shipping( $txn ) && $product_item->get_line_items()->with_only( 'shipping' )->count() > 0 ) : ?>
						<div class="product-shipping-method item-shipping-method">
							<?php printf( __( 'Ship this item with %s.', 'it-l10n-ithemes-exchange' ), it_exchange_get_transaction_shipping_method_for_product( $post, $product_item->get_id() ) ); ?>
						</div>
					<?php endif; ?>

					<?php if ( isset( $download_index[ $product_item->get_product_id() ] ) ) : ?>
						<?php foreach ( $download_index[ $product_item->get_product_id() ] as $download_id => $hash ) : ?>
							<?php $download_data = it_exchange_get_download_data_from_hash( $hash[0] ); ?>
							<div class="product-download product-download-<?php esc_attr_e( $download_id ); ?>">
								<h4 class="product-download-title">
									<?php do_action( 'it_exchange_transaction_print_metabox_before_product_feature_download_title', $post, $download_id, $download_data ); ?>
									<?php echo __( 'Download:', 'it-l10n-ithemes-exchange' ) . ' ' . get_the_title( $download_id ); ?>
									<?php do_action( 'it_exchange_transaction_print_metabox_after_product_feature_download_title', $post, $download_id, $download_data ); ?>
								</h4>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php do_action( 'it_exchange_transaction_details_end_product_details', $post, $product_item->bc() ); ?>
				</div>

				<?php $children = $product_item->get_line_items()->non_summary_only(); ?>

				<?php if ( $children->count() > 0 ): ?>
					<ul class="product-children line-item-children">
						<?php foreach ( $children->to_array() as $child ) : /** @var \ITE_Line_Item $child */ ?>
							<li>
								<span class="item-title">
									<?php echo $child->get_name(); ?>
									<?php if ( $child instanceof ITE_Quantity_Modifiable_Item && $child->is_quantity_modifiable() ): ?>
										(<?php echo $child->get_quantity(); ?>)
									<?php endif; ?>
								</span>

								<span class="item-subtotal right">
									(<?php echo it_exchange_format_price( $child->get_total() ); ?>)
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php do_action( 'it_exchange_transaction_details_end_product_container', $post, $product_item->bc() ); ?>
			</div>
		<?php endforeach; ?>

		<?php foreach ( $other_items as $other_item ) : /** @var \ITE_Line_Item $other_item */ ?>
			<div class="item <?php echo $other_item->get_type(); ?> spacing-wrapper">
				<div class="<?php echo $other_item->get_type(); ?>-header item-header clearfix">
					<?php do_action( 'it_exchange_transaction_details_begin_item_header', $txn, $other_item ); ?>
					<div class="<?php echo $other_item->get_type(); ?>--title item-title left">
						<?php do_action( 'it_exchange_transaction_print_metabox_before_item_title', $txn, $other_item ); ?>
						<?php echo $other_item->get_name(); ?>
						<?php if ( $other_item instanceof ITE_Quantity_Modifiable_Item && $other_item->is_quantity_modifiable() ): ?>
							(<?php echo $other_item->get_quantity(); ?>)
						<?php endif; ?>
						<?php do_action( 'it_exchange_transaction_print_metabox_after_item_title', $txn, $other_item ); ?>
					</div>
					<div class="<?php echo $other_item->get_type(); ?>--subtotal item-subtotal right">
						<?php do_action( 'it_exchange_transaction_print_metabox_before_item_total', $txn, $other_item ); ?>
						<?php
						$total = $other_item->get_total();

						if ( $other_item instanceof ITE_Aggregate_Line_Item ) {
							$total_negative = $other_item->get_line_items()->filter( function ( ITE_Line_Item $item ) {
								return ! $item->is_summary_only() && $item->get_total() < 0;
							} )->total();

							$total += $total_negative * -1;
						}

						esc_attr_e( it_exchange_format_price( $total ) );
						?>
						<?php do_action( 'it_exchange_transaction_print_metabox_after_item_total', $txn, $other_item ); ?>
					</div>
					<?php do_action( 'it_exchange_transaction_details_end_item_header', $txn, $other_item ); ?>
				</div>
				<div class="<?php echo $other_item->get_type(); ?>-details item-details">
					<?php do_action( 'it_exchange_transaction_details_begin_item_details', $txn, $other_item ); ?>

					<?php if ( it_exchange_transaction_includes_shipping( $txn ) && $method = it_exchange_get_shipping_method_for_item( $other_item ) ) : ?>
						<div class="<?php echo $other_item->get_type(); ?>-shipping-method item-shipping-method">
							<?php printf( __( 'Ship this item with %s.', 'it-l10n-ithemes-exchange' ), $method->label ); ?>
						</div>
					<?php endif; ?>

					<?php do_action( 'it_exchange_transaction_details_end_item_details', $txn, $other_item ); ?>
				</div>

				<?php $children = $other_item instanceof ITE_Aggregate_Line_Item ? $other_item->get_line_items()->non_summary_only()->to_array() : array(); ?>

				<?php if ( $children ) : ?>
					<ul class="<?php echo $other_item->get_type(); ?>-children line-item-children">
						<?php foreach ( $children as $child ) : /** @var \ITE_Line_Item $child */ ?>
							<li>
								<span class="<?php echo $child->get_type(); ?>-title item-title">
									<?php echo $child->get_name(); ?>
									<?php if ( $child instanceof ITE_Quantity_Modifiable_Item && $child->is_quantity_modifiable() ): ?>
										(<?php echo $child->get_quantity(); ?>)
									<?php endif; ?>
								</span>

								<span class="<?php echo $child->get_type(); ?>-subtotal item-subtotal right">
									(<?php echo it_exchange_format_price( $child->get_total() ); ?>)
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php do_action( 'it_exchange_transaction_details_end_item_container', $txn, $other_item ); ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php do_action( 'it_exchange_transaction_details_after_products', $post ); ?>
	<?php do_action( 'it_exchange_transaction_details_before_costs', $post ); ?>

	<div class="transaction-costs clearfix spacing-wrapper bottom-border">

		<div class="transaction-costs-subtotal right clearfix">
			<div class="transaction-costs-subtotal-label left"><?php _e( 'Subtotal', 'it-l10n-ithemes-exchange' ); ?></div>
			<div class="transaction-costs-subtotal-price">
				<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_subtotal', $post ); ?>
				<?php esc_attr_e( it_exchange_get_transaction_subtotal( $post ) ); ?>
				<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_subtotal', $post ); ?>
			</div>
		</div>

		<?php if ( $coupons = it_exchange_get_transaction_coupons( $post ) ) : ?>
			<div class="transaction-costs-coupons right">
				<div class="transaction-costs-coupon-total-label left"><?php _e( 'Total Discount', 'it-l10n-ithemes-exchange' ); ?></div>
				<div class="transaction-costs-coupon-total-amount">
					<?php do_action( 'it_exchange_transaction_print_metabox_before_coupons_total_discount', $post ); ?>
					<?php esc_attr_e( it_exchange_get_transaction_coupons_total_discount( $post ) ); ?>
					<?php do_action( 'it_exchange_transaction_print_metabox_after_coupons_total_discount', $post ); ?>
				</div>
			</div>
			<label><strong><?php _e( 'Coupons', 'it-l10n-ithemes-exchange' ); ?></strong></label>
			<?php foreach ( $coupons as $type => $coupon ) : ?>
				<?php foreach ( $coupon as $data ) : ?>
					<div class="transaction-cost-coupon">
						<span class="code"><?php echo $data['code'] ?></span>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<?php endif; ?>

		<div class="transaction-refunds-container <?php echo $txn->has_refunds() ? '' : 'hidden'; ?>">
			<div class="transaction-costs-refunds right">
				<div class="transaction-costs-refund-total">
					<div class="transaction-costs-refund-total-label left"><?php _e( 'Total Refund', 'it-l10n-ithemes-exchange' ); ?></div>
					<div class="transaction-costs-refund-total-amount">
						<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_refunds_total', $post ); ?>
						<span><?php esc_attr_e( it_exchange_get_transaction_refunds_total( $post ) ); ?></span>
						<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_refunds_total', $post ); ?>
					</div>
				</div>
			</div>
			<div class="transaction-refunds-list">
				<label><strong><?php _e( 'Refunds', 'it-l10n-ithemes-exchange' ); ?></strong></label>
				<?php foreach ( $txn->refunds as $refund ) : ?>
					<div class="transaction-costs-refund">
						<span class="code">
							<?php echo esc_html( sprintf(
							/* translators: $1$s refund amount %2$s refund date. */
								__( '%1$s on %2$s', 'it-l10n-ithemes-exchange' ),
								it_exchange_format_price( $refund->amount ),
								get_date_from_gmt( $refund->created_at->format( DateTime::ISO8601 ), $dtf )
							) ); ?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<?php
	$totals = $txn->get_items( '', true )->summary_only()->without( 'shipping', 'coupon' )->segment();

	foreach ( $totals as $total_by_type ):
		$segmented = $total_by_type->segment( function ( ITE_Line_Item $item ) { return get_class( $item ) . $item->get_name(); } );
		foreach ( $segmented as $segment ):
			$type = $segment->first()->get_type();
			$description = $segment->filter( function ( ITE_Line_Item $item ) { return trim( $item->get_description() !== '' ); } )->first();
			?>
			<div class="summary-item summary-item-<?php echo $type; ?> clearfix spacing-wrapper bottom-border">
				<div class="summary-item-description left">
					<?php if ( $description ): ?>
						<p class="description"><?php echo $description->get_description(); ?></p>
					<?php endif; ?>
				</div>
				<div class="summary-item-cost right clearfix">
					<div class="summary-item-cost-label left"><?php echo $segment->first()->get_name(); ?></div>
					<div class="summary-item-cost-price">
						<?php do_action( "it_exchange_transaction_print_metabox_before_transaction_{$type}_total", $txn ); ?>
						<?php esc_attr_e( it_exchange_format_price( $segment->total() ) ); ?>
						<?php do_action( "it_exchange_transaction_print_metabox_after_transaction_{$type}_total", $txn ); ?>
					</div>
				</div>
			</div>
		<?php endforeach;
	endforeach; ?>

	<?php if ( it_exchange_transaction_includes_shipping( $post ) ) : ?>
		<div class="transaction-shipping-summary clearfix spacing-wrapper bottom-border">
			<div class="payment-shipping left">
				<div class="payment-shipping-label"><?php _e( 'Shipping Method', 'it-l10n-ithemes-exchange' ); ?></div>
				<div class="payment-shipping-name">
					<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_shipping_name', $post ); ?>
					<?php esc_attr_e( empty( it_exchange_get_transaction_shipping_method( $post )->label ) ? __( 'Unknown Shipping Method', 'it-l10n-ithemes-exchange' ) : it_exchange_get_transaction_shipping_method( $post )->label ); ?>
					<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_shipping_name', $post ); ?>
				</div>
			</div>

			<div class="payment-shipping-total right clearfix">
				<div class="payment-shipping-total-label left"><?php _e( 'Shipping', 'it-l10n-ithemes-exchange' ); ?></div>
				<div class="payment-shipping-total-amount">
					<?php do_action( 'it_exchange_transaction_print_metabox_before_shipping_total', $post ); ?>
					<?php echo it_exchange_format_price( it_exchange_get_transaction_shipping_total( $post ) ); ?>
					<?php do_action( 'it_exchange_transaction_print_metabox_after_shipping_total', $post ); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="transaction-summary clearfix spacing-wrapper bottom-border">
		<div class="payment-method left">
			<div class="payment-method-label"><?php _e( 'Payment Method', 'it-l10n-ithemes-exchange' ); ?></div>
			<div class="payment-method-name">
				<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_method_name', $post ); ?>
				<?php esc_attr_e( it_exchange_get_transaction_method_name( $post ) ); ?>
				<code><?php echo it_exchange_get_transaction_method_id( $post ); ?></code>
				<?php if ( $source = $txn->get_payment_source() ) : ?>
					<span class="payment-method-source"><?php echo $source->get_label(); ?></span>
				<?php endif; ?>
				<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_method_name', $post ); ?>
			</div>
		</div>
		<div class="payment-total right clearfix">
			<div class="payment-total-label left"><?php _e( 'Total', 'it-l10n-ithemes-exchange' ); ?></div>
			<div class="payment-total-amount">
				<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_total', $post ); ?>
				<?php _e( it_exchange_get_transaction_total( $post ) ); ?>
				<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_total', $post ); ?>
			</div>

			<div class="payment-original-total-label left <?php echo $txn->has_refunds() ? '' : 'hidden'; ?>">
				<?php _e( 'Total before refunds', 'it-l10n-ithemes-exchange' ); ?>
			</div>
			<div class="payment-original-total-amount <?php echo $txn->has_refunds() ? '' : 'hidden'; ?>">
				<?php do_action( 'it_exchange_transaction_print_metabox_before_transaction_total_before_refunds', $post ); ?>
				<?php _e( it_exchange_get_transaction_total( $post, true, false ) ); ?>
				<?php do_action( 'it_exchange_transaction_print_metabox_after_transaction_total_before_refunds', $post ); ?>
			</div>
		</div>
	</div>

	<?php do_action( 'it_exchange_after_payment_details', $post ); ?>

	<?php do_action( 'it_exchange_before_payment_actions', $txn ); ?>

	<div class="spacing-wrapper bottom-border clearfix hide-if-no-js transaction-actions">
		<?php do_action( 'it_exchange_before_payment_update_status', $txn ); ?>

		<?php if ( it_exchange_transaction_status_can_be_manually_changed( $txn ) && $options = it_exchange_get_status_options_for_transaction( $txn ) ): ?>
			<select id='it-exchange-update-transaction-status' style="width: 150px">
				<option style="display:none;" value="0" disabled selected>
					<?php _e( 'Update Status', 'it-1l10n-ithemes-exchange' ); ?>
				</option>
				<?php
				$current_status = it_exchange_get_transaction_status( $txn );
				foreach ( $options as $key => $label ) {
					$status_label = it_exchange_get_transaction_status_label( $txn, array( 'status' => $key ) );
					?>
					<option value="<?php esc_attr_e( $key ); ?>">
						<?php echo esc_html( $status_label ); ?>
					</option>
					<?php
				}
				?>
			</select>

			<?php it_exchange_admin_tooltip(
				__( 'The customer will be emailed if the status changes from un-cleared for delivery to a status that is cleared for delivery.',
					'it-l10n-ithemes-exchange'
				) ); ?>
		<?php endif; ?>

		<?php do_action( 'it_exchange_after_payment_update_status', $txn ); ?>
		<?php do_action( 'it_exchange_before_payment_resend_receipt', $txn ); ?>

		<button class="button button-secondary right" id="open-receipt-manager">
			<?php _e( 'Send Receipt', 'it-l10n-ithemes-exchange' ); ?>
		</button>

		<?php do_action( 'it_exchange_after_payment_resend_receipt', $txn ); ?>
		<?php do_action( 'it_exchange_before_payment_refund', $txn ); ?>

		<?php if ( it_exchange_transaction_can_be_refunded( $txn ) ): ?>
			<button class="button button-secondary right" id="open-refund-manager">
				<?php _e( 'Refund', 'it-l10n-ithemes-exchange' ); ?>
			</button>
		<?php endif; ?>

		<?php do_action( 'it_exchange_after_payment_refund', $txn ); ?>

		<?php wp_nonce_field( 'resend-receipt-transaction-' . $post->ID, 'it-exchange-resend-receipt-nonce' ); ?>
	</div>

	<?php do_action( 'it_exchange_after_payment_actions', $txn ); ?>

	<div class="hidden spacing-wrapper bottom-border clearfix" id="receipt-manager"
	     style="background: #F5F5F5;">

		<button class="button button-secondary left" id="cancel-receipt">
			<?php _e( 'Back', 'it-l10n-ithemes-exchange' ); ?>
		</button>

		<button class="button button-primary right" id="send-receipt" style="margin-left: 10px;">
			<?php _e( 'Send', 'it-l10n-ithemes-exchange' ) ?>
		</button>

		<input type="text" placeholder="<?php echo esc_attr( $txn->get_customer_email() ); ?>"
		       id="receipt-email"
		       class="right" style="text-align: left;width: 250px;"/>
	</div>

	<div class="hidden spacing-wrapper bottom-border clearfix" id="refund-manager">

		<button class="button button-secondary left" id="cancel-refund">
			<?php _e( 'Back', 'it-l10n-ithemes-exchange' ); ?>
		</button>

		<button class="button button-primary right" id="add-refund">
			<?php printf( __( 'Refund from %s', 'it-l10n-ithemes-exchange' ), it_exchange_get_transaction_method_name( $txn ) ); ?>
		</button>

		<input type="text" placeholder="<?php echo esc_attr( it_exchange_format_price( 0 ) ); ?>"
		       id="refund-amount" class="right"
		       data-max="<?php echo esc_attr( $txn->get_total() ); ?>"
		       data-symbol="<?php echo esc_attr( $currency ); ?>"
		       data-symbol-position="<?php echo esc_attr( $settings['currency-symbol-position'] ); ?>"
		       data-thousands-separator="<?php echo esc_attr( $settings['currency-thousands-separator'] ); ?>"
		       data-decimals-separator="<?php echo esc_attr( $settings['currency-decimals-separator'] ); ?>" />

		<?php wp_nonce_field( "it-exchange-add-refund-{$txn->ID}-transaction", 'it-exchange-refund-nonce' ); ?>

	</div>

<?php
echo '</div></div>';

IT_Exchange_Transaction_Post_Type::print_activity( $post );
