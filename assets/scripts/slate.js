/**
 * This script takes care of all frontend issues:
 * - Detects browser SVG support and handles fallbacks.
 * - Declares the webfonts to the WebFont.js loader.
 * - Resizes all SVG.slate tags by text size.
 *
 * Note:
 * This script expects `webFontConfig` to be set from the PHP plugin code.
 * `webFontConfig` is an array of all fonts used by SVG text in the currently
 * rendered page. It is passed from the PHP plugin code into this script.
 */

(function($) {
	'use strict';

	/**
	 * Modernizr test
	 */
	var div = document.createElement( 'div' );
	div.innerHTML = '<svg />';
	if ( (div.firstChild && div.firstChild.namespaceURI) == 'http://www.w3.org/2000/svg' && !$( 'body' ).hasClass('slate-customizer') ) {
		$( 'html' ).addClass( 'svg' );
	} else {
		$( 'html' ).addClass( 'no-svg' );
	}

	/**
	 * Resizes all elements rendered by this plugin according to the size of
	 * their text.
	 *
	 * To be called with $.forEach() after the fonts are loaded since the final
	 * text size in pixels depends on the font family.
	 */
	var resizeSvg = function() {
		var $body = $('body');

		$( 'html.svg .slate.svg:not(.slate-resized) > svg' ).each(
			function(i, el) {
				var $el = $( el );

				$el.parent( '.slate.svg' ).addClass( 'slate-resized' );

				var $cropThis = $( '.slate-crop-this', el );

				if ( $cropThis.length ) {
					var bbox, w, h;
					var cropThis = $cropThis.get( 0 );

					try {
						bbox = cropThis.getBBox();
						w = bbox.width || $cropThis.width();
						h =	bbox.height || $cropThis.height();
					} catch ( e ) {
						// https://bugzilla.mozilla.org/show_bug.cgi?id=612118

						var $parent = $el.parent();
						$el.appendTo( $body );
						bbox = cropThis.getBBox();
						w = bbox.width || $cropThis.width();
						h =	bbox.height || $cropThis.height();
						$el.appendTo( $parent );
					}

					var viewBox = $cropThis.attr( 'viewBox' );
					var match;

					if ( typeof (viewBox) !== 'undefined' && viewBox ) {
						match = viewBox.match( /0 0 (\d+) (\d+)/ );
					}

					if ( typeof (match) === 'undefined' || match &&
						(match[ 1 ] != w || match[ 2 ] != h) ) {

						el.setAttribute( 'viewBox', [ 0, 0, w, h ].join( ' ' ) );

						el.setAttribute( 'width', w );
						el.setAttribute( 'height', h );

					}
				}
			} );
		};

	/**
	 * Declare the webfonts to the WebFont.js loader.
	 */
	if ( 'undefined' !== typeof (webFontConfig) ) {
		webFontConfig.active = webFontConfig.inactive = resizeSvg;
		WebFont.load( webFontConfig );
	}

	/**
	 * Also do some resizing on ready.
	 */
	$( resizeSvg );

})( jQuery );
