<?php

/**
 * Apply a glow filter around your text
 *
 * @var integer $radius (default:0) The glow's radius in pixels
 * @var integer $radiusmax (default:0) The glow's maximum radius in pixels (for animation)
 * @var float $duration (default:0) Duration in seconds (for animation)
 * @var color $color (default:#ff0000) The glow's color in hex rgb
 *
 * @package slate
 * @subpackage core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die( '-1' ); // don't load directly

$radius = floatval( $radius );
$a = 1;
if ( preg_match( '/^#?([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})$/', $color, $match ) ) :
	$r = hexdec( $match[1] ) / 256;
	$g = hexdec( $match[2] ) / 256;
	$b = hexdec( $match[3] ) / 256;

elseif ( preg_match(
	'/rgba?\s*\(\s*([\d\.]+)\s*,\s*([\d\.]+)\s*,\s*([\d\.]+)\s*(,\s*([\d\.]+))?\s*\)/',
	$color,
	$match ) ) :

	$r = intval( $match[1] ) / 256;
	$g = intval( $match[2] ) / 256;
	$b = intval( $match[3] ) / 256;

	if ( isset( $match[5] ) ) :
		$a = floatval( $match[5] );
	endif;
endif;

if ( isset( $r ) ) :

	?>

<feColorMatrix
	type="matrix"
	in="SourceGraphic"
	values="
	0 0 0 0 <?php echo $r; ?>
	0 0 0 0 <?php echo $g; ?>
	0 0 0 0 <?php echo $b; ?>
	0 0 0 <?php echo $a; ?> 0" />


<feGaussianBlur
	stdDeviation="<?php echo "$radius $radius"; ?>"
	result="<?php echo $_result; ?>" >

	<?php if ($duration) : ?>

	<animate
		attributeName="stdDeviation"
		values="<?php echo "$radius $radius;$radiusmax $radiusmax;$radius $radius"; ?>"
		dur="<?php echo "${duration}s"; ?>"
		repeatCount="indefinite" />

	<?php endif; ?>
</feGaussianBlur>

<?php

endif;

?>