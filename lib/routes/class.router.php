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
	 * @var string $_store slug for the store
	 * @since 0.4.0
	*/
	public $_store_slug;

	/**
	 * @var string $_product slug for products
	 * @since 0.4.0
	*/
	public $_product_slug;

	/**
	 * @var string $_account slug for the account page
	 * @since 0.4.0
	*/
	public $_account_slug;

	/**
	 * @var string $_profile slug for the profile slug
	 * @since 0.4.0
	*/
	public $_profile_slug;

	/**
	 * @var string $_profile_edit slug for the profile_edit page
	 * @since 0.4.0
	*/
	public $_profile_edit_slug;

	/**
	 * @var string $_downloads_slug slug for the downloads page
	 * @since 0.4.0
	*/
	public $_downloads_slug;

	/**
	 * @var string $_purchases_slug slug for the purchases page
	 * @since 0.4.0
	*/
	public $_purchases_slug;

	/**
	 * @var string $_log_in_slug slug for the purchases page
	 * @since 0.4.0
	*/
	public $_log_in_slug;

	/**
	 * @var boolean $_is_store is this a store page?
	 * @since 0.4.0
	*/
	public $_is_store = false;

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
	 * @var boolean $_is_profile_edit is this the profile edit page?
	 * @since 0.4.0
	*/
	public $_is_profile_edit = false;

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
	 * @var boolean $_is_downloads is this the downloads page?
	 * @since 0.4.0
	*/
	public $_is_downloads = false;

	/**
	 * @var $_account the WP username for the current user
	 * @since 0.4.0
	*/
	public $_account = false;

	/**
	 * @var $_current_view the current Exchange frontend view
	 * @since 0.4.0
	*/
	public $_current_view = false;

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function IT_Exchange_Router() {
		if ( is_admin() ) {
			add_action( 'init', array( $this, 'set_slugs' ) );
			add_filter( 'rewrite_rules_array', array( $this, 'register_rewrite_rules' ) );
		} else {
			add_action( 'init', array( $this, 'set_slugs' ) );
			add_action( 'template_redirect', array( $this, 'set_environment' ), 8 );
			add_action( 'template_redirect', array( $this, 'set_account' ), 9 );

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
	function set_slugs() {
		$slugs = it_exchange_get_option( 'exchange_settings_pages' );	
		$this->_store_slug        = $slugs['store'];
		$this->_product_slug      = $slugs['product'];
		$this->_account_slug      = $slugs['account'];
		$this->_profile_slug      = $slugs['profile'];
		$this->_profile_edit_slug = $slugs['profile-edit'];
		$this->_downloads_slug    = $slugs['downloads'];
		$this->_purchases_slug    = $slugs['purchases'];
		$this->_log_in_slug       = $slugs['log-in'];
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
		$this->_is_product      = (boolean) get_query_var( 'it_exchange_prod' );
		$this->_is_account      = (boolean) get_query_var( $this->_account_slug );
		$this->_is_profile      = (boolean) get_query_var( $this->_profile_slug );
		$this->_is_profile_edit = (boolean) get_query_var( $this->_profile_edit_slug );
		$this->_is_downloads    = (boolean) get_query_var( $this->_downloads_slug );
		$this->_is_purchases    = (boolean) get_query_var( $this->_purchases_slug );
		$this->_is_log_in       = (boolean) get_query_var( $this->_log_in_slug );

		// Set current view property
		if ( $this->_is_log_in ) {
			$this->_current_view = 'log-in';
		} else if ( $this->_is_purchases ) {
			$this->_current_view = 'purchases';
		} else if ( $this->_is_downloads ) {
			$this->_current_view = 'downloads';
		} else if ( $this->_is_profile_edit ) {
			$this->_current_view = 'profile-edit';
		} else if ( $this->_is_profile ) {
			$this->_current_view = 'profile';
		} else if ( $this->_is_account ) {
			$this->_current_view = 'account';
		} else if ( $this->_is_product ) {
			$this->_current_view = 'product';
		} else if ( $this->_is_store ) {
			$this->_current_view = 'store';
		}
	}

	/**
	 * Sets the account property based on current query_var or current user
	 *
	 * @since 0.4.0
	 *
	 * @return void
	*/
	function set_account() {
		// Set false if not viewing an account based page: account, profile, profile-edit, downloads, purchases, log-in
		if ( ! $this->_is_account )
			$this->_account = false;

		// Get current customer
		$customer = it_exchange_get_current_customer();
		$customer_name = empty( $customer->wp_user->data->user_login ) ? false : $customer->wp_user->data->user_login;

		// Get requested account
		$account = get_query_var( $this->_account_slug );
		if ( 1 == $account && $customer_name )
			$account = $customer_name;
		if ( 1 == $account )
			$account = false;

		$this->_account = $account;
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

		// Return the iThemes Exchange Template if one is found
		if ( $template = it_exchange_locate_template( $this->_current_view ) )
			return $template;

		// If this is a single product and no iThemes Exchange template was found, set some filters
		if ( 'product' == $this->_current_view )
			$this->add_single_product_filters();

		// If no iThemes Exchange Template was found, use the theme's page template
		if ( $template = get_page_template() )
			return $template;

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
			$this->_account_slug,
			$this->_profile_slug,
			$this->_profile_edit_slug,
			$this->_downloads_slug,
			$this->_purchases_slug,
			$this->_log_in_slug,
		);
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
		$new_rules = array(
			// Edit Profile
			$this->_account_slug . '/([^/]+)/' . $this->_profile_slug . '/' . $this->_profile_edit_slug => 'index.php?' . $this->_account_slug . '=$matches[1]&' . $this->_profile_edit_slug . '=1', 
			$this->_account_slug . '/' . $this->_profile_slug . '/' . $this->_profile_edit_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_profile_edit_slug . '=1',

			// Log in
			$this->_account_slug . '/([^/]+)/' . $this->_log_in_slug => 'index.php?' . $this->_account_slug . '=$matches[1]&' . $this->_log_in_slug . '=1',
			$this->_account_slug . '/' . $this->_log_in_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_log_in_slug . '=1',

			// Purchases
			$this->_account_slug . '/([^/]+)/' . $this->_purchases_slug => 'index.php?' . $this->_account_slug . '=$matches[1]&' . $this->_purchases_slug . '=1',
			$this->_account_slug . '/' . $this->_purchases_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_purchases_slug . '=1',

			// Downloads 
			$this->_account_slug . '/([^/]+)/' . $this->_downloads_slug => 'index.php?' . $this->_account_slug . '=$matches[1]&' . $this->_downloads_slug . '=1',
			$this->_account_slug . '/' . $this->_downloads_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_downloads_slug . '=1',

			// Profile
			$this->_account_slug . '/([^/]+)/' . $this->_profile_slug => 'index.php?' . $this->_account_slug . '=$matches[1]&' . $this->_profile_slug . '=1',
			$this->_account_slug . '/' . $this->_profile_slug => 'index.php?' . $this->_account_slug . '=1&' . $this->_profile_slug . '=1',

			// Account
			$this->_account_slug . '/([^/]+)/?$' => 'index.php?' . $this->_account_slug . '=$matches[1]',
			$this->_account_slug => 'index.php?' . $this->_account_slug . '=1',

			// Store
			$this->_store_slug  => 'index.php?' . $this->_store_slug . '=1',
		);  
		return array_merge( $new_rules, $existing );
	}
}
$IT_Exchange_Router = new IT_Exchange_Router();
