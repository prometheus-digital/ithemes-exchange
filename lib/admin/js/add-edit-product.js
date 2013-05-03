jQuery(document).ready(function($) {
	
	$('.hndle, .handlediv').remove();
	
//	var it_exchange_set_image_ids = new Array();
	
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
					$('#it-exchange-feature-image .feature-image').append('<a href id="' + image.id + '" class="image-edit"><img src=" ' + data_image_sizes[0] + '" data-large="' + data_image_sizes[0] + '" data-thumb="' + data_image_sizes[1] + '" alt="' + image.alt + '" /><input type="hidden" name="it-exchange-image-id[' + current_image_count + ']" value="' + image.id + '" /></a>');
					feature_image_set = 1;
				}
				else {
					$('#it-exchange-add-new-image').before('<li><a href id="' + image.id + '" class="image-edit"><img src=" ' + data_image_sizes[1] + '" data-large="' + data_image_sizes[0] + '" data-thumb="' + data_image_sizes[1] + '" alt="' + image.alt + '" /><input type="hidden" name="it-exchange-image-id[' + current_image_count + ']" value="' + image.id + '" /></a></li>');
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
			
			this._frame.state('library');
			
			return this._frame;
		},
		
		init: function() {
			$('#wpbody').on('click', '#it-exchange-gallery-images li .image-edit', function(e) {
				e.preventDefault();
				
				wp.media.model.settings.post.featuredImageId = $(this).attr('id');
				
				it_exchange_edit_image_frame.frame().open();
			});
		}
	};
	
	it_exchange_edit_image_frame.init();
	
	/*
	 * Set up the thumbnails as sortable items.
	*/
	it_exchange_gallery_sortable = {
		items: 'li:not(.disable-sorting)',
		placeholder: 'sorting-placeholder',
		start: function( e, ui ) {
			$('.sorting-placeholder').html( ui.item.context.innerHTML );
			jQuery(this).addClass('sorting');
		},
		stop: function( e, ui ) {
			$(this).removeClass('sorting');
		}
	}
	
	$('#it-exchange-gallery-images').sortable(it_exchange_gallery_sortable);
	
	/*
	 * Set up the options, methods and events for
	 * setting the featured image.
	*/
	it_exchange_feature_droppable = {
		accept: "#it-exchange-gallery-images li",
		over: function( e, ui ) {
			$(this).addClass('over').find('.replace-feature-image').animate({opacity:1},200).find('span').css('top',$(this).height()/2-25);
		},
		out: function( e, ui ) {
			$(this).removeClass('over').find('.replace-feature-image').animate({opacity:0},200);
		},
		drop: function( e, ui ) {
			$(this).removeClass('over').css('opacity','0').find('.replace-feature-image').css('opacity','0');
			
			$('.sorting-placeholder').remove();
			
			$('#' + $(this).attr('id') + ' a img').attr('src', $('#' + $(this).attr('id') + ' a img').attr('data-thumb'));
			
			$('#it-exchange-gallery-images').prepend('<li>' + $(this).find('.feature-image').html() + '</li>');
			
			inner_html = ui.draggable.context.innerHTML;
			
			$('#' + $(inner_html).attr('id') + ' img').attr('src', $('#' + $(inner_html).attr('id') + ' img').attr('data-large'));
			
			$(this).find('.feature-image').html( $('#' + $(inner_html).attr('id') ).parent().html() );
			
			$('#it-exchange-gallery-images #' + $(inner_html).attr('id')).parent().remove();
			
			$(this).animate({opacity:1},750);
		}
	}
	
	jQuery( "#it-exchange-feature-image" ).droppable(it_exchange_feature_droppable);
	
});