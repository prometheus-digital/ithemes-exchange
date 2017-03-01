<?php
/**
 * Contains the capabilities class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Capabilities
 */
class IT_Exchange_Capabilities {

	const PRODUCT = 'it_product';
	const TRANSACTION = 'it_transaction';
	const COUPON = 'it_coupon';

	/**
	 * IT_Exchange_Capabilities constructor.
	 */
	public function __construct() {
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
	}

	/**
	 * Map meta capabilities.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $caps    Primitive capabilities required.
	 * @param string $cap     Meta capability requested.
	 * @param int    $user_id User ID testing against.
	 * @param array  $args    Additional arguments. `$args[0]` typically contains the object ID.
	 *
	 * @return array
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {

		switch ( $cap ) {
			case 'create_it_coupons':
				return array( 'edit_it_coupons' );
			case 'create_it_products':
				return array( 'edit_it_products' );
			case 'read_it_transaction':
				if ( empty( $args[0] ) || ! $txn = it_exchange_get_transaction( $args[0] ) ) {
					return array( 'do_not_allow' );
				}

				if ( $txn->customer_id === (int) $user_id ) {
					return array();
				}

				return map_meta_cap( 'edit_it_transaction', $user_id, $txn->ID );
			case 'edit_it_transaction':

				if ( doing_action( 'save_post_it_exchange_tran' ) || doing_action( 'save_post' ) || doing_action( 'wp_insert_post' ) ) {
					return array( 'edit_posts' );
				}

				if ( empty( $args[0] ) || ! $txn = it_exchange_get_transaction( $args[0] ) ) {
					return array( 'do_not_allow' );
				}

				return array( 'edit_others_it_transactions' );
			case 'delete_it_transaction':
				return array( 'delete_others_it_transactions' );
			case 'list_it_transactions':
				return array( 'edit_others_it_transactions' );
			case 'it_list_payment_tokens':

				if ( ! empty( $args[0] ) && (int) $user_id === (int) $args[0] ) {
					return array();
				}

				return array( 'it_list_others_payment_tokens' );
			case 'it_create_payment_tokens':

				if ( ! empty( $args[0] ) && $user_id === (int) $args[0] ) {
					return array();
				}

				return array( 'it_create_others_payment_tokens' );
			case 'it_read_payment_token':

				if ( ! $user_id || empty( $args[0] ) ) {
					return array( 'do_not_allow' );
				}

				$token = $args[0] instanceof ITE_Payment_Token ? $args[0] : ITE_Payment_Token::get( $args[0] );

				if ( ! $token ) {
					return array( 'do_not_allow' );
				}

				if ( $token->customer && $token->customer->ID === (int) $user_id ) {
					return array(); // a user can edit their own payment tokens
				}

				return array( 'it_list_others_payment_tokens' );
			case 'it_delete_payment_token':
			case 'it_edit_payment_token':

				if ( ! $user_id || empty( $args[0] ) ) {
					return array( 'do_not_allow' );
				}

				$token = $args[0] instanceof ITE_Payment_Token ? $args[0] : ITE_Payment_Token::get( $args[0] );

				if ( ! $token ) {
					return array( 'do_not_allow' );
				}


				if ( $token->customer && $token->customer->ID === (int) $user_id ) {
					return array(); // a user can edit their own payment tokens
				}

				return array( 'it_edit_others_payment_tokens' );
			case 'it_use_payment_token':

				if ( ! $user_id || empty( $args[0] ) ) {
					return array( 'do_not_allow' );
				}

				$token = $args[0] instanceof ITE_Payment_Token ? $args[0] : ITE_Payment_Token::get( $args[0] );

				if ( ! $token ) {
					return array( 'do_not_allow' );
				}

				if ( $token->customer && $token->customer->ID === (int) $user_id ) {
					return array(); // a user can edit use own payment tokens
				}

				// Necessary for a manual admin purchase
				return array( 'it_use_others_payment_tokens' );
			case 'it_create_carts':
				return array();
			case 'it_read_cart':
			case 'it_edit_cart':

				$cart = null;

				if ( ! isset( $args[0] ) ) {
					$cart = it_exchange_get_current_cart( false );
				} elseif ( $args[0] instanceof \ITE_Cart ) {
					$cart = $args[0];
				} elseif ( is_string( $args[0] ) ) {
					$cart = it_exchange_get_cart( $args[0] );
				}

				if ( ! $cart || $cart->is_guest() || ! $cart->get_customer() ) {
					return array( 'do_not_allow' );
				}

				if ( $cart->get_customer()->get_ID() == $user_id ) {
					return array();
				}

				return array( 'do_not_allow' );
		}

		return $caps;
	}

	/**
	 * Get capabilities for the product post type.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_caps_for_product() {
		return $this->get_post_type_caps_for( self::PRODUCT );
	}

	/**
	 * Get capabilities for the transaction post type.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_caps_for_transaction() {
		return $this->get_post_type_caps_for( self::TRANSACTION );
	}

	/**
	 * Get capabilities for the coupon post type.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_caps_for_coupons() {
		return $this->get_post_type_caps_for( self::COUPON );
	}

	/**
	 * Get post type capabilities for a given post type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_post_type_caps_for( $type ) {
		return array(
			"edit_{$type}s",
			"edit_others_{$type}s",
			"publish_{$type}s",
			"read_private_{$type}s",
			"delete_{$type}s",
			"delete_private_{$type}s",
			"delete_published_{$type}s",
			"delete_others_{$type}s",
			"edit_private_{$type}s",
			"edit_published_{$type}s"
		);
	}
}
