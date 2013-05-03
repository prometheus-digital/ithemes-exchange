jQuery(function(){

	jQuery('a.it-exchange-test-load-state-via-ajax').click(function(event){
		event.preventDefault();
		var state = jQuery(this).data('it-exchange-sw-state');
		jQuery.get( itExchangeSWAjaxURL+'&state='+state, function(data) {
			jQuery('.it-exchange-super-widget').html(data);
		});
	});
});
