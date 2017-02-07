<?php
/**
 * Line Item Type class.
 *
 * @since   2.0.0
 * @license GPLv2
 */
use iThemes\Exchange\REST\Route\v1\Cart\Item_Serializer;

/**
 * Class ITE_Line_Item_Type
 */
class ITE_Line_Item_Type {

	/** @var string */
	private $type;

	/** @var string */
	private $label;

	/** @var bool */
	private $show_in_rest = false;

	/** @var bool */
	private $editable_in_rest = false;

	/** @var \iThemes\Exchange\REST\Route\v1\Cart\Item_Serializer */
	private $rest_serializer;

	/** @var callable */
	private $create_from_request;

	/** @var array */
	private $additional_schema_props = array();

	/** @var bool */
	private $aggregate = false;

	/** @var bool */
	private $aggregatable = false;

	/**
	 * ITE_Line_Item_Type constructor.
	 *
	 * @param string $type
	 * @param array  $args
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $type, array $args ) {

		$this->type  = $type;
		$this->label = ucfirst( $type );

		if ( isset( $args['aggregate'] ) ) {
			$this->aggregate = (bool) $args['aggregate'];
		}

		if ( isset( $args['aggregatable'] ) ) {
			$this->aggregatable = (bool) $args['aggregatable'];
		}

		if ( isset( $args['show_in_rest'] ) ) {
			$this->show_in_rest = (bool) $args['show_in_rest'];
		}

		if ( isset( $args['editable_in_rest'] ) ) {
			$this->editable_in_rest = (bool) $args['editable_in_rest'];
		}

		if ( ! empty( $args['schema'] ) ) {
			$this->additional_schema_props = $args['schema'];
		}

		if ( isset( $args['rest_serializer'] ) ) {

			if ( $args['rest_serializer'] instanceof Item_Serializer ) {
				$this->rest_serializer = $args['rest_serializer'];
			} elseif ( $args['rest_serializer'] instanceof Closure ) {
				$this->rest_serializer = new Item_Serializer( $this );
				$this->rest_serializer->extend( $args['rest_serializer'] );
			} else {
				throw new InvalidArgumentException( sprintf(
					'Invalid data type for rest_serializer. Expected Item_Serializer, received %s.',
					is_object( $args['rest_serializer'] ) ? get_class( $args['rest_serializer'] ) : gettype( $args['rest_serializer'] )
				) );
			}
		} else {
			$this->rest_serializer = new Item_Serializer( $this );
		}

		if ( ! empty( $args['create_from_request'] ) ) {
			$this->create_from_request = $args['create_from_request'];
		}
	}

	/**
	 * Is this an aggregate line item type.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_aggregate() {
		return $this->aggregate;
	}

	/**
	 * Is this an aggregatable line item type.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_aggregatable() {
		return $this->aggregatable;
	}

	/**
	 * Should line items of this type be shown in the REST API.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_show_in_rest() {
		return $this->show_in_rest;
	}

	/**
	 * Is this line item type editable in REST.
	 *
	 * @since 2.0.0
	 *
	 * @return boolean
	 */
	public function is_editable_in_rest() {
		return $this->editable_in_rest;
	}

	/**
	 * Get the line item type.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get the item type label.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Get the rest serializer.
	 *
	 * @since 2.0.0
	 *
	 * @return \iThemes\Exchange\REST\Route\v1\Cart\Item_Serializer
	 */
	public function get_rest_serializer() {
		return $this->rest_serializer;
	}

	/**
	 * Get additional schema properties.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_additional_schema_props() {
		return $this->additional_schema_props;
	}

	/**
	 * Create a line item from a REST Request object.
	 *
	 * @since 2.0.0
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 *
	 * @return ITE_Line_Item|null
	 */
	public function create_from_request( \iThemes\Exchange\REST\Request $request ) {
		if ( $this->create_from_request ) {
			return call_user_func( $this->create_from_request, $request );
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->get_type();
	}
}
