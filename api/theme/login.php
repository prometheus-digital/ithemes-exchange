<?php
/**
 * Login class for THEME API
 *
 * @since 0.4.0
*/

class IT_Theme_API_Login implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 0.4.0
	*/
	private $_context = 'login';
	
	/**
	 * Current customer being viewed
	 * @var string $_customer
	 * @since 0.4.0
	*/
	private $_customer = '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 0.4.0
	*/
	public $_tag_map = array(
		'formopen'    => 'form_open',
		'username'    => 'username',
		'password'    => 'password',
		'rememberme'  => 'remember_me',
		'loginbutton' => 'login_button',
		'recover'     => 'recover',
		'register'    => 'register',
		'cancel'      => 'cancel',
		'formclose'   => 'form_close',
	);

	/**
	 * Constructor
	 *
	 * @since 0.4.0
	 * @todo get working for admins looking at other users profiles
	 * @return void
	*/
	function IT_Theme_API_Login() {
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 0.4.0
	 * 
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Outputs the login page start of form
	 *
	 * @todo why is get_session coming back as an array
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function form_open( $options=array() ) {
		$defaults = array(
			'redirect' => it_exchange_in_superwidget() ? it_exchange_clean_query_args() : it_exchange_get_page_url( 'profile' ),
			'class'    => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Grab redirect var from session
		$login_redirect = it_exchange_get_session_data( 'login_redirect' );
		if ( ! empty( $login_redirect ) ) {
			$options['redirect'] = reset( $login_redirect );
			it_exchange_clear_session_data( 'login_redirect' );
		}

		if ( it_exchange_in_superwidget() )
			$class= empty( $options['class'] ) ? 'it-exchange-sw-log-in' : 'it-exchange-sw-log-in ' . esc_attr( $class );
		else
			$class= empty( $options['class'] ) ? 'it-exchange-log-in' : 'it-exchange-log-in ' . esc_attr( $class );
		
		return '<form id="loginform" class="' . $class . '" action="' . wp_login_url( $options['redirect'] ) . '" method="post">';
	}
	
	/**
	 * Outputs the login's username data
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function username( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Username', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$field_id = 'user_login';
		$field_name = 'log';
		
		switch( $options['format'] ) {
			
			case 'field-id':
				$output = $field_id;
			
			case 'field-name':
				$output = $field_name;
			
			case 'label':
				$output = $options['label'];
			
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . $field_id. '" name="' . $field_name. '" value="" />';
			
		}
		
		return $output;
	}
	
	/**
	 * Outputs the login's password input data
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function password( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Password', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$field_id = 'user_pass';
		$field_name = 'pwd';
		
		switch( $options['format'] ) {
			
			case 'field-id':
				$output = $field_id;
			
			case 'field-name':
				$output = $field_name;
			
			case 'label':
				$output = $options['label'];
			
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . $options['label'] . '</label>';
				$output .= '<input type="password" id="' . $field_id. '" name="' . $field_name. '" value="" />';
			
		}
		
		return $output;
	}
	
	/**
	 * Outputs the login's remember me input data
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function remember_me( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Remember Me', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$field_id = 'rememberme';
		$field_name = $field_id;
		
		switch( $options['format'] ) {
			
			case 'field-id':
				$output = $field_id;
			
			case 'field-name':
				$output = $field_name;
			
			case 'label':
				$output = $options['label'];
			
			case 'html':
			default:
				$output = '<input type="checkbox" id="' . $field_id. '" name="' . $field_name. '" value="forever" />';
				$output .= '<label for="' . $field_id. '">' . $options['label'] . '</label>';
			
		}
		
		return $output;
	}
	
	/**
	 * Outputs the login page login button
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function login_button( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Log In', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$field_id = 'wp-submit';
		$field_name = $field_id;
		
		switch( $options['format'] ) {
			
			case 'field-id':
				$output = $field_id;
			
			case 'field-name':
				$output = $field_name;
			
			case 'label':
				$output = $options['label'];
			
			case 'html':
			default:
				$output = '<input type="submit" id="' . $field_id. '" name="' . $field_name. '" value="' . $options['label'] . '" />';
			
		}
		
		return $output;
	}
	
	/**
	 * Outputs the login page login button
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function recover( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Lost your password?', 'LION' ),
			'class'  => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$field_id = 'wp-submit';
		$field_name = $field_id;
		$class = empty( $options['class'] ) ? 'it-exchange-sw-lost-pass-link' : 'it-exchange-sw-lost-pass-link ' . $options['class'];
		
		switch( $options['format'] ) {
			
			case 'text':
				$output = $options['label_recover'];
				
			case 'url':
				$output = esc_attr( wp_lostpassword_url() );
			
			case 'label':
				$output = $options['label'];
			
			case 'html':
			default:
				$output = '<a class="' . esc_attr( $class ) . '" href="' . esc_attr( wp_lostpassword_url() ) . '">' . esc_attr( $options['label'] ) . '</a>';
			
		}
		
		return $output;
	}
	
	/**
	 * Outputs the registration link by default. 
	 *
	 * Can also output the registration URL
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function register( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  => __( 'Register', 'LION' ),
			'class'  => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$field_id = 'wp-submit';
		$field_name = $field_id;
		$class = empty( $options['class'] ) ? 'it-exchange-sw-register-link' : 'it-exchange-sw-register-link ' . $options['class'];
		
		switch( $options['format'] ) {
				
			case 'url':
				$output = it_exchange_get_page_url( 'registration' );
			
			case 'label':
				$output = esc_attr( $options['label'] );
			
			case 'html':
			default:
				$output = '<a class="' . esc_attr( $class ) . '" href="' . it_exchange_get_page_url( 'registration' ) . '">' . esc_attr( $options['label'] ) . '</a>';
			
		}
		
		return $output;
	}
	
	/**
	 * Outputs the login page cancel button
	 *
	 * @since 0.4.8
	 * @return string
	*/
	function cancel( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  =>  __( 'Cancel', 'LION' ),
			'class'  => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$field_id = 'it-exchange-cancel-login-customer';
		$field_name = $field_id;
		$class = empty( $options['class'] ) ? 'it-exchange-sw-cancel-login-link' : 'it-exchange-sw-cancel-login-link ' . $options['class'];
		
		if ( it_exchange_is_multi_item_cart_allowed() )
			$page = 'cart';
		else
			$page = 'product';
		
		switch( $options['format'] ) {
				
			case 'url':
				$output = it_exchange_get_page_url( $page );
			
			case 'label':
				$output = esc_attr( $options['label'] );
			
			case 'html':
			default:
				$output = '<a class="' . esc_attr( $class ) . '" href="' . it_exchange_get_page_url( $page ) . '">' .esc_attr( $options['label'] ) . '</a>';
			
		}
		return $output;
	}
	
	/**
	 * Outputs the profile page end of form
	 *
	 * @since 0.4.0
	 * @return string
	*/
	function form_close( $options=array() ) {
		return '</form>';
	}
}
