<?php

include_once( 'form-fields.php' );
include_once( 'data-functions.php' );

add_filter( 'it_cart_buddy_get_customer', 'it_cart_buddy_default_customer_management_get_customer', 9, 2);
add_filter( 'it_cart_buddy_get_current_customer', 'it_cart_buddy_default_customer_management_get_current_customer', 9 );
add_filter( 'it_cart_buddy_get_customer_profile_fields', 'it_cart_buddy_default_customer_management_get_customer_profile_fields', 9, 2 );
add_filter( 'it_cart_buddy_get_customer_registration_fields', 'it_cart_buddy_default_customer_management_get_customer_registration_fields', 9, 2 );
add_filter( 'it_cart_buddy_get_customer_login_form', 'it_cart_buddy_default_customer_management_get_customer_login_form', 9 );
