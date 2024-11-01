<?php

/**
 * Apply a fill pattern made up of two colored stripes (lines) into your text.
 *
 * @var color $color1 (default:#000000) The first color
 * @var string $width1 (default:6) The first width
 * @var color $color2 (default:#ffffff) The second color
 * @var string $width2 (default:2) The second width
 * @var string $tilt (default:0) Angle in degrees for rotation
 *
 * @package slate
 * @subpackage core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die( '-1' ); // don't load directly ?>

<pattern
	id="<?php echo $_id ?>"
	x="0"
	y="0"
	width="<?php echo $width1 + $width2; ?>"
	height="<?php echo $width1 + $width2; ?>"
	patternTransform="rotate(<?php echo $tilt; ?>)"
	patternUnits="userSpaceOnUse">

	<rect
		x="0"
		y="0"
		width="<?php echo $width1 + $width2; ?>"
		height="<?php echo $width1; ?>"
		fill="<?php echo $color1; ?>"
		stroke-width="0" />

	<rect
		x="0"
		y="<?php echo $width1; ?>"
		width="<?php echo $width1 + $width2; ?>"
		height="<?php echo $width1 + $width2; ?>"
		fill="<?php echo $color2; ?>"
		stroke-width="0" />

</pattern>