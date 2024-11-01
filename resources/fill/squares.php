<?php

/**
 * Apply a fill pattern made up of colored squares into your text.
 *
 * @var color $color (default:#000000) The square pattern color
 * @var color $bgcolor (default:#ffffff) The background color
 * @var string $side (default:8) The square's side
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
	width="<?php echo $side * 2; ?>"
	height="<?php echo $side * 2; ?>"
	patternUnits="userSpaceOnUse">

	<rect
		x="0"
		y="0"
		width="<?php echo $side*2; ?>"
		height="<?php echo $side*2; ?>"
		fill="<?php echo $bgcolor; ?>"
		stroke-width="0" />

	<rect
		transform="rotate(<?php echo "$tilt $side $side"; ?>)"
		x="<?php echo $side; ?>"
		y="<?php echo $side; ?>"
		width="<?php echo $side*2; ?>"
		height="<?php echo $side*2; ?>"
		style="stroke: none;"
		fill="<?php echo $color; ?>" />

	<rect
		transform="rotate(<?php echo "$tilt $side $side"; ?>)"
		x="<?php echo -$side; ?>"
		y="<?php echo -$side; ?>"
		width="<?php echo $side*2; ?>"
		height="<?php echo $side*2; ?>"
		style="stroke: none;"
		fill="<?php echo $color; ?>" />

</pattern>