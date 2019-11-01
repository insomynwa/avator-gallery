<?php

/**
 * Shortcode handler
 */

if(!isset($id)) 		{$id = '';}
if(!isset($class)) 		{$class = '';}
if(!isset($style)) 		{$style = '';}

cs_atts( array('id' => $id, 'class' => $class, 'style' => $style ) );

$atts = array(
	'gid' => $gid,
	'width' => (int)$width.$width_unit,
	'height' => (int)$height.$height_unit, 
	'random' => $random,
	'watermark' => $watermark,
	'autoplay' => $autoplay,
);

$params = '';
foreach($atts as $key => $val) {
	$params .= ' '. $key .'="'. esc_attr($val) .'"';
}

echo do_shortcode('[g-slider '. $params .']');
