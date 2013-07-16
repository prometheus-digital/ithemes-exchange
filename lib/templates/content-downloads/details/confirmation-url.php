<?php
/**
 * The default template part for the download confirmation url in
 * the content-downloads template part's product-info loop
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php do_action( 'it_exchange_content_download_info_before_confirmation_url' ); ?>
<div class="download-product">
	<a href="<?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'confirmation-url' ) ); ?>" class="button">
		<?php _e( 'Transaction', 'LION' ); ?>
	</a>
</div>
<?php do_action( 'it_exchange_content_download_info_after_confirmation_url' ); ?>
