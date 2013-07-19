<?php
/**
 * The product template for the Super Widget.
 * 
 * @since 0.4.0
 * @version 1.0.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 * 
 * Example: theme/exchange/super-widget-product.php
*/
?>

<?php it_exchange_get_template_part( 'messages' ); ?>

<?php if ( it_exchange( 'product', 'found' ) ) : ?>
	<?php do_action( 'it_exchange_super_widget_product_before_product' ); ?>
	<div class="it-exchange-sw-product it-exchange-sw-processing-product">
		<?php do_action( 'it_exchange_super_widget_product_begin_product' ); ?>

		<?php if ( it_exchange_is_multi_item_cart_allowed() ) : ?>
			<?php it_exchange_get_template_part( 'super-widget', 'cart' ); ?>
		<?php endif; ?>
		
		<?php if ( it_exchange_is_page( 'product' ) && ( !it_exchange_is_multi_item_cart_allowed() || !it_exchange_is_current_product_in_cart() ) ) : ?>
			<?php it_exchange_get_template_part( 'super-widget-product/elements/purchase-options' ); ?>
		<?php endif; ?>

		<?php do_action( 'it_exchange_super_widget_product_end_product' ); ?>
	</div>
	<?php do_action( 'it_exchange_super_widget_product_after_product' ); ?>
<?php endif; ?>
