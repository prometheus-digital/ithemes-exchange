<?php
/**
 * The default transactions loop for the 
 * content-purchases.php template part.
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
 * 
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-purchases/loops/ directory
 * located in your theme.
*/
?>

<?php do_action( 'it_exchange_content_purchases_before_loop' ); ?>
<?php while ( it_exchange( 'transactions', 'exist' ) ) : ?>
	<?php do_action( 'it_exchange_content_purchases_begin_loop' ); ?>
	<div class="it-exchange-purchase">
		<div class="it-exchange-purchase-top it-exchange-transaction-info">
			<?php it_exchange_get_template_part( 'content-purchases/loops/transaction-info' ); ?>
		</div>
		<?php if ( it_exchange( 'transaction', 'has-products' ) ) : ?>
			<?php it_exchange_get_template_part( 'content-purchases/loops/transaction-products' ); ?>
		<?php else : ?>
			<?php it_exchange_get_template_part( 'content-purchases/elements/no-transaction-products-found' ); ?>
		<?php endif; ?>
	</div>
	<?php do_action( 'it_exchange_content_purchases_end_loop' ); ?>
<?php endwhile; ?>
<?php do_action( 'it_exchange_content_purchases_after_loop' ); ?>