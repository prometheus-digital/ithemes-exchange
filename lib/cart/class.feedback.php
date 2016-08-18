<?php
/**
 * Cart Feedback Class.
 *
 * @since   1.36
 * @license GPLv2
 */

/**
 * Class ITE_Cart_Feedback
 */
class ITE_Cart_Feedback {

	/** @var ITE_Cart_Feedback_Item[] */
	private $notices = array();

	/** @var ITE_Cart_Feedback_Item[] */
	private $errors = array();

	/**
	 * Indicate a successful action to the user.
	 *
	 * @since 1.36.0
	 *
	 * @param string              $message
	 * @param \ITE_Line_Item|null $item
	 *
	 * @return $this
	 */
	public function add_notice( $message, ITE_Line_Item $item = null ) {

		if ( $message instanceof ITE_Cart_Feedback_Item ) {
			$this->notices[] = $message;
		} else {
			$this->notices[] = new ITE_Cart_Feedback_Item( $message, $item );
		}

		return $this;
	}

	/**
	 * Indicate a failed action to the user.
	 * 
	 * @since 1.36.0
	 * 
	 * @param string              $message
	 * @param \ITE_Line_Item|null $item
	 *
	 * @return $this
	 */
	public function add_error( $message, ITE_Line_Item $item = null ) {

		if ( $message instanceof ITE_Cart_Feedback_Item ) {
			$this->errors[] = $message;
		} else {
			$this->errors[] = new ITE_Cart_Feedback_Item( $message, $item );
		}

		return $this;
	}

	/**
	 * Get an iterator for all the errors.
	 * 
	 * @since 1.36.0
	 * 
	 * @return \Iterator
	 */
	public function errors() {
		return new ArrayIterator( $this->errors );
	}

	/**
	 * Get an iterator for all the notices.
	 * 
	 * @since 1.36.0
	 * 
	 * @return \Iterator
	 */
	public function notices() {
		return new ArrayIterator( $this->notices );
	}

	/**
	 * Clear all feedback items.
	 *
	 * @since 1.36.0
	 */
	public function clear() {
		$this->clear_errors();
		$this->clear_notices();
	}

	/**
	 * Clear only error items.
	 *
	 * @since 1.36.0
	 */
	public function clear_errors() {
		$this->errors = array();
	}

	/**
	 * Clear only notices.
	 *
	 * @since 1.36.0
	 */
	public function clear_notices() {
		$this->notices = array();
	}
}
