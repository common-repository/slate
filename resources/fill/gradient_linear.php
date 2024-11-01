<?php

/**
 * Apply a linear gradient fill into your text.
 *
 * @var color $color1 (default:#000000) The first color
 * @var string $x1 (default:50%) The horizontal position of the first color
 * @var string $y1 (default:0%) The vertical position of the first color
 * @var color $color2 (default:#ffffff) The second color
 * @var string $x2 (default:50%) The horizontal position of the second color
 * @var string $y2 (default:100%) The vertical position of the second color
 *
 * @package slate
 * @subpackage core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die( '-1' ); // don't load directly ?>

<linearGradient
	id="<?php echo $_id; ?>"
	x1="<?php echo $x1; ?>"
	y1="<?php echo $y1; ?>"
	x2="<?php echo $x2; ?>"
	y2="<?php echo $y2; ?>">

	<stop offset="0%" stop-color="<?php echo $color1; ?>" />

	<stop offset="100%" stop-color="<?php echo $color2; ?>" />

</linearGradient>