<?php
/**
 * Contains the class or the customer object
 * @since 0.3.8
 * @package IT_Cart_Buddy
*/

/**
 * The IT_Cart_Buddy_Customer class holds all important data for a specific customer
 *
 * @since 0.3.8
*/
class IT_Cart_Buddy_Customer {
	
	/**
	 * @var integer $id the customer id. corresponds with the WP user id
	 * @since 0.3.8
	*/
	var $id;

	/**
	 * @var object $wp_user the wp_user or false
	 * @since 0.3.8
	*/
	var $wp_user;

	/**
	 * @var object $customer_data customer information
	 * @since 0.3.8
	*/
	var $data;

	/**
	 * @var array $transaction_history an array of all transactions the user has ever created
	 * @since 0.3.8
	*/
	var $transaction_history;

	/**
	 * @var array $purchase_history an array of all products ever purchased
	 * @since 0.3.8
	*/
	var $purchase_history;

	/**
	 * Constructor. Sets up the customer
	 *
	 * @since 0.3.8
	 * @param integer $id customer id
	 * @return mixed false if no customer is found. self if customer is located
	*/
	function IT_Cart_Buddy_Customer( $id ) {
		// Set the ID
		$this->id = $id;

		// Set properties
		$this->init();

		// Return false if not a WP User
		if ( ! $this->is_wp_user() )
			return false;
		
		// Return object if found a WP user
		return $this;
	}

	/**
	 * Sets up the class
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function init() {
		$this->set_wp_user();
		$this->set_customer_data();
		$this->set_transaction_history();
		$this->set_purchase_history();
	}

	/**
	 * Sets the $wp_user property
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function set_wp_user() {
		$this->wp_user = new WP_User( $this->id );

		if ( is_wp_error( $this->wp_user ) )
			$this->wp_user = false;
	}

	/**
	 * Sets customer data
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function set_customer_data() {
		$data = (object) $this->data;
		
		$wp_user_data = get_object_vars( $this->wp_user->data );
		foreach( (array) $wp_user_data as $key => $value ) {
			$data->$key = $value;
		}
		$data->first_name = get_user_meta( $this->id, 'first_name', true );
		$data->last_name  = get_user_meta( $this->id, 'last_name', true );

		$data = apply_filters( 'it_cart_buddy_set_customer_data', $data, $this->id );
		$this->data = $data;
	}

	/**
	 * Returns true or false based on whether the $id property is a WP User id
	 *
	 * @since 0.3.8
	 * @return boolean
	*/
	function is_wp_user() {
		return (bool) $this->wp_user;
	}

	/**
	 * Sets an array of all transactions the customer has ever created.
	 *
	 * set_purchase_history() will use this
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function set_transaction_history() {

		if ( ! $this->is_wp_user() )
			return;

		$args = array(
			'numberposts' => -1,
			'post_author' => $this->id,
		);
		
		// Set all user transactions
		$this->transaction_history = it_cart_buddy_get_transactions( $args );
	}

	/**
	 * Sets an array of products purchased by product_id
	 *
	 * @since 0.3.8
	 * @return void
	*/
	function set_purchase_history() {
		if ( ! empty( $this->transaction_history ) ) {
			foreach( (array) $this->transaction_history as $key => $details ) {
				if ( ! empty( $txn->transaction_data['_it_cart_buddy_transaction_products'] ) ) {
					foreach( (array) $txn->transaction_data['_it_cart_buddy_transaction_products'] as $product ) {
						$this->purchase_history[$product['id']][] = $product;
					}
				}
			}
		}
	}
}
