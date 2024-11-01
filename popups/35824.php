<?php defined( 'ABSPATH' ) || die( '-1' ); // don't load directly ?>

<div
	class="notice slate-notice notice-warn is-dismissible"
	data-dismiss-url="<?php echo esc_url( $dismiss_url ); ?>">

	<h2><?php echo 'SVG Logo and Text Effects 1.3.1 notice'; ?></h2>

	<p>There is <a href="https://core.trac.wordpress.org/ticket/35824">a known bug</a>
	in WordPress versions earlier than 4.7,
	where <strong>the WordPress Customizer cannot correctly show SVG content.</strong></p>

	<p><strong>It is recommended that you <a href="<?php echo admin_url("/update-core.php"); ?>">upgrade to a version of WordPress greater than or equal to 4.7</a>, once it becomes available. In version 4.7 this bug is fixed.</strong></p>

	<p>Until you upgrade, when viewing the customizer preview,
	the SLATE plugin will show you a raster or HTML fallback instead.</p>

	<p>SVG fallbacks do not always show exactly the same as real SVG.
	To make sure that the end result is exactly what you want it to be,
	click the <emph>Save &amp; Publish</emph> button,
	then double-check your page without the customizer.</p>

</div>
