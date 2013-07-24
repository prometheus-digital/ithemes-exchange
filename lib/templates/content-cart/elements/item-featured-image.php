<?php
/**
 * The main template file for the Featured Image
 * element in the cart-items loop for content-cart
 * template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-cart/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_cart_item_details_before_featured_image' ); ?>
<div class="it-exchange-cart-item-thumbnail it-exchange-table-column">
	<?php do_action( 'it_exchange_content_cart_item_details_begin_featured_image' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php it_exchange( 'cart-item', 'featured-image' ); ?>
	</div>
	<?php do_action( 'it_exchange_content_cart_item_details_end_featured_image' ); ?>
</div>
<?php do_action( 'it_exchange_content_cart_item_details_after_featured_image' ); ?>