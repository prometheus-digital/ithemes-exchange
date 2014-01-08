jQuery(document).ready(function($) {

	// copying/modifying from wp-admin/js/post.js
	// categories
	$('.categorydiv').each( function(){
		var this_id = $(this).attr('id'), prodCatAddBefore, prodCatAddAfter, taxonomyParts, taxonomy, settingName;

		taxonomyParts = this_id.split('-');
		taxonomyParts.shift();
		taxonomy = taxonomyParts.join('-');
		settingName = taxonomy + '_tab';
		
		// TODO: move to jQuery 1.3+, support for multiple hierarchical taxonomies, see wp-lists.js
		$('a', '#' + taxonomy + '-tabs').live('click', function(){ //changed to live
			var t = $(this).attr('href');
			$(this).parent().addClass('tabs').siblings('li').removeClass('tabs');
			$('#' + taxonomy + '-tabs').siblings('.tabs-panel').hide();
			$(t).show();
			if ( '#' + taxonomy + '-all' == t )
				deleteUserSetting( settingName );
			else
				setUserSetting( settingName, 'pop' );
			return false;
		});
		
		$('#new' + taxonomy).live( 'keypress', function(event){
			if( 13 === event.keyCode ) {
				event.preventDefault();
				$('#' + taxonomy + '-add-submit').click();
			}
		});
		
		
		prodCatAddBefore = function( s ) {
			if ( !$('#new'+taxonomy).val() )
				return false;
			s.data += '&' + $( ':checked', '#'+taxonomy+'checklist' ).serialize();
			$( '#' + taxonomy + '-add-submit' ).prop( 'disabled', true );
			return s;
		};

		prodCatAddAfter = function( r, s ) {
			//Core WP doesn't add the product categories to the checklist, we need to with this selectit var
			var sup, drop = $('#new'+taxonomy+'_parent'), selectit = $( '#'+taxonomy+'checklist' );

			$( '#' + taxonomy + '-add-submit' ).prop( 'disabled', false );
			//And this is where we prepend it
			if ( 'undefined' != s.parsed.responses[0] && (data = s.parsed.responses[0].data) ) {
				selectit.prepend( data );
			}
			if ( 'undefined' != s.parsed.responses[0] && (sup = s.parsed.responses[0].supplemental.newcat_parent) ) {
				drop.before(sup);
				drop.remove();
			}
		};

		$('#' + taxonomy + 'checklist').wpList({
			alt: '',
			response: taxonomy + '-ajax-response',
			addBefore: prodCatAddBefore,
			addAfter: prodCatAddAfter
		});
		
		$('#' + taxonomy + '-add-submit').live('click', function(){ //changed to live
			$('#new' + taxonomy).focus(); 
		});

		$('#' + taxonomy + '-add-toggle').live('click', function() { //changed to live
			$('#' + taxonomy + '-adder').toggleClass( 'wp-hidden-children' );
			$('a[href="#' + taxonomy + '-all"]', '#' + taxonomy + '-tabs').click();
			$('#new'+taxonomy).focus();
			return false;
		});
	});
	
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
				title: addEditProductL10n.mediaManagerTitle,
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
					alert( addEditProductL10n.largerThan150 );
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
				title: addEditProductL10n.editMediaManagerTitle,
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
				title: addEditProductL10n.uploadSource,
				button: {
					text: addEditProductL10n.insert
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
			$( '#' + ui.draggable.context.id ).animate({opacity:.5},200);
			$(this).addClass('over').find('.replace-feature-image').css('display','block').animate({opacity:1},200).find('span').css('top',$(this).height()/2-25);
		},
		out: function( e, ui ) {
			$( '#' + ui.draggable.context.id ).animate({opacity:1},200);
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

	$( '#it-exchange-feature-image .remove-item' ).live( 'click', function() {
		$( this ).parent().remove();

		var new_first_item = $( '#it-exchange-gallery-images li' ).first();

		if ( new_first_item[0].id == 'it-exchange-add-new-image' ) {
			$( '#it-exchange-add-new-image' ).addClass( 'empty' );
		} else {
			$( '#it-exchange-gallery-images li' ).first().remove();
			$( '#it-exchange-feature-image ul' ).append( new_first_item[0].outerHTML ).find( 'img' ).attr( 'src', $( '#it-exchange-feature-image img' ).attr( 'data-large' ) );
		}
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

	$( window ).resize( function() {
		$( '#it-exchange-advanced-tabs .inner').css( 'min-height', $( '#it-exchange-advanced-tab-nav').height() );
	});

	$( '#it-exchange-advanced-tabs' ).tabs();

	setTimeout(function() {
		$( '#it-exchange-advanced-tabs .inner').css( 'min-height', $( '#it-exchange-advanced-tab-nav').height() );
		$( '#it-exchange-advanced-tabs' ).css( 'display', 'none' );
	}, 0 );


	$( '#it_exchange_advanced-sortables' ).prepend( '<a href id="it-exchange-advanced-tabs-toggle" class="button button-large advanced-hidden">' + addEditProductL10n.advanced + '</a> <span class="tip" title="' + addEditProductL10n.advanced_tooltip + '">i</span>' );

	$( '#it-exchange-advanced-tabs-toggle' ).on( 'click', function( event ) {
		event.preventDefault();

		if ( $( this ).hasClass( 'advanced-hidden' ) ) {
			$( '#it-exchange-advanced-tabs' ).stop().fadeIn(500);
			document.cookie = 'it_exchange_product_show_advanced=_0; path=/;';
		} else {
			$( '#it-exchange-advanced-tabs' ).stop().fadeOut(500);
			document.cookie = 'it_exchange_product_show_advanced=0; path=/;';
		};

		$( this ).toggleClass( 'advanced-hidden' );
	});

	// Update Advanced tab on page load if set
	if (!$('#it-exchange-advanced-tabs').hasClass('advanced-hidden')) {
		var itExchangeSavedAdvancedTab = read_cookie('it_exchange_product_show_advanced') || '_0';
		itExchangeSavedAdvancedTab = itExchangeSavedAdvancedTab.substring(1);
		$('#it-exchange-advanced-tabs').tabs({active:itExchangeSavedAdvancedTab});
	}

	// Update cookie when tab is changed
	$('#it-exchange-advanced-tabs').on('tabsactivate', function(event, ui) {
		var newIndex = ui.newTab.index();
		document.cookie = 'it_exchange_product_show_advanced=_' + newIndex + '; path=/;';
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


	if ( read_cookie( 'it_exchange_product_show_advanced' ) != 0 ) {
		$( '#it-exchange-advanced-tabs-toggle' ).removeClass( 'advanced-hidden' );
		setTimeout(function() {
			$( '#it-exchange-advanced-tabs' ).css( 'display', 'block' );
		}, 0 );
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
	$( '#title' ).on('keydown.editor-focus', function(e) {
		var ed;

		if ( e.which != 9 )
			return;

		if ( !e.ctrlKey && !e.altKey && !e.shiftKey ) {
			$('#base-price').focus();

			e.preventDefault();
		}
	});

	// Tooltips.
	$( '.tip' ).tooltip();

	// Datepicker for the start and end dates.
	$( '.datepicker' ).datepicker({
		prevText: '',
		nextText: '',
		minDate: 0,
		dateFormat: $( 'input[name=it_exchange_availability_date_picker_format]' ).val(),
		onSelect: function( date ) {
			if ( ! $( '#' + $( this ).attr( 'data-append' ) ).val() )
				$( '#' + $( this ).attr( 'data-append' ) ).val( date );

			if ( $( this ).attr( 'id' ) == 'it-exchange-product-availability-start' )
				$( '#it-exchange-product-availability-end' ).datepicker( 'option', 'minDate', date );
		}
	});

	// Depends on post.js var tagBox in wp-admin/js/
	$('#it-exchange-advanced-tabs').children('div.ui-tabs-panel').each(function(){
		if ( this.id.indexOf('tagsdiv-') === 0 ) {
			tagBox.init();
			return false;
		}
	});

	function it_exchange_number_format( number, decimals, dec_point, thousands_sep ) {
		number = (number + '').replace(thousands_sep, ''); //remove thousands
		number = (number + '').replace(dec_point, '.'); //turn number into proper float (if it is an improper float)
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number;
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
		sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point;
		s = '',
		toFixedFix = function (n, prec) {
			var k = Math.pow(10, prec);
			return '' + Math.round(n * k) / k;
		};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

	// Format base price
	$( '#base-price' ).on( 'focusout', function() {
		if ( $( this ).data( 'symbol-position') == 'before' )
			$( this ).val( $( this ).data( 'symbol') + it_exchange_number_format( $( this ).val(), 2, $( this ).data( 'decimals-separator' ), $( this ).data( 'thousands-separator' ) ) );
		else
			$( this ).val( it_exchange_number_format( $( this ).val(), 2, $( this ).data( 'decimals-separator' ), $( this ).data( 'thousands-separator' ) ) + $( this ).data( 'symbol' ) );
	});

	// Format price in flat rate shipping
	$( '#it-exchange-flat-rate-shipping-cost' ).on( 'focusout', function() {
		if ( $( this ).data( 'symbol-position') == 'before' )
			$( this ).val( $( this ).data( 'symbol') + it_exchange_number_format( $( this ).val(), 2, $( this ).data( 'decimals-separator' ), $( this ).data( 'thousands-separator' ) ) );
		else
			$( this ).val( it_exchange_number_format( $( this ).val(), 2, $( this ).data( 'decimals-separator' ), $( this ).data( 'thousands-separator' ) ) + $( this ).data( 'symbol' ) );
	});

	// Toggles for shipping.
	$( '#it-exchange-product-shipping' ).on( 'change', '#it-exchange-shipping-disabled', function() {
		$( '.shipping-wrapper' ).toggleClass( 'hidden' );
	}).on( 'change', '#it-exchange-shipping-override-methods', function() {
		$( '.core-shipping-overridable-methods' ).toggleClass( 'hidden' );
		$( '.it-exchange-enabled-shipping-methods-for-product' ).toggleClass( 'hidden' );
	}).on( 'change', '#core-shipping-feature-override-from-address-label', function() {
		$( '.core-shipping-feature-from-address-ul' ).toggleClass( 'hidden' );
	});
});
