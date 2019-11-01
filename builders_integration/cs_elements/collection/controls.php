<?php

/**
 * Element Controls
 */
 

/* FIELDS */
$fields =  array(
	'cid' => array(
		'type'    => 'select',
		'ui' => array(
			'title'   => __('Collection', 'ag_ml'),
			'tooltip' => '',
		),
		'options' => array(
			'choices' => $GLOBALS['ag_colls_arr']
		),
	),
	
	'filter' => array(
		'type'    => 'toggle',
		'ui' => array(
			'title'   => __('Allow Filters?', 'ag_ml'),
			'tooltip' => __('Allow galleries filtering by category', 'ag_ml'),
		),
	),

	'random' => array(
		'type'    => 'toggle',
		'ui' => array(
			'title'   => __('Random display?', 'ag_ml'),
			'tooltip' => __('Display images randomly', 'ag_ml'),
		),
	),

);



///// OVERLAY MANAGER ADD-ON ///////////
if(isset($GLOBALS['agom_cs_field'])) {
	$fields['overlay'] = $GLOBALS['agom_cs_field'];
}
////////////////////////////////////////


return $fields;
