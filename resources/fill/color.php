<?php

/**
 * Apply a simple color fill into your text.
 *
 * @var color $color (default:#ffffff) The color
 *
 * @package slate
 * @subpackage core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die( '-1' ); // don't load directly ?>

<pattern
	id="<?php echo $_id; ?>"
	x="0"
	y="0"
	width="1"
	height="1"
	patternUnits="userSpaceOnUse">

		<rect
			x="0"
			y="0"
			width="1"
			height="1"
			fill="<?php echo $color; ?>"
			stroke-width="0"></rect>

</pattern>