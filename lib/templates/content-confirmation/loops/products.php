<?php
/**
 * The transaction products loop
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'transaction', 'has-products' ) ) : ?>
    <div class="transaction-products">
        <?php while( it_exchange( 'transaction', 'products' ) ) : ?>
            <?php it_exchange_get_template_part( 'content-confirmation/details/fields/transaction-product' ); ?>
        <?php endwhile; ?>
    </div>
<?php endif; ?>