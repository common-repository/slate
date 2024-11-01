<?php
if ( defined('WP_UNINSTALL_PLUGIN' ) ) {
	delete_option( 'slate-opt-plugin-version' );
	delete_option( 'slate-opt-cache' );
	delete_option( 'slate-popup-35824' );
	delete_option( 'slate-popup-vc' );
	delete_option( 'slate-popup-raster' );
	delete_option( 'slate-popup-titles' );
	delete_option( 'slate-popup-shapes' );
}
