<?php
/**
 * CartBuddy admin class.
 *
 * This class manages the admin side of the plugin
 *
 * @package IT_Cart_Buddy
 * @since 0.1.0
*/
class IT_Cart_Buddy_Admin {

	/**
	 * @var object $_parent parent class
	 * @since 0.1.0
	*/
	var $_parent;

	/**
	 * @var string $_current_page current page based on $_GET['page']
	 * @since 0.3.4
	*/
	var $_current_page;

	/**
	 * @var string $_current_tab
	 * @since 0.3.4
	*/
	var $_current_tab;

	/**
	 * Class constructor
	 *
	 * @uses add_action()
	 * @since 0.1.0
	 * @return void 
	*/
	function IT_Cart_Buddy_Admin( &$parent ) {

		// Set parent property
		$this->_parent = $parent;

		// Admin Menu Capability
		$this->admin_menu_capability = apply_filters( 'it_cart_buddy_admin_menu_capability', 'read' );

		// Set current properties
		$this->set_current_properties();

		// Open cart buddy menu when on add/edit cartbuddy product post type
		add_action( 'parent_file', array( $this, 'open_cart_buddy_menu_on_post_type_views' ) );

		// Add actions for iThemes registration
		add_action( 'admin_menu', array( $this, 'add_cart_buddy_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'enable_disable_registered_add_on' ) );

		// Redirect to Product selection on Add New if needed
		add_action( 'admin_init', array( $this, 'redirect_post_new_to_product_type_selection_screen' ) );

		// Save core settings
		add_action( 'admin_init', array( $this, 'save_core_general_settings' ) );
		add_action( 'admin_init', array( $this, 'save_core_email_settings' ) );

		// Email settings callback
		add_filter( 'it_cart_buddy_general_settings_tab_callback-email', array( $this, 'register_email_settings_tab_callback' ) );
		add_action( 'it_cart_buddy_print_general_settings_tab_links', array( $this, 'print_email_settings_tab_link' ) );
	}

	/**
	 * Sets the _current_page and _current_tab properties
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function set_current_properties() {
		$this->_current_page = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_tab = empty( $_GET['tab'] ) ? false : $_GET['tab'];
	}

	/**
	 * Adds the main Cart Buddy menu item to the WP admin menu
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function add_cart_buddy_admin_menu() {
		// Add main cart buddy menu item
		add_menu_page( 'Cart Buddy', 'Cart Buddy', $this->admin_menu_capability, 'it-cart-buddy', array( $this, 'print_cart_buddy_setup_page' ) );

		// Add setup wizard if not complete or if on page
		if ( 'it-cart-buddy-setup' == $this->_current_page || ! get_option( 'it_cart_buddy_setup_complete' ) )
			add_submenu_page( 'it-cart-buddy', 'Cart Buddy Setup Wizard', 'Setup Wizard', $this->admin_menu_capability, 'it-cart-buddy-setup', array( $this, 'print_cart_buddy_setup_page' ) );

		// Remove default cart buddy sub-menu item created with parent menu item
		remove_submenu_page( 'it-cart-buddy', 'it-cart-buddy' );

		// Add the product submenu pages depending on active product add-ons
		$this->add_product_submenus();

		// Add Transactions menu item
		add_submenu_page( 'it-cart-buddy', 'Cart Buddy Transactions', 'Transactions', $this->admin_menu_capability, 'edit.php?post_type=it_cart_buddy_tran' );

		// Add Settings Menu Item
		$settings_callback = array( $this, 'print_cart_buddy_settings_page' );
		if ( 'it-cart-buddy-settings' == $this->_current_page && ! empty( $this->_current_tab ) )
			$settings_callback = apply_filters( 'it_cart_buddy_general_settings_tab_callback-' . $this->_current_tab, $settings_callback );
		add_submenu_page( 'it-cart-buddy', 'Cart Buddy Settings', 'Settings', $this->admin_menu_capability, 'it-cart-buddy-settings', $settings_callback );

		// Add Add-ons menu item
		add_submenu_page( 'it-cart-buddy', 'Cart Buddy Add-ons', 'Add-ons', $this->admin_menu_capability, 'it-cart-buddy-addons', array( $this, 'print_cart_buddy_add_ons_page' ) );
	}

	/**
	 * Adds the product submenus based on number of enabled product-type add-ons
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function add_product_submenus() {
		// Check for enabled product add-ons. Don't need product pages if we don't have product add-ons enabled
		if ( $enabled_product_types = it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'product-type' ) ) ) ) {
			$add_on_count = count( $enabled_product_types );
			add_submenu_page( 'it-cart-buddy', 'All Products', 'All Products', $this->admin_menu_capability, 'edit.php?post_type=it_cart_buddy_prod' );
			if ( 1 == $add_on_count ) {
				// If we only have one product-type enabled, add standard post_type pages
				$product = reset( $enabled_product_types );
				add_submenu_page( 'it-cart-buddy', 'Add Product', 'Add Product', $this->admin_menu_capability, 'post-new.php?post_type=it_cart_buddy_prod&product_type=' . $product['slug'] );
			} else if ( $add_on_count > 1 ) {
				// If we have more than one product type, make the add link go to product selection page
				add_submenu_page( 'it-cart-buddy', 'Add Product', 'Add Product', $this->admin_menu_capability, 'it-cart-buddy-choose-product-type', array( $this, 'print_choose_product_type_admin_page' ) );
			}
		}
	}

	/**
	 * Registers the callback for the email tab
	 *
	 * @param mixed default callback for general settings. 
	 * @since 0.3.4
	 * @return mixed function or class method name
	*/
	function register_email_settings_tab_callback( $default ) {
		return array( $this, 'print_general_settings_email_page' );
	}

	/**
	 * Prints the email tab for general settings
	 *
	 * @since 0.3.4
	 * @param $current_tab the current tab
	 * @return void
	*/
	function print_email_settings_tab_link( $current_tab ) {
		$active = 'email' == $current_tab ? 'nav-tab-active' : '';
		?><a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-cart-buddy-settings&tab=email' ); ?>"><?php _e( 'Email Settings', 'LION' ); ?></a><?php
	}

	/**
	 * Prints the email page for cart buddy
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_general_settings_email_page() {
		$values = $this->set_email_settings_field_values();
		?>
		<div class="wrap">
			<?php screen_icon( 'page' ); ?>
			<?php $this->print_general_settings_tabs(); ?>
			<?php echo do_action( 'it_cart_buddy_general_settings_email_page_top' ); ?>
			<form action='' method='post'>
				<?php echo do_action( 'it_cart_buddy_general_settings_email_form_top' ); ?>
				<table class="form-table">
					<?php do_action( 'it_cart_buddy_general_settings_email_top' ); ?>
					<tr valign="top">
						<th scope="row"><strong>Customer Receipt Emails</strong></th>
						<td></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_receipt_email_address"><?php _e( 'Email Address' ) ?></label></th>
						<td>
							<input type="text" name="it_cart_buddy_receipt_email_address" value="<?php esc_attr_e( $values['receipt_email_address'] ); ?>" class="normal-text" />
							<br /><span class="description"><?php _e( 'Email address used for customer receipt emails.', 'LION' ); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_from_email_name"><?php _e( 'Email Name' ) ?></label></th>
						<td>
							<input type="text" name="it_cart_buddy_receipt_email_name" value="<?php esc_attr_e( $values['receipt_email_name'] ); ?>" class="normal-text" />
							<br /><span class="description"><?php _e( 'Name used for account that sends customer receipt emails.', 'LION' ); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_receipt_email_subject"><?php _e( 'Subject Line' ) ?></label></th>
						<td>
							<input type="text" name="it_cart_buddy_receipt_email_subject" value="<?php esc_attr_e( $values['receipt_email_subject'] ); ?>" class="normal-text" />
							<br /><span class="description"><?php _e( 'Subject line used for customer receipt emails.', 'LION' ); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_receipt_email_template"><?php _e( 'Email Template' ) ?></label></th>
						<td>
							<textarea name="it_cart_buddy_receipt_email_template" rows="10" cols="30" class="large-text"><?php esc_attr_e( $values['receipt_email_template'] ); ?></textarea>
						</td>
					</tr>
					<?php do_action( 'it_Cart_buddy_general_settings_email_table_bottom' ); ?>
				</table>
				<?php wp_nonce_field( 'save-email-settings', 'cart-buddy-email-settings' ); ?>
				<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
				<?php do_action( 'it_cart_buddy_general_settings_email_form_bottom' ); ?>
			</form>
			<?php do_action( 'it_cart_buddy_general_settings_email_page_bottom' ); ?>
		</div>
		<?php
	}

	/**
	 * Prints the tabs for the Cart Buddy General Settings
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_general_settings_tabs() {
		$active = empty( $this->_current_tab ) ? 'nav-tab-active' : '';
		?>
		<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-cart-buddy-settings' ); ?>"><?php _e( 'General', 'LION' ); ?></a>
		<?php do_action( 'it_cart_buddy_print_general_settings_tab_links', $this->_current_tab ); ?>
		</h2>
		<?php
	}

	/**
	 * Prints the setup page for cart buddy
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function print_cart_buddy_setup_page() {
		?>
		<div class="wrap">
			<?php screen_icon( 'page' ); ?>
			<h2>Cart Buddy Setup</h2>
			<p>Possibly place setup wizzard here</p>
			<p>Definitely replace icon</p>
		</div>
		<?php
	}

	/**
	 * Prints the settings page for cart buddy
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function print_cart_buddy_settings_page() {
		$values = $this->set_general_settings_field_values();
		?>
		<div class="wrap">
			<?php
			screen_icon( 'page' );
			$this->print_general_settings_tabs();
			?>
			<?php echo do_action( 'it_cart_buddy_general_settings_page_top' ); ?>
			<form action='' method='post'>
				<?php echo do_action( 'it_cart_buddy_general_settings_form_top' ); ?>
				<table class="form-table">
					<?php do_action( 'it_cart_buddy_general_settings_table_top' ); ?>
					<tr valign="top">
						<th scope="row"><strong><?php _e( 'Company Details', 'LION' ); ?></strong></th>
						<td></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_company_name"><?php _e( 'Company Name' ) ?></label></th>
						<td>
							<input type="text" name="it_cart_buddy_company_name" value="<?php esc_attr_e( $values['company_name'] ); ?>" class="normal-text" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_company_tax_id"><?php _e( 'Company Tax ID' ) ?></label></th>
						<td>
							<input type="text" name="it_cart_buddy_company_tax_id" value="<?php esc_attr_e( $values['company_tax_id'] ); ?>" class="normal-text" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_company_email"><?php _e( 'Company Email' ) ?></label></th>
						<td>
							<input type="text" name="it_cart_buddy_company_email" value="<?php esc_attr_e( $values['company_email'] ); ?>" class="normal-text" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_company_phone"><?php _e( 'Company Phone' ) ?></label></th>
						<td>
							<input type="text" name="it_cart_buddy_company_phone" value="<?php esc_attr_e( $values['company_phone'] ); ?>" class="normal-text" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_company_address"><?php _e( 'Company Address' ) ?></label></th>
						<td>
							<textarea name="it_cart_buddy_company_address" rows="5" cols="30" ><?php esc_attr_e( $values['company_address'] ); ?></textarea>
						</td>
					</tr>
					<?php do_action( 'it_cart_buddy_general_settings_before_currency' ); ?>
					<tr valign="top">
						<th scope="row"><strong><?php _e( 'Currency Settings', 'LION' ); ?></strong></th>
						<td></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_default_currency"><?php _e( 'Default Currency' ) ?></label></th>
						<td><select name="it_cart_buddy_default_currency" id="it_cart_buddy_default_currency"><?php $this->print_default_currency_select_options( $values['default_currency'] ); ?></select></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_currency_symbol_position"><?php _e( 'Symbol Position' ) ?></label></th>
						<td>
							<select name="it_cart_buddy_currency_symbol_position">
								<option value="before" <?php selected( $values['currency_symbol_position'], 'before' ); ?>>Before: $10.00</option>
								<option value="after" <?php selected( $values['currency_symbol_position'], 'after' ); ?>>After: 10.00$</option></select>
							<br /><span class="description"><?php _e( 'Where should the currency symbol be placed in relation to the price?', 'LION' ); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_currency_thousands_separator"><?php _e( 'Thousands Separator' ) ?></label></th>
						<td>
							<input type="text" name="it_cart_buddy_currency_thousands_separator" value="<?php esc_attr_e( $values['currency_thousands_separator'] ); ?>" class="small-text" />
							<br /><span class="description"><?php _e( 'What character would you like to use to separate thousands when display prices?', 'LION' ); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="it_cart_buddy_currency_decimals_separator"><?php _e( 'Decimals Separator' ) ?></label></th>
						<td>
							<input type="text" name="it_cart_buddy_currency_decimals_separator" value="<?php esc_attr_e( $values['currency_decimals_separator'] ); ?>" class="small-text"/>
							<br /><span class="description"><?php _e( 'What character would you like to use to separate decimals when display prices?', 'LION' ); ?></span>
						</td>
					</tr>
					<?php do_action( 'it_cart_buddy_general_settings_table_bottom' ); ?>
				</table>
				<?php wp_nonce_field( 'save-settings', 'cart-buddy-general-settings' ); ?>
				<p class="submit"><input type="submit" value="<?php _e( 'Save Changes', 'LION' ); ?>" class="button button-primary" /></p>
				<?php echo do_action( 'it_cart_buddy_general_settings_form_bottom' ); ?>
			</form>
			<?php echo do_action( 'it_cart_buddy_general_settings_page_bottom' ); ?>
		</div>
		<?php
	}

	/**
	 * Prints the add-ons page in the admin area
	 *
	 * @since 0.2.0
	 * @return void
	*/
	function print_cart_buddy_add_ons_page() {
		$registered = it_cart_buddy_get_add_ons();
		$add_on_cats = it_cart_buddy_get_add_on_categories();
		?>
		<div class="wrap">
			<!-- temp icon --> 
			<?php screen_icon( 'page' ); ?> 
			<h2>Cart Buddy Add-Ons</h2>

			<h3>Enabled Add-ons</h3>
			<?php
			if ( $enabled = get_option( 'it_cart_buddy_enabled_add_ons' ) ) {
				foreach( (array) $enabled as $slug => $location ) {
					if ( empty( $registered[$slug] ) )
						continue;
					$params = $registered[$slug];
					// TEMPORARY UI
					echo '<div style="height:200px;width:200px;border: 1px solid #444;float:left;margin-right:10px;"><div style="height:20px;background:#999;color:#fff;width:100%;text-align:center;padding:10px 0;">' . $params['name'] . '</div><p style="padding:5px">Category: ' . $add_on_cats[$params['options']['category']]['name'] . '</p><p style="padding:5px;">' . $params['description'] . '</p><p style="margin-left:60px;text-align:center;width:75px;background:#999;border:1px solid #777;padding:2px;"><a href="' . get_site_url() . '/wp-admin/admin.php?page=it-cart-buddy-addons&it-cart-buddy-disable-addon=' . $slug . '" style="text-decoration:none;color:#fff;">Disable</a></p></div>';
				}
			} else {
				echo '<p>' . __( 'No Add-ons currently enabled', 'LION' ) . '</p>';
			}
			?>
			<div style="height:1px;clear:both;-top:10px;"></div>
			<hr />

			<h3>Available Add-ons</h3>
			<?php
			$available_addons = false;
			if ( ( $registered ) ) {
				foreach( $registered as $slug => $params ) {
					if ( ! empty( $enabled[$slug] ) )
						continue;

					$available_addons = true;
					// TEMPORARY UI
					echo '<div style="height:200px;width:200px;border: 1px solid #444;float:left;margin-right:10px;"><div style="height:20px;background:#999;color:#fff;width:100%;text-align:center;padding:10px 0;">' . $params['name'] . '</div><p style="padding:5px">Category: ' . $add_on_cats[$params['options']['category']]['name'] . '</p><p style="padding:5px;">' . $params['description'] . '</p><p style="margin-left:60px;text-align:center;width:75px;background:#999;border:1px solid #777;padding:2px;"><a href="' . get_site_url() . '/wp-admin/admin.php?page=it-cart-buddy-addons&it-cart-buddy-enable-addon=' . $slug . '" style="text-decoration:none;color:#fff;">Enable</a></p></div>';
				}
			}
			if ( ! $available_addons )
				echo '<p>' . __( 'No Add-ons available', 'LION' ) . '</p>';
			?>
		</div>
		<?php
	}

	/**
	 * Adds a registered Add-on to list of enabled add-ons
	 *
	 * @since 0.2.0
	*/
	function enable_disable_registered_add_on() {
		$enable_addon  = empty( $_GET['it-cart-buddy-enable-addon'] ) ? false : $_GET['it-cart-buddy-enable-addon'];
		$disable_addon = empty( $_GET['it-cart-buddy-disable-addon'] ) ? false : $_GET['it-cart-buddy-disable-addon'];

		if ( ! $enable_addon && ! $disable_addon )
			return;

		$registered    = it_cart_buddy_get_add_ons();

		// Enable or Disable addon requested by user
		if ( $enable_addon ) {
			$enabled = it_cart_buddy_enable_add_on( $enable_addon );
		} else if ( $disable_addon ) {
			$enabled = it_cart_buddy_disable_add_on( $disable_addon );
		}
		
		// Disable any enabled add-ons that aren't registered any more while we're here.
		foreach( $enabled as $slug => $file ) {
			if ( empty( $registered[$slug] ) )
				it_cart_buddy_disable_add_on( $slug );
		}
			
		wp_safe_redirect( admin_url( '/admin.php?page=it-cart-buddy-addons' ) );
		die();
	}

	/**
	 * Page content for adding a product type
	 *
	 * @since 0.3.0
	 * @return void
	*/
	function print_choose_product_type_admin_page() {
		?>
		<div class="wrap">
			<?php screen_icon( 'page' ); ?>
			<h2>Choose an Product Type to add</h2>
			<p>Temp UI...</p>
			<ul>
			<?php
			foreach( it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'product-type' ) ) ) as $slug => $params ) {
				echo '<li><a href="' . get_site_url() . '/wp-admin/post-new.php?post_type=it_cart_buddy_prod&product_type=' . $slug . '">' . $params['name'] . '</a>';
			}
			?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Opens the Cart Buddy Admin Menu when viewing the Add New page
	 *
	 * @since 0.3.0
	 * @return string
	*/
	function open_cart_buddy_menu_on_post_type_views( $parent_file, $revert=false ) {
		global $submenu_file, $pagenow, $post;

		if ( 'post-new.php' != $pagenow && 'post.php' != $pagenow )
			return $parent_file;

		if ( empty( $post->post_type ) || ( 'it_cart_buddy_prod' != $post->post_type && 'it_cart_buddy_tran' != $post->post_type ) )
			return $parent_file;

		// Set Add New as bold when on the post-new.php screen
		if ( 'post-new.php' == $pagenow )
			$submenu_file = 'it-cart-buddy-choose-product-type';

		// Return it-cart-buddy as the parent (open) menu when on post-new.php and post.php for it_cart_buddy_prod post_types
		return 'it-cart-buddy';
	}

	/**
	 * Redirects post-new.php to it-cart-buddy-choose-product-type when needed
	 *
	 * If we have landed on post-new.php?post_type=it_cart_buddy_prod without the product_type param
	 * and with multiple product-type add-ons enabled.
	 *
	 * @since 0.3.1
	 * @return void
	*/
	function redirect_post_new_to_product_type_selection_screen() {
		global $pagenow;
		$product_type_add_ons = it_cart_buddy_get_enabled_add_ons( array( 'category' => array( 'product-type' ) ) );
		$post_type            = empty( $_GET['post_type'] ) ? false : $_GET['post_type'];
		$product_type         = empty( $_GET['product_type'] ) ? false : $_GET['product_type'];

		if ( count( $product_type_add_ons ) > 1 && 'post-new.php' == $pagenow && 'it_cart_buddy_prod' == $post_type ) {
			if ( empty( $product_type_add_ons[$product_type] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=it-cart-buddy-choose-product-type' ) );
				die();
			}
		}
	}

	/**
	 * Prints select options for the currency type
	 *
	 * @since 0.3.4
	 * return void
	*/
	function print_default_currency_select_options() {
		$currency_options = it_cart_buddy_get_currency_options();
		if ( is_array( $currency_options ) ) {
			foreach( $currency_options as $currency ) {
				echo '<option value="' . $currency->cc . '">' . ucwords( $currency->name ) . ' (' . $currency->symbol . ')</option>' . "\n";
			}
		}
	}

	/**
	 * Save core general settings
	 *
	 * Validates data and saves to options.
	 *
	 * @todo provide feedback to user
	 * @todo validate data
	 * @since 0.3.4
	 * @return void
	*/
	function save_core_general_settings() {
		if ( empty( $_POST ) || 'it-cart-buddy-settings' != $this->_current_page || ! empty( $this->_current_tab ) )
			return;

		if ( ! wp_verify_nonce( $_POST['cart-buddy-general-settings'], 'save-settings' ) )
			return false;

		$settings = get_option( 'it_cart_buddy_general_settings' );

		$updated_settings['company_name']    = stripslashes( $_POST['it_cart_buddy_company_name'] );
		$updated_settings['company_tax_id']  = stripslashes( $_POST['it_cart_buddy_company_tax_id'] );
		$updated_settings['company_email']   = stripslashes( $_POST['it_cart_buddy_company_email'] );
		$updated_settings['company_phone']   = stripslashes( $_POST['it_cart_buddy_company_phone'] );
		$updated_settings['company_address'] = stripslashes( $_POST['it_cart_buddy_company_address'] );

		$updated_settings['default_currency']             = stripslashes( $_POST['it_cart_buddy_default_currency'] );
		$updated_settings['currency_symbol_position']     = stripslashes( $_POST['it_cart_buddy_currency_symbol_position'] );
		$updated_settings['currency_thousands_separator'] = stripslashes( $_POST['it_cart_buddy_currency_thousands_separator'] );
		$updated_settings['currency_decimals_separator']  = stripslashes( $_POST['it_cart_buddy_currency_decimals_separator'] );

		$settings = wp_parse_args( $updated_settings, $settings );
		update_option( 'it_cart_buddy_general_settings', $settings );
	}

	/**
	 * Save core general settings
	 *
	 * Validates data and saves to options.
	 *
	 * @todo provide feedback to user
	 * @todo validate data
	 * @since 0.3.4
	 * @return void
	*/
	function save_core_email_settings() {
		if ( empty( $_POST ) || 'it-cart-buddy-settings' != $this->_current_page || 'email' != $this->_current_tab )
			return;

		if ( ! wp_verify_nonce( $_POST['cart-buddy-email-settings'], 'save-email-settings' ) )
			return false;

		$settings = get_option( 'it_cart_buddy_email_settings' );

		$updated_settings['receipt_email_address']  = stripslashes( $_POST['it_cart_buddy_receipt_email_address'] );
		$updated_settings['receipt_email_name']     = stripslashes( $_POST['it_cart_buddy_receipt_email_name'] );
		$updated_settings['receipt_email_subject']  = stripslashes( $_POST['it_cart_buddy_receipt_email_subject'] );
		$updated_settings['receipt_email_template'] = stripslashes( $_POST['it_cart_buddy_receipt_email_template'] );

		$settings = wp_parse_args( $updated_settings, $settings );
		update_option( 'it_cart_buddy_email_settings', $settings );
	}

	/**
	 * Sets the values for the form fields on the General Settings page
	 *
	 * This is a filter hooked to the it_cart_buddy_general_settings_values filter.
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function set_general_settings_field_values() {
		$options = get_option( 'it_cart_buddy_general_settings' );

		$values['company_name']    = empty( $options['company_name'] ) ? '' : $options['company_name'];
		$values['company_tax_id']  = empty( $options['company_tax_id'] ) ? '' : $options['company_tax_id'];
		$values['company_email']   = empty( $options['company_email'] ) ? get_option( 'admin_email' ) : $options['company_email'];
		$values['company_phone']   = empty( $options['company_phone'] ) ? '' : $options['company_phone'];
		$values['company_address'] = empty( $options['company_address'] ) ? '' : $options['company_address'];

		$values['default_currency']             = empty( $options['default_currency'] ) ? '' : $options['default_currency'];
		$values['currency_symbol_position']     = empty( $options['currency_symbol_position'] ) ? 'before' : $options['currency_symbol_position'];
		$values['currency_thousands_separator'] = empty( $options['currency_thousands_separator'] ) ? ',' : $options['currency_thousands_separator'];
		$values['currency_decimals_separator']  = empty( $options['currency_decimals_separator'] ) ? '.' : $options['currency_decimals_separator'];
		return apply_filters( 'it_cart_buddy_general_settings_values', $values );
	}

	/**
	 * Sets the values for the form fields on the Email Settings page
	 *
	 * This is a filter hooked to the it_cart_buddy_email_settings_values filter.
	 *
	 * @since 0.3.4
	 * @return void
	*/
	function set_email_settings_field_values() {
		$options = get_option( 'it_cart_buddy_email_settings' );

		$values['receipt_email_address']  = empty( $options['receipt_email_address'] ) ? '' : $options['receipt_email_address'];
		$values['receipt_email_name']     = empty( $options['receipt_email_name'] ) ? '' : $options['receipt_email_name'];
		$values['receipt_email_subject']  = empty( $options['receipt_email_subject'] ) ? '' : $options['receipt_email_subject'];
		$values['receipt_email_template'] = empty( $options['receipt_email_template'] ) ? '' : $options['receipt_email_template'];
		return apply_filters( 'it_cart_buddy_email_settings_values', $values );
	}
}
if ( is_admin() )
	$IT_Cart_Buddy_Admin = new IT_Cart_Buddy_Admin( $this );
