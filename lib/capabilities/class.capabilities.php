<?php
/**
 * Contains the capabilities class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Capabilities
 */
class IT_Exchange_Capabilities {

	const PRODUCT = 'it_product';
	const TRANSACTION = 'it_transaction';

	/**
	 * Get capabilities for the product post type.
	 *
	 * @since 1.36
	 *
	 * @return array
	 */
	public function get_caps_for_product() {
		return $this->get_post_type_caps_for( self::PRODUCT );
	}

	/**
	 * Get capabilities for the transaction post type.
	 * 
	 * @since 1.36
	 * 
	 * @return array
	 */
	public function get_caps_for_transaction() {
		return $this->get_post_type_caps_for( self::TRANSACTION );
	}

	/**
	 * Get post type capabilities for a given post type.
	 *
	 * @since 1.36
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_post_type_caps_for( $type ) {
		return array(
			"edit_{$type}",
			"read_{$type}",
			"delete_{$type}",
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