<?php
/**
 * This file contains the core-addon product feature for scheduling sales.
 *
 * @since   1.32.0
 * @package IT_Exchange
 */

/**
 * Class IT_Exchange_Sale_Schedule
 *
 * @since 1.32.0
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

		parent::__construct( $args );

		add_filter( 'it_exchange_is_product_sale_active', array( $this, 'enforce_schedule' ), 10, 2 );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.32.0
	 *
	 * @param \WP_Post $post
	 */
	function print_metabox( $post ) {

		$df        = get_option( 'date_format' );
		$jquery_df = it_exchange_php_date_format_to_jquery_datepicker_format( $df );

		$data = it_exchange_get_product_feature( $post->ID, $this->slug );

		$start_enabled = $data['enable_start'];
		$end_enabled   = $data['enable_end'];

		if ( $data['start'] ) {
			$start = date( get_option( 'date_format' ), $data['start'] );
		} else {
			$start = '';
		}

		if ( $data['end'] ) {
			$end = date( get_option( 'date_format' ), $data['end'] );
		} else {
			$end = '';
		}
		?>

		<p><?php echo $this->description; ?></p>

		<div class="it-exchange-sale-schedule-settings">
			<p>
				<input type="checkbox" id="it-exchange-enable-sale-schedule-start" class="it-exchange-checkbox-enable"
				       name="it-exchange-sale-schedule[enable_start]" value="yes" <?php checked( $start_enabled ); ?>>
				<label for="it-exchange-enable-sale-schedule-start">
					<?php _e( 'Use a start date', 'it-l10n-ithemes-exchange' ); ?>
				</label>
				&nbsp;
				<input type="checkbox" id="it-exchange-enable-sale-schedule-end" class="it-exchange-checkbox-enable"
				       name="it-exchange-sale-schedule[enable_end]" value="yes" <?php checked( $end_enabled ); ?> />
				<label for="it-exchange-enable-sale-schedule-end">
					<?php _e( 'Use an end date', 'it-l10n-ithemes-exchange' ); ?>
				</label>
			</p>

			<p id="it-exchange-sale-schedule-start-container" class="<?php echo $start_enabled ? '' : ' hide-if-js' ?>">
				<label for="it-exchange-sale-schedule-start"><?php _e( 'Start Date', 'it-l10n-ithemes-exchange' ); ?></label>
				<input type="text" class="datepicker" id="it-exchange-sale-schedule-start" name="it-exchange-sale-schedule[start]" value="<?php esc_attr_e( $start ); ?>" />
			</p>

			<p id="it-exchange-sale-schedule-end-container" class="<?php echo $end_enabled ? '' : ' hide-if-js' ?>">
				<label for="it-exchange-sale-schedule-end"><?php _e( 'End Date', 'it-l10n-ithemes-exchange' ); ?></label>
				<input type="text" class="datepicker" id="it-exchange-sale-schedule-end" name="it-exchange-sale-schedule[end]" value="<?php esc_attr_e( $end ); ?>" />
			</p>

			<input type="hidden" name="it-exchange-sale-schedule-df">
		</div>
		<?php
	}

	/**
	 * This saves the value.
	 *
	 * @since 1.32.0
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
			'start' => isset( $data['start'] ) ? $data['start'] : '',
			'end'   => isset( $data['end'] ) ? $data['end'] : ''
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

		it_exchange_update_product_feature( $product_id, $this->slug, $data );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 1.32.0
	 *
	 * @param integer $product_id the product id
	 * @param array   $new_value  the new value
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function save_feature( $product_id, $new_value, $options = array() ) {

		if ( isset( $new_value['enable_start'] ) && it_exchange_str_true( $new_value['enable_start'] ) ) {
			$new_value['enable_start'] = true;
		} else {
			$new_value['enable_start'] = false;
		}

		if ( isset( $new_value['enable_end'] ) && it_exchange_str_true( $new_value['enable_end'] ) ) {
			$new_value['enable_end'] = true;
		} else {
			$new_value['enable_end'] = false;
		}

		$new_value['start'] = isset( $new_value['start'] ) ? absint( $new_value['start'] ) : '';
		$new_value['end']   = isset( $new_value['end'] ) ? absint( $new_value['end'] ) : '';

		if ( ! empty( $new_value['start'] ) && ! empty( $new_value['end'] ) && $new_value['start'] >= $new_value['end'] ) {
			return false;
		}

		update_post_meta( $product_id, '_it_exchange_sale_schedule_start', $new_value['start'] );
		update_post_meta( $product_id, '_it_exchange_sale_schedule_end', $new_value['end'] );

		unset( $new_value['start'] );
		unset( $new_value['end'] );

		return update_post_meta( $product_id, '_it_exchange_sale_schedule', $new_value );
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.32.0
	 *
	 * @param mixed   $existing   the values passed in by the WP Filter API. Ignored here.
	 * @param integer $product_id the WordPress post ID
	 * @param array   $options
	 *
	 * @return string product feature
	 */
	public function get_feature( $existing, $product_id, $options = array() ) {

		$start = get_post_meta( $product_id, '_it_exchange_sale_schedule_start', true );
		$end   = get_post_meta( $product_id, '_it_exchange_sale_schedule_end', true );

		$raw_meta = ITUtility::merge_defaults( get_post_meta( $product_id, '_it_exchange_sale_schedule', true ), array(
			'enable_start' => false,
			'enable_end'   => false,
			'start'        => '',
			'end'          => ''
		) );

		$raw_meta['start'] = $start;
		$raw_meta['end']   = $end;

		if ( ! isset( $options['setting'] ) ) { // if we aren't looking for a particular field
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
	 * @since 1.32.0
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
	 * @since 1.32.0
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

	/**
	 * Enforce the sale schedule.
	 *
	 * @param bool                $enabled
	 * @param IT_Exchange_Product $product
	 *
	 * @return bool
	 */
	public function enforce_schedule( $enabled, $product ) {

		// we don't want to re-enable the sale if another add-on has already disabled it
		if ( $enabled ) {

			if ( it_exchange_product_has_feature( $product->ID, $this->slug ) ) {

				$start = it_exchange_get_product_feature( $product->ID, $this->slug, array( 'setting' => 'start' ) );
				$end   = it_exchange_get_product_feature( $product->ID, $this->slug, array( 'setting' => 'end' ) );

				$start_enabled = it_exchange_get_product_feature( $product->ID, $this->slug, array( 'setting' => 'enable_start' ) );
				$end_enabled   = it_exchange_get_product_feature( $product->ID, $this->slug, array( 'setting' => 'enable_end' ) );

				$past_start_date = true;
				$before_end_date = true;
				$now_start       = strtotime( date( 'Y-m-d 00:00:00' ) );
				$now_end         = strtotime( date( 'Y-m-d 23:59:59' ) );

				// Check start time
				if ( $start && $start_enabled ) {
					$start_date = strtotime( date( 'Y-m-d', $start ) . ' 00:00:00' );
					if ( $now_start < $start_date ) {
						$past_start_date = false;
					}
				}

				// Check end time
				if ( $end && $end_enabled ) {
					$end_date = strtotime( date( 'Y-m-d', $end ) . ' 23:59:59' );
					if ( $now_end > $end_date ) {
						$before_end_date = false;
					}
				}

				$enabled = $past_start_date && $before_end_date;
			}
		}

		return $enabled;
	}
}

new IT_Exchange_Sale_Schedule();