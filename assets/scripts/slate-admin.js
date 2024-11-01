(function($) {
	'use strict';
	$( function() {

		$( '#slate-widget-tabs' ).tabs();

		$( '.slate-notice' ).on( 'click', '.notice-dismiss', function( event, el ) {

			var $notice = $(this).parent('.notice.is-dismissible');
			var dismiss_url = $notice.attr('data-dismiss-url');

			$.get( dismiss_url );
		});

	} );
})( jQuery );
