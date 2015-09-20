<?php
/**
 * This file contains the core-addon product feature for scheduling sales.
 *
 * @since   1.24.0
 * @package IT_Exchange
 */

/**
 * Class IT_Exchange_Sale_Schedule
 *
 * @since 1.24.0
 */
class IT_Exchange_Sale_Schedule extends IT_Exchange_Product_Feature_Abstract {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$args = array(
			'slug'          => 'sale-schedule',
			'description'   => __( "Offer the sale price for a limited amount of time.", 'it-l10n-ithemes-exchange' ),
			'metabox_title' => __( "Sale Schedule", 'it-l10n-ithemes-exchange' )
		);

		parent::IT_Exchange_Product_Feature_Abstract( $args );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.0
	 *
	 * @param \WP_Post $post
	 */
	function print_metabox( $post ) {

		$data = it_exchange_get_product_feature( isset( $post->ID ) ? $post->ID : 0, $this->slug );

		$start_enabled = $data['start-enabled'];
		$end_enabled   = $data['end-enabled'];
		$start         = $data['start'];
		$end           = $data['end'];
		?>

		<p><?php echo $this->description; ?></p>

		<div class="it-exchange-sale-schedule-settings">
			<p>
				<input type="checkbox" id="it-exchange-enable-sale-schedule-start" class="it-exchange-checkbox-enable"
				       name="it-exchange-sale-schedule[enable_start]" value="yes" <?php checked( true, $start_enabled ); ?>>
				<label for="it-exchange-enable-sale-schedule-start">
					<?php _e( 'Use a start date', 'it-l10n-ithemes-exchange' ); ?>
				</label>
				&nbsp;
				<input type="checkbox" id="it-exchange-enable-sale-schedule-end" class="it-exchange-checkbox-enable"
				       name="it-exchange-sale-schedule[enable_end]" value="yes" <?php checked( true, $end_enabled ); ?> />
				<label for="it-exchange-enable-sale-schedule-end">
					<?php _e( 'Use an end date', 'it-l10n-ithemes-exchange' ); ?>
				</label>
			</p>

			<p id="it-exchange-sale-schedule-start<?php echo ( ! $start_enabled ) ? ' hide-if-js' : '' ?>">
				<label for="it-exchange-sale-schedule-start"><?php _e( 'Start Date', 'it-l10n-ithemes-exchange' ); ?></label>
				<input type="text" class="datepicker" id="it-exchange-sale-schedule-start" name="it-exchange-sale-schedule[start]" value="<?php esc_attr_e( $start ); ?>" />
			</p>

			<p id="it-exchange-sale-schedule-end<?php echo ( ! $end_enabled ) ? ' hide-if-js' : '' ?>">
				<label for="it-exchange-sale-schedule-end"><?php _e( 'End Date', 'it-l10n-ithemes-exchange' ); ?></label>
				<input type="text" class="datepicker" id="it-exchange-sale-schedule-end" name="it-exchange-sale-schedule[end]" value="<?php esc_attr_e( $end ); ?>" />
			</p>
		</div>
		<?php
	}

	/**
	 * This saves the value.
	 *
	 * @since 1.0
	 */
	public function save_feature_on_product_save() {

		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() ) {
			return;
		}

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id ) {
			return;
		}

		// Abort if this product type doesn't support base-price
		if ( ! it_exchange_product_type_supports_feature( $product_type, $this->slug ) ) {
			return;
		}

		$data = $_POST['it-exchange-sale-schedule'];

		$dates = array(
			'start' => $data['start'],
			'end'   => $data['end']
		);

		foreach ( $dates as $key => $val ) {

			// Get the user's option set in WP General Settings
			$wp_date_format = get_option( 'date_format', 'm/d/Y' );

			// strtotime requires formats starting with day to be separated by - and month separated by /
			if ( 'd' == substr( $wp_date_format, 0, 1 ) ) {
				$val = str_replace( '/', '-', $val );
			}

			// Transfer to epoch
			if ( $epoch = strtotime( $val ) ) {

				// Returns an array with values of each date segment
				$date = date_parse( $val );

				// Confirms we have a legitimate date
				if ( checkdate( $date['month'], $date['day'], $date['year'] ) ) {
					$data[ $key ] = $epoch;
				}
			}
		}

		it_exchange_update_product_feature( $product_id, $this->slug, $_POST['it-exchange-sale-schedule'] );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 1.0
	 *
	 * @param integer $product_id the product id
	 * @param array   $new_value  the new value
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function save_feature( $product_id, $new_value, $options = array() ) {

		$defaults  = it_exchange_get_product_feature( $product_id, $this->slug );
		$new_value = ITUtility::merge_defaults( $new_value, $defaults );

		$new_value['enable_start'] = (bool) $new_value['enable_start'];
		$new_value['enable_end']   = (bool) $new_value['enable_end'];
		$new_value['start']        = absint( $new_value['start'] );
		$new_value['end']          = absint( $new_value['end'] );

		if ( ! empty( $new_value['start'] ) && ! empty( $new_value['end'] ) && $new_value['start'] >= $new_value['end'] ) {
			return false;
		}

		return update_post_meta( $product_id, '_it_exchange_sale_schedule', $new_value );
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.0
	 *
	 * @param mixed   $existing   the values passed in by the WP Filter API. Ignored here.
	 * @param integer $product_id the WordPress post ID
	 * @param array   $options
	 *
	 * @return string product feature
	 */
	public function get_feature( $existing, $product_id, $options = array() ) {

		$raw_meta = ITUtility::merge_defaults( get_post_meta( $product_id, '_it_exchange_sale_schedule', true ), array(
			'enable_start' => false,
			'enable_end'   => false,
			'start'        => '',
			'end'          => ''
		) );


		if ( ! isset( $options['field'] ) ) { // if we aren't looking for a particular field
			return $raw_meta;
		}

		$field = $options['setting'];

		if ( isset( $raw_meta[ $field ] ) ) { // if the field exists with that name just return it
			return $raw_meta[ $field ];
		} else if ( strpos( $field, "." ) !== false ) { // if the field name was passed using array dot notation
			$pieces  = explode( '.', $field );
			$context = $raw_meta;
			foreach ( $pieces as $piece ) {
				if ( ! is_array( $context ) || ! array_key_exists( $piece, $context ) ) {
					// error occurred
					return null;
				}
				$context = &$context[ $piece ];
			}

			return $context;
		} else {
			return null; // we didn't find the data specified
		}
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.0
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function product_has_feature( $result, $product_id, $options = array() ) {

		$start = it_exchange_get_product_feature( $product_id, $this->slug, array( 'setting' => 'enable_start' ) );
		$end   = it_exchange_get_product_feature( $product_id, $this->slug, array( 'setting' => 'enable_end' ) );

		return $start || $end;
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 * @since 1.0
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function product_supports_feature( $result, $product_id, $options = array() ) {
		$product_type = it_exchange_get_product_type( $product_id );

		return it_exchange_product_type_supports_feature( $product_type, $this->slug );
	}
}

new IT_Exchange_Sale_Schedule();