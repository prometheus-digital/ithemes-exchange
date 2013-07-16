<?php
/**
 * The transaction meta loop
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<div class='transaction-meta'>
    <?php do_action( 'it_exchange_content_confirmation_transaction_meta_top' ); ?>
    <?php foreach( it_exchange_get_confirmation_template_transaction_meta_elements() as $meta ) : ?>
        <?php it_exchange_get_template_part( 'content-confirmation/transaction-meta-' . $meta ); ?>
    <?php endforeach; ?>
    <?php do_action( 'it_exchange_content_confirmation_transaction_meta_bottom' ); ?>
</div>