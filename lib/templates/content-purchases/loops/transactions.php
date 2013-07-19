<?php do_action( 'it_exchange_content_purchases_before_loop' ); ?>
<?php while( it_exchange( 'transactions', 'exist' ) ) : ?>
	<?php do_action( 'it_exchange_content_purchases_begin_loop' ); ?>
	<div class="it-exchange-purchase">
		<div class="it-exchange-purchase-top transaction-info">
			<?php it_exchange_get_template_part( 'content-purchases/loops/transaction-info' ); ?>
		</div>
		<?php 
		if ( it_exchange( 'transaction', 'has-products' ) )
			it_exchange_get_template_part( 'content-purchases/loops/transaction-products' );
		else
			it_exchange_get_template_part( 'content-purchases/elements/no-transaction-products-notice' );
		?>
	</div>
	<?php do_action( 'it_exchange_content_purchases_end_loop' ); ?>
<?php endwhile; ?>
<?php do_action( 'it_exchange_content_purchases_after_loop' ); ?>
