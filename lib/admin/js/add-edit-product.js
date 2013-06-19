jQuery(document).ready(function($) {
	
	$( '#it_exchange_normal-sortables .hndle, #side-sortables .hndle, #it-exchange-add-edit-product-interface-main > .hndle, .handlediv, #post-body-content' ).remove();
	
	/*
	 * Set up the frame for adding new images. This
	 * frame first checks to see if any images are
	 * set. If not, set the first image as the
	 * featured image and the others as thumbnails.
	 * If items are set, each new item is added as a
	 * thumbnail to the end of the thumbnails.
	*/
	it_exchange_add_images_frame = {
		
		frame: function() {
			if ( this._frame )
				return this._frame;
				
			this._frame = wp.media({
				title: 'Select Images',
				library: {
					type: 'image'
				},
				multiple: true
			});
			
			this._frame.on( 'open', this.open ).state('library').on( 'select', this.select );
			
			return this._frame;
		},
		
		select: function() {
			selected_images = this.get('selection').toJSON();
			
			var feature_image_set = $('#it-exchange-feature-image input').length;
			
			var current_image_count = $('#it-exchange-gallery-images li input').length + feature_image_set;
			
			var data_image_sizes = new Array();
			
			$( selected_images ).each( function( i, image ) {
				if ( typeof image.sizes.large !== 'undefined' && typeof image.sizes.thumbnail !== 'undefined' ) {
					data_image_sizes[0] = image.sizes.large.url;
					data_image_sizes[1] = image.sizes.thumbnail.url;
				}
				else if ( typeof image.sizes.thumbnail !== 'undefined' ) {
					data_image_sizes[0] = image.url;
					data_image_sizes[1] = image.sizes.thumbnail.url;
				}
				else {
					alert( 'You must upload an image larger than 150x150.');
					return;
				}
				
				if ( feature_image_set == 0 ) {
					$('#it-exchange-feature-image .feature-image').append('<li id="' + image.id + '"><a href class="image-edit"><img src=" ' + data_image_sizes[0] + '" data-large="' + data_image_sizes[0] + '" data-thumb="' + data_image_sizes[1] + '" alt="' + image.alt + '" /><span class="overlay"></span></a><span class="remove-item">&times;</span><input type="hidden" name="it-exchange-product-images[' + current_image_count + ']" value="' + image.id + '" /></li>');
					feature_image_set = 1;
				}
				else {
					$('#it-exchange-add-new-image').before('<li id="' + image.id + '"><a href class="image-edit"><img src=" ' + data_image_sizes[1] + '" data-large="' + data_image_sizes[0] + '" data-thumb="' + data_image_sizes[1] + '" alt="' + image.alt + '" /><span class="overlay"></span></a><span class="remove-item">&times;</span><input type="hidden" name="it-exchange-product-images[' + current_image_count + ']" value="' + image.id + '" /></li>');
				}
				
				current_image_count++;
			});
			
			if ( feature_image_set == 1 )
				$('#it-exchange-add-new-image').removeClass('empty');
		},
		
		init: function() {
			$('#wpbody').on('click', '#it-exchange-add-new-image', function(e) {
				e.preventDefault();
				
				it_exchange_add_images_frame.frame().open();
			});
		}
	};
	
	it_exchange_add_images_frame.init();
	
	/*
	 * We want the ability to edit the data for the
	 * thumbnails. Clicking on the any of the items
	 * will open this frame with the selected item.
	*/
	it_exchange_edit_image_frame = {
		frame: function() {
			if ( this._frame )
				return this._frame;
				
			this._frame = wp.media({
				title: 'Edit Image',
				library: {
					type: 'image'
				},
				multiple: false,
				state: 'featured-image',
				states: [ new wp.media.controller.FeaturedImage() ]
			});
			
			this._frame.on( 'open', this.open ).state('library').on( 'select', this.select );
			
			return this._frame;
		},
		
		select: function() {
			selected_images = this.get('selection').toJSON();
		},
		
		init: function() {
			$('#wpbody').on('click', '#it-exchange-product-images .image-edit', function(e) {
				e.preventDefault();
				
				wp.media.model.settings.post.featuredImageId = $(this).attr('id');
				
				it_exchange_edit_image_frame.frame().open();
			});
		}
	};
	
	it_exchange_edit_image_frame.init();
	
	/*
	 * We want the ability to edit the data for the
	 * thumbnails. Clicking on the any of the items
	 * will open this frame with the selected item.
	*/
	it_exchange_source_upload_frame = {
		frame: function() {
			if ( this._frame )
				return this._frame;
				
			this._frame = wp.media({
				title: 'Upload Source',
				button: {
					text: 'Insert'
				},
				multiple: false
			});
			
			this._frame.on( 'open', this.open ).state('library').on( 'select', this.select );
			
			return this._frame;
		},
		
		select: function() {
			source = this.get( 'selection' ).single().toJSON();
			
			$( source_parent_id + ' .download-source input').attr( 'value', source.url );
			
			if ( $( source_parent_id + ' .download-name input' ).val() == '' )
				$( source_parent_id + ' .download-name input').attr( 'value', source.title );
		},
		
		init: function() {
			$( '#wpbody' ).on( 'click', '.it-exchange-upload-digital-download', function( event ) {
				event.preventDefault();
				
				source_parent_id = '#' + $( this ).parent().parent().attr( 'id' );
				
				it_exchange_source_upload_frame.frame().open();
			});
		}
	};
	
	it_exchange_source_upload_frame.init();
	
	/*
	 * Set up the thumbnails as sortable items.
	*/
	it_exchange_gallery_sortable = {
		items: 'li:not(.disable-sorting)',
		placeholder: 'sorting-placeholder',
		start: function( e, ui ) {
			$( '.sorting-placeholder' ).html( ui.item.context.innerHTML );
			$( this ).addClass( 'sorting' );
		},
		stop: function( e, ui ) {
			$( this ).removeClass( 'sorting' );
			
			it_exchange_gallery_sort_iteration = 0;
			
			$( '#it-exchange-gallery-images li' ).each( function() {
				it_exchange_gallery_sort_iteration++;
				if ( $( this ).attr( 'id' ) != 'it-exchange-add-new-image' ) {
					$( this ).find( 'input' ).attr( 'name', 'it-exchange-product-images[' + it_exchange_gallery_sort_iteration + ']' );
				}
			});
		}
	}
	
	$( '#it-exchange-gallery-images').sortable( it_exchange_gallery_sortable );
	
	/*
	 * Set up the options, methods and events for
	 * setting the featured image.
	*/
	it_exchange_feature_droppable = {
		accept: "#it-exchange-gallery-images li",
		over: function( e, ui ) {
			$(this).addClass('over').find('.replace-feature-image').css('display','block').animate({opacity:1},200).find('span').css('top',$(this).height()/2-25);
		},
		out: function( e, ui ) {
			$(this).removeClass('over').find('.replace-feature-image').css('display','none').animate({opacity:0},200);
		},
		drop: function( e, ui ) {
			$(this).removeClass('over').css('opacity','0').find('.replace-feature-image').css({'opacity':'0','display':'none'});
			
			$('#it-exchange-gallery-images').removeClass('sorting');
			
			$('.sorting-placeholder').remove();
			
			$('#' + $(this).attr('id') + ' a img').attr('src', $('#' + $(this).attr('id') + ' a img').attr('data-thumb'));
			
			$('#it-exchange-gallery-images').prepend( $(this).find('.feature-image').html() );
			
			$('#' + ui.draggable.context.id + ' a img').attr('src', $('#' + ui.draggable.context.id + ' a img').attr('data-large'));
			
			$( this ).find( '.feature-image' ).html( '<li id="' + ui.draggable.context.id + '">' + ui.draggable.context.innerHTML + '</li>' );
			
			$('#it-exchange-gallery-images #' + ui.draggable.context.id ).remove();
			
			$( this ).animate( { opacity: 1} , 750 );
			
			$( '#' + $( this ).attr( 'id' ) ).find( 'input' ).attr( 'name', 'it-exchange-product-images[0]' );
			
			it_exchange_gallery_sort_iteration = 0;
			
			$( '#it-exchange-gallery-images li' ).each( function() {
				it_exchange_gallery_sort_iteration++;
				if ( $( this ).attr( 'id' ) != 'it-exchange-add-new-image' ) {
					$( this ).find( 'input' ).attr( 'name', 'it-exchange-product-images[' + it_exchange_gallery_sort_iteration + ']' );
				}
			});
		}
	}
	
	$( "#it-exchange-feature-image" ).droppable( it_exchange_feature_droppable );
	
	$('#it-exchange-gallery-images li .remove-item').live('click', function() {
		$(this).parent().animate({opacity:0},300, function(){ $(this).remove() });
	});
	
	/**
	 * Submit Metabox
	 * 
	 * Here we add a simple event to show and hide
	 * this advanced publishing actions.
	*/
	$( '#it-exchange-submit-box' ).on( 'click', '#advanced-action a', function( event ) {
		event.preventDefault();
		
		if ( $( this ).hasClass( 'advanced-hidden' ) ) {
			$( this ).text( $( this ).attr( 'data-visible') );
			$( '.advanced-actions' ).slideDown();
		} else {
			$( this ).text( $( this ).attr( 'data-hidden') );
			$( '.advanced-actions' ).slideUp();
		};
		
		$( this ).toggleClass( 'advanced-hidden' );
	});
	
	$( '#it-exchange-submit-box' ).on( 'click', 'a.edit-product-visibility, a.cancel-product_visibility, a.save-product_visibility', function( event ) {
		event.preventDefault();
		
		$( 'a.edit-product-visibility' ).toggle();
		$( '#product-visibility-select' ).slideToggle( 'fast' );
		
		
	});
	
	/**
	 * Advanced Metaboxes
	 * 
	 * First we create our new HTML to handle the
	 * tabbing structure for our advance options.
	 * 
	 * Next we loop through the advanced metaboxes,
	 * grab the title and HTML then append that data
	 * to #it-exchange-advanced-tabs. After that, we
	 * remove those items.
	 * 
	 * Then we apply a min-height to all the .inner
	 * wraps in order to maintain a consistent UI.
	 * 
	 * Last we initiate the Tabs jQuery UI.
	*/	
	$( '#it_exchange_advanced-sortables' ).append( '<div id="it-exchange-advanced-tabs" class=""><ul id="it-exchange-advanced-tab-nav"></ul></div>' );
	
	$( '#it_exchange_advanced-sortables > div' ).each( function() {
		if ( $( this ).attr( 'id' ) != 'it-exchange-advanced-tabs' ) {
			$( '#it-exchange-advanced-tab-nav' ).append( '<li><a href="#' + $( this ).attr( 'id' ) + '">' + $( this ).find( '.hndle span' ).text() + '</a></li>' );
			$( '#it-exchange-advanced-tabs' ).append( '<div id="' + $( this ).attr( 'id' ) + '"><div class="inner">' + $( this ).find( '.inside' ).html() + '</div></div>');
			$( this ).remove();
		};
	});
	
	setTimeout(function(){ $( '#it-exchange-advanced-tabs .inner').css( 'min-height', $( '#it-exchange-advanced-tab-nav').height() ) }, 0);
	
	$( '#it-exchange-advanced-tabs' ).tabs().slideUp();
	
	// NOTE This needs to be localized.
	$( '#it_exchange_advanced-sortables' ).prepend( '<a href id="it-exchange-advanced-tabs-toggle" class="button button-large advanced-hidden">Advanced</a>' );
	
	$( '#it-exchange-advanced-tabs-toggle' ).on( 'click', function( event ) {
		event.preventDefault();
		
		if ( $( this ).hasClass( 'advanced-hidden' ) ) {
			$( '#it-exchange-advanced-tabs' ).animate({opacity:1},250).slideDown();
			document.cookie = 'it_exchange_product_show_advanced=1; path=/;';
		} else {
			$( '#it-exchange-advanced-tabs' ).slideUp().animate({opacity:0},250);
			document.cookie = 'it_exchange_product_show_advanced=0; path=/;';
		};
		
		$( this ).toggleClass( 'advanced-hidden' );
	});
	
	// NOTE I feel like this could be done within the PHP instead. Or find a better way to read a cookie with Javascript.
	function read_cookie( name ) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for( var i=0; i < ca.length; i++ ) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}
	
	if ( read_cookie( 'it_exchange_product_show_advanced' ) == 1 ) {
		$( '#it-exchange-advanced-tabs' ).animate({opacity:1},250).slideDown();
		$( '#it-exchange-advanced-tabs-toggle' ).removeClass( 'advanced-hidden' );
	}
	// ENDNOTE
	
	$( '.it-exchange-checkbox-enable' ).click( function() {
		it_exchange_input_change = $( this ).attr( 'name' );
		
		if ( $( this ).is( ':checked' ) ) {
			$( '.' + it_exchange_input_change ).removeClass( 'hide-if-js' );
	    } else {
	        $( '.' + it_exchange_input_change ).addClass( 'hide-if-js' );
	    }
	});
	
	// Download Expiration
	$('#it-exchange-digital-downloads-expires').change( function( event ) {
		if ( $( this ).val() == 0 )
			$( $( this ).parent() ).find( '.hide-if-no-expire' ).css( 'display', 'none' );
		else
			$( $( this ).parent() ).find( '.hide-if-no-expire' ).css( 'display', 'inline' );
	});
		
	// Downloads
	$( '#it-exchange-product-downloads' ).on( 'click', '.download-remove a', function( event ) {
		event.preventDefault();
		
		$( this ).parent().parent().animate({
			opacity: 0,
			height: 0
		}, 300, function() {
			$( this ).remove();
		});
	}).on( 'click', '.download-add-new a', function( event ) {
		event.preventDefault();
		
		var it_exchange_new_item_clone = $('.download-item.download-item-clone').clone();
		
		$( it_exchange_new_item_clone ).removeClass('hidden download-item-clone').attr( 'id', 'download-item-' + it_exchange_new_download_interation ).find( '.download-name input' ).attr( 'name', 'it-exchange-digital-downloads[' + it_exchange_new_download_interation + '][name]' );
		$( it_exchange_new_item_clone ).removeClass('hidden download-item-clone').attr( 'id', 'download-item-' + it_exchange_new_download_interation ).find( '.download-source input' ).attr( 'name', 'it-exchange-digital-downloads[' + it_exchange_new_download_interation + '][source]' );
		
		$( '#it-exchange-product-downloads .downloads-list' ).append( it_exchange_new_item_clone );
		
		it_exchange_new_download_interation++;
		
	}).on( 'focusout', 'input', function() {
		if ( ! $( this ).val() ) {
			$( this ).removeClass( 'not-empty' );
		} else {
			$( this ).addClass( 'not-empty' );
		};
	});
	
	// Unregister WordPress tab jQuery from autosave.js
	$( '#title' ).off( 'keydown.editor-focus' );
	
	// This code is meant to allow tabbing from Title to Article Base Price.
	$('#title').on('keydown.editor-focus', function(e) {
		var ed;

		if ( e.which != 9 )
			return;

		if ( !e.ctrlKey && !e.altKey && !e.shiftKey ) {
			$('#base-price').focus();

			e.preventDefault();
		}
	});
	
});