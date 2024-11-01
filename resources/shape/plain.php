<?php
/**
 * Plain old vector text.
 *
 * @var string $bleed_percent (default:100) Enter a percentage. Width and height will be extended by this percentage in each direction. This will allow shadows and other filters to "bleed" around the text's margin and into the surrounding area. A value of 100 should be sufficient.
 *
 * @package slate
 * @subpackage core
 * @since 1.0.0
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

<text
	y="100%"
	fill="url(#<?php echo $_fill_id; ?>)"
	filter="url(#<?php echo $_filters_id ?>)"

><?php echo $_text_tspan; ?></text>
