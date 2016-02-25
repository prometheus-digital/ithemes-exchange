<?php
/**
 * Load the email notifications component.
 *
 * @since   1.36
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/class.email-notifications.php';
require_once dirname( __FILE__ ) . '/class.customizer.php';

new IT_Exchange_Email_Customizer();