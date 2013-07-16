<?php
/**
 * The default download-info loop for the content-downloads.php template part
 *
 * @since 1.1.0
 * @version 1.1.0
 * @package IT_Exchange
*/
?>
<?php if ( it_exchange( 'transaction', 'has-product-downloads' ) ) : ?>
<?php do_action( 'it_exchange_content_downloads_before_download_info_loop' ); ?>
	<div class="downloads-wrapper">
	<?php do_action( 'it_exchange_content_downloads_fields_begin_download_info_loop' ); ?>
		<?php while ( it_exchange( 'transaction', 'product-downloads' ) ) : ?>
			<div class="download">
				<?php it_exchange_get_template_part( 'content-downloads/details/fields/confirmation-url' ); ?>
				<div class="download-info">
                    <?php it_exchange_get_template_part( 'content-downloads/details/fields/download-title' ); ?>
					<?php if ( it_exchange( 'transaction', 'has-product-download-hashes' ) ) : ?>
                    <?php it_exchange_get_template_part( 'content-downloads/loops/download-hashes' ); ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endwhile; ?>
   	<?php do_action( 'it_exchange_content_downloads_fields_end_download_info_loop' ); ?>
	</div>
<?php do_action( 'it_exchange_content_downloads_fields_after_download_info_loop' ); ?>
<?php endif; ?>