<?php
/**
 * Line Item Coercion Failed Exception.
 *
 * @since   1.36.0
 * @license GPLv2
 */

/**
 * Class ITE_Line_Item_Coercion_Failed_Exception
 */
class ITE_Line_Item_Coercion_Failed_Exception extends Exception {

	/**
	 * @var \ITE_Line_Item
	 */
	private $line_item;

	/**
	 * @var \ITE_Line_Item_Validator
	 */
	private $validator;

	/**
	 * ITE_Line_Item_Coercion_Failed_Exception constructor.
	 *
	 * @param string                   $message
	 * @param \ITE_Line_Item           $line_item
	 * @param \ITE_Line_Item_Validator $validator
	 * @param int                      $code
	 * @param \Exception|null          $previous
	 */
	public function __construct( $message, ITE_Line_Item $line_item, ITE_Line_Item_Validator $validator, $code = 0, Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );

		$this->line_item = $line_item;
		$this->validator = $validator;
	}

	/**
	 * Get the Line_Item validator.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Line_Item_Validator
	 */
	public function get_validator() {
		return $this->validator;
	}

	/**
	 * Get the Line_Item that failed coercion.
	 *
	 * @since 1.36.0
	 *
	 * @return \ITE_Line_Item
	 */
	public function get_line_item() {
		return $this->line_item;
	}
}