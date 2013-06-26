jQuery( document ).ready( function( $ ) {
	$( '.it-exchange-thumbnail-images li' ).on( 'hover', function() {
		$( '.it-exchange-thumbnail-images span' ).removeClass( 'current' );
		$( this ).find( 'span' ).addClass( 'current' );
		$( '.it-exchange-featured-image img' ).attr( 'src', $( this ).find( 'img' ).attr( 'data-src-large' ) );
		
	});
	
	 $( '.it-exchange-featured-image .featured-image-wrapper' ).zoom({ url: $( this ).find( 'img' ).attr( 'data-src-large' ) });
});