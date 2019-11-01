<?php
// ARRAY CONTAINING OPTION VALUES TO SETUP PRESET STYLES


// preset style names
function ag_preset_style_names() {
	$ml_key = 'ag_ml';
	
	return array(
		'l_standard'	=> __("Light", $ml_key) .' - '. __('Standard', $ml_key),
		'l_minimal'		=> __("Light", $ml_key) .' - '. __('Minimal', $ml_key),
		'l_noborder'	=> __("Light", $ml_key) .' - '. __('No border', $ml_key),
		'l_photowall'	=> __("Light", $ml_key) .' - '. __('Photo wall', $ml_key),
		
		'd_standard'	=> __("Dark", $ml_key) .' - '. __('Standard', $ml_key),
		'd_minimal'		=> __("Dark", $ml_key) .' - '. __('Minimal', $ml_key),
		'd_noborder'	=> __("Dark", $ml_key) .' - '. __('No border', $ml_key),
		'd_photowall'	=> __("Dark", $ml_key) .' - '. __('Photo wall', $ml_key),		
	);			
}


// option values to apply
function ag_preset_styles_data($style = '') {
	$styles = array();
	
	
	/*** LIGHTS ***/
	$styles['l_standard'] = array(
		'ag_standard_hor_margin' => 5,
		'ag_standard_ver_margin' => 5,
		'ag_masonry_margin' => 7,
		'ag_photostring_margin' => 7,
		
		'ag_img_border' => 4,
		'ag_img_radius' => 4,
		'ag_img_shadow' => 'outshadow',
		'ag_img_border_color' => '#FFFFFF',
		
		'ag_main_ol_color' => '#ffffff',
		'ag_main_ol_opacity' => 80,
		'ag_main_ol_txt_color' => '#222222',
		'ag_sec_ol_color' => '#555555',
		'ag_icons_col' => '#fcfcfc',
		'ag_txt_u_title_color' => '#444444',
		'ag_txt_u_descr_color' => '#555555',
		
		'ag_filters_border_w' => 2,
		'ag_filters_radius' => 2,
		
		'ag_filters_txt_color' => '#666666', 
		'ag_filters_bg_color' => '#ffffff',
		'ag_filters_border_color' => '#bbbbbb', 
		'ag_filters_txt_color_h' => '#535353', 
		'ag_filters_bg_color_h' => '#fdfdfd', 
		'ag_filters_border_color_h' => '#777777',
		'ag_filters_txt_color_sel' => '#333333', 
		'ag_filters_bg_color_sel' => '#efefef', 
		'ag_filters_border_color_sel' => '#aaaaaa',
		
		'ag_search_txt_color' => '#666666', 
		'ag_search_bg_color' => '#ffffff',
		'ag_search_border_color' => '#bbbbbb',
		'ag_search_txt_color_h' => '#333333', 
		'ag_search_bg_color_h' => '#fdfdfd',
		'ag_search_border_color_h' => '#aaaaaa',
		
		'ag_pag_txt_col' => '#666666', 
		'ag_pag_bg_col' => '#ffffff',
		'ag_pag_border_col' => '#bbbbbb',
		'ag_pag_txt_col_h' => '#333333', 
		'ag_pag_bg_col_h' => '#efefef',
		'ag_pag_border_col_h' => '#aaaaaa',
	);
	
	
	$styles['l_minimal'] = ag_ps_override_indexes($styles['l_standard'], array(
		'ag_standard_hor_margin' => 6,
		'ag_standard_ver_margin' => 6,
		'ag_masonry_margin' => 8,
		'ag_photostring_margin' => 8,
		
		'ag_img_border' => 4,
		'ag_img_radius' => 1,
		'ag_img_shadow' => 'outline',
		'ag_img_outline_color' => '#bbbbbb', 
		'ag_img_border_color' => 'transparent',
		
		'ag_main_ol_color' => '#ffffff',
		'ag_main_ol_opacity' => 90,
		'ag_main_ol_txt_color' => '#222222',
		'ag_sec_ol_color' => '#555555',
		'ag_icons_col' => '#fefefe',
		'ag_txt_u_title_color' => '#444444',
		'ag_txt_u_descr_color' => '#555555',
		
		'ag_filters_border_w' => 1,
	));
	

	$styles['l_noborder'] = ag_ps_override_indexes($styles['l_standard'], array(
		'ag_standard_hor_margin' => 5,
		'ag_standard_ver_margin' => 5,
		'ag_masonry_margin' => 5,
		'ag_photostring_margin' => 5,
		
		'ag_img_border' => 0,
		'ag_img_radius' => 2,
		'ag_img_shadow' => 'outshadow',
		'ag_img_border_color' => '#FFFFFF',
		
		'ag_main_ol_color' => '#FFFFFF',
		'ag_main_ol_opacity' => 80,
		'ag_main_ol_txt_color' => '#222222',
		'ag_sec_ol_color' => '#555555',
		'ag_icons_col' => '#fcfcfc',
		'ag_txt_u_title_color' => '#444444',
		'ag_txt_u_descr_color' => '#555555',
		
		'ag_filters_border_w' => 0,
		
		'ag_filters_txt_color' => '#606060', 
		'ag_filters_bg_color' => '#f5f5f5',
		'ag_filters_txt_color_h' => '#4a4a4a', 
		'ag_filters_bg_color_h' => '#fafafa', 
		'ag_filters_txt_color_sel' => '#333333', 
		'ag_filters_bg_color_sel' => '#dfdfdf', 
		
		'ag_search_txt_color' => '#606060', 
		'ag_search_bg_color' => '#f5f5f5',
		'ag_search_txt_color_h' => '#333333', 
		'ag_search_bg_color_h' => '#eeeeee',
	));
	
	
	$styles['l_photowall'] = ag_ps_override_indexes($styles['l_noborder'], array(
		'ag_standard_hor_margin' => 0,
		'ag_standard_ver_margin' => 0,
		'ag_masonry_margin' => 0,
		'ag_photostring_margin' => 0,
		
		'ag_img_border' => 0,
		'ag_img_radius' => 0,
		'ag_img_shadow' => 'outshadow',
		'ag_img_border_color' => '#CCCCCC',

		'ag_main_ol_color' => '#FFFFFF',
		'ag_main_ol_opacity' => 80,
		'ag_main_ol_txt_color' => '#222222',
		'ag_sec_ol_color' => '#555555',
		'ag_icons_col' => '#fcfcfc',
		'ag_txt_u_title_color' => '#444444',
		'ag_txt_u_descr_color' => '#555555',
	));
	
	
	
	
	
	/*** DARKS ***/
	$styles['d_standard'] = array(
		'ag_standard_hor_margin' => 5,
		'ag_standard_ver_margin' => 5,
		'ag_masonry_margin' => 7,
		'ag_photostring_margin' => 7,
		
		'ag_img_border' => 4,
		'ag_img_radius' => 4,
		'ag_img_shadow' => 'outshadow',
		'ag_img_border_color' => '#888888',
		
		'ag_main_ol_color' => '#141414',
		'ag_main_ol_opacity' => 90,
		'ag_main_ol_txt_color' => '#ffffff',
		'ag_sec_ol_color' => '#bbbbbb',
		'ag_icons_col' => '#555555',
		'ag_txt_u_title_color' => '#fefefe',
		'ag_txt_u_descr_color' => '#f7f7f7',
		
		'ag_filters_border_w' => 2,
		'ag_filters_radius' => 2,
		
		'ag_filters_txt_color' => '#eeeeee', 
		'ag_filters_bg_color' => '#4f4f4f',
		'ag_filters_border_color' => '#4f4f4f', 
		'ag_filters_txt_color_h' => '#ffffff', 
		'ag_filters_bg_color_h' => '#585858', 
		'ag_filters_border_color_h' => '#777777',
		'ag_filters_txt_color_sel' => '#f3f3f3', 
		'ag_filters_bg_color_sel' => '#6a6a6a', 
		'ag_filters_border_color_sel' => '#6a6a6a',
		
		'ag_search_txt_color' => '#eeeeee', 
		'ag_search_bg_color' => '#4f4f4f',
		'ag_search_border_color' => '#4f4f4f',
		'ag_search_txt_color_h' => '#f3f3f3', 
		'ag_search_bg_color_h' => '#6a6a6a',
		'ag_search_border_color_h' => '#6a6a6a',
		
		'ag_pag_txt_col' => '#eeeeee', 
		'ag_pag_bg_col' => '#4f4f4f',
		'ag_pag_border_col' => '#4f4f4f',
		'ag_pag_txt_col_h' => '#f3f3f3', 
		'ag_pag_bg_col_h' => '#6a6a6a',
		'ag_pag_border_col_h' => '#6a6a6a',
	);
	
	
	$styles['d_minimal'] = ag_ps_override_indexes($styles['d_standard'], array(
		'ag_standard_hor_margin' => 6,
		'ag_standard_ver_margin' => 6,
		'ag_masonry_margin' => 8,
		'ag_photostring_margin' => 8,
		
		'ag_img_border' => 4,
		'ag_img_radius' => 1,
		'ag_img_shadow' => 'outline',
		'ag_img_outline_color' => '#777777', 
		'ag_img_border_color' => 'transparent',
		
		'ag_main_ol_color' => '#141414',
		'ag_main_ol_opacity' => 90,
		'ag_main_ol_txt_color' => '#ffffff',
		'ag_sec_ol_color' => '#bbbbbb',
		'ag_icons_col' => '#555555',
		'ag_txt_u_title_color' => '#fefefe',
		'ag_txt_u_descr_color' => '#f7f7f7',
		
		'ag_filters_border_w' => 1,
	));
	
	
	$styles['d_noborder'] = ag_ps_override_indexes($styles['d_standard'], array(
		'ag_standard_hor_margin' => 5,
		'ag_standard_ver_margin' => 5,
		'ag_masonry_margin' => 5,
		'ag_photostring_margin' => 5,
		
		'ag_img_border' => 0,
		'ag_img_radius' => 2,
		'ag_img_shadow' => 'outshadow',
		'ag_img_border_color' => '#999999',
		
		'ag_main_ol_color' => '#141414',
		'ag_main_ol_opacity' => 90,
		'ag_main_ol_txt_color' => '#ffffff',
		'ag_sec_ol_color' => '#bbbbbb',
		'ag_icons_col' => '#555555',
		'ag_txt_u_title_color' => '#fefefe',
		'ag_txt_u_descr_color' => '#f7f7f7',
		
		'ag_filters_border_w' => 0,
	));

	
	$styles['d_photowall'] = ag_ps_override_indexes($styles['d_noborder'], array(
		'ag_standard_hor_margin' => 0,
		'ag_standard_ver_margin' => 0,
		'ag_masonry_margin' => 0,
		'ag_photostring_margin' => 0,
		
		'ag_img_border' => 0,
		'ag_img_radius' => 0,
		'ag_img_shadow' => 'outshadow',
		'ag_img_border_color' => '#999999',
		
		'ag_main_ol_color' => '#141414',
		'ag_main_ol_opacity' => 90,
		'ag_main_ol_txt_color' => '#ffffff',
		'ag_sec_ol_color' => '#bbbbbb',
		'ag_icons_col' => '#555555',
		'ag_txt_u_title_color' => '#fefefe',
		'ag_txt_u_descr_color' => '#f7f7f7',
	));


	if(empty($style)) {return $styles;}
	else {
		return (isset($styles[$style])) ? $styles[$style] : false;
	}	
}




// override only certain indexes to write less code
function ag_ps_override_indexes($array, $to_override) {
	foreach($to_override as $key => $val) {
		$array[$key] = $val;	
	}
	
	return $array;
}


