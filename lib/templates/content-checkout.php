<?php
/**
 * Default template part for the checkout page.
 * 
 * @since 0.4.0
 * @version 1.0.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates* @updated 1.0.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange/ directory located
 * in your theme.
*/
?>

<?php do_action( 'it_exchange_content_checkout_before_wrap' ); ?>
<div id="it-exchange-cart" class="it-exchange-wrap it-exchange-checkout">
	<?php do_action( 'it_exchange_content_checkout_begin_wrap' ); ?>
	
	<?php it_exchange_get_template_part( 'messages' ); ?>
	
	<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) :  ?>
		
		<?php do_action( 'it_exchange_content_checkout_before_items' ); ?>
		<div id="it-exchange-cart-items" class="it-exchange-table">
			<?php it_exchange_get_template_part( 'content-checkout/loops/items' ); ?>
		</div>
		<?php do_action( 'it_exchange_content_checkout_after_items' ); ?>
		
		<?php do_action( 'it_exchange_content_checkout_before_coupons' ); ?>
		<div id="it-exchange-cart-coupons" class="it-exchange-table">
			<?php it_exchange_get_template_part( 'content-checkout/loops/coupons' ); ?>
		</div>
		<?php do_action( 'it_exchange_content_checkout_after_coupons' ); ?>
		
		<?php do_action( 'it_exchange_content_checkout_before_totals' ); ?>
		<div id="it-exchange-cart-totals" class="it-exchange-table">
			<?php it_exchange_get_template_part( 'content-checkout/loops/totals' ); ?>
		</div>
		<?php do_action( 'it_exchange_content_checkout_after_totals' ); ?>
		
		<?php do_action( 'it_exchange_content_checkout_before_actions' ); ?>
		<div id="it-exchange-cart-actions" class="it-exchange-payment">
			<?php it_exchange_get_template_part( 'content-checkout/loops/actions' ); ?>
		</div>
		<?php do_action( 'it_exchange_content_checkout_after_actions' ); ?>
		
	<?php else : ?>
		<?php do_action( 'it_exchange_content_cart_start_empty_cart' ); ?>
			<p><?php _e( 'There are no items in your cart', 'LION' ); ?></p>
		<?php do_action( 'it_exchange_content_cart_end_empty_cart' ); ?>
	<?php endif; ?>
	<?php do_action( 'it_exchange_content_cart_end_wrap' ); ?>
</div>
<?php do_action( 'it_exchange_content_cart_after_wrap' ); ?>