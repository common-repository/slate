<?php

/**
 * Apply a three color radial fill into your text.
 *
 * @var color  $color1 (default:#ff0000) The first color
 * @var color  $color2 (default:#00ff00) The second color
 * @var color  $color3 (default:#0000ff) The third color
 * @var string $radius (default:100%) Radius of radial fill. Can be a integer value of pixels or a percentage (e.g. 10 or 10%).
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
	r="<?php echo $radius ?>"
	spreadMethod="reflect">

	<stop
		offset="0%"
		stop-color="<?php echo $color1; ?>"
		stop-opacity="1" />

	<stop
		offset="50%"
		stop-color="<?php echo $color2; ?>"
		stop-opacity="1" />

	<stop
		offset="100%"
		stop-color="<?php echo $color3; ?>"
		stop-opacity="1" />

</radialGradient>
