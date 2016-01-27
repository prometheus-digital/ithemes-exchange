<?php
/**
 * Generate downloads for testing.
 *
 * @since   1.35
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Test_Factory_For_Downloads
 */
class IT_Exchange_Test_Factory_For_Downloads extends WP_UnitTest_Factory_For_Post {

	/**
	 * Create the download in the database.
	 *
	 * @param array $args
	 *
	 * @return int|WP_Error
	 *
	 * @throws Exception
	 */
	function create_object( $args ) {

		if ( empty( $args['product'] ) ) {
			throw new Exception( 'Product required.' );
		}

		$product_id = $args['product'];

		$args['post_type']   = 'it_exchange_download';
		$args['post_status'] = 'publish';

		$download_id = parent::create_object( $args );

		if ( is_wp_error( $download_id ) ) {
			return $download_id;
		}

		$name = get_the_title( $product_id );
		$name .= '.zip';

		$attachment_factory = $this->factory->attachment;
		$attachment_id      = $attachment_factory->create_object( $name, $product_id, array(
			'post_mime_type' => 'application/zip'
		) );

		update_post_meta( $download_id, '_it-exchange-download-info', array(
			'source'      => wp_get_attachment_url( $attachment_id ),
			'product_id'  => $product_id,
			'download_id' => $download_id,
			'name'        => $name
		) );

		return $download_id;
	}
}
