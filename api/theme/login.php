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
		'recoverurl'  => 'recover_url',
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
	 * @since 0.4.0
	 * @return string
	*/
	function form_open( $options=array() ) {
		$defaults      = array(
			'redirect'      => it_exchange_get_page_url( 'profile' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		return '<form id="loginform" action="' . wp_login_url( $options['redirect'] ) . '" method="post">';
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
	function recover_url( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Lost your password?', 'LION' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$field_id = 'wp-submit';
		$field_name = $field_id;
		
		switch( $options['format'] ) {
			
			case 'text':
				$output = $options['label_recover'];
				
			case 'url':
				$output = wp_lostpassword_url();
			
			case 'label':
				$output = $options['label'];
			
			case 'html':
			default:
				$output = '<a href="' . wp_lostpassword_url() . '">' . $options['label'] . '</a>';
			
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
