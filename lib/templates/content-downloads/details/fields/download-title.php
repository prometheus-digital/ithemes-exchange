<?php
/**
 * The default template part for the download title in
 * the content-downloads template part's download-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_download_info_before_download_title' ); ?>
<h4><?php it_exchange( 'transaction', 'product-download', array( 'attribute' => 'title' ) ); ?></h4>
<?php do_action( 'it_exchange_content_download_info_after_download_title' ); ?>