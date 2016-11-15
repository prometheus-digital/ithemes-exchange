<?php
/**
 * Cart Feedback Item class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Cart_Feedback_Item
 */
class ITE_Cart_Feedback_Item {

	/** @var string */
	private $message;

	/** @var ITE_Line_Item|null */
	private $item;

	/**
	 * ITE_Cart_Feedback_Item constructor.
	 *
	 * @param string              $message
	 * @param \ITE_Line_Item|null $item
	 */
	public function __construct( $message, ITE_Line_Item $item = null ) {
		$this->message = $message;
		$this->item    = $item;
	}

	/**
	 * @inheritdoc
	 */
	public function __toString() {
		return $this->message;
	}

	/**
	 * Get the line item.
	 *
	 * @since 2.0.0
	 *
	 * @return \ITE_Line_Item|null
	 */
	public function get_item() {
		return $this->item;
	}
}
