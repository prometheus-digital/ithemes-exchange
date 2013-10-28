jQuery( document ).ready( function( $ ) {
	
	// Set up the gallery thumbnail switch.
	var it_exchange_gallery_thumbnail_switch_action = $( '.it-exchange-product-images-gallery' ).attr( 'data-switch' );
	
	// Store the javascript in a function that we can call. 
	$( '.it-exchange-thumbnail-images li' ).on( it_exchange_gallery_thumbnail_switch_action, function() {
		$( this ).parent().find( 'span' ).removeClass( 'current' );
		$( this ).find( 'span' ).addClass( 'current' );
		$( this ).parent().parent().find( '.it-exchange-featured-image img' ).attr({
			'src':               $( this ).find( 'img' ).attr( 'data-src-large' ),
			'data-src-large':    $( this ).find( 'img' ).attr( 'data-src-large' ),
			'data-height-large': $( this ).find( 'img' ).attr( 'data-height-large' ),
			'data-src-full':     $( this ).find( 'img' ).attr( 'data-src-full' )
		});
	});
	
	// Set up the feature image zoom action.
	var it_exchange_feature_image_zoom_action = $( '.it-exchange-product-images-gallery' ).attr( 'data-zoom' );
	
	// Set up the zoom.
	function it_exchange_feature_image_zoom() {
		if ( it_exchange_feature_image_zoom_action == 'false' || $( '.it-exchange-product-images-gallery' ).attr( 'data-popup' ) == 'true' )
			return;
		
		$( '.it-exchange-featured-image .featured-image-wrapper' ).addClass( 'it-exchange-zoom-enabled' ).zoom({
			url: $( this ).find( 'img' ).attr( 'data-src-large' ),
			on: it_exchange_feature_image_zoom_action,
			onZoomIn: function() {
				$( '.it-exchange-featured-image .featured-image-wrapper' ).toggleClass( 'it-exchange-zooming' );
			},
			onZoomOut: function() {
				$( '.it-exchange-featured-image .featured-image-wrapper' ).toggleClass( 'it-exchange-zooming' );
			}
		});
	}
	
	// Initilize the zoom.
	it_exchange_feature_image_zoom();
	
	// Set up the product gallery popup.
	$( '.it-exchange-featured-image' ).on( 'click', function() {
		if ( $( '.it-exchange-product-images-gallery' ).attr( 'data-popup' ) == 'false' )
			return;
		
		$( this ).parent().clone().addClass( 'it-exchange-hidden' ).attr( 'id', $( this ).parent().attr( 'id' ) + '-temp' ).appendTo( 'body' );
		
		var gallery = '#' + $( this ).parent().attr( 'id' ) + '-temp';
		
		$( this ).colorbox({
			inline: true,
			href: gallery,
			opacity: 1,
			innerWidth: '100%',
			innerHeight: '100%',
			close: '<span class="it-ex-icon-close"></span>',
			overlayClose: false,
			scrolling: false,
			fixed: true,
			className: 'it-exchange-colorbox it-exchange-colorbox-light',
			onOpen: function() {
				$( '#cboxClose' ).delay( 500 ).fadeTo( 1, 1 );
				$( '#cboxContent, #cboxOverlay' ).delay( 350 ).fadeTo( 350, 1 );
				
				$( gallery ).on( it_exchange_gallery_thumbnail_switch_action, '.it-exchange-thumbnail-images li', function() {
					$( gallery ).find( '.it-exchange-thumbnail-images span' ).removeClass( 'current' );
					$( this ).find( 'span' ).addClass( 'current' );
					
					$( gallery ).find( '.it-exchange-featured-image img' ).attr({
						'src':               $( this ).find( 'img' ).attr( 'data-src-large' ),
						'data-src-large':    $( this ).find( 'img' ).attr( 'data-src-large' ),
						'data-height-large': $( this ).find( 'img' ).attr( 'data-height-large' ),
						'data-src-full':     $( this ).find( 'img' ).attr( 'data-src-full' )
					}).parent().css( 'margin', $( this ).find( 'img' ).attr( 'data-featured-position' ) );
				});
			},
			onComplete: function() {
				var featured = $( gallery ).find( '.featured-image' );
				
				$( gallery ).find( 'img' ).css( 'max-height', ( ( $( window ).height() - 200 ) ) + 'px' );
				
				if ( $( window ).height() > $( featured ).data( 'height-large' ) ) {
					$( featured ).attr( 'data-featured-position', ( ( $( window ).height() - $( featured ).data( 'height-large' ) ) / 2 ) + 'px 0px ' );
				} else {
					$( featured ).attr( 'data-featured-position', ( $( window ).height() / 10 ) + 'px 0px' );
				}
				
				$( featured ).parent().css( 'margin', $( featured ).data( 'featured-position' ) );
				
				var thumbnails = $( gallery ).find( '.it-exchange-thumbnail-images' );
				
				$( thumbnails ).find( 'li' ).each( function( index, element ) {
					var thumbnail = $( element ).find( 'img' );
					
					if ( $( window ).height() > $( thumbnail ).data( 'height-large' ) ) {
						$( thumbnail ).attr( 'data-featured-position', ( ( $( window ).height() - $( thumbnail ).data( 'height-large' ) ) / 2 ) + 'px 0px' );
					} else {
						$( thumbnail ).attr( 'data-featured-position', ( $( window ).height() / 10 ) + 'px 0px' );
					}
				});
				
				$( gallery ).fadeIn();
				
				document.onkeydown = function(event) {
					event = event || window.event;
					var current = $( gallery ).find( '.it-exchange-thumbnail-images' ).find( '.current' );
					switch ( event.keyCode ) {
						case 37 :
							if ( $( current ).parent().is( ':first-child' ) ) {
								$( current ).delay(1000).removeClass( 'current' ).parent().parent().find( 'li:last-child' ).find( 'span' ).addClass( 'current' );
							} else {
								$( current ).delay(1000).removeClass( 'current' ).parent().prev().find( 'span' ).addClass( 'current' );
							}
							
							var current = $( gallery ).find( '.it-exchange-thumbnail-images' ).find( '.current' );
							
							$( gallery ).find( '.it-exchange-featured-image .featured-image' ).attr({
								'src':               $( current ).find( 'img' ).attr( 'data-src-large' ),
								'data-src-large':    $( current ).find( 'img' ).attr( 'data-src-large' ),
								'data-height-large': $( current ).find( 'img' ).attr( 'data-height-large' ),
								'data-src-full':     $( current ).find( 'img' ).attr( 'data-src-full' )
							}).parent().css( 'margin', $( current ).find( 'img' ).attr( 'data-featured-position' ) );
							
							$( gallery ).find( '.featured-image-wrapper' ).trigger( 'zoom.destroy' );
						break;
						
						case 39 :
							if ( $( current ).parent().is( ':last-child' ) ) {
								$( current ).removeClass( 'current' ).parent().parent().find( 'li:first-child' ).find( 'span' ).addClass( 'current' );
							} else {
								$( current ).removeClass( 'current' ).parent().next().find( 'span' ).addClass( 'current' );
							}
							
							var current = $( gallery ).find( '.it-exchange-thumbnail-images' ).find( '.current' );
							
							$( gallery ).find( '.it-exchange-featured-image .featured-image' ).attr({
								'src':               $( current ).find( 'img' ).attr( 'data-src-large' ),
								'data-src-large':    $( current ).find( 'img' ).attr( 'data-src-large' ),
								'data-height-large': $( current ).find( 'img' ).attr( 'data-height-large' ),
								'data-src-full':     $( current ).find( 'img' ).attr( 'data-src-full' )
							}).parent().css( 'margin', $( current ).find( 'img' ).attr( 'data-featured-position' ) );
							
							$( gallery ).find( '.featured-image-wrapper' ).trigger( 'zoom.destroy' );
						break;
					}
		 		}
				
				if ( it_exchange_feature_image_zoom_action != 'false' ) {
					$( gallery ).find( '.featured-image-wrapper' ).addClass( 'it-exchange-zoom-enabled' ).on( 'hover', function() {
						$( this ).zoom({
							url: $( this ).find( 'img' ).attr( 'data-src-full' ),
							on: it_exchange_feature_image_zoom_action,
							onZoomIn: function() {
								$( gallery ).find( '.featured-image-wrapper' ).toggleClass( 'it-exchange-zooming' );
							},
							onZoomOut: function() {
								$( gallery ).find( '.featured-image-wrapper' ).toggleClass( 'it-exchange-zooming' );
							}
						});
					}).on( 'mouseleave', function() {
						$( this ).find( '.zoomImg' ).remove();
					});
				}
				
				$( document ).on( 'click', function( event ) {
					if ( $( event.target ).attr( 'id' ) ) {
						var closer = $( event.target ).attr( 'id' );
					} else {
						var closer = $( event.target ).attr( 'class' );
					}
					if ( closer == 'cboxLoadedContent' || closer.match( 'it-exchange-featured-image' ) ) {
						$.colorbox.remove();
					}
				});
			},
			onCleanup: function() {
				$( gallery ).remove().find( '.featured-image-wrapper' ).trigger( 'zoom.destroy' );
			},
			onClosed: function() {
				$( '#cboxClose' ).fadeTo( 1, 0 );
			}
		});
	});
});