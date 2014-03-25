<?php
/**
 * This file contains the contents of the Settings page
 * @since 0.3.6
 * @package IT_Exchange
*/
?>
<div class="wrap">
	<?php
	ITUtility::screen_icon( 'it-exchange' );
	$this->print_general_settings_tabs();
	do_action( 'it_exchange_general_settings_page_top' );

	$form->start_form( $form_options, 'exchange-general-settings' );
	?>
		<?php do_action( 'it_exchange_general_settings_form_top', $form ); ?>
		<table class="form-table">
			<?php do_action( 'it_exchange_general_settings_table_top', $form ); ?>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Company Details', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="company-name"><?php _e( 'Company Name', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-name', array( 'class' => 'normal-text' ) ); ?>
					<br /><span class="description"><?php _e( 'The name used in customer receipts.', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<?php $tax_link = 'http://www.irs.gov/Businesses/Small-Businesses-&amp;-Self-Employed/Employer-ID-Numbers-(EINs)-"'; ?>
				<th scope="row"><label for="company-tax-id"><?php _e( 'Company Tax ID', 'LION' ) ?> <?php it_exchange_admin_tooltip( sprintf( __( 'In the U.S., this is your Federal %sTax ID Number%s.', 'LION' ), '<a href="' . $tax_link . '" target="_blank">', '</a>' ) ); ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-tax-id', array( 'class' => 'normal-text' ) ); ?>
					<p class="description"><a href="http://www.irs.gov/Businesses/Small-Businesses-&amp;-Self-Employed/Employer-ID-Numbers-(EINs)-" target="_blank"><?php _e( 'Click here for more info about obtaining a Tax ID in the US', 'LION' ); ?></a></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="company-email"><?php _e( 'Company Email', 'LION' ) ?> <?php it_exchange_admin_tooltip( __( 'Where do you want customer inquiries to go?', 'LION' ) ); ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-email', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="company-phone"><?php _e( 'Company Phone', 'LION' ) ?> <?php it_exchange_admin_tooltip( __( 'This is your main customer service line.', 'LION' ) ); ?></label></th>
				<td>
					<?php $form->add_text_box( 'company-phone', array( 'class' => 'normal-text' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="company-address"><?php _e( 'Company Address', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_area( 'company-address', array( 'rows' => 5, 'cols' => 30 ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="company-base-country"><?php _e( 'Base Country', 'LION' ) ?> <?php it_exchange_admin_tooltip( __( 'This is the country where your business is located', 'LION' ) ); ?></label></th>
				<td>
					<?php $form->add_drop_down( 'company-base-country', it_exchange_get_data_set( 'countries' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="company-base-state"><?php _e( 'Base State / Province', 'LION' ) ?> <?php it_exchange_admin_tooltip( __( 'This is the state / province where your business is located', 'LION' ) ); ?></label></th>
				<td class="company-base-state-field-td">
					<?php
					$country = $form->get_option( 'company-base-country' );
					$states  = it_exchange_get_data_set( 'states', array( 'country' => $country ) );
					if ( ! empty( $states ) ) {
						$form->add_drop_down( 'company-base-state', $states );
					} else {
						$form->add_text_box( 'company-base-state', array( 'class' => 'small-text', 'max-length' => 3 ) );
						?><p class="description"><?php printf( __( 'Please use the 2-3 character %sISO abbreviation%s for country subdivisions', 'LION' ), '<a href="http://en.wikipedia.org/wiki/ISO_3166-2" target="_blank">', '</a>' ); ?></p><?php
					}
					?>
				</td>
			</tr>
			<?php do_action( 'it_exchange_general_settings_before_settings_store', $form ); ?>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Store Settings', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="store-product-order-by"><?php _e( 'Order Products By', 'LION' ) ?></label></th>
				<td>
					<?php
					$order_by = apply_filters( 'it_exchange_store_order_by_options', array(
						'ID'         => __( 'Product ID', 'LION' ),
						'title'      => __( 'Product Title', 'LION' ),
						'name'       => __( 'Product Slug', 'LION' ),
						'date'       => __( 'Product Published Date/Time', 'LION' ),
						'modified'   => __( 'Product Modified Date/Time', 'LION' ),
						'rand'       => __( 'Random', 'LION' ),
						'menu_order' => __( 'Product Order #', 'LION' ),
					) );
					$form->add_drop_down( 'store-product-order-by', $order_by ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="store-product-order"><?php _e( 'Order', 'LION' ) ?></label></th>
				<td>
					<?php
					$order_by = apply_filters( 'it_exchange_store_order_options', array(
						'ASC'  => __( 'Ascending', 'LION' ),
						'DESC' => __( 'Descending', 'LION' ),
					) );
					$form->add_drop_down( 'store-product-order', $order_by ); ?>
				</td>
			</tr>

			<?php do_action( 'it_exchange_general_settings_before_settings_currency', $form ); ?>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Currency Settings', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="default-currency"><?php _e( 'Default Currency', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_drop_down( 'default-currency', $this->get_default_currency_options() ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="currency-symbol-position"><?php _e( 'Symbol Position', 'LION' ) ?></label></th>
				<td>
					<?php
					$symbol_positions = array( 'before' => __( 'Before: $10.00', 'LION' ), 'after' => __( 'After: 10.00$', 'LION' ) );
					$form->add_drop_down( 'currency-symbol-position', $symbol_positions ); ?>
					<br /><span class="description"><?php _e( 'Where should the currency symbol be placed in relation to the price?', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="currency-thousands-separator"><?php _e( 'Thousands Separator', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'currency-thousands-separator', array( 'class' => 'small-text', 'maxlength' => '1' ) ); ?>
					<br /><span class="description"><?php _e( 'What character would you like to use to separate thousands when displaying prices?', 'LION' ); ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="currency-decimals-separator"><?php _e( 'Decimals Separator', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_text_box( 'currency-decimals-separator', array( 'class' => 'small-text', 'maxlength' => '1' ) ); ?>
					<br /><span class="description"><?php _e( 'What character would you like to use to separate decimals when displaying prices?', 'LION' ); ?></span>
				</td>
			</tr>
            <?php do_action( 'it_exchange_general_settings_before_settings_registration', $form ); ?>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Customer Registration Settings', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="site-registration"><?php _e( 'Customer Registration', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_radio( 'site-registration', array( 'value' => 'it' ) ); ?>
                	<label for="site-registration-it"><?php _e( 'Use Exchange Registration Only', 'LION' ) ?></label>
                    <br />
					<?php $form->add_radio( 'site-registration', array( 'value' => 'wp' ) ); ?>
                	<label for="site-registration-wp"><?php _e( 'Use WordPress Registration Setting', 'LION' ) ?></label><?php it_exchange_admin_tooltip( __( 'In order to use this setting, you will first need to check the "Anyone can register" checkbox from the WordPress General Settings page to allow site membership.', 'LION' ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="checkout-reg-form"><?php _e( 'Default Checkout Form', 'LION' ) ?></label></th>
				<td>
					<?php
					$options = array(
						'registration' => __( 'Registration', 'LION' ),
						'login'        => __( 'Log in', 'LION' ),
					);
					?>
					<?php $form->add_drop_down( 'checkout-reg-form', $options ); ?>
				</td>
			</tr>
            <?php do_action( 'it_exchange_general_settings_before_settings_styles', $form ); ?>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Stylesheet Settings', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="custom-styles"><?php _e( 'Custom Styles', 'LION' ) ?></label></th>
				<td>
					<?php _e( 'If they exist, the following files will be loaded in order after core Exchange stylesheets:', 'LION' ); ?><br />
					<span class="description">
						<?php
						$parent = get_template_directory() . '/exchange/style.css';
						$child  = get_stylesheet_directory() . '/exchange/style.css';
						$custom_style_locations[$parent] = '&#151;&nbsp;&nbsp;' . $parent;
						$custom_style_locations[$child]  = '&#151;&nbsp;&nbsp;' . $child;
						echo implode( $custom_style_locations, '<br />' );
						?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Product Gallery', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="enable-gallery-popup"><?php _e( 'Enable Popup', 'LION' ) ?></label></th>
				<td>
					<?php $form->add_yes_no_drop_down( 'enable-gallery-popup' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="product-gallery-zoom"><?php _e( 'Enable Zoom', 'LION' ) ?><?php it_exchange_admin_tooltip( __( 'Zoom will only work properly when uploading large images.', 'LION' ) ); ?></label></th>
				<td>
					<?php $form->add_yes_no_drop_down( 'enable-gallery-zoom' ); ?>
					<div class="product-gallery-zoom-actions <?php echo ( $form->_options['enable-gallery-zoom'] != 1 ) ? 'hidden' : ''; ?>">
						<?php $form->add_radio( 'product-gallery-zoom-action', array( 'value' => 'click' ) ); ?>
						<label for="product-gallery-zoom-action-click"><?php _e( 'Click', 'LION' ) ?></label>
						<br />
						<?php $form->add_radio( 'product-gallery-zoom-action', array( 'value' => 'hover' ) ); ?>
						<label for="product-gallery-zoom-action-hover"><?php _e( 'Hover', 'LION' ) ?></label>
						<span class="description popup-enabled <?php echo ( $form->_options['enable-gallery-popup'] != 1 ) ? 'hidden' : ''; ?>">
							<p><?php _e( 'Zoom will occur in the popup when popup is enabled.', 'LION' ); ?></p>
						</span>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><strong><?php _e( 'Customer Messages', 'LION' ); ?></strong></th>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="customer-account-page"><?php _e( 'Customer Account Page', 'LION' ) ?></label></th>
				<td>
					<?php
					if ( $GLOBALS['wp_version'] >= 3.3 && function_exists( 'wp_editor' ) ) {
						echo wp_editor( $settings['customer-account-page'], 'customer-account-page', array( 'textarea_name' => 'it_exchange_settings-customer-account-page', 'textarea_rows' => 20, 'textarea_cols' => 20, 'editor_class' => 'large-text' ) );
						//We do this for some ITForm trickery... just to add customer-account-page to the used inputs field
						$form->get_text_area( 'customer-account-page', array( 'rows' => 20, 'cols' => 20, 'class' => 'large-text' ) );
					} else {
						$form->add_text_area( 'customer-account-page', array( 'rows' => 20, 'cols' => 20, 'class' => 'large-text' ) );
					}
					?>
					<p class="description">
					<?php
					_e( 'Enter your content for the Customer\'s account page. HTML is accepted. Available shortcode functions:', 'LION' );
					echo '<br />';
					printf( __( 'You call these shortcode functions like this: %s', 'LION' ), '[it_exchange_customer show=avatar avatar_size=50]' );
					echo '<ul>';
					echo '<li>first-name - ' . __( "The customer's first name", 'LION' ) . '</li>';
					echo '<li>last-name - ' . __( "The customer's last name", 'LION' ) . '</li>';
					echo '<li>username - ' . __( "The customer's username on the site", 'LION' ) . '</li>';
					echo '<li>email - ' . __( "The customer's email address", 'LION' ) . '</li>';
					echo '<li>avatar - ' . __( "The customer's gravatar image. Use the avatar_size param for square size. Default is 128", 'LION' ) . '</li>';
					echo '<li>sitename - ' . __( 'Your site name', 'LION' ) . '</li>';
					do_action( 'it_customer_account_page_shortcode_tags_list' );
					echo '</ul>';
					?>
					</p>
				</td>
			</tr>
			<?php do_action( 'it_exchange_general_settings_table_bottom', $form ); ?>
		</table>
		<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
		<?php do_action( 'it_exchange_general_settings_form_bottom', $form ); ?>
	<?php $form->end_form(); ?>
	<?php do_action( 'it_exchange_general_settings_page_bottom' ); ?>
</div>
