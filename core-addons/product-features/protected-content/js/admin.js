;(function ( $, window, document, undefined ) {
	$( function() {
		/*
		$('.edit-protected-content-setting').click(function(event) {
			event.preventDefault();
			var $this = $(this);
			var id = $this.attr('id');
			$('#it_exchange_protected_content_ul_dialog').hide();
			$('#it_exchange_protected_content_who_dialog').show();
		});
		*/

		function itExchangeProtectedContentProtectPageOnchange() {
			var selected = $(this).find(":selected").val();

			if ( '1' === selected ) { 
				$("#it_exchange_protected_content_addon_select_post_restrictions").removeClass('hide-if-js');
			} else {
				$("#it_exchange_protected_content_addon_select_post_restrictions").addClass('hide-if-js');
			}   
		}   
		$('#it_exchange_protected_content_addon_post_is_protected').change(itExchangeProtectedContentProtectPageOnchange).triggerHandler("change");

		var myoptions = { "selectorsClass" : "it-pc-selector", "groupsClass" : "it-pc-group" };
		$("#it_exchange_protected_content_container").iThemesNestedFormToggles(myoptions);

		$(".it_exchange_protected_content_select_products_link").click(function(event) {
			event.preventDefault();
			$(".it_exchange_protected_content_select_products_link").removeClass('current');
			$(this).addClass('current');

			value = $(this).data('value');
			$("#it_exchange_protected_content_select_products").data('dependant-classes', 'it_exchange_protected_content_' +  value + '_products').change();
		});
	});
})( jQuery, window, document );
