<?php
/**
 * This file contains the class in charge of rewrites and template fetching
 *
 * @since 0.4.0
 * @package IT_Exchange 
*/

/**
 * Router Class. Registers rewrite rules and associated logic
 *
 * @since 0.4.0
*/
class IT_Exchange_Router {

	/**
	 * @var string $_store_slug slug for the store
	 * @since 0.4.0
	*/
	public $_store_slug;

	/**
	 * @var string $_store_name name for the store
	 * @since 0.4.0
	*/
	public $_store_name;
	
	/**
	 * @var string $_transaction_slug slug for processing transactions
	 * @since 0.4.0
	*/
	public $_transaction_slug;

	/**
	 * @var string $_transaction_name name for processing transactions
	 * @since 0.4.0
	*/
	public $_transaction_name;

	/**
	 * @var string $_product_slug slug for products
	 * @since 0.4.0
	*/
	public $_product_slug;

	/**
	 * @var string $_product_name name for products
	 * @since 0.4.0
	*/
	public $_product_name;

	/**
	 * @var string $_account_slug slug for the account page
	 * @since 0.4.0
	*/
	public $_account_slug;

	/**
	 * @var string $_account_name name for the account page
	 * @since 0.4.0
	*/
	public $_account_name;

	/**
	 * @var string $_customer_name name for the account page
	 * @since 0.4.0
	*/
	public $_customer_name;

	/**
	 * @var string $_profile_slug slug for the profile slug
	 * @since 0.4.0
	*/
	public $_profile_slug;

	/**
	 * @var string $_profile_name name for the profile slug
	 * @since 0.4.0
	*/
	public $_profile_name;

	/**
	 * @var string $_registration_slug slug for the registration slug
	 * @since 0.4.0
	*/
	public $_registration_slug;

	/**
	 * @var string $_registration_name name for the registration slug
	 * @since 0.4.0
	*/
	public $_registration_name;

	/**
	 * @var string $_downloads_slug slug for the downloads page
	 * @since 0.4.0
	*/
	public $_downloads_slug;

	/**
	 * @var string $_downloads_name name for the downloads page
	 * @since 0.4.0
	*/
	public $_downloads_name;

	/**
	 * @var string $_purchases_slug slug for the purchases page
	 * @since 0.4.0
	*/
	public $_purchases_slug;

	/**
	 * @var string $_purchases_name name for the purchases page
	 * @since 0.4.0
	*/
	public $_purchases_name;

	/**
	 * @var string $_log_in_slug slug for the purchases page
	 * @since 0.4.0
	*/
	public $_log_in_slug;

	/**
	 * @var string $_log_in_name name for the purchases page
	 * @since 0.4.0
	*/
	public $_log_in_name;
	
	/**
	 * @var string $_log_out_slug slug for the purchases page
	 * @since 0.4.0
	*/
	public $_log_out_slug;

	/**
	 * @var string $_log_out_name name for the purchases page
	 * @since 0.4.0
	*/
	public $_log_out_name;

	/**
	 * @var string $_confirmation_slug slug for the confirmation page
	 * @since 0.4.0
	*/
	public $_confirmation_slug;

	/**
	 * @var string $_confirmation_name name for the confirmation page
	 * @since 0.4.0
	*/
	public $_confirmation_name;

	/**
	 * @var string $_reports_slug slug for the reports page
	 * @since 0.4.0
	*/
	public $_reports_slug;

	/**
	 * @var string $_reports_name name for the reports page
	 * @since 0.4.0
	*/
	public $_reports_name;

	/**
	 * @var boolean $_is_store is this a store page?
	 * @since 0.4.0
	*/
	public $_is_store = false;

	/**
	 * @var boolean $_is_transaction is this the transaction page?
	 * @since 0.4.0
	*/
	public $_is_transaction = false;

	/**
	 * @var boolean $_is_product is this a single product page?
	 * @since 0.4.0
	*/
	public $_is_product = false;

	/**
	 * @var boolean is this the account page?
	 * @since 0.4.0
	*/
	public $_is_account = false;

	/**
	 * @var boolean $_is_profile is the the profile page?
	 * @since 0.4.0
	*/
	public $_is_profile = false;

	/**
	 * @var boolean $_is_registration is the the registration page?
	 * @since 0.4.0
	*/
	public $_is_registration = false;

	/**
	 * @var boolean $_is_purchases is this the purchases page?
	 * @since 0.4.0
	*/
	public $_is_purchases = false;

	/**
	 * @var boolean $_is_log_in is this the log inpage?
	 * @since 0.4.0
	*/
	public $_is_log_in = false;

	/**
	 * @var boolean $_is_log_out is this the log inpage?
	 * @since 0.4.0
	*/
	public $_is_log_out = false;

	/**
	 * @var boolean $_is_downloads is this the downloads page?
	 * @since 0.4.0
	*/
	public $_is_downloads = false;

	/**
	 * @var boolean $_is_confirmation is this the confirmation page?
	 * @since 0.4.0
	*/
	public $_is_confirmation = false;

	/**
	 * @var $_account the WP username for the current user
	 * @since 0.4.0
	*/
	public $_account = false;

	/**
	 * @var boolean $_is_reports is this the reports page?
	 * @since 0.4.0
	*/
	public $_is_reports = false;

	/**
	 * @var string $_current_view the current Exchange frontend view
	 * @since 0.4.0
	*/
	public $_current_view = false;

	/**
	 * @var boolean $_pretty_permalinks are pretty permalinks set in WP Settings?
	 * @since 0.4.0
	*/
	public $_pretty_permalinks = false;

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Exchange_Router() {
		add_action( 'init', array( $this, 'set_slugs_and_names' ) );
		add_action( 'init', array( $this, 'set_pretty_permalinks_boolean' ) );
		if ( is_admin() ) {
			add_filter( 'rewrite_rules_array', array( $this, 'register_rewrite_rules' ) );
		} else {
			add_action( 'template_redirect', array( $this, 'set_environment' ), 8 );
			add_action( 'template_redirect', array( $this, 'registration_redirect' ), 9 );
			add_action( 'template_redirect', array( $this, 'login_out_page_redirect' ), 9 );
			add_action( 'template_redirect', array( $this, 'set_account' ), 10 );
			add_action( 'template_redirect', array( $this, 'protect_pages' ), 11 );
			add_action( 'template_redirect', array( $this, 'prevent_empty_checkouts' ), 11 );
			add_action( 'template_redirect', array( $this, 'process_transaction' ), 12 );
			add_action( 'template_redirect', array( $this, 'set_wp_query_vars' ) );

			add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
			add_filter( 'template_include', array( $this, 'fetch_template' ) );
			add_filter( 'template_include', array( $this, 'load_casper' ), 11 );
		}
	}

	/**
	 * Loads the slug properties from settings
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_slugs_and_names() {
		$slugs                    = it_exchange_get_option( 'settings_pages' );	
		$this->_store_slug        = $slugs['store-slug'];
		$this->_store_name        = $slugs['store-name'];
		$this->_transaction_slug  = $slugs['transaction-slug'];;
		$this->_transaction_name  = $slugs['transaction-name'];
		$this->_product_slug      = $slugs['product-slug'];
		$this->_product_name      = $slugs['product-name'];
		$this->_account_slug      = $slugs['account-slug'];
		$this->_account_name      = $slugs['account-name'];
		$this->_profile_slug      = $slugs['profile-slug'];
		$this->_profile_name      = $slugs['profile-name'];
		$this->_registration_slug = $slugs['registration-slug'];
		$this->_registration_name = $slugs['registration-name'];
		$this->_downloads_slug    = $slugs['downloads-slug'];
		$this->_downloads_name    = $slugs['downloads-name'];
		$this->_purchases_slug    = $slugs['purchases-slug'];
		$this->_purchases_name    = $slugs['purchases-name'];
		$this->_log_in_slug       = $slugs['log-in-slug'];
		$this->_log_in_name       = $slugs['log-in-name'];
		$this->_log_out_slug      = $slugs['log-out-slug'];
		$this->_log_out_name      = $slugs['log-out-name'];
		$this->_confirmation_slug = $slugs['confirmation-slug'];
		$this->_confirmation_name = $slugs['confirmation-name'];
		$this->_reports_slug      = $slugs['reports-slug'];
		$this->_reports_name      = $slugs['reports-name'];

		// Allow add-ons to create their own ghost pages
		$add_on_ghost_pages = apply_filters( 'it_exchange_add_ghost_pages', array() );
		foreach( (array) $add_on_ghost_pages as $page => $data ) {
			$slug = '_' . $data['slug'] . '_slug';
			$name = '_' . $data['slug'] . '_name';
			$this->$slug = $slugs[$data['slug'] . '-slug'];
			$this->$name = $slugs[$data['slug'] . '-name'];
		}
	}

	/**
	 * Sets the pretty permalinks boolean
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_pretty_permalinks_boolean() {
		$permalinks = get_option( 'permalink_structure' );
		$this->_pretty_permalinks = ! empty( $permalinks );
	}

	/**
	 * Sets the environment based properties
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_environment() {
		$this->_is_store        = (boolean) get_query_var( $this->_store_slug );
		$this->_is_transaction  = (boolean) get_query_var( $this->_transaction_slug );
		$this->_is_product      = (boolean) get_query_var( $this->_product_slug );
		$this->_is_account      = (boolean) get_query_var( $this->_account_slug );
		$this->_is_profile      = (boolean) get_query_var( $this->_profile_slug );
		$this->_is_registration = (boolean) get_query_var( $this->_registration_slug );
		$this->_is_downloads    = (boolean) get_query_var( $this->_downloads_slug );
		$this->_is_purchases    = (boolean) get_query_var( $this->_purchases_slug );
		$this->_is_log_in       = (boolean) get_query_var( $this->_log_in_slug );
		$this->_is_log_out      = (boolean) get_query_var( $this->_log_out_slug );
		$this->_is_confirmation = (boolean) get_query_var( $this->_confirmation_slug );
		$this->_is_reports      = (boolean) get_query_var( $this->_reports_slug );

		// Allow add-ons to create their own ghost pages
		$add_on_ghost_pages     = apply_filters( 'it_exchange_add_ghost_pages', array() );
		foreach( (array) $add_on_ghost_pages as $page => $data ) {
			$is_property        = '_is_' . $data['slug'];
			$slug_property      = '_' . $data['slug'] . '_slug';
			$this->$is_property = (boolean) get_query_var( $this->$slug_property );
		}

		// Set current view property
		if ( $this->_is_log_in ) {
			$this->_current_view = 'log-in';
		} else if ( $this->_is_log_out ) {
			$this->_current_view = 'log-out';
		} else if ( $this->_is_purchases ) {
			$this->_current_view = 'purchases';
		} else if ( $this->_is_reports ) {
			$this->_current_view = 'reports';
		} else if ( $this->_is_confirmation ) {
			$this->_current_view = 'confirmation';
		} else if ( $this->_is_downloads ) {
			$this->_current_view = 'downloads';
		} else if ( $this->_is_registration ) {
			$this->_current_view = 'registration';
		} else if ( $this->_is_profile ) {
			$this->_current_view = 'profile';
		} else if ( $this->_is_account ) {
			$this->_current_view = 'account';
		} else if ( $this->_is_product ) {
			$this->_current_view = 'product';
		} else if ( $this->_is_transaction ) {
			$this->_current_view = 'transaction';
		} else if ( $this->_is_store ) {
			$this->_current_view = 'store';
		}

		// Allow add-ons to create their own ghost pages
		$add_on_ghost_pages     = apply_filters( 'it_exchange_add_ghost_pages', array() );
		foreach( (array) $add_on_ghost_pages as $page => $data ) {
			$is_property        = '_is_' . $data['slug'];
			$current_view       = $data['slug'];
			if ( $this->$is_property )
				$this->_current_view = $current_view;
		}

		// Add hook for things that need to be done when on an exchange page
		if ( $this->_current_view )
			do_action( 'it_exchange_template_redirect', $this->_current_view );
	}

	/**
	 * Sets the account property based on current query_var or current user
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_account() {
		// Return if not viewing an account based page: account, profile, downloads, purchases, log-in
		if ( ! $this->_is_account )
			return;
		
		$account = get_query_var( $this->_account_slug );

		if ( 1 == $account ) {
		
			$customer_id = get_current_user_id();
			
		} else {
			
			if ( $customer = get_user_by( 'login', $account ) )
				$customer_id = $customer->ID;
			else
				$customer_id = false;
			
		}
		
		$this->_account = $customer_id;
		set_query_var( 'account', $customer_id );
		
	}

	/**
	 * Adds some custom query vars to WP_Query
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_wp_query_vars() {
		set_query_var( 'it_exchange_view', $this->_current_view );
	}
	
	/**
	 * Redirects users away from login page if they're already logged in
	 * or Redirects to /store/ if they log out.
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function login_out_page_redirect() {
		if ( is_user_logged_in() && 'log-in' == $this->_current_view ) {
			wp_redirect( it_exchange_get_page_url( 'profile' ) );
			die();
		} else if ( is_user_logged_in() && 'log-out' == $this->_current_view ) {
			wp_redirect( str_replace( '&amp;', '&', wp_logout_url( it_exchange_get_page_url( 'store' ) ) ) );
			die();
		} else if ( ! is_user_logged_in() && 'log-out' == $this->_current_view ) {
			wp_redirect( it_exchange_get_page_url( 'log-in' ) );
			die();
		}
	}
	
	/**
	 * Redirects users away from registration page if they're already logged in
	 * except for Administrators, because they might want to see the registration page.
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function registration_redirect() {
		if ( is_user_logged_in() && 'registration' == $this->_current_view 
			&& ! current_user_can( 'administrator' ) ) {
			wp_redirect( it_exchange_get_page_url( 'profile' ) );
			die();
		}
	}

	/**
	 * Redirects users away from pages they don't have permission to view
	 *
	 * @since 0.4.0
	 *
	 * @todo Make this more robust. Give it an API
	 * @return void
	*/
	function protect_pages() {

		// If user is an admin, abandon this. They can see it all
		if ( current_user_can( 'administrator' ) )
			return;

		// Set pages that we want to protect in one way or another
		$pages_to_protect = array(
			'account', 'profile', 'downloads', 'purchases', 'reports',
		);

		// Abandon if not a proteced page
		if ( ! in_array( $this->_current_view, $pages_to_protect ) )
			return;

		// If user isn't logged in, redirect
		if ( !is_user_logged_in() ) {
			wp_redirect( it_exchange_get_page_url( 'log-in' ) );
			die();
		}

		// Get current user
		$user_id = get_current_user_id();

		// If trying to view reports and not an admin, redirect
		if ( 'reports' == $this->_current_view && ! current_user_can( 'administrator' ) ) {
			wp_redirect( it_exchange_get_page_url( 'account' ) );
			die();
		}

		// If trying to view reports and not an admin, redirect
		if ( in_array( $this->_current_view, $pages_to_protect ) 
				&& $this->_account != $user_id && ! current_user_can( 'administrator' ) ) {
			wp_redirect( it_exchange_get_page_url( $this->_current_view ) );
			die();
		}

		// If current user isn't an admin and doesn't match the account, redirect
		if ( $this->_account != $user_id && ! current_user_can( 'administrator' ) ) {
			wp_redirect( it_exchange_get_page_url( 'store' ) );
			die();
		}
	}
	
	/**
	 * Redirect away from checkout if cart is empty
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function prevent_empty_checkouts() {
		if ( 'checkout' != $this->_current_view )
			return;

		if ( ! it_exchange_get_cart_products() ) {
			wp_redirect( it_exchange_get_page_url( 'cart' ) );
			die();
		}

	}

	/**
	 * Redirects users to confirmation page if the transaction was successful
	 * or to the checkout page if there was a failure.
	 *
	 * @since 0.4.0
	 *
	 * @todo Make this more robust. Give it an API
	 * @return void
	*/
	function process_transaction() {
				
		if ( 'transaction' == $this->_current_view ) {

			if ( is_user_logged_in() ) {
				$transaction_id = apply_filters( 'it_exchange_process_transaction', false );
				
				// If we made a transaction
				if ( $transaction_id ) {

					// Clear the cart
					it_exchange_empty_shopping_cart();
					
					// Grab the transaction confirmation URL. fall back to store if confirmation url fails
					$confirmation_url = it_exchange_get_transaction_confirmation_url( $transaction_id );
					if ( empty( $confirmation_url ) )
						$confirmation_url = it_exchange_get_page_url( 'store' );
					
					// Redirect
					wp_redirect( $confirmation_url );
					die();
				}
			}
			
			wp_redirect( it_exchange_get_page_url( 'checkout' ) );
			die();
			
		}
	
	}

	/**
	 * Determines which template file should be used for the current frontend view.
	 *
	 * If this is an Exchange view, look for the appropriate Exchange template in the users current theme.
	 * If an Exchange template is found in the theme, use the theme's page template and swap out our the_content for our template_parts
	 *
	 * @since 0.4.0
	 *
	 * @param the default template as determined by WordPress
	 * @return string a template file
	*/
	function fetch_template( $existing ) {
		// Return existing if this isn't an Exchange frontend view
		if ( ! $this->_current_view )
			return $existing;
			
		// Set pages that we want to protect in one way or another
		$profile_pages = array(
			'account', 'profile', 'downloads', 'purchases',
		);
						
		if ( in_array( $this->_current_view, $profile_pages ) ) {
			if ( ! $this->_account )
				return get_404_template();
		}

		// Return the iThemes Exchange Template if one is found
		if ( $template = it_exchange_locate_template( $this->_current_view ) )
			return $template;

		// If this is a single product and no iThemes Exchange template was found, and no theme template was found, set some filters
		if ( 'product' == $this->_current_view ) {
			if ( $theme_singular = get_query_template( 'single', array( 'single-it_exchange_prod.php' ) ) )
				return $theme_singular;
			else
				$this->add_single_product_filters();
		}

		// If no iThemes Exchange Template was found, use the theme's page template
		if ( $template = get_page_template() ) {
			remove_filter( 'the_content', 'wpautop' );
			return $template;
		}

		// If nothing was found here, the theme has issues. Just return whatever template WP was going to use
		return $existing;
	}

	/**
	 * This loads our ghost post data and vars into the wp_query global when needed
	 *
	 * @since 0.4.0
	 *
	 * @param string $template We are hooking into a filter for an action. Always return value unchanged
	 * @return string 
	*/
	function load_casper( $template ) {
		if ( $this->_current_view && 'product' != $this->_current_view ) {
			require( dirname( __FILE__ ) . '/class.casper.php' );
			new IT_Exchange_Casper( $this->_current_view, $this );
		}
		return $template;
	}

	/**
	 * This adds some fiters that are needed if viewing a single product w/o iThemes Exchange template files in the active theme or theme parent
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function add_single_product_filters() {
		add_filter( 'the_content', array( $this, 'single_product_content_filter' ) );
	}

	/**
	 * This substitutes the themes content for our content-product template part
	 *
	 * @since 0.4.0
	 *
	 * @param string $content exising default content
	 * @param string content generated from template part
	*/
	function single_product_content_filter() {
		ob_start();
		it_exchange_get_template_part( 'content', 'product' );
		return  ob_get_clean();
	}

	/**
	 * Registers our custom query vars with WordPress
	 *
	 * @since 0.4.0
	 *
	 * @param array $existing existing query vars
	 * @return array modified query vars
	*/
	function register_query_vars( $existing ) {
		$vars = array(
			$this->_store_slug,
			$this->_transaction_slug,
			$this->_account_slug,
			$this->_profile_slug,
			$this->_registration_slug,
			$this->_downloads_slug,
			$this->_purchases_slug,
			$this->_log_in_slug,
			$this->_log_out_slug,
			$this->_confirmation_slug,
			$this->_reports_slug,
		);

		// Allow add-ons to create their own ghost pages
		$add_on_ghost_pages = apply_filters( 'it_exchange_add_ghost_pages', array() );
		foreach( (array) $add_on_ghost_pages as $page => $data ) {
			$slug = '_' . $data['slug'] . '_slug';
			$vars[] = $this->$slug;
		}
		
		return array_merge( $vars, $existing );
	}

	/**
	 * Registers our custom rewrite rules based on slug settings
	 *
	 * @since 0.4.0
	 *
	 * @param array $exisiting existing rewrite rules
	 * @return array modified rewrite rules
	*/
	function register_rewrite_rules( $existing ) {
		$this->set_slugs_and_names();
		$new_rules = array(
			// Log in
			$this->_account_slug . '/' . $this->_log_in_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_log_in_slug . '=1',
			
			// Log out
			$this->_account_slug . '/' . $this->_log_out_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_log_out_slug . '=1',

			// Purchases
			$this->_account_slug  . '/([^/]+)/' . $this->_purchases_slug => 'index.php?' . $this->_account_slug . '=$matches[1]&' . $this->_purchases_slug . '=1',
			$this->_account_slug . '/' . $this->_purchases_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_purchases_slug . '=1',

			// Downloads 
			$this->_account_slug  . '/([^/]+)/' . $this->_downloads_slug => 'index.php?' . $this->_account_slug . '=$matches[1]&' . $this->_downloads_slug . '=1',
			$this->_account_slug . '/' . $this->_downloads_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_downloads_slug . '=1',

			// Profile
			$this->_account_slug  . '/([^/]+)/' . $this->_profile_slug  => 'index.php?' . $this->_account_slug . '=$matches[1]&' . $this->_profile_slug . '=1',
			$this->_account_slug . '/' . $this->_profile_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_profile_slug . '=1',
			
			// Registration
			$this->_account_slug  . '/' . $this->_registration_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_registration_slug . '=1',

			// Account
			$this->_account_slug . '/([^/]+)/?$' => 'index.php?' . $this->_account_slug . '=$matches[1]&' . $this->_profile_slug . '=1',
			$this->_account_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_profile_slug . '=1',
			
			// Confirmation
			$this->_store_slug . '/' . $this->_confirmation_slug . '/([^/]+)/?$' => 'index.php?' . $this->_store_slug . '=1&' . $this->_confirmation_slug . '=$matches[1]',

			// Admin Reports
			$this->_store_slug . '/' . $this->_reports_slug => 'index.php?' . $this->_store_slug . '=1&' . $this->_reports_slug . '=1',
			
			// Transaction
			$this->_store_slug . '/' . $this->_transaction_slug  => 'index.php?' . $this->_store_slug . '=1&' . $this->_transaction_slug . '=1',

			// Store
			$this->_store_slug  => 'index.php?' . $this->_store_slug . '=1',
		);
		
		// Merge core Exchange rewrites with core WP rewrites
		$existing =  array_merge( $new_rules, $existing );

		// Allow add-ons to create their own ghost pages
		$add_on_ghost_pages = apply_filters( 'it_exchange_add_ghost_pages', array() );
		foreach( (array) $add_on_ghost_pages as $page => $data ) {
			if ( ! empty ( $data['rewrites'] ) && is_array( $data['rewrites'] ) )
				$existing = array_merge( $data['rewrites'], $existing );
		}

		return $existing;
	}
}
$IT_Exchange_Router = new IT_Exchange_Router();
