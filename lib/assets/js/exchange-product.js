jQuery( document ).ready( function( $ ) {
	
	var it_exchange_gallery_thumbnail_switch = $( '.it-exchange-product-images-gallery' ).attr( 'data-switch' );
	
	$( '.it-exchange-thumbnail-images li' ).on( it_exchange_gallery_thumbnail_switch, function() {
		$( '.it-exchange-thumbnail-images span' ).removeClass( 'current' );
		$( this ).find( 'span' ).addClass( 'current' );
		$( '.it-exchange-featured-image img' ).attr( 'src', $( this ).find( 'img' ).attr( 'data-src-large' ) );
	});
	
	var it_exchange_feature_image_zoom = $( '.it-exchange-product-images-gallery' ).attr( 'data-zoom' );
	
	$( '.it-exchange-featured-image .featured-image-wrapper' ).zoom({
		url: $( this ).find( 'img' ).attr( 'data-src-large' ),
		on: it_exchange_feature_image_zoom
	});
});