(function ( $ ) {

	var orderNotesForm = $( ".it-exchange-customer-order-notes-form" );
	var orderNotesSummary = $( ".it-exchange-customer-order-notes-summary" );

	$( ".it-exchange-edit-customer-order-notes" ).click( function ( e ) {
		e.preventDefault();

		orderNotesSummary.hide();
		orderNotesForm.show();
	} );

	$( ".it-exchange-customer-order-note-cancel" ).click( function ( e ) {
		e.preventDefault();

		orderNotesSummary.show();
		orderNotesForm.hide();
	} );

	$( document ).on( 'click', '.it-exchange-checkout-transaction-methods form[data-type="iframe"] .it-exchange-purchase-button', function ( e ) {
		e.preventDefault();

		var $this = $( this ),
			$form = $this.closest( 'form' ),
			$selector = $( '.it-exchange-credit-card-selector[data-method]', $form );

		if ( !$selector.length ) {
			launchIFrame( $form );

			return;
		}

		$( '.it-exchange-checkout-transaction-methods form, .it-exchange-purchase-dialog-trigger' ).not( $form ).hide();
		$this.hide();
		$selector.show();
	} );

	$( document ).on( 'change', '.it-exchange-credit-card-selector input[type="radio"]', function ( e ) {

		var $this = $( this ),
			$form = $this.closest( 'form' ),
			$selector = $( '.it-exchange-credit-card-selector[data-method]', $form );

		if ( $this.val() !== 'new_method' ) {
			return;
		}

		launchIFrame( $form, $selector );
	} );

	$( document ).on( 'click', '.it-exchange-checkout-cancel-complete', function ( e ) {

		var $this = $( this ),
			$form = $this.closest( 'form' ),
			$selector = $( '.it-exchange-credit-card-selector[data-method]', $form );

		e.preventDefault();

		if ( !$selector.length ) {
			return;
		}

		$( '.it-exchange-checkout-transaction-methods form, .it-exchange-purchase-dialog-trigger' ).show();
		$( '.it-exchange-purchase-button', $form ).show();
		$selector.hide();
	} );

	/**
	 * Launch the payment iFrame.
	 *
	 * @param {*} $form
	 * @param {*} [$selector]
	 */
	function launchIFrame( $form, $selector ) {

		var gateway = $form.data( 'gateway' );
		var deferred = $.Deferred();
		itExchange.hooks.doAction( 'iFramePurchaseBegin.' + gateway, deferred );

		deferred.done( function ( data ) {

			if ( data.cancelled ) {
				if ( $selector ) {
					$( 'input[type="radio"]:first', $selector ).prop( 'checked', true );
				}

				return;
			} else if ( data.tokenize ) {
				$form.append( $( '<input type="hidden" name="to_tokenize">' ).val( data.tokenize ) );
			} else if ( data.one_time_token ) {
				$form.append( $( '<input type="hidden" name="one_time_token">' ).val( data.one_time_token ) );
			}

			$form.submit();
		} );
		deferred.fail( function ( message ) {
			alert( message );
		} );
	}

})( jQuery );
