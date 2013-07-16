<?php do_action( 'it_exchange_content_purchases_before_transaction_products' ); ?>
<?php while( it_exchange( 'transaction', 'products' ) ) : ?>
	<?php do_action( 'it_exchange_content_purchases_begin_transaction_products' ); ?>
	<div class="it-exchange-purchase-items">
		<div class="item-info">
			<div class="item-thumbnail">
				<?php it_exchange_get_template_part( 'content-purchases/details/fields/product-featured-image' ); ?>
			</div>
			<div class="item-data">
				<?php it_exchange_get_template_part( 'content-purchases/loops/product-info' ); ?>
			</div>
		</div>
	</div>
	<?php do_action( 'it_exchange_content_purchases_end_transaction_products' ); ?>
<?php endwhile; ?>
<?php do_action( 'it_exchange_content_purchases_after_transaction_products' ); ?>
