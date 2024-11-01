<?php

/**
 * Apply a one color radial fill into your text. The color is opaque at the center
 * and expands to transparent outwards.
 *
 * @var color $color (default:#00ffff) The color
 * @var string $xcenter (default:50%) Horizontal position of gradient center. Can be a integer value of pixels or a percentage (e.g. 50 or 50%).
 * @var string $ycenter (default:50%) Vertical position of gradient center. Can be a integer value of pixels or a percentage (e.g. 50 or 50%).
 *
 * @package slate
 * @subpackage core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die( '-1' ); // don't load directly ?>

<radialGradient
	xmlns="http://www.w3.org/2000/svg"
	id="<?php echo $_id; ?>"
	fx="<?php echo $xcenter; ?>"
	fy="<?php echo $ycenter; ?>"
	r="100%"
	spreadMethod="reflect">

	<stop
		offset="0%"
		stop-color="<?php echo $color; ?>"
		stop-opacity="1" />

	<stop
		offset="100%"
		stop-color="<?php echo $color; ?>"
		stop-opacity="0" />

</radialGradient>
