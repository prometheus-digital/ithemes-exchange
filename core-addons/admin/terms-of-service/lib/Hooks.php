<?php
/**
 * Main plugin hooks.
 *
 * @author iThemes
 * @since  1.0
 */

namespace ITETOS;

/**
 * Class Hooks
 *
 * @package ITETOS
 */
class Hooks {

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		add_action( 'it_exchange_content_checkout_before_transaction_methods', array(
			$this,
			'add_terms_to_super_widget'
		) );

		add_action( 'it_exchange_content_checkout_before_actions', array(
			$this,
			'add_terms_to_checkout'
		) );

		add_action( 'wp_enqueue_scripts', array(
			$this,
			'scripts_and_styles'
		), 20 );

		add_filter( 'it_exchange_generate_transaction_object', array(
			$this,
			'add_terms_to_transaction_object'
		) );
	}

	/**
	 * Get the entire text of the terms of service.
	 *
	 * @since 1.0
	 *
	 * @param \ITE_Cart|null $cart
	 *
	 * @return string
	 */
	private static function get_tos( \ITE_Cart $cart = null ) {

		$main = Settings::get( 'terms' );

		if ( trim( $main ) !== '' ) {
			$main = wpautop( $main );
		}

		$cart = $cart ?: it_exchange_get_current_cart( false );

		if ( ! $cart ) {
			return '';
		}

		/** @var \ITE_Cart_Product $item */
		foreach ( $cart->get_items( 'product' ) as $item ) {
			$product = $item->get_product();

			if ( ! $product->has_feature( 'terms-of-service' ) ) {
				continue;
			}

			$title = '<h5>' . $item->get_name() . '</h5>';

			/**
			 * Filter the product heading section.
			 *
			 * By default this is the product title wrapped in H5 tags.
			 *
			 * @since 1.0
			 * @since 2.0.0 Add the `$cart` parameter.
			 *
			 * @param string               $title
			 * @param \IT_Exchange_Product $product
			 * @param \ITE_Cart            $cart
			 */
			$title = apply_filters( 'itetos_product_heading', $title, $product, $cart );

			$main .= $title;

			$custom = $product->get_feature( 'terms-of-service', array( 'field' => 'terms' ) );

			$custom = wpautop( $custom );

			/**
			 * Filter the terms for a certain product.
			 *
			 * @since 1.0
			 * @since 2.0.0 Add the `$cart` parameter.
			 *
			 * @param string               $custom
			 * @param \IT_Exchange_Product $product
			 * @param \ITE_Cart            $cart
			 */
			$custom = apply_filters( 'itetos_product_terms', $custom, $product, $cart );

			$main .= $custom;
		}

		$main = trim( $main );

		/**
		 * Filter the entirety of the Terms of Service.
		 *
		 * @since 1.0
		 * @since 2.0.0 Add the `$cart` parameter.
		 *
		 * @param string    $main
		 * @param \ITE_Cart $cart
		 */
		$main = apply_filters( 'itetos_terms', $main, $cart );

		return trim( $main );
	}

	/**
	 * Add our terms to the super widget.
	 *
	 * These are displayed before the transaction methods.
	 *
	 * @since 1.0
	 */
	public function add_terms_to_super_widget() {

		$tos = self::get_tos();

		if ( ! $tos ) {
			return;
		}

		?>

		<div class="terms-of-service-container">

			<p class="tos-agree-container">
				<input type="checkbox" id="agree-terms" value="agree">
				<label for="agree-terms"><?php echo $agree = Settings::get( 'label' ); ?></label>
			</p>

			<a href="javascript:" id="show-terms"><?php _e( 'Show Terms', 'it-l10n-ithemes-exchange' ); ?></a>

			<div class="terms">
				<?php echo $tos; ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Add our terms to the checkout page.
	 *
	 * These are displayed before the transaction methods.
	 *
	 * @since 1.0
	 */
	public function add_terms_to_checkout() {

		$tos = self::get_tos();

		if ( ! $tos ) {
			return;
		}
		?>

		<div class="terms-of-service-container">

			<p class="tos-agree-container">
				<input type="checkbox" id="agree-terms" value="agree">
				<label for="agree-terms"><?php echo $agree = Settings::get( 'label' ); ?></label>
			</p>

			<a href="javascript:" id="show-terms"><?php _e( 'Show Terms', 'it-l10n-ithemes-exchange' ); ?></a>

			<div class="terms">
				<?php echo $tos; ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Enqueue scripts and styles onto the front-end.
	 *
	 * @since 1.0
	 */
	public function scripts_and_styles() {

		$tos = self::get_tos();

		if ( ! $tos ) {
			return;
		}

		if ( it_exchange_in_superwidget() || it_exchange_is_page( 'product' ) ) {
			wp_enqueue_script( 'itetos-sw' );
			wp_enqueue_style( 'itetos-sw' );

			wp_localize_script( 'itetos-sw', 'ITETOS', array(
				'show' => __( 'Show Terms', 'it-l10n-ithemes-exchange' ),
				'hide' => __( 'Hide Terms', 'it-l10n-ithemes-exchange' )
			) );
		}

		if ( it_exchange_is_page( 'checkout' ) ) {
			wp_enqueue_script( 'itetos-checkout' );
			wp_enqueue_style( 'itetos-checkout' );

			wp_localize_script( 'itetos-checkout', 'ITETOS', array(
				'show' => __( 'Show Terms', 'it-l10n-ithemes-exchange' ),
				'hide' => __( 'Hide Terms', 'it-l10n-ithemes-exchange' )
			) );
		}
	}

	/**
	 * Save the entire Terms of Service to the transaction object on checkout.
	 *
	 * @since 1.0
	 * @since 2.0.0 Add $cart parameter.
	 *
	 * @param object         $transaction_object
	 * @param \ITE_Cart|null $cart
	 *
	 * @return object
	 */
	public function add_terms_to_transaction_object( $transaction_object, \ITE_Cart $cart = null ) {

		$tos = self::get_tos( $cart );

		if ( trim( $tos ) != '' ) {
			$transaction_object->terms_of_service = $tos;
		}

		return $transaction_object;
	}
}