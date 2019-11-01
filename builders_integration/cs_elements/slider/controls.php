<?php

/**
 * Element Controls
 */
 

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

	'width' => array(
		'type'    => 'number',
		'ui' => array(
			'title'   => __('Width', 'ag_ml'),
			'tooltip' => __("Define slider's width", 'ag_ml'),
		),
	),
	'width_unit' => array(
		'type'    => 'select',
		'ui' => array(
			'title'   => __('Width Unit', 'ag_ml'),
			'tooltip' => '',
		),
		'options' => array(
			'choices' => array(
				array('value' => '%', 	'label' => '%'),
				array('value' => 'px', 	'label' => 'px'),
			)
		),
	),

	'height' => array(
		'type'    => 'number',
		'ui' => array(
			'title'   => __('Height', 'ag_ml'),
			'tooltip' => __("Define slider's height (percentage is related to width)", 'ag_ml'),
		),
	),
	'height_unit' => array(
		'type'    => 'select',
		'ui' => array(
			'title'   => __('Height Unit', 'ag_ml'),
			'tooltip' => '',
		),
		'options' => array(
			'choices' => array(
				array('value' => '%', 	'label' => '%'),
				array('value' => 'px', 	'label' => 'px'),
			)
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



return $fields;
