<?php
/**
 * Apply a shadow filter on your text.
 *
 * @var string $radius (default:0) The glow's radius in pixels
 * @var integer $xoff (default:0) The glow's offset on the x axis
 * @var integer $yoff (default:0) The glow's offset on the y axis
 *
 * @package slate
 * @subpackage core
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || die( '-1' ); // don't load directly

$radius = isset( $radius ) ? intval( $radius ) : 0;
$xoff = isset( $xoff ) ? intval( $xoff ) : 0;
$yoff = isset( $yoff ) ? intval( $yoff ) : 0;

?>
<feGaussianBlur in="SourceAlpha"
	stdDeviation="<?php echo "$radius $radius"; ?>" />
<feOffset dx="<?php echo $xoff; ?>" dy="<?php echo $yoff; ?>"
	result="<?php echo $_result; ?>" />