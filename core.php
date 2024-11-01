<?php defined( 'ABSPATH' ) || die( '-1' ); // don't load directly


/**
 * This is the "core" built-in plugin that provides some basic resources.
 *
 * @package slate
 * @subpackage core
 * @since 1.0.0
 */

if ( !function_exists( 'slate_core_shapes' ) ) {
	function slate_core_shapes( $file_names ) {
		$file_names[] = realpath( __DIR__ . '/resources/shape/' );
		return $file_names;
	}

	add_filter( 'slate_shape_files', 'slate_core_shapes' );
}

if ( !function_exists( 'slate_core_fills' ) ) {
	function slate_core_fills( $file_names ) {
		$file_names[] = realpath( __DIR__ . '/resources/fill/' );
		return $file_names;
	}

	add_filter( 'slate_fill_files', 'slate_core_fills' );
}

if ( !function_exists( 'slate_core_filters' ) ) {
	function slate_core_filters( $file_names ) {
		$file_names[] = realpath( __DIR__ . '/resources/filter/' );
		return $file_names;
	}

	add_filter( 'slate_filter_files', 'slate_core_filters' );
}

if ( !function_exists( 'slate_core_fonts' ) ) {
	function slate_core_fonts( $fonts ) {
		foreach ( file(
			__DIR__ . '/resources/font/google-font-families.txt',
			FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $font_family ) {
			$fonts[ $font_family ] = array( 'source' => 'google' );
		}
		return $fonts;
	}

	add_filter( 'slate_font_files_or_urls', 'slate_core_fonts' );
}

if ( !function_exists( 'slate_core_presets' ) ) {
	function slate_core_presets( $presets ) {
		$presets['Motel'] = '[slate_plain _font_family="Krona One" _font_size="36pt" _stroke="#eeee22" _stroke_width="2px" fill="gradient_linear" fill_gradient_linear_color1="#dd3333" fill_gradient_linear_x1="0%" fill_gradient_linear_y1="50%" fill_gradient_linear_color2="#dd7d33" fill_gradient_linear_x2="100%" fill_gradient_linear_y2="50%" filter_glow_radius="5" filter_glow_color="#eeee22" filter_shadow_radius="2" filter_shadow_xoff="2" filter_shadow_yoff="2"]M O T E L[/slate_plain]';
		$presets['IBN'] = '[slate_plain _font_family="Bevan" _font_size="36pt" _stroke_width="0" fill="stripes" fill_stripes_color1="#ffffff" fill_stripes_width1="3" fill_stripes_color2="#3f3fff" fill_stripes_width2="3"]IBN[/slate_plain]';
		$presets['Noir Detective'] = '[slate_plain _font_family="Limelight" _font_size="36pt" _stroke="#000000" _stroke_width="0px" fill="stripes" fill_stripes_color1="#141414" fill_stripes_color2="#bfbfbf"]NOIR DETECTIVE[/slate_plain]';

		$presets['Cow'] = '[slate_plain _font_family="Salsa" _font_size="36pt" _stroke="#000000" fill="dots" fill_dots_radius="6" fill_dots_distance="10" filter_shadow_radius="3" filter_shadow_xoff="5" filter_shadow_yoff="5"]Cows are cool...[/slate_plain]';
		$presets['Giraffe'] = '[slate_plain _font_family="Crafty Girls" _font_size="36pt" _stroke="#452c0e" fill="dots" fill_dots_color="#452c0e" fill_dots_bgcolor="#e0bc8a" fill_dots_radius="10" fill_dots_distance="10" filter_glow_radius="5" filter_glow_color="#442907"]...so are Giraffes![/slate_plain]';
		$presets['Harlequin'] = '[slate_plain _font_family="Lusitana" _font_size="46pt" _stroke="#ffffff" _stroke_width="0px" fill="squares" fill_squares_bgcolor="#dd3333" fill_squares_side="5" fill_squares_tilt="35" filter_glow_radius="13" filter_glow_color="#ff0000"]HARLEQUIN[/slate_plain]';
		$presets['Way up in the clouds'] = '[slate_plain _font_family="Love Ya Like A Sister" _font_size="36pt" _stroke="#0000ff" _stroke_width="0.4" fill="gradient_linear" fill_gradient_linear_color1="#f4f4ff" fill_gradient_linear_x1="30%" fill_gradient_linear_y1="20%" fill_gradient_linear_color2="#a5a5ff" fill_gradient_linear_x2="70%" filter_glow_radius="5" filter_shadow_radius="15" filter_shadow_xoff="10" filter_shadow_yoff="20"]Way up in the clouds[/slate_plain]';
		$presets['Flower Power'] = '[slate_plain _font_family="Sansita One" _font_size="36pt" _stroke="#dd3333" fill="gradient_radial_3" fill_gradient_radial_3_radius="10%" filter_glow_radius="2" filter_glow_color="#ff00fa"]Flower Power[/slate_plain]';
		$presets['Shadow: Patterned Shadow 1'] = '[slate_shadow _font_family="Modern Antiqua" _font_size="72pt" _stroke="#dd3333" color="#910000" xoff="6" yoff="6" fill="stripes" fill_stripes_color1="#a5a5a5" fill_stripes_width1="3" fill_stripes_color2="rgba(0,0,0,0.01)" fill_stripes_tilt="-40"]Patterned Shadow 1[/slate_shadow]';
		$presets['Shadow: Patterned Shadow 2'] = '[slate_shadow _font_family="Merriweather Sans" _font_size="72pt" _stroke="#dd3333" color="#910000" xoff="10" yoff="10" fill="dots" fill_dots_color="#eeee22" fill_dots_bgcolor="#c8ace2" fill_dots_radius="3" fill_dots_distance="5" fill_dots_tilt="30"]Patterned Shadow 2[/slate_shadow]';
		return $presets;
	}

	add_filter( 'slate_presets', 'slate_core_presets' );
}