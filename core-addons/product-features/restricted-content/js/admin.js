;(function ( $, window, document, undefined ) {
	$( function() {

    function itCartBuddyRestrictedContentProtectPageOnchange() {
        var selected = $(this).find(":selected").val();

        if ( '1' === selected ) { 
            $("#it_cart_buddy_restricted_content_addon_select_post_restrictions").removeClass('hide-if-js');
		} else {
            $("#it_cart_buddy_restricted_content_addon_select_post_restrictions").addClass('hide-if-js');
        }   
    }   
    $('#it_cart_buddy_restricted_content_addon_post_is_protected').change(itCartBuddyRestrictedContentProtectPageOnchange).triggerHandler("change");
	});
})( jQuery, window, document );
