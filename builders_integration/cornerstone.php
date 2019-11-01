<?php

add_action('cornerstone_register_elements', 'ag_cornerstone_register_elements');
add_filter('cornerstone_icon_map', 'ag_cornerstone_icon_map', 900);


function ag_cornerstone_register_elements() {
	include_once(AG_DIR .'/admin_menu.php'); // be sure tax are registered
	register_cpt_ag_gallery();
	register_taxonomy_ag_collections();
	

	// galleries array
	$args = array(
		'post_type' => 'ag_galleries',
		'numberposts' => -1,
		'post_status' => 'publish'
	);
	$galleries = get_posts($args);
	
	$GLOBALS['ag_galls_arr'] = array(); 
	foreach($galleries as $gallery) {
    	$GLOBALS['ag_galls_arr'][] = array(
			'value' => $gallery->ID,
			'label' => $gallery->post_title
		);
    }
	
	
	// collections array array (use full list for now)
	$collections = get_terms('ag_collections', 'hide_empty=0');
	
	$GLOBALS['ag_colls_arr'] = array(); 
	foreach($collections as $collection) {
    	$GLOBALS['ag_colls_arr'][] = array(
			'value' => $collection->term_id,
			'label' => $collection->name
		);
    }
	
	
	///// OVERLAY MANAGER ADD-ON ///////////
	if(defined('AGOM_DIR')) {
		register_taxonomy_agom(); // be sure tax are registered
		$overlays = get_terms('agom_overlays', 'hide_empty=0');
		
		$ol_arr = array(
			0 => array(
				'value' => '',
				'label' => __('default one', 'mg_ml')
			)
		);
		foreach($overlays as $ol) {
			$ol_arr[] = array(
				'value' => $ol->term_id,
				'label' => $ol->name
			);
		}
		
		$GLOBALS['agom_cs_field'] = array(
			'type'    => 'select',
			'ui' => array(
				'title'   => __('Custom Overlay', 'ag_ml'),
				'tooltip' => '',
			),
			'options' => array(
				'choices' => $ol_arr
			),
		);
	}


	///////////////////////////////////////////////////////////
	
	
	cornerstone_register_element('lcweb_ag_gallery', 		'lcweb_ag_gallery', 	AG_DIR .'/builders_integration/cs_elements/gallery');
	cornerstone_register_element('lcweb_ag_collection', 	'lcweb_ag_collection', 	AG_DIR .'/builders_integration/cs_elements/collection');
	cornerstone_register_element('lcweb_ag_slider', 		'lcweb_ag_slider', 		AG_DIR .'/builders_integration/cs_elements/slider');
	cornerstone_register_element('lcweb_ag_carousel', 		'lcweb_ag_carousel', 	AG_DIR .'/builders_integration/cs_elements/carousel');
}


function ag_cornerstone_icon_map( $icon_map ) {
	$icon_map['lcweb_ag_gallery'] 		= AG_URL .'/img/cs_icon.svg';
	$icon_map['lcweb_ag_collection'] 	= AG_URL .'/img/cs_icon.svg';
	$icon_map['lcweb_ag_slider'] 		= AG_URL .'/img/cs_icon.svg';
	$icon_map['lcweb_ag_carousel'] 		= AG_URL .'/img/cs_icon.svg';
	
	return $icon_map;
}
