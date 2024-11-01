/**
 * Adds a button to the wordpress TinyMCE editor
 * that lets you insert SVG shortcode presets.
 */
(function($) {


	// create a button for the TinyMCE editor
	tinymce.create( 'tinymce.plugins.slate', {
		init: function(ed, url) {
			ed.addButton( 'slate', {
				title: 'SVG Logo and Text Effects',
				image: url + '/../sprites/logo-32x32.png',
				onclick: function() {

					var homeUrl = window.location.href.substring(
						0, window.location.href.indexOf('/wp-admin') );

					$.ajax( homeUrl, {
						data: { '__slate_presets' : 1 },
						dataType: 'json',
						success: function( data ) {
							var values = [];
							for ( var i in data ) {
								values.push( { text: i, value: data[i] } );
							}

							ed.windowManager.open( {
								title: 'SVG Logo and Text Effects',
								body: [	{
									type: 'listbox',
									name: 'preset',
									label: 'Insert SVG shortcode:',
									values: values
								} ],
								onsubmit: function(e) {
									ed.insertContent( e.data.preset );
								}
							} );

						},
						error: function( jqXHR, textStatus, errorThrown ) {
							ed.windowManager.alert( 'Could not get presets from server: ' + errorThrown );
						}
					} );


				}
			} );

		},
		getInfo: function() {
			return {
				longname: 'SVG Logo and Text Effects',
				author: 'Dashed-Slug',
				authorurl: 'https://dashed-slug.net/',
				infourl: 'https://www.dashed-slug.net/svg-logo-and-text-effects-wordpress-plugin/',
				version: '1.3.1'
			};
		}
	} );

	// add the button to TinyMCE
	tinymce.PluginManager.add( 'slate', tinymce.plugins.slate );

})( jQuery );