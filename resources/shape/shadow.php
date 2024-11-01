<?php
/**
 * Text with a patterned shadow.
 *
 * @var color $color (default:gray) The color of the text front
 * @var integer $xoff (default:10) The offset of the patterned shadow on the x axis
 * @var integer $yoff (default:10) The offset of the patterned shadow on the y axis
 * @var string $bleed_percent (default:100) Enter a percentage. Width and height will be extended by this percentage in each direction. This will allow shadows and other filters to "bleed" around the text's margin and into the surrounding area. A value of 100 should be sufficient.
 *
 * @package slate
 * @subpackage core
 * @since 1.3.0
 */

defined( 'ABSPATH' ) || die( '-1' ); // don't load directly

$bleed_percent = abs( intval( $bleed_percent ) ); ?>

<defs>
	<filter
		id="<?php echo $_filters_id; ?>"
		x="-<?php echo $bleed_percent; ?>%"
		y="-<?php echo $bleed_percent; ?>%"
		width="<?php echo 100 + 2*$bleed_percent; ?>%"
		height="<?php echo 100 + 2*$bleed_percent; ?>%"
		filterUnits="objectBoundingBox">
		<?php echo $_filters; ?>
	</filter>
	<?php echo $_fill; ?>
</defs>

<g
	filter="url(#<?php echo $_filters_id ?>)">

	<text
		y="100%"
		stroke="none"
		dx="<?php echo $xoff ?>"
		dy="<?php echo $yoff ?>"
		fill="url(#<?php echo $_fill_id; ?>)">
		<?php echo $_text_tspan; ?>
	</text>

	<text
		y="100%"
		fill="<?php echo $color; ?>">
		<?php echo $_text_tspan; ?>
	</text>

</g>