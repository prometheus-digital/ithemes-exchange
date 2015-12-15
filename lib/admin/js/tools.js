jQuery(document).ready(function ($) {

	$(".upgrade-row button").click(function (e) {

		var $row = $(this).closest( '.upgrade-row' );

		$row.addClass('upgrading');
	});

	$(".upgrade-row .upgrade-progress a").click(function (e) {
		e.preventDefault();

		var $row = $(this).closest( '.upgrade-row' );

		if ( ! $row.hasClass( 'show-feedback' ) ) {
			$row.addClass( 'show-feedback' );
			$(this).text( 'Hide Details' );
		} else {
			$row.removeClass( 'show-feedback' );
			$(this).text( 'Show Details' );
		}
	});
});