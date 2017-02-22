<?php
/**
 * In Memory Optionally Supported Feature.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Optionally_Supported_In_Memory_Feature
 */
class ITE_Optionally_Supported_In_Memory_Feature implements ITE_Optionally_Supported_Feature {

	/**
	 * @var string
	 */
	private $slug = '';

	/**
	 * @var string
	 */
	private $label = '';

	/**
	 * @var array
	 */
	private $allowed_details = array();

	/**
	 * ITE_Optionally_Supported_In_Memory_Feature constructor.
	 *
	 * @param string $slug
	 * @param string $label
	 * @param array  $allowed_details
	 */
	public function __construct( $slug, $label, array $allowed_details ) {
		$this->slug            = $slug;
		$this->label           = $label;
		$this->allowed_details = $allowed_details;
	}

	/**
	 * @inheritDoc
	 */
	public function get_feature_slug() { return $this->slug; }

	/**
	 * @inheritDoc
	 */
	public function get_feature_label() { return $this->label; }

	/**
	 * @inheritDoc
	 */
	public function get_allowed_details() { return $this->allowed_details; }
}