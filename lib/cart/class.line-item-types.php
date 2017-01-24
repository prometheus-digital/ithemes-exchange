<?php
/**
 * Line Item Types registrar.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Line_Item_Types
 */
class ITE_Line_Item_Types {

	/** @var ITE_Line_Item_Type[] */
	private static $types;

	/**
	 * Register a line item type.
	 *
	 * @since 2.0.0
	 *
	 * @param \ITE_Line_Item_Type $type
	 */
	public static function register_type( ITE_Line_Item_Type $type ) {
		static::$types[ $type->get_type() ] = $type;
	}

	/**
	 * Get a line item type.
	 *
	 * @since 2.0.0
	 *
	 * @param string $type
	 *
	 * @return \ITE_Line_Item_Type|null
	 */
	public static function get( $type ) {
		return isset( static::$types[ $type ] ) ? static::$types[ $type ] : null;
	}

	/**
	 * Get all line item types.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Type[]
	 */
	public static function all() {
		return array_values( static::$types );
	}

	/**
	 * Get all line item types that are aggregates.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Type[]
	 */
	public static function aggregates() {

		$types = array();

		foreach ( static::$types as $type ) {
			if ( $type->is_aggregate() ) {
				$types[] = $type;
			}
		}

		return $types;
	}

	/**
	 * Get all line item types that are aggregatables.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Type[]
	 */
	public static function aggregatables() {

		$types = array();

		foreach ( static::$types as $type ) {
			if ( $type->is_aggregatable() ) {
				$types[] = $type;
			}
		}

		return $types;
	}

	/**
	 * Retrieve all line items that should be shown in REST.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Type[]
	 */
	public static function shows_in_rest() {

		$types = array();

		foreach ( static::$types as $type ) {
			if ( $type->is_show_in_rest() ) {
				$types[] = $type;
			}
		}

		return $types;
	}

	/**
	 * Retrieve all line items that should not be shown in REST.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Type[]
	 */
	public static function not_shows_in_rest() {

		$types = array();

		foreach ( static::$types as $type ) {
			if ( ! $type->is_show_in_rest() ) {
				$types[] = $type;
			}
		}

		return $types;
	}

	/**
	 * Retrieve all line items that should be editable via REST.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item_Type[]
	 */
	public static function editable_in_rest() {

		$types = array();

		foreach ( static::$types as $type ) {
			if ( $type->is_editable_in_rest() ) {
				$types[] = $type;
			}
		}

		return $types;
	}
}