<?php

/**
 * Element Controls
 */


// image rows
$img_rows = array();
for($a=1; $a<=10; $a++) {
	$img_rows[] = array('value' => $a, 'label' => $a);	 
} 
 
 

/* FIELDS */
$fields =  array(
	'gid' => array(
		'type'    => 'select',
		'ui' => array(
			'title'   => __('Gallery', 'ag_ml'),
			'tooltip' => '',
		),
		'options' => array(
			'choices' => $GLOBALS['ag_galls_arr']
		),
	),
	
	'img_max_w' => array(
		'type'    => 'number',
		'ui' => array(
			'title'   => __('Images max width', 'mg_ml'),
			'tooltip' => ''
		),
	),
	
	'height' => array(
		'type'    => 'number',
		'ui' => array(
			'title'   => __('Images Height (in pixels)', 'mg_ml'),
			'tooltip' => __("Carousel images height in pixels", 'mg_ml')
		),
	),
	
	'h_type' => array(
		'type'    => 'select',
		'ui' => array(
			'title'   => __('Height type', 'ag_ml'),
			'tooltip' => '',
		),
		'options' => array(
			'choices' => array(
				array('value' => 'px', 'label' => 'px'),
				array('value' => '%', 'label' => '%'),
			)
		),
	),
	
	'rows' => array(
		'type'    => 'select',
		'ui' => array(
			'title'   => __('Rows', 'ag_ml'),
			'tooltip' => __("Choose how many image rows to use", 'mg_ml'),
		),
		'options' => array(
			'choices' => $img_rows
		),
	),
	
	'multiscroll' => array(
		'type'    => 'toggle',
		'ui' => array(
			'title'   => __('Multiple Scroll?', 'ag_ml'),
			'tooltip' => __('Slides multiple images per time', 'ag_ml'),
		),
	),
	
	'center' => array(
		'type'    => 'toggle',
		'ui' => array(
			'title'   => __('Center mode?', 'ag_ml'),
			'tooltip' => __('Enables center display mode', 'ag_ml')
		),
	),
	
	'nocrop' => array(
		'type'    => 'toggle',
		'ui' => array(
			'title'   => __('Avoid images crop?', 'ag_ml'),
			'tooltip' => __('Just downscales images', 'ag_ml')
		),
	),
	
	'random' => array(
		'type'    => 'toggle',
		'ui' => array(
			'title'   => __('Random display?', 'ag_ml'),
			'tooltip' => __('Display images randomly', 'ag_ml'),
		),
	),
	
	'watermark' => array(
		'type'    => 'toggle',
		'ui' => array(
			'title'   => __('Use Watermark?', 'ag_ml'),
			'tooltip' => __('Apply watermark to images (if available)', 'ag_ml'),
		),
	),

	'autoplay' => array(
		'type'    => 'select',
		'ui' => array(
			'title'   => __('Autoplay', 'ag_ml'),
			'tooltip' => '',
		),
		'options' => array(
			'choices' => array(
				array('value' => 'auto',	'label' => __('(as default)', 'ag_ml')),
				array('value' => 1, 		'label' => __('Yes', 'ag_ml')),
				array('value' => 0,			'label' => __('No', 'ag_ml')),
			)
		),
	),
);



///// OVERLAY MANAGER ADD-ON ///////////
if(isset($GLOBALS['agom_cs_field'])) {
	$fields['overlay'] = $GLOBALS['agom_cs_field'];
}
////////////////////////////////////////


return $fields;
