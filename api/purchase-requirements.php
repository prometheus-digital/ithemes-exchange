<?php
/**
 * Purchase Requirement API functions.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Registers a purchase requirement
 *
 * @since 1.2.0
 *
 * @param string $slug
 * @param array  $properties
 *
 * @return void
 */
function it_exchange_register_purchase_requirement( $slug, $properties = array() ) {
	$defaults = array(
		'priority'               => 10,
		'requirement-met'        => '__return_true',
		// This is a callback, not a boolean.
		'sw-template-part'       => 'checkout',
		'checkout-template-part' => 'checkout',
		'notification'           => __( 'Please complete all purchase requirements before checkout out.', 'it-l10n-ithemes-exchange' ),
		// This really needs to be customized.
	);

	// Merge Defaults
	$properties = ITUtility::merge_defaults( $properties, $defaults );

	$properties['slug'] = $slug;

	// Don't allow false notification value. If you don't want a notification, make it ''.
	$properties['notification'] = (string) $properties['notification'];

	// Grab existing requirements
	$requirements = it_exchange_get_purchase_requirements();

	// Add the purchase requriement
	$requirements[ $slug ] = $properties;

	// Write updated to global
	$GLOBALS['it_exchange']['purchase-requirements'] = $requirements;
}

/**
 * Unregister a purchase requirement
 *
 * @since 1.10.6
 *
 * @param string $slug the purchase requirement slug that we want to unregister
 *
 * @return void
 */
function it_exchange_unregister_purchase_requirement( $slug ) {
	if ( isset( $GLOBALS['it_exchange']['purchase-requirements'][ $slug ] ) ) {
		unset( $GLOBALS['it_exchange']['purchase-requirements'][ $slug ] );
	}
}

/**
 * Grab all registered purchase requirements
 *
 * @since 1.2.0
 *
 * @return array
 */
function it_exchange_get_purchase_requirements() {

	if ( it_exchange_get_requested_cart_and_check_auth() ) {
		return array();
	}

	$requirements = empty( $GLOBALS['it_exchange']['purchase-requirements'] ) ? array() : (array) $GLOBALS['it_exchange']['purchase-requirements'];
	$requirements = (array) apply_filters( 'it_exchange_get_purchase_requirments', $requirements );

	// Sort the array by priority
	$priorities = array();
	foreach ( $requirements as $key => $requirement ) {
		$priorities[ $key ] = $requirement['priority'];
	}
	array_multisort( $priorities, SORT_ASC, SORT_NUMERIC, $requirements );

	return $requirements;
}

/**
 * Returns the next required purchase requirement
 *
 * @since 1.2.0
 *
 * @return array|bool requirement string
 */
function it_exchange_get_next_purchase_requirement() {

	if ( it_exchange_get_requested_cart_and_check_auth() ) {
		return false;
	}

	$requirements = it_exchange_get_purchase_requirements();

	// Loop through each purchase requirement and check their callback to see if it's requirement is met
	foreach ( (array) $requirements as $slug => $requirement ) {
		if ( is_callable( $requirement['requirement-met'] ) ) {
			$requirement_met = (boolean) call_user_func( $requirement['requirement-met'] );
		} else {
			$requirement_met = true;
		}

		// If the requirement is not met, return the purchase requirement details
		if ( ! $requirement_met ) {
			return $requirement;
		}
	}

	return false;
}

/**
 * Returns a list of all page template parts for purchase requirements
 *
 * Purchase requirements need to register a template part file to be included
 * in the purchase-requirements loop at the top of the checkout page.
 *
 * @since 1.2.0
 *
 * @return array
 */
function it_exchange_get_all_purchase_requirement_checkout_element_template_parts() {
	$template_parts = array();
	foreach ( (array) it_exchange_get_purchase_requirements() as $slug => $requirement ) {
		if ( ! empty( $requirement['checkout-template-part'] ) ) {
			;
		}
		$template_parts[] = $requirement['checkout-template-part'];
	}

	return $template_parts;
}

/**
 * Returns a specific property from the next required and unfulfilled purchase requriement
 *
 * @since 1.2.0
 *
 * @param string $name the registered property we are looking for
 *
 * @return mixed
 */
function it_exchange_get_next_purchase_requirement_property( $name ) {
	$requirement = it_exchange_get_next_purchase_requirement();
	$property    = ! isset( $requirement[ $name ] ) ? false : $requirement[ $name ];

	// Send them to checkout in the SuperWidget if a template-part wasn't
	if ( 'sw-template-part' === $name && ! $property ) {
		$property = 'checkout';
	}

	/**
	 * Filter the property for the next purchase requirement.
	 *
	 * The $requirement['slug'] variable refers to the slug of the purchase requirement. For example, 'logged-in'.
	 * The $name variable refers to the property name. For example, 'notification.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $property
	 */
	$property = apply_filters( "it_exchange_get_next_purchase_requirement_{$requirement['slug']}_{$name}", $property );

	/**
	 * Filter the property for the next purchase requirement.
	 *
	 * The $requirement['slug'] variable refers to the slug of the purchase requirement. For example, 'logged-in'.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed  $property
	 * @param string $name
	 */
	$property = apply_filters( "it_exchange_get_next_purchase_requirement_{$requirement['slug']}_property", $property, $name );

	/**
	 * Filter the property for the next purchase requirement.
	 *
	 * @since 1.2.0
	 * @since 2.0.0 Add $requirement property.
	 *
	 * @param mixed  $property
	 * @param string $name
	 * @param array  $requirement
	 */
	return apply_filters( 'it_exchange_get_next_purchase_requirement_property', $property, $name, $requirement );
}

/**
 * Returns an array of all pending purchase requiremnts
 *
 * @since 1.3.0
 *
 * @return string[]
 */
function it_exchange_get_pending_purchase_requirements() {
	$pending      = array();
	$requirements = it_exchange_get_purchase_requirements();

	foreach ( (array) $requirements as $slug => $requirement ) {
		if ( is_callable( $requirement['requirement-met'] ) ) {
			$requirement_met = (boolean) call_user_func( $requirement['requirement-met'] );
		} else {
			$requirement_met = true;
		}

		if ( ! $requirement_met ) {
			$pending[] = $requirement['slug'];
		}
	}

	return $pending;
}

/**
 * Check if a purchase requirement is registered.
 *
 * @since 2.0.0
 *
 * @param string $requirement_slug
 *
 * @return bool
 */
function it_exchange_is_purchase_requirement_registered( $requirement_slug ) {
	return isset( $GLOBALS['it_exchange']['purchase-requirements'][ $requirement_slug ] );
}