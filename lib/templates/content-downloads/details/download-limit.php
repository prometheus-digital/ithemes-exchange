<?php
/**
 * The default template part for the download's download limit in
 * the content-downloads template part's download-hash loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_download_info_before_download_limit' ); ?>
<span class="download-limit">
<?php 
if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'download-limit' ) ) ) :
    it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'downloads-remaining' ) );
	echo ' '; _e( 'download(s) remaining', 'LION' );
else :
	_e( 'Unlimited downloads', 'LION' );
endif; 
?>
</span>
<?php do_action( 'it_exchange_content_download_info_after_download_limit' ); ?>
