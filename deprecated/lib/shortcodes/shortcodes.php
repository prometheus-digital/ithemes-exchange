<?php
/**
 * Contains shortcodes.
 *
 * @author    iThemes
 * @since     1.32
 */

/**
 * Class IT_Exchange_Shortcodes
 */
class IT_Exchange_SW_Shortcode {

	/**
	 * @var \IT_Exchange_Product
	 */
	private $product;

	/**
	 * @var array
	 */
	private $hide_parts = array();

	/**
	 * @var array
	 */
	private $add_parts = array();

	/**
	 * IT_Exchange_Shortcodes constructor.
	 */
	public function __construct() {
		add_shortcode( 'it_exchange_sw', array( $this, 'callback' ) );
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'register_feature' ) );
		add_action( 'media_buttons', array( $this, 'insert_button' ) );
		add_action( 'admin_footer', array( $this, 'thickbox' ) );
	}

	/**
	 * Check if this page has the shortcode in it.
	 *
	 * @since 1.32
	 *
	 * @return bool
	 */
	public static function has_shortcode() {

		global $post;

		return ( $post && has_shortcode( $post->post_content, 'it_exchange_sw' ) );
	}

	/**
	 * Register the feature with Exchange.
	 *
	 * @sine 1.32
	 */
	public function register_feature() {

		$desc = __( "Allows products to be embedded in a shortcode.", 'it-l10n-ithemes-exchange' );

		it_exchange_register_product_feature( 'sw-shortcode', $desc );
	}

	/**
	 * Insert the embed a SW button.
	 *
	 * This is embedded only for public post types, and NOT for products.
	 *
	 * @since 1.32
	 */
	public function insert_button() {

		// media buttons can be displayed on the front-end.
		// bail if we aren't in the admin
		if ( ! is_admin() ) {
			return;
		}

		$screen = get_current_screen();

		$post_type        = ! empty( $screen->post_type ) ? $screen->post_type : get_post_type();
		$post_type_object = get_post_type_object( $post_type );

		if ( $post_type_object && $post_type_object->public && $post_type != 'it_exchange_prod' ) {
			add_thickbox();
			$id    = 'it-exchange-insert-sw-shortcode';
			$class = 'thickbox button it-exchange-insert-sw-shortcode';
			$title = __( "Embed Super Widget", 'it-l10n-ithemes-exchange' );
			echo '<a href="#TB_inline?width=150height=250&inlineId=' . $id . '" class="' . $class . '" title="' . $title . '"> ' . $title . '</a>';
		}
	}

	/**
	 * Render the thickbox for inserting a SW shortcode.
	 *
	 * @since 1.32
	 */
	public function thickbox() {

		$screen = get_current_screen();

		$post_type = ! empty( $screen->post_type ) ? $screen->post_type : get_post_type();
		$post_type = get_post_type_object( $post_type );

		if ( ! $post_type || ! $post_type->public || $post_type == 'it_exchange_prod' ) {
			return;
		}

		$product_types = it_exchange_get_addons( array(
			'category' => 'product-type'
		) );

		foreach ( $product_types as $product_type => $addon ) {
			if ( ! it_exchange_product_type_supports_feature( $product_type, 'sw-shortcode' ) ) {
				unset( $product_types[ $product_type ] );
			}
		}

		$products = it_exchange_get_products( array(
			'show_hidden'    => true,
			'posts_per_page' => - 1,
			'meta_query'     => array(
				array(
					'key'     => '_it_exchange_product_type',
					'compare' => 'IN',
					'value'   => array_keys( $product_types )
				)
			)
		) );
		?>

		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$(document).on('click', '#it-exchange-sw-insert', function (e) {
					var prod = jQuery("#it-exchange-sw-product").val();
					if (prod.length == 0 || prod == -1) {
						alert("<?php echo esc_js( __( "You must select a product", 'it-l10n-ithemes-exchange' ) ); ?>");
						return;
					}

					var desc = '', title = '';

					if (jQuery("#it-exchange-sw-description").is(':checked')) {
						desc = ' description="yes"';
					}

					if (jQuery("#it-exchange-sw-title").is(':checked')) {
						title = ' title="yes"';
					}

					var short = '[it_exchange_sw product="' + prod + '"' + desc + title + ']';

					window.send_to_editor(short);
					tb_remove();
				});
			});
		</script>

		<div id="it-exchange-insert-sw-shortcode" style="display: none">
			<div class="wrap">

				<div>
					<label for="it-exchange-sw-product"><?php _e( 'Select a Product', 'it-l10n-ithemes-exchange' ); ?></label><br>
					<select id="it-exchange-sw-product">
						<option value="-1"><?php _e( 'Select', 'it-l10n-ithemes-exchange' ); ?></option>

						<?php foreach ( $products as $product ): ?>
							<option value="<?php echo $product->ID; ?>">
								<?php echo $product->post_title; ?>
							</option>
						<?php endforeach; ?>
					</select>

					<br><br>

					<input type="checkbox" id="it-exchange-sw-title">
					<label for="it-exchange-sw-title">
						<?php _e( "Include product title?", 'it-l10n-ithemes-exchange' ); ?>
					</label>

					<br><br>

					<input type="checkbox" id="it-exchange-sw-description">
					<label for="it-exchange-sw-description">
						<?php _e( "Include product description?", 'it-l10n-ithemes-exchange' ); ?>
					</label>
				</div>

				<div style="padding: 15px 15px 15px 0">
					<input type="button" class="button-primary" id="it-exchange-sw-insert" value="<?php _e( "Embed", 'it-l10n-ithemes-exchange' ); ?>" />
					&nbsp;&nbsp;&nbsp;
					<a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;">
						<?php _e( "Cancel", 'it-l10n-ithemes-exchange' ); ?>
					</a>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 *
	 * Super widget shortcode callback.
	 *
	 * @since 1.32
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function callback( $atts ) {

		if ( $this->product ) {
			if ( current_user_can( 'edit_post', $GLOBALS['post']->ID ) ) {
				return __( "Only one Super Widget can be embedded per-page.", 'it-l10n-ithemes-exchange' );
			}

			return '';
		}

		$atts = shortcode_atts( array( 'product' => null, 'description' => 'no', 'title' => 'no' ), $atts, 'it_exchange_sw' );

		$product = it_exchange_get_product( $atts['product'] );

		if ( ! $product ) {
			if ( current_user_can( 'edit_post', $GLOBALS['post']->ID ) ) {
				return __( "Invalid product ID.", 'it-l10n-ithemes-exchange' );
			}

			return '';
		} else if ( ! it_exchange_product_type_supports_feature( it_exchange_get_product_type( $product->ID ), 'sw-shortcode' ) ) {

			if ( current_user_can( 'edit_post', $GLOBALS['post']->ID ) ) {
				return __( "This product does not support being embedded in shortcodes.", 'it-l10n-ithemes-exchange' );
			}

			return '';
		}

		if ( is_archive() || is_home() ) {
			return '';
		}

		it_exchange_set_product( $product->ID );
		$this->product = $product;

		if ( $atts['description'] !== 'yes' ) {
			$this->hide_parts[] = 'description';
		}

		if ( $atts['title'] === 'yes' ) {
			$this->add_parts[] = 'title';
		}

		add_filter( 'it_exchange_super_widget_empty_product_id', array( $this, 'set_sw_product_id' ) );
		add_filter( 'it_exchange_get_content_product_product_info_loop_elements', array( $this, 'modify_templates' ) );
		add_filter( 'it_exchange_super_widget_args', array( $this, 'prevent_hide_css' ) );
		add_filter('it_exchange_api_theme_product_title_options', array( $this, 'modify_product_title_tag' ) );

		ob_start();

		it_exchange_get_template_part( 'content-product/loops/product-info' );

		$html = ob_get_clean();

		remove_filter( 'it_exchange_super_widget_empty_product_id', array( $this, 'set_sw_product_id' ) );
		remove_filter( 'it_exchange_get_content_product_product_info_loop_elements', array( $this, 'modify_templates' ) );
		remove_filter('it_exchange_api_theme_product_title_options', array( $this, 'modify_product_title_tag' ) );

		return $html;
	}

	/**
	 * Set the product ID for use in the SW when no product found.
	 *
	 * @since 1.32
	 *
	 * @param int $product
	 *
	 * @return int|bool
	 */
	public function set_sw_product_id( $product ) {

		if ( $this->product ) {
			return $this->product->ID;
		}

		return $product;
	}

	/**
	 * Modify template parts.
	 *
	 * @since 1.32
	 *
	 * @param array $parts
	 *
	 * @return array
	 */
	public function modify_templates( $parts ) {

		if ( $this->add_parts ) {
			$parts = array_merge( $this->add_parts, $parts );
		}

		foreach ( $this->hide_parts as $part ) {

			$index = array_search( $part, $parts );

			if ( $index !== false ) {
				unset( $parts[ $index ] );
			}
		}

		return $parts;
	}

	/**
	 * Prevent the SW from enqueuing the css to hide itself when the sidebar is being used.
	 *
	 * @since 1.32
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function prevent_hide_css( $args ) {
		$args['enqueue_hide_script'] = false;

		return $args;
	}

	/**
	 * Modify the product title tag.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function modify_product_title_tag( $options ) {
		$options['wrap'] = 'h2';

		return $options;
	}
}

new IT_Exchange_SW_Shortcode();
