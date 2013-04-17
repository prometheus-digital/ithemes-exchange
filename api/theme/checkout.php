<?php
/**
 * This file contains functions useful for building a checkout page
 * @since 0.3.7
 * @package IT_Exchange
*/

/**
 * This will print the chekcout page HTML
 *
 * Heavy lifing it done by the active shopping cart add-on
 * This is a highlevel function that may also be called by a shortcode.
 *
 * @since 0.3.7
 * @param array $shortcode_atts atts passed from WP Shortcode API if function is being invoked by it.
 * @param string $shortcode_content content passed from WP Shortcode API if function is being invoked by it.
 * @return string html for the shopping cart
*/
function it_exchange_get_checkout_html() {
	$html  = it_exchange_get_errors_div();
	$html .= it_exchange_get_alerts_div();

	ob_start();
	it_exchange_get_template_part( 'checkout' );
	$html .= ob_get_clean();
	return $html;
}

/**
 * Returns the form fields requesting customer information at checkout
 *
 * @todo make this a tempalte
 *
 * @since 0.3.7
 * @param array $args optional. not used by all add-ons
 * @return string HTML
*/
function it_exchange_get_cart_checkout_customer_form_fields( $args=array() ) {
    $form = new ITForm();
    $fields = it_exchange_get_customer_profile_fields();
    $html = ''; 
    $customer = it_exchange_get_current_customer();

    foreach( (array) $fields as $field => $args ) { 
        $function = 'get_' . $args['type'];
        $var      = empty( $args['var'] ) ? '' : $args['var'];
        $values   = empty( $args['values'] ) ? array() : (array) $args['values'];
        $label    = empty( $args['label'] ) ? '' : $args['label'];
        $value    = empty( $customer->data->$var ) ? '' : esc_attr( $customer->data->$var );
        $values['value'] = stripslashes( $value );
        if ( is_callable( array( $form, $function ) ) ) { 
            $html .= '<p class="it-exchange-profile-field">';
            $html .= '<label for="' . esc_attr( $var ) . '">' . $label . '</label>';
            $html .= $form->$function( $var, $values );
            $html .= '</p>';
        } else if ( 'password' == $args['type'] ) { 
            $values['type'] = 'password';
            $html .= '<p class="it-exchange-profile-field">';
            $html .= '<label for="' . esc_attr( $var ) . '">' . $label . '</label>';
            $html .= $form->_get_simple_input( $var, $values, false );
            $html .= '</p>';
        }   
    }

	return $html;
}
