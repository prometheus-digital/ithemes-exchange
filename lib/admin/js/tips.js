(function(it_exchange_dialog) {

	it_exchange_dialog(window.jQuery, window, document);

	}(function($, window, document) {
		$(function() {
			$( '.tip' ).tooltip({
				items: "[data-tip-content], [title]",
				content:  function() {
					var element = $( this );

					if ( element.is( '[data-tip-content]' ) ) {
						return element.attr( 'data-tip-content' );
					}
					if ( element.is( '[title]' ) ) {
						return element.attr( 'title' );
					}

					return 'This tooltip is broken. Please add content to the attribute \'data-tip-content\' for this tip.';
				}
			});

			$( '.tip' ).on( 'click', function() {
				if ( ! $( this ).attr( 'data-dialog-content' ) )
					return;

				if ( $( 'body' ).hasClass( 'it-exchange-dialog-open' ) )
					return;

				var element = $( this );

				element.tooltip( 'destroy' );

				$( 'body' ).addClass( 'it-exchange-dialog-open' );

				element.parent().append('<div class="it-exchange-dialog"></div>').find( '.it-exchange-dialog' ).html( $( this ).attr( 'data-dialog-content' ) );

				element.parent().find( '.it-exchange-dialog' ).dialog({
					appendTo: element.parent(),
					draggable: false,
					dialogClass: 'it-exchange-tip-dialog',
					// minWidth: ( $( this ).attr( 'data-dialog-width' ) + 1 ),
					title: element.attr( 'title' ),
					minHeight: 'none',
					width: 'auto',
					closeText: '&times;',
					position: {
						my: 'left top+15',
						at: 'left bottom',
						of: $( this )
					},
					close: function( event, ui ) {
						$( '.tip' ).tooltip();
						$( 'body' ).removeClass( 'it-exchange-dialog-open' );
						$( this ).remove();
					}
				});
			});
		});
	})
);
