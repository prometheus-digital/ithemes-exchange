<?php
/**
 * Line Item API Theme class.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class IT_Theme_API_Line_Item
 */
class IT_Theme_API_Line_Item implements IT_Theme_API {

	/** @var ITE_Line_Item */
	protected $item;

	/**
	 * IT_Theme_API_Line_Item constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->item = isset( $GLOBALS['it_exchange']['line-item'] ) ? $GLOBALS['it_exchange']['line-item'] : null;

		if ( isset( $GLOBALS['it_exchange']['line-item-child'] ) ) {
			$this->item = $GLOBALS['it_exchange']['line-item-child'];
		}
	}

	/**
	 * @inheritdoc
	 */
	public function get_api_context() {
		return 'line-item';
	}

	/**
	 * @var array
	 */
	public $_tag_map = array(
		'name'        => 'name',
		'description' => 'description',
		'quantity'    => 'quantity',
		'amount'      => 'amount',
		'total'       => 'total',
		'type'        => 'type',
		'children'    => 'children',
	);

	/**
	 * Print the item name.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function name( array $options = array() ) {

		$defaults = array(
			'before' => '',
			'after'  => '',
			'wrap'   => 'span',
			'class'  => "it-exchange-line-item-name it-exchange-{$this->item->get_type()}-item-name"
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		if ( $options['has'] ) {
			return trim( $this->item->get_name() ) !== '';
		}

		$before = $options['before'] . "<{$options['wrap']} class=\"{$options['class']}\">";
		$name = apply_filters( 'it_theme_api_line_item_name', $this->item->get_name(), $this->item );

		return $before . $name . "</{$options['wrap']}>" . $options['after'];
	}

	/**
	 * Print the item's description.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options
	 *
	 * @return bool|string
	 */
	public function description( array $options = array() ) {

		$defaults = array(
			'before' => '',
			'after'  => '',
			'wrap'   => 'p',
			'class'  => "it-exchange-line-item-description it-exchange-{$this->item->get_type()}-item-description"
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		if ( $options['has'] ) {
			return trim( $this->item->get_description() ) !== '';
		}

		$before = $options['before'] . "<{$options['wrap']} class=\"{$options['class']}\">";

		return $before . $this->item->get_description() . "</{$options['wrap']}>" .  $options['after'];
	}

	/**
	 * Print the item's amount.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function amount( array $options = array() ) {

		$defaults = array(
			'before' => '',
			'after'  => '',
			'format' => true,
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		if ( $options['format'] ) {
			return $options['before'] . it_exchange_format_price( $this->item->get_amount() ) . $options['after'];
		} else {
			return $options['before'] . $this->item->get_amount() . $options['after'];
		}
	}


	/**
	 * Print the item's total.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function total( array $options = array() ) {

		$defaults = array(
			'before' => '',
			'after'  => '',
			'format' => true,
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		$total = $this->item->get_total();

		if ( $this->item instanceof ITE_Aggregate_Line_Item ) {
			$total_negative = $this->item->get_line_items()->filter( function ( ITE_Line_Item $item ) {
				return ! $item->is_summary_only() && $item->get_total() < 0;
			} )->total();

			$total += $total_negative * -1;
		}

		if ( $options['format'] ) {
			return $options['before'] . it_exchange_format_price( $total ) . $options['after'];
		} else {
			return $options['before'] . $total . $options['after'];
		}
	}

	/**
	 * Returns the quantity element / var based on format option
	 *
	 * @since 0.4.0
	 *
	 * @param array $options
	 *
	 * @return int
	 */
	public function quantity( $options = array() ) {
		// Set options
		$defaults = array(
			'before' => '',
			'after'  => '',
			'format' => 'text-field',
			'class'  => 'it-exchange-line-item-quantity',
			'label'  => '',
		);

		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( $options['supports'] ) {
			return $this->item instanceof ITE_Quantity_Modifiable_Item;
		}

		if ( $options['has'] ) {
			return $this->item instanceof ITE_Quantity_Modifiable_Item && $this->item->is_quantity_modifiable();
		}

		$var_key   = it_exchange_get_field_name( 'line_item_quantity' );
		$var_value = $this->item->get_quantity();

		if ( $this->item instanceof ITE_Quantity_Modifiable_Item && $this->item->is_quantity_modifiable() ) {

			switch ( $options['format'] ) {
				case 'var_key' :
					$output = $var_key;
					break;
				case 'var_value' :
					$output = $var_value;
					break;
				case 'text-field' :
				default :
					$output = $options['before'];

					$max_quantity = $this->item->get_max_quantity_available();

					$max   = $max_quantity !== '' ? "max='$max_quantity'" : '';
					$name  = esc_attr( $this->item->get_id() ) . ':' . $this->item->get_type();
					$class = esc_attr( $options['class'] );
					$output .= "<input type='number' min='1' {$max} name='{$var_key}[$name]' value='{$var_value}' class='{$class}' />";

					$output .= $options['after'];
					break;
			}
		} else {
			$output = $var_value;
		}

		return $output;
	}

	/**
	 * Print the item's type.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function type( array $options = array() ) {

		$defaults = array(
			'before' => '',
			'after'  => '',
			'label'  => true,
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		return $options['before'] . $this->item->get_type( $options['label'] ) . $options['after'];
	}

	/**
	 * Iterate over the item's children.
	 *
	 * @since 2.0.0
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public function children( array $options = array() ) {

		if ( $options['supports'] ) {
			return $this->item instanceof ITE_Aggregate_Line_Item;
		}

		if ( $options['has'] ) {
			return $this->item instanceof ITE_Aggregate_Line_Item && $this->item->get_line_items()->non_summary_only()->count() > 0;
		}

		if ( ! $this->item instanceof ITE_Aggregate_Line_Item ) {
			return false;
		}

		if ( empty( $GLOBALS['it_exchange']['line-item-child'] ) ) {

			$GLOBALS['it_exchange']['line-item-children'] = $this->item->get_line_items()->non_summary_only()->to_array();
			$GLOBALS['it_exchange']['line-item-child']    = reset( $GLOBALS['it_exchange']['line-item-children'] );

			return true;
		} elseif ( next( $GLOBALS['it_exchange']['line-item-children'] ) ) {
			$GLOBALS['it_exchange']['line-item-child'] = current( $GLOBALS['it_exchange']['line-item-children'] );

			return true;
		} else {
			$GLOBALS['it_exchange']['line-item-children'] = array();
			end( $GLOBALS['it_exchange']['line-item-children'] );
			$GLOBALS['it_exchange']['line-item-child'] = null;

			return false;
		}
	}
}
