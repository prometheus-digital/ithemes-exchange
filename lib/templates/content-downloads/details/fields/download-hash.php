<?php
/**
 * The default template part for the download's download hash in
 * the content-downloads template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_download_info_before_download_hash' ); ?>
<code class="download-hash">
    <?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'hash' ) ); ?>
</code>
<?php do_action( 'it_exchange_content_download_info_after_download_hash' ); ?>
