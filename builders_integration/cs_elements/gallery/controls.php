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

	'filters' => array(
		'type'    => 'toggle',
		'ui' => array(
			'title'   => __('Use tags filter?', 'ag_ml'),
			'tooltip' => '',
		),
	),


	'pagination' => array(
		'type'    => 'select',
		'ui' => array(
			'title'   => __('Pagination System', 'ag_ml'),
			'tooltip' => '',
		),
		'options' => array(
			'choices' => array(
				array('value' => '', 			'label' => __('Auto - follow global settings', 'ag_ml')),
				array('value' => 'standard', 	'label' => __('Standard', 'ag_ml')),
				array('value' => 'inf_scroll',	'label' => __('Infinite scroll', 'ag_ml')),
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
