<?php 

// get WP pages list - id => title
function ag_get_pages() {
	$pages = array();
	
	foreach(get_pages() as $pag) {
		$pages[ $pag->ID ] = $pag->post_title;	
	}
	
	return $pages;	
}


// galleries pagination systems
function ag_pag_sys() {
	return array(
		'standard' 		=> __('Standard', 'ag_ml'),
		'inf_scroll'	=> __('Infinite scroll', 'ag_ml'),
		'num_btns'	 	=> __('Numbered buttons', 'ag_ml'),
		'dots'	 		=> __('Dots', 'ag_ml'),
	);
}


// pagination layouts
function ag_pag_layouts($type = false) {
	$types = array(
		'standard' 	 	=> __('Commands + full text', 'ag_ml'),
		'only_num'  	=> __('Commands + page numbers', 'ag_ml'),
		'only_arr'		=> __('Only arrows', 'ag_ml'),
		'only_arr_mb'	=> __('Only arrows - monoblock', 'ag_ml'),	
	);
	
	if($type === false) {return $types;}
	else {return $types[$type];}
}


// image-to-gallery layouts
function ag_itg_layouts($layout = false) {
	$layouts = array(
		'corner_txt' 	=> __('Bottom-right corner overlay on last image', 'ag_ml'),
		'100_op_ol'  	=> __('100% opaque - full overlay on last image', 'ag_ml'),
		'50_op_ol'		=> __('50% opaque - full overlay on last image', 'ag_ml'),
		'0_op_ol'		=> __('0% opaque - full overlay on last image', 'ag_ml'),
		'block_over'	=> __('Centered text block over images', 'ag_ml'),	
		'main_n_sides'	=> __('Main image with central overlay + two smaller on sides', 'ag_ml'),	
	);
	
	if($layout === false) {return $layouts;}
	else {return $layouts[$layout];}	
}


// slider cropping methods
function ag_galleria_crop_methods($type = false) {
	$types = array(
		'true' 		=> __('Fit, center and crop', 'ag_ml'),
		'false' 	=> __('Scale down', 'ag_ml'),
		'height'	=> __('Scale to fill the height', 'ag_ml'),
		'width'		=> __('Scale to fill the width', 'ag_ml'),
		'landscape'	=> __('Fit images with landscape proportions', 'ag_ml'),
		'portrait' 	=> __('Fit images with portrait proportions', 'ag_ml')
	);
	
	if($type === false) {return $types;}
	else {return $types[$type];}
}


// slider effects
function ag_galleria_fx($type = false) {
	$types = array(
		'fadeslide' => __('Fade and slide', 'ag_ml'),
		'fade' 		=> __('Fade', 'ag_ml'),
		'flash'		=> __('Flash', 'ag_ml'),
		'pulse'		=> __('Pulse', 'ag_ml'),
		'slide'		=> __('Slide', 'ag_ml'),
		''			=> __('None', 'ag_ml')
	);
	
	if($type === false) {return $types;}
	else {return $types[$type];}
}


// slider thumbs visibility options
function ag_galleria_thumb_opts($type = false) {
	$types = array(
		'always'	=> __('Always', 'ag_ml'),
		'yes' 		=> __('Yes with toggle button', 'ag_ml'),
		'no' 		=> __('No with toggle button', 'ag_ml'),
		'never' 	=> __('Never', 'ag_ml'),
	);
	
	if($type === false) {return $types;}
	else {return $types[$type];}
}


// preloader types
function ag_preloader_types($type = false) {
	$types = array(
		'default' 				=> __('Default loader', 'ag_ml'),
		'rotating_square' 		=> __('Rotating square', 'ag_ml'),
		'overlapping_circles' 	=> __('Overlapping circles', 'ag_ml'),
		'stretch_rect' 			=> __('Stretching rectangles', 'ag_ml'),
		'spin_n_fill_square'	=> __('Spinning & filling square', 'ag_ml'),
		'pulsing_circle' 		=> __('Pulsing circle', 'ag_ml'),
		'spinning_dots'			=> __('Spinning dots', 'ag_ml'),
		'appearing_cubes'		=> __('Appearing cubes', 'ag_ml'),
		'folding_cube'			=> __('Folding cube', 'ag_ml'),
		'old_style_spinner'		=> __('Old-style spinner', 'ag_ml'),
		'minimal_spinner'		=> __('Minimal spinner', 'ag_ml'),
		'spotify_like'			=> __('Spotify-like spinner', 'ag_ml'),
		'vortex'				=> __('Vortex', 'ag_ml'),
		'bubbling_dots'			=> __('Bubbling Dots', 'ag_ml'),
		'overlapping_dots'		=> __('Overlapping dots', 'ag_ml'),
		'fading_circles'		=> __('Fading circles', 'ag_ml'),
	);
	return (!$type) ? $types : $types[$type];
}


// lightboxes list
function ag_lightboxes($type = false) {
	$types = array(
		'lcweb' 	=> 'LC Lightbox',
		'lightcase' => 'Lightcase',
		'simplelb' 	=> 'Simple Lightbox',
		'tosrus' 	=> 'Tos "R"Us',
		'mag_popup' => 'Magnific Popup',
		'imagelb' 	=> 'imageLightbox',
		'photobox' 	=> 'Photobox',
		'fancybox'	=> 'Fancybox (not responsive)',
		'colorbox' 	=> 'Colorbox',
		'prettyphoto' 	=> 'PrettyPhoto (not responsive)',
	);
	return (!$type) ? $types : $types[$type];
}



// lightcase lightbox - transition styles
function ag_lightcase_trans_styles() {
	return array(
		'none'				=> __('No transition', 'ag_ml'), 
		'fade'				=> __('Fade', 'ag_ml'),  
		'elastic'			=> __('Elastic', 'ag_ml'),
		'scrollTop'			=> __('Downwards', 'ag_ml'),
		'scrollRight'		=> __('Leftwards', 'ag_ml'), 
		'scrollBottom'		=> __('Upwards', 'ag_ml'),
		'scrollLeft'		=> __('Rightwards', 'ag_ml'),
		'scrollHorizontal'	=> __('Horizontal scroll', 'ag_ml'),
		'scrollVertical'	=> __('Vertical scroll', 'ag_ml'),
	);
}


// LC Lightbox - openClose effects list
function ag_lcl_openclose_list() {
	return array(
		'lcl_fade_oc' 		=> __('Fade', 'ag_ml'),
		'lcl_zoomin_oc' 	=> __('Zoom-in', 'ag_ml'),
		'lcl_bottop_oc' 	=> __('Bottom to top', 'ag_ml'),
		'lcl_bottop_v2_oc' 	=> __('Bottom to top v2', 'ag_ml'),
		'lcl_rtl_oc' 		=> __('Right to left', 'ag_ml'),
		'lcl_horiz_flip_oc' => __('Horizontal flip', 'ag_ml'),
		'lcl_vert_flip_oc' 	=> __('Vertical flip', 'ag_ml'),
		'' 					=> __('None (customizable through CSS)', 'ag_ml'),
	);
}


// get the LC lightbox patterns list 
function ag_lcl_patterns_list() {
	$patterns = array();
	$patterns_list = scandir(AG_DIR."/js/lightboxes/lc-lightbox/img/patterns");
	
	foreach($patterns_list as $pattern_name) {
		if($pattern_name != '.' && $pattern_name != '..') {
			$patterns[$pattern_name] = substr($pattern_name, 0, -4);
		}
	}
	return $patterns;	
}

