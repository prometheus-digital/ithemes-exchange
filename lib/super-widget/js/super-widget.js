/**
 * Any events need to be connected with jQuery(document).on([event], [selector], [function/callback];
 *
*/
jQuery(function(){

	// Test menu for changing states
	jQuery(document).on('click', 'a.it-exchange-test-load-state-via-ajax', function(event){
		event.preventDefault();
		var state = jQuery(this).data('it-exchange-sw-state');
		itExchangeGetSuperWidgetState( state );
	});

});

/**
 * Loads a template part for the widget
*/
function itExchangeGetSuperWidgetState( state ) {
	jQuery.get( itExchangeSWAjaxURL+'&state=' + state, function(data) {
		jQuery('.it-exchange-super-widget').html(data);
		itExchangeSWState = state;
	});
}
