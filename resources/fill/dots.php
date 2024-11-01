<?php

/**
 * Apply a nice dotted fill into your text.
 *
 * @var color $color (default:#000000) The dot pattern color
 * @var color $bgcolor (default:#ffffff) The background color
 * @var string $radius (default:5) The dot radius
 * @var string $distance (default:20) The dot distance
 * @var string $tilt (default:0) Angle in degrees for rotation
 *
 * @package slate
 * @subpackage core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die( '-1' ); // don't load directly

$a = $distance * cos( M_PI / 4 ); ?>

<pattern
	id="<?php echo $_id ?>"
	x="0"
	y="0"
	width="<?php echo 4 * $a; ?>"
	height="<?php echo 4 * $a; ?>"
	patternUnits="userSpaceOnUse"
	patternTransform="rotate(<?php echo $tilt; ?>)">

	<rect
		x="0"
		y="0"
		width="<?php echo 4 * $a; ?>"
		height="<?php echo 4 * $a; ?>"
		fill="<?php echo $bgcolor; ?>"
		stroke-width="0" />


	<circle
		cx="<?php echo $a; ?>"
		cy="<?php echo $a; ?>"
		r="<?php echo $radius; ?>"
		style="stroke: none;"
		fill="<?php echo $color; ?>" />

	<circle
		cx="<?php echo 3 * $a; ?>"
		cy="<?php echo 3 * $a; ?>"
		r="<?php echo $radius; ?>"
		style="stroke: none;"
		fill="<?php echo $color; ?>" />

</pattern>