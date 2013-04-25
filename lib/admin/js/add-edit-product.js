jQuery(function(){
	jQuery('.hndle, .handlediv').remove();
	
	var it_exchange_set_image_ids = new Array();
	
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
			
			var current_image_count = jQuery('#it-exchange-product-images li input').length;
			var data_image_sizes = new Array();
			
			jQuery( selected_images ).each( function( i, image ) {
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
				
				jQuery('#it-exchange-add-new-image').before('<li><a href id="' + image.id + '" class="img-edit-test"><img src=" ' + data_image_sizes[1] + '" data-large="' + data_image_sizes[0] + '" data-thumb="' + data_image_sizes[1] + '" alt="' + image.alt + '" /><input type="hidden" name="it-exchange-image-id[' + current_image_count + ']" value="' + image.id + '" /></a></li>');
				
				current_image_count++;
			});
			
			jQuery('#it-exchange-product-images li:first-child img').attr( 'src', jQuery('#it-exchange-product-images li:first-child img').attr( 'data-large' ) );
			

			console.log( selected_images );
		},
		
		init: function() {
			jQuery('#wpbody').on('click', '#it-exchange-add-new-image', function(e) {
				e.preventDefault();
				
				it_exchange_add_images_frame.frame().open();
			});
		}
	};
	
	it_exchange_add_images_frame.init();
	/*
	it_exchange_edit_image_frame = {
		get: function() {
			return id;
		},
		
		frame: function() {
			if ( this._frame )
				return this._frame;
				
				selection = new wp.media.model.Selection({
					multiple: false
				});
				
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
		
		open: function() {
			
			
			console.log(selection);
			
			attachment = wp.media.attachment(id);
			console.log(attachment);
			
			attachment.fetch();
			console.log(attachment);
			
			//selection.add( attachment ? [ attachment ] : [] );
		},
		
		select: function() {
			selected_images = this.get('selection').toJSON();
		},
		
		init: function() {
			jQuery('#wpbody').on('click', '#it-exchange-product-images li a.img-edit-test', function(e) {
				e.preventDefault();
				
				id = jQuery(this).attr('id');
				
				it_exchange_edit_image_frame.frame().open();
			});
		}
	};
	
	it_exchange_edit_image_frame.init();
	*/
});
