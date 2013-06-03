<?php
/**
 * Contains the class or the email notifications object
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * The IT_Exchange_Email_notifications class is for sending out email notification using wp_mail()
 *
 * @since 0.4.0
*/
class IT_Exchange_Email_notifications {
	
	private $transaction_object;
	private $transaction_id;
	private $customer_id;

	/**
	 * Constructor. Sets up the class
	 *
	 * @since 0.4.0
	*/
	function IT_Exchange_Email_notifications() {
		
		add_action( 'it_exchange_add_transaction_completed', array( $this, 'transaction_completed_notification' ), 20, 3 );
		
	}
	
	/**
	 * Process the transaction and send appropriate emails
	 *
	 * @since 0.4.0
	 *
	 * @param object $transaction_object The transaction object
	 * @param int $transaction_id The transaction ID
	 * @param int $customer_id The customer ID
	*/
	function transaction_completed_notification( $transaction_object, $transaction_id, $customer_id ) {
		
		$this->transaction_object = $transaction_object;
		$this->transaction_id     = $transaction_id;
		$this->customer_id        = $customer_id;
		$this->user               = get_userdata( $customer_id );
		
		$settings = it_exchange_get_option( 'settings_email' );	
		
		$headers[] = 'From: ' . $settings['receipt-email-name'] . ' <' . $settings['receipt-email-address'] . '>';
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-Type: text/html';
		$headers[] = 'charset=utf-8';
		
		$subject = $this->replace_template_tags( $settings['receipt-email-subject'] );
		$body = $this->body_header() . wpautop( $this->replace_template_tags( $settings['receipt-email-template'] ) ) . $this->body_footer();
		
		wp_mail( $this->user->user_email, strip_tags( $subject ), $body, $headers );
		
		if ( !empty( $settings['notification-email-address'] ) ) {
			
			$emails = split( ',', $settings['notification-email-address'] );
			
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
		
		$output .= '</body>';
		$output  = '</html>';
		
		return apply_filters( 'it_exchange_email_notification_body_footer', $output );
		
	}
	
	/**
	 * Replace template tags with actual data
	 *
	 * @since 0.4.0
	 *
	 * @param string $text String to be replaced
	 * @return string Replaced string
	*/
	function replace_template_tags( $text ) {
	
		$tags = $this->get_template_tags();
		
		foreach( $tags as $tag => $callback )
			$text = str_replace( $tag, call_user_func_array( $callback, array( $this ) ), $text );
		
		return $text;
		
	}
	
	/**
	 * Get available template tags
	 * Array of tags (key) and callback functions (value)
	 *
	 * @since 0.4.0
	 *
	 * @return array available replacement template tags
	*/
	function get_template_tags() {
	
		//Key = replacement tag
		//Value = callback function
		$defaults = array(
			'{download_list}'  => array( $this, 'replace_download_list_tag' ),
			'{file_urls}'      => array( $this, 'replace_file_urls_tag' ),
			'{name}'           => array( $this, 'replace_name_tag' ),
			'{fullname}'       => array( $this, 'replace_fullname_tag' ),
			'{username}'       => array( $this, 'replace_username_tag' ),
			'{purchase_date}'  => array( $this, 'replace_purchase_date_tag' ),
			'{subtotal}'       => array( $this, 'replace_subtotal_tag' ),
			'{total}'          => array( $this, 'replace_total_tag' ),
			'{price}'          => array( $this, 'replace_price_tag' ),
			'{payment_id}'     => array( $this, 'replace_payment_id_tag' ),
			'{receipt_id}'     => array( $this, 'replace_receipt_id_tag' ),
			'{payment_method}' => array( $this, 'replace_payment_method_tag' ),
			'{sitename}'       => array( $this, 'replace_sitename_tag' ),
			'{receipt_link}'   => array( $this, 'replace_receipt_link_tag' ),
		);
		
		return apply_filters( 'it_exchange_email_notification_template_tags', $defaults );
		
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_download_list_tag( $args ) {
		return 'replace_download_list_tag';
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_file_urls_tag( $args ) {
		return 'replace_file_urls_tag';
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_name_tag( $args ) {
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
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_fullname_tag( $args ) {		
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
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_username_tag( $args ) {
		return $this->user->user_login;
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_purchase_date_tag( $args ) {
		return it_exchange_get_transaction_date( $this->transaction_object );
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_subtotal_tag( $args ) {
		return 'replace_subtotal_tag';
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_total_tag( $args ) {
		return $this->transaction_object->total;	
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_price_tag( $args ) {
		return 'replace_price_tag';
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_payment_id_tag( $args ) {
		return get_post_meta( $this->transaction_id, '_it_exchange_transaction_method_id', true );
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_receipt_id_tag( $args ) {
		return $this->transaction_id;		
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_payment_method_tag( $args ) {
		return it_exchange_get_transaction_method( $this->transaction_object );
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_sitename_tag( $args ) {
		return get_bloginfo( 'name' );
	}
	
	/**
	 * Replacement Tag
	 *
	 * @since 0.4.0
	 *
	 * @param object $args of IT_Exchange_Email_notifications
	 * @return string Replaced value
	*/
	function replace_receipt_link_tag( $args ) {
		return 'replace_receipt_link_tag';
	}
	
}
$IT_Exchange_Email_notifications = new IT_Exchange_Email_notifications();
