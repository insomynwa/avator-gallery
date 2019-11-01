<?php 
include_once(AG_DIR . '/settings/field_options.php'); 
include_once(AG_DIR . '/settings/preset_styles.php'); 
include_once(AG_DIR . '/functions.php'); 


// no more slider's oldstyle graphic
delete_option('ag_slider_old_cmd');


// collection layout - use just min and max cols width
//// if ag_coll_thumb_w already exists - set a max-width default value using this algorithm: ((elems_max_w / coll_cols) + 50) 
if(!get_option('ag_coll_thumb_max_w')) {
	$elems_basewidth = ((int)get_option('ag_masonry_basewidth', 1400)) ? (int)get_option('ag_masonry_basewidth', 1400) : 1400;
	$old_coll_cols = ((int)get_option('ag_coll_thumb_w', 4)) ? (int)get_option('ag_coll_thumb_w', 4) : 4;
	
	$val = ($elems_basewidth / $old_coll_cols)	+ 50;
	update_option('ag_coll_thumb_max_w', round($val));
}


$ml_key = 'ag_ml';
$cpt = ag_get_cpt();


// AG-FILTER - manipulate settings tabs
$tabs = array(
	'main_opts' => __('Main Options', $ml_key),
	'layouts' 	=> __('Layouts', $ml_key),
	'styling' 	=> __('Styling', $ml_key),
	'lightbox'	=> __('Lightbox', $ml_key),
	'watermark'	=> __('Watermark', $ml_key),
	'cust_css'	=> __('Custom CSS', $ml_key),
);
$GLOBALS['ag_settings_tabs'] = apply_filters('ag_settings_tabs', $tabs);	




// STRUCTURE
/* tabs index => array( 
	'sect_id' => array(
		'sect_name'	=> name
		'fields'	=> array(
			...
		)
	)
   )
*/

$structure = array();



####################################
########## MAIN OPTIONS ############
####################################
$structure['main_opts'] = array(
	
	'def_gall_sett' => array(
		'sect_name'	=>  __('Default Gallery Settings', $ml_key),
		'fields' 	=> array(
			
			'ag_layout' => array(
				'label' 	=> __('Default galleries layout', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'standard' 	 => 'Standard', 
					'columnized' => 'Columnized', 
					'masonry' 	 => 'Masonry',
					'string'	 => 'PhotoString',
				),
				'note'		=> ''
			),   
			'ag_masonry_basewidth' => array(
				'label' 	=> __('Elements maximum width', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 850,
				'max_val'	=> 2000,	
				'step'		=> 50,
				'def'		=> 1400,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Set maximum container's width where plugin elements will be placed", $ml_key),
			), 
			'ag_paginate' => array(
				'label' => __('Use pagination?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("Whether to split galleries into pages by default", $ml_key),
			), 
			'ag_per_page' => array(
				'label' 	=> __('Default images number per page', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 1,
				'max_val'	=> 100,	
				'step'		=> 1,
				'def'		=> 8,
				'value'		=> '',
				'required'	=> true,
				'note'		=> '',
			), 
			'ag_link_target' => array(
				'label' 	=> __('Linked images behavior', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'_top' 		=> __('Open link in same page', $ml_key), 
					'_blank'	=> __('Open link in a new page', $ml_key),
				),
				'note'		=> __('Choose how linked images are managed', $ml_key), 
			),   
			'ag_delayed_fx' => array(
				'label' => __('Show images without delay?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, shows gallery images without delayed effect", $ml_key),
			), 
			'ag_affect_wp_gall' => array(
				'label' => __('Manage WP galleries with Avator Gallery by default?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, displays wordpress galleries through Avator Gallery engine", $ml_key),
			), 
			'ag_extend_wp_gall' => array(
				'label' 	=> __('Extend WP galleries management for these custom post types', $ml_key),
				'type'		=> 'select',
				'val' 		=> $cpt,
				'multiple'	=> true,
				'fullwidth'	=> true,
				'hide'		=> (empty($cpt)) ? true : false,
				'note'		=> ''
			),   
			'ag_preview_pag' => array(
				'label' 	=> __('Preview container', $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_get_pages(),
				'note'		=> __('Choose the page to use as galleries preview container', $ml_key), 
			),   
		),
	),
	
	
	
	'filters' => array(
		'sect_name'	=>  __('Tags and collection filters', $ml_key),
		'fields' 	=> array(
			
			'ag_filters_align' => array(
				'label' 	=> __('Filters alignment', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'center' 	=> __('Center', $ml_key), 
					'left' 		=> __('Left', $ml_key),
					'right'		=> __('Right', $ml_key),
				),
				'note'		=> '' 
			),  
			'ag_dd_mobile_filter' => array(
				'label' => __('Use dropdown on mobile mode?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __('If checked, replace filters with a dropdown on mobile mode', $ml_key),
			),
			'ag_use_old_filters' => array(
				'label' => __('Use textual filters style?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> '',
			),  
			'ag_os_filters_separator' => array(
				'label' => __('Textual filters - separator', $ml_key),
				'type'	=> 'text',
				'note'	=> __("Specify what divides filters", $ml_key),
				
				'js_vis'=> array(
					'linked_field' 	=> 'ag_use_old_filters',
					'condition'		=> true 
				)
			),	
			'spcr1' => array(
				'type' => 'spacer',
			),
			'ag_tags_all_txt' => array(
				'label' => __('Tags - custom "All" filter\'s text', $ml_key),
				'type'	=> 'text',
				'note'	=> __("Leave empty to use the default 'All' value", $ml_key),
			),	
			'ag_filters_all_txt' => array(
				'label' => __('Collections - custom "All" filter\'s text', $ml_key),
				'type'	=> 'text',
				'note'	=> __("Leave empty to use the default 'All' value", $ml_key),
			),	
			'spcr2' => array(
				'type' => 'spacer',
			),
			'ag_tags_sort' => array(
				'label' 	=> __('Sort tags by', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'name' 	=> __('Name (A to Z)', $ml_key), 
					'count' => __('Matching images count', $ml_key),
				),
				'note'		=> '' 
			),  
			'ag_show_tags_counter' => array(
				'label' => __('Show matched images count? <small>(only for tags)</small>', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> '',
			),  
			'ag_monopage_filter' => array(
				'label' => __('Limit tags filter to shown images?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=>  __("If checked, only images belonging to shown page will be filtered", $ml_key),
			), 
			'ag_discard_tags' => array(
				'label' 	=> __('Discard tags matching less than this images number', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 1,
				'max_val'	=> 10,	
				'step'		=> 1,
				'def'		=> 1,
				'value'		=> '',
				'required'	=> true,
				'note'		=> __("Use '1' to show every tag", $ml_key),
			), 
		),
	),
	
	
	
	'pag' => array(
		'sect_name'	=>  __('Pagination Settings', $ml_key),
		'fields' 	=> array(
			  
			'ag_pag_system' => array(
				'label' 	=> __('Pagination system', $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_pag_sys(),
				'note'		=> __('Select default pagination system', $ml_key), 
			),   
			'ag_pag_layout' => array(
				'label' 	=> __('Standard pagination - Layout', $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_pag_layouts(),
				'note'		=> __('Select standard pagination elements layout', $ml_key), 
			),  
			'ag_pag_align' => array(
				'label' 	=> __('Buttons alignment', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'center' 	=> __('Center', $ml_key), 
					'left' 		=> __('Left', $ml_key),
					'right'		=> __('Right', $ml_key),
				),
				'note'		=> '' 
			),    
		),
	),
	
	
	'itg' => array(
		'sect_name'	=>  __('Image-to-Gallery Settings', $ml_key),
		'fields' 	=> array(
			  
			'ag_itg_layout' => array(
				'label' 	=> __('Default layout', $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_itg_layouts(),
				'note'		=> '', 
				'fullwidth'	=> true,
			),   
			'ag_itg_text' => array(
				'label' 	=> __('Default overlay text', $ml_key),
				'type'		=> 'textarea',
				'def' 		=> '+ %IMG-NUM% <i class="fa fa-camera-retro" style="padding-left: 2px;"></i>',
				'required'	=> true,
				'fullwidth'	=> true,
				'note'		=>  __("This text will be applied by default to image's overlay", $ml_key) .'<br/>'.
								__('Available keywords: <strong>%IMG-NUM%</strong> and <strong>%GALL-TITLE%</strong> - ', $ml_key) . '<a href="http://fontawesome.io/icons/" target="_blank">'. __('you can use also any FontAwesome icon using HTML', $ml_key) .'</a>', 
			), 
			'ag_itg_margin' => array(
				'label' 	=> __('Images margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 10,
				'value'		=> 'px',
				'required'	=> true,
			),  
		),
	),
	
	
	'coll' => array(
		'sect_name'	=>  __('Collection Settings', $ml_key),
		'fields' 	=> array(
			
			'ag_coll_back_to' => array(
				'label' => __("\"back to collection\" filter's text", $ml_key),
				'type'	=> 'text',
				'def' 	=> 'Back to collection',
				'note'	=> __("Use a custom text for \"back to collection\" button", $ml_key),
			),
			'ag_coll_show_gall_title' => array(
				'label' => __('Display loaded galleries title?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __('If checked, displays title on top of loaded galleries', $ml_key),
			),  
			'ag_coll_back_to_scroll' => array(
				'label' => __('"Back to collection" - keep visible on scroll?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __('If checked, "back to collection" button is kept visible on scroll', $ml_key),
			),
		),
	),
	
	
	'slider' => array(
		'sect_name'	=>  __('Slider Settings', $ml_key),
		'fields' 	=> array(
			
			'ag_slider_style' => array(
				'label' 	=> __('Style', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'light' => __('Light', $ml_key), 
					'dark' 	=> __('Dark', $ml_key),
				),
				'note'		=> '', 
			),   
			'ag_slider_no_border' => array(
				'label' => __('Hide borders and shadows?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __('If enabled, displays slider without external borders and shadows', $ml_key),
			),
			'ag_slider_crop' => array(
				'label' 	=> __('Images management system', $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_galleria_crop_methods(),
				'note'		=> __('Select how displayed images will be managed', $ml_key), 
			),   
			'ag_slider_fx' => array(
				'label' 	=> __('Transition effect', $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_galleria_fx(),
				'note'		=> __('Select transition effect between slides', $ml_key), 
			), 
			'ag_slider_fx_time' => array(
				'label' 	=> __('Transition duration', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 100,
				'max_val'	=> 1200,	
				'step'		=> 50,
				'def'		=> 400,
				'value'		=> 'ms',
				'required'	=> true,
				'note'		=> __("How much time transition takes (in milliseconds)", $ml_key),
			), 
			'ag_slider_autoplay' => array(
				'label' => __('Autoplay slideshow?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("Check to autoplay slider's slideshow by default", $ml_key),
			),
			'ag_slider_interval' => array(
				'label' 	=> __('Transition duration', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 1000,
				'max_val'	=> 10000,	
				'step'		=> 500,
				'def'		=> 3500,
				'value'		=> 'ms',
				'required'	=> true,
				'note'		=> __("How long each slide will be shown (in milliseconds)", $ml_key),
			), 
			'ag_slider_thumbs' => array(
				'label' 	=> __('Show thumbnails?', $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_galleria_thumb_opts(),
				'note'		=> __('Select whether and how thumbs will be shown', $ml_key), 
			), 
			'ag_st_sizes' => array(
				'label' 	=> __("Thumbnails size", $ml_key),
				'type'		=> 'custom',
				'callback'	=> 'ag_w_h_fields',
				'note'		=> __('Set slider thumbnails size (width x height)', $ml_key),
				'validation'=> array(
					array('index' => 'ag_slider_thumb_w', 'label' => __('Slider thumbnails width', $ml_key), 'type' => 'int', 'required' => true),
					array('index' => 'ag_slider_thumb_h', 'label' => __('Slider thumbnails height', $ml_key), 'type' => 'int', 'required' => true),
				)
			),
			'ag_slider_tgl_info' => array(
				'label' => __("Hide image's data by default?", $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, hides image's data by default", $ml_key),
			),
			'ag_slider_to_hide' => array(
				'label' 	=> __('Elements to remove', $ml_key),
				'type'		=> 'select',
				'val' 		=> array( 
					'play' 		=> __('Play button', 'ag_ml'), 
					'lightbox' 	=> __('Lightbox button', 'ag_ml'), 
					'info' 		=> __("Images data", 'ag_ml'),
					'counter' 	=> __("Images counter", 'ag_ml') 
				),
				'multiple'	=> true,
				'note'		=> __('Select slider elements you want to remove', $ml_key), 
			),   
		),
	),
	
	
	'carousel' => array(
		'sect_name'	=>  __('Carousel Settings', $ml_key),
		'fields' 	=> array(
			
			'ag_car_elem_style' => array(
				'label' 	=> __('Style', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'light' => __('Light', $ml_key), 
					'dark' 	=> __('Dark', $ml_key),
				),
				'note'		=> '', 
			),  
			'ag_car_hor_margin' => array(
				'label' 	=> __('Horizontal margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 10,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Set horizontal margin between images", $ml_key),
			),  
			'ag_car_ver_margin' => array(
				'label' 	=> __('Vertical margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 10,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Set vertical margin between images", $ml_key),
			), 
			'ag_car_infinite' => array(
				'label' => __("Infinite loop sliding?", $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, navigation won't stop if latest image is reached", $ml_key),
			), 
			'ag_car_ss_time' => array(
				'label' 	=> __('Slideshow interval', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 1000,
				'max_val'	=> 10000,	
				'step'		=> 500,
				'def'		=> 5000,
				'value'		=> 'ms',
				'required'	=> true,
				'note'		=> __("Set slideshow interval time in milliseconds  (in milliseconds)", $ml_key),
			), 
			'ag_car_autoplay' => array(
				'label' => __("Autoplay slideshow?", $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("Check to autoplay carousel slideshow by default", $ml_key),
			), 
			'ag_car_pause_on_h' => array(
				'label' => __("Pause slideshow on hover?", $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, slideshow will be paused hovering an image", $ml_key),
			), 
			'ag_car_hide_nav_elem' => array(
				'label' 	=> __('Hide navigation elements', $ml_key),
				'type'		=> 'select',
				'val' 		=> array( 
					 'arrows' 	=> __('Side arrows', 'ag_ml'),
					 'dots' 	=> __('Bottom dots', 'ag_ml') 
				),
				'multiple'	=> true,
				'note'		=> __('Select navigation elements to hide in carousels', $ml_key), 
			),   
		),
	),
	
	
	'img_protect' => array(
		'sect_name'	=>  __('Images Protection', $ml_key),
		'fields' 	=> array(
			
			'ag_disable_rclick' => array(
				'label' => __("Disable right click?", $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, disables right click on gallery images", $ml_key),
			), 
		),
	),
	
	
	'aga_bases' => array(
		'sect_name'	=>  __('Avator Gallery Album Bases <small>(changing these values could break albums, be careful)</small>', $ml_key),
		'fields' 	=> array(
			
			'ag_albums_basepath' => array(
				'label' 	=> __("Albums basepath", $ml_key),
				'type'		=> 'text',
				'def' 		=> AGA_DIR,
				'fullwidth'	=> true,
				'note'		=> __("Default one is", $ml_key) .' <em>'. AGA_DIR .'</em>',
			),
			'ag_albums_baseurl' => array(
				'label' 	=> __("Albums baseurl", $ml_key),
				'type'		=> 'text',
				'def' 		=> AGA_URL,
				'fullwidth'	=> true,
				'note'		=> __("Default one is", $ml_key) .' <em>'. AGA_URL .'</em>',
			),
		),
	),
	
	
	'advanced' => array(
		'sect_name'	=>  __('Advanced', $ml_key),
		'fields' 	=> array(
			  
			'ag_thumb_q' => array(
				'label' 	=> __('Thumbnails quality', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 30,
				'max_val'	=> 100,	
				'step'		=> 1,
				'def'		=> 90,
				'value'		=> '%',
				'required'	=> true,
				'note'		=> __("Set thumbnails quality. Low value = lighter but fuzzier images (default: 90%)", $ml_key),
			), 
			'ag_preload_hires_img' => array(
				'label' => __('Preload full-resolution images?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("Check to preload full resolution images once galleries are shown. This speeds-up lightbox opening but increases page's weight", $ml_key),
			), 
			'ag_use_admin_thumbs' => array(
				'label' => __('Use thumbnails on admin side?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("Check to use thumbnails on admin side. <strong>Could slow down the server in case of huge galleries</strong>", $ml_key),
			), 
			'ag_wp_term_autolink' => array(
				'label' => __('Auto-link images coming from WP posts?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, automatically set added images to link to their related posts (only for WP category and CPT sources)", $ml_key),
			), 
			'aga_img_title_src' => array(
				'label' 	=> __('Avator Gallery Albums - images title', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'filename' 	=> __('File name', $ml_key), 
					'iptc' 		=> __('IPTC metadata', $ml_key),
				),
				'note'		=> __('Choose what is used to get AG album images title', $ml_key), 
			), 
			'ag_disable_dl' => array(
				'label' => __('Disable deeplinking?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, disables collection filters and galleries deeplinking", $ml_key),
			), 
			'ag_force_inline_css' => array(
				'label' => __('Use custom CSS inline?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, uses custom CSS inline <strong>(useful for multisite installations)</strong>", $ml_key),
			), 
			'ag_js_head' => array(
				'label' => __("Use javascript in website's head?", $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("<strong>Check it <strong>ONLY IF</strong> you notice some incompatibilities</strong>", $ml_key),
			),   
			'ag_rtl_mode' => array(
				'label' => __("RTL mode?", $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("Checking it, elements layout is changed to be viewed on a RTL site", $ml_key),
			),
			'ag_no_auto_upd' => array(
				'label' => __('Disable auto-updates?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("Use it only noticing serious backend slowdowns when this plugin is enabled", $ml_key),
			), 
			'spcr1' => array(
				'type' => 'spacer',
			),    
			'ag_use_timthumb' => array(
				'label' => __('Use TimThumb?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, use Timthumb instead of Easy WP Thumbs", $ml_key),
			),  
			'ag_ewpt_force' => array(
				'label' => __('Use Easy WP Thumbs forcing system?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("Tries forcing thumbnails creation, <strong>check it ONLY if you notice thumbnail issues</strong>", $ml_key),
				
				'js_vis'=> array(
					'linked_field' 	=> 'ag_use_timthumb',
					'condition'		=> false
				)
			),   
		),
	),
	
	
	'ewpt_status' => array(
		'sect_name'	=> '',
		'fields' 	=> array(
			
			'ewpt_status' => array(
				'type'		=> 'custom',
				'callback'	=> 'ag_ewpt_status',
			), 
		),
	),
);	
	
	
	
	
####################################
############ LAYOUTS ###############
####################################		
$structure['layouts'] = array(
	
	'standard_lyt' => array(
		'sect_name'	=>  __('Standard Galleries Layout (fixed sizes)', $ml_key),
		'fields' 	=> array(
			
			'ag_standard_hor_margin' => array(
				'label' 	=> __('Horizontal margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 10,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Set horizontal margin between images", $ml_key),
			),  
			'ag_standard_ver_margin' => array(
				'label' 	=> __('Vertical margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 10,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Set vertical margin between images", $ml_key),
			), 
			'ag_slayout_sizes' => array(
				'label' 	=> __("Images size", $ml_key),
				'type'		=> 'custom',
				'callback'	=> 'ag_w_h_fields',
				'note'		=> __('Set default thumbnail sizes for standard layout (width x height)', $ml_key),
				'validation'=> array(
					array('index' => 'ag_thumb_w', 'label' => __('Standard layout - thumbnails width', $ml_key), 'type' => 'int', 'required' => true),
					array('index' => 'ag_thumb_h', 'label' => __('Standard layout - thumbnails height', $ml_key), 'type' => 'int', 'required' => true),
				)
			),
		),
	),
	
	
	'colnzd_lyt' => array(
		'sect_name'	=>  __('Columnized Galleries Layout', $ml_key),
		'fields' 	=> array(
			
			'ag_colnzd_thumb_max_w' => array(
				'label' 	=> __('Gallery columns maximum width', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 40,
				'max_val'	=> 2000,	
				'step'		=> 20,
				'def'		=> 260,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Over this treshold, gallery columns number will be increased", $ml_key),
			),   
			'ag_colnzd_thumbs_h_f' => array(
				'label' 	=> __("Thumbnails height", $ml_key),
				'type'		=> 'custom',
				'callback'	=> 'ag_size_type_fields',
				'note'		=> __("Sets columnized gallery thumbnails height (percentage value is related to image's width)", $ml_key),
				'validation'=> array(
					array('index' => 'ag_colnzd_thumb_h', 'label' => __('Columnized galleries - thumbnails height', $ml_key), 'type' => 'int', 'required' => true),
					array('index' => 'ag_colnzd_thumb_h_type', 'label' => 'Columnized galleries - thumbs height type'),
				)
			),
			'ag_colnzd_hor_margin' => array(
				'label' 	=> __('Horizontal margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 10,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Set horizontal margin between images", $ml_key),
			), 
			'ag_colnzd_ver_margin' => array(
				'label' 	=> __('Vertical margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 10,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Set vertical margin between images", $ml_key),
			), 
		),
	),
	
	
	'masonry_lyt' => array(
		'sect_name'	=>  __('Masonry Galleries Layout', $ml_key),
		'fields' 	=> array(
			
			'ag_masonry_cols' => array(
				'label' 	=> __('Image columns', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 1,
				'max_val'	=> 20,	
				'step'		=> 1,
				'def'		=> 4,
				'value'		=> '',
				'required'	=> true,
				'note'		=> __("Sets default columns number for masonry galleries", $ml_key),
			), 
			'ag_masonry_min_width' => array(
				'label' 	=> __('Minimum images width', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 40,
				'max_val'	=> 350,	
				'step'		=> 10,
				'def'		=> 150,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Sets minimum images width in masonry galleries", $ml_key),
			),  
			'ag_masonry_margin' => array(
				'label' 	=> __('Images margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 7,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Sets images margin in masonry galleries", $ml_key),
			), 
		),
	),
	
	
	'photostring_lyt' => array(
		'sect_name'	=>  __('PhotoString Galleries Layout', $ml_key),
		'fields' 	=> array(
			
			'ag_photostring_h' => array(
				'label' 	=> __('Thumbnails height', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 40,
				'max_val'	=> 400,	
				'step'		=> 10,
				'def'		=> 140,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Sets default columns number for photostring layout", $ml_key),
			), 
			'ag_photostring_min_width' => array(
				'label' 	=> __('Minimum images width', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 40,
				'max_val'	=> 350,	
				'step'		=> 10,
				'def'		=> 120,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Sets minimum images width in photostring galleries", $ml_key),
			),  
			'ag_photostring_margin' => array(
				'label' 	=> __('Images margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 7,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Sets images margin in photostring galleries", $ml_key),
			),
		),
	),
	
	
	'coll_lyt' => array(
		'sect_name'	=>  __('Collections Layout', $ml_key),
		'fields' 	=> array(
			 
			'ag_coll_thumb_max_w' => array(
				'label' 	=> __('Collection columns maximum width', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 400,
				'max_val'	=> 2000,	
				'step'		=> 20,
				'def'		=> 260,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Over this treshold, collection columns number will be increased", $ml_key),
			),   
			'ag_coll_thumbs_h_f' => array(
				'label' 	=> __("Thumbnails height", $ml_key),
				'type'		=> 'custom',
				'callback'	=> 'ag_size_type_fields',
				'note'		=> __("Sets collection thumbnails height (percentage value is related to image's width)", $ml_key),
				'validation'=> array(
					array('index' => 'ag_coll_thumb_h', 'label' => __('Collection thumbnails height', $ml_key), 'type' => 'int', 'required' => true),
					array('index' => 'ag_coll_thumb_h_type', 'label' => 'Collection thumbs height type'),
				)
			),
			'ag_coll_hor_margin' => array(
				'label' 	=> __('Horizontal margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 10,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Sets horizontal margin between collection images", $ml_key),
			), 
			'ag_coll_ver_margin' => array(
				'label' 	=> __('Vertical margin', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 40,	
				'step'		=> 1,
				'def'		=> 10,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Sets vertical margin between collection images", $ml_key),
			), 
			'ag_coll_title_under' => array(
				'label' => __('Texts under images?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, texts will be displayed under collection images", $ml_key),
			), 
		),
	),
);

	
	
	
	
####################################
############ STYLING ###############
####################################		
$structure['styling'] = array(
		
	'preset_styles' => array(
		'sect_name'	=>  __('Preset Styles', $ml_key),
		'fields' 	=> array(
			
			'preset_styles_field' => array(
				'type'		=> 'custom',
				'callback'	=> 'ag_preset_styles'
			), 
		),
	),
	
	
	'loader' => array(
		'sect_name'	=>  __('Loader', $ml_key),
		'fields' 	=> array(
			
			'ag_loader' => array(
				'label' 	=> __("Preloader", $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_preloader_types(),
				'required'	=> true,
				'note'		=> ''
			),
			'ag_loader_color' => array(
				'label' 	=> __("Preloader color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#aaaaaa',
				'required'	=> true,
			),
		),
	),
	
	
	'img_layout' => array(
		'sect_name'	=>  __('Images Layout', $ml_key),
		'fields' 	=> array(
			
			'ag_img_border' => array(
				'label' 	=> __('Images border width', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 15,	
				'step'		=> 1,
				'def'		=> 4,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> ''
			),  
			'ag_img_border_color' => array(
				'label' 	=> __("Images border color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#fafafa',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_img_shadow' => array(
				'label' 	=> __('Outer image effect', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'' 			=> __('No effect', $ml_key), 
					'outshadow'	=> __('Soft shadow', $ml_key),
					'outline'	=> __('Outline', $ml_key),
				),
				'note'		=> __('Choose which effect use for images aspect', $ml_key), 
			),
			'ag_img_outline_color' => array(
				'label' 	=> __("Images outline color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#aaaaaa',
				'required'	=> true,
				'note'		=> __('Sets images outline color (must be enabled to be shown)', $ml_key),
			),  
			'ag_img_radius' => array(
				'label' 	=> __('Images border radius', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 25,	
				'step'		=> 1,
				'def'		=> 2,
				'value'		=> 'px',
				'required'	=> true,
			), 
			'ag_thumb_fx' => array(
				'label' 	=> __('Outer image effect', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'' 			=> __('No effect', $ml_key), 
					'grayscale'	=> __('Grayscale on default state', $ml_key),
					'blur'		=> __('Blurred on hover', $ml_key),
				),
				'note'		=> __('Choose which effect use for images aspect', $ml_key), 
			),
		),
	),
	
	
	'overlays' => array(
		'sect_name'	=>  __('Overlays', $ml_key),
		'fields' 	=> array(
			
			'ag_overlay_type' => array(
				'label' 	=> __('Overlay type', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'both'		=> __('Both overlays', $ml_key),
					'primary'	=> __('Only main overlay', $ml_key),
					'' 			=> __('No overlay', $ml_key), 
				),
			),
			'ag_main_overlay' => array(
				'label' 	=> __('Main overlay mode', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'bottom' 	=> __('Bottom bar', $ml_key), 
					'top'		=> __('Top bar', $ml_key),
					'full'		=> __('Full image', $ml_key),
				),
			),
			'ag_main_ol_behav' => array(
				'label' 	=> __('Main overlay behavior', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'show_on_h'		=> __('Show on hover', $ml_key), 
					'always_shown'	=> __('Always shown', $ml_key),
					'hide_on_h'		=> __('Hide on hover', $ml_key),
				),
			),
			'ag_slowzoom_ol' => array(
				'label' => __('Slowly zoom images on hover?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, images will be slowly zoomed on hover, giving a bit of animation to galleries", $ml_key),
			), 
			'ag_main_ol_color' => array(
				'label' 	=> __("Main overlay color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#fdfdfd',
				'required'	=> true,
			), 
			'ag_main_ol_opacity' => array(
				'label' 	=> __('Main overlay opacity', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 100,	
				'step'		=> 5,
				'def'		=> 70,
				'value'		=> '%',
				'required'	=> true,
			),
			'ag_main_ol_txt_color' => array(
				'label' 	=> __("Main overlay - text color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#444444',
				'required'	=> true,
			),  
			'ag_sec_overlay' => array(
				'label' 	=> __('Secondary overlay position', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'tl'	=> __('Top-left corner', $ml_key), 
					'tr'	=> __('Top-right corner', $ml_key),
					'bl'	=> __('Bottom-left corner', $ml_key), 
					'br'	=> __('Bottom-right corner', $ml_key),
				),
			), 
			'ag_sec_ol_color' => array(
				'label' 	=> __("Secondary overlay color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#747474',
				'required'	=> true,
				'note'		=> __("Sets secondary overlay's background color", $ml_key), 
			),
			'ag_sec_ol_icon' => array(
				'label' 	=> __('Secondary overlay - icon type', $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'eye'		=> __('Eye', $ml_key), 
					'camera'	=> __('Camera', $ml_key),
					'magnifier'	=> __('Magnifier', $ml_key), 
					'image'		=> __('Image', $ml_key),
				),
			),
			'ag_icons_col' => array(
				'label' 	=> __("Secondary overlay - icon color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#fcfcfc',
				'required'	=> true,
			),
		),
	),
	
	
	'itg_ols' => array(
		'sect_name'	=>  __('Image-to-Gallery overlay', $ml_key),
		'fields' 	=> array(
			
			'ag_itg_bg_color' => array(
				'label' 	=> __("Overlay's background color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#333333',
				'required'	=> true,
			),
			'ag_itg_txt_color' => array(
				'label' 	=> __("Overlay's text color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#fefefe',
				'required'	=> true,
			),
			'ag_itg_ol_on_h' => array(
				'label' => __('Only show overlays on hover?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, overlays will be hidden by default, showing clean images", $ml_key),
			), 
		),
	),
	
	
	'coll_tui' => array(
		'sect_name'	=>  __('Collections - Texts Under Images', $ml_key),
		'fields' 	=> array(
			
			'ag_txt_u_title_color' => array(
				'label' 	=> __("Titles color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#444444',
				'required'	=> true,
			),
			'ag_txt_u_descr_color' => array(
				'label' 	=> __("Descriptions color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#686868',
				'required'	=> true,
			),
		),
	),
	
	
	'filters' => array(
		'sect_name'	=>  __('Filters', $ml_key),
		'fields' 	=> array(
			
			'ag_filters_padding' => array(
				'label' 	=> __("Padding", $ml_key),
				'type'		=> '2_numbers',
				'min_val'	=> 0,
				'max_val'	=> 20,	
				'value'		=> 'px',
				'def'		=> array(6, 12),
				'note'		=> __('Sets filters padding (vertical / horizontal)', $ml_key)
			),	
			'ag_filters_font_size' => array(
				'label' 	=> __('Font size', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 8,
				'max_val'	=> 20,	
				'step'		=> 1,
				'def'		=> 15,
				'value'		=> 'px',
				'required'	=> true,
			),
			'ag_filters_border_w' => array(
				'label' 	=> __("Border's width", $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 4,	
				'step'		=> 1,
				'def'		=> 1,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Sets filters border width (not for textual mode)", $ml_key),
			),
			'ag_filters_radius' => array(
				'label' 	=> __('Border radius', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 15,	
				'step'		=> 1,
				'def'		=> 2,
				'value'		=> 'px',
				'required'	=> true,
				'note'		=> __("Sets filters border radius (not for textual mode)", $ml_key),
			), 
			
			'spcr1' => array(
				'type' => 'spacer',
			),
			
			'ag_filters_txt_color' => array(
				'label' 	=> __("Filters text color", $ml_key) .' - '. __('default state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#444444',
				'required'	=> true,
			),
			'ag_filters_bg_color' => array(
				'label' 	=> __("Filters background color", $ml_key) .' - '. __('default state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#ffffff',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_filters_border_color' => array(
				'label' 	=> __("Filters border color", $ml_key) .' - '. __('default state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#999999',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_filters_txt_color_h' => array(
				'label' 	=> __("Filters text color", $ml_key) .' - '. __('hover state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#666666',
				'required'	=> true,
			),
			'ag_filters_bg_color_h' => array(
				'label' 	=> __("Filters background color", $ml_key) .' - '. __('hover state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#ffffff',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_filters_border_color_h' => array(
				'label' 	=> __("Filters border color", $ml_key) .' - '. __('hover state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#666666',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_filters_txt_color_sel' => array(
				'label' 	=> __("Filters text color", $ml_key) .' - '. __('selected state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#333333',
				'required'	=> true,
			),
			'ag_filters_bg_color_sel' => array(
				'label' 	=> __("Filters background color", $ml_key) .' - '. __('selected state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#f0f0f0',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_filters_border_color_sel' => array(
				'label' 	=> __("Filters border color", $ml_key) .' - '. __('selected state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#555555',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			
			'spcr2' => array(
				'type' => 'spacer',
			),
			
			'ag_search_txt_color' => array(
				'label' 	=> __("Search bar text color", $ml_key) .' - '. __('default state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#707070',
				'required'	=> true,
			),
			'ag_search_bg_color' => array(
				'label' 	=> __("Search bar background color", $ml_key) .' - '. __('default state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#f5f5f5',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_search_border_color' => array(
				'label' 	=> __("Search bar border color", $ml_key) .' - '. __('default state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#f5f5f5',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_search_txt_color_h' => array(
				'label' 	=> __("Search bar text color", $ml_key) .' - '. __('active state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#5e5e5e',
				'required'	=> true,
			),
			'ag_search_bg_color_h' => array(
				'label' 	=> __("Search bar background color", $ml_key) .' - '. __('active state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#ffffff',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_search_border_color_h' => array(
				'label' 	=> __("Search bar border color", $ml_key) .' - '. __('active state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#707070',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
		),
	),
	
	
	'pag_btns' => array(
		'sect_name'	=>  __('Pagination Elements', $ml_key),
		'fields' 	=> array(
   
   			'ag_pag_txt_col' => array(
				'label' 	=> __("Texts and arrows color", $ml_key) .' - '. __('default state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#707070',
				'required'	=> true,
			),
			'ag_pag_bg_col' => array(
				'label' 	=> __("Background color", $ml_key) .' - '. __('default state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#f5f5f5',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_pag_border_col' => array(
				'label' 	=> __("Border color", $ml_key) .' - '. __('default state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#f5f5f5',
				'required'	=> true,
			),
			
			'spcr2' => array(
				'type' => 'spacer',
			),
			
			'ag_pag_txt_col_h' => array(
				'label' 	=> __("Texts and arrows color", $ml_key) .' - '. __('active state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#5e5e5e',
				'required'	=> true,
			),
			'ag_pag_bg_col_h' => array(
				'label' 	=> __("Background color", $ml_key) .' - '. __('active state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#f0f0f0',
				'allow_transparent'	=> true,
				'required'	=> true,
				'note'		=> __('accepts also "transparent" value', $ml_key),
			),
			'ag_pag_border_col_h' => array(
				'label' 	=> __("Border color", $ml_key) .' - '. __('active state', $ml_key),
				'type'		=> 'color',
				'def'		=> '#999999',
				'required'	=> true,
			),
		),
	),

);
	
	


	
####################################
########### LIGHTBOX ###############
####################################		
$structure['lightbox'] = array(
		
	'lightbox' => array(
		'sect_name'	=>  '', //__('Loader', $ml_key),
		'fields' 	=> array(
			
			'ag_lightbox' => array(
				'label' 	=> __("Which lightbox to use?", $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_lightboxes(),
				'required'	=> true,
				'note'		=> ''
			),
		),
	),	
		
	/*
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
	*/
	
	
	'lb_opts' => array(
		'sect_name'	=> __('Lightbox Options', $ml_key),
		'fields' 	=> array(
			
			'ag_lb_lcl_style' => array(
				'label' 	=> __("Style", $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'minimal'	=> __('Minimal', $ml_key), 
					'light'		=> __('Light', $ml_key),
					'dark'		=> __('Dark', $ml_key), 
				),
				'note'		=> __('Select lightbox skin', $ml_key),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_lcl_openclose' => array(
				'label' 	=> __("Open/close effect", $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_lcl_openclose_list(),
				'note'		=> __("Select which opening/closing effect to use (for custom CSS, classes are <em>.lcl_pre_show</em> and <em>.lcl_is_closing</em>)", $ml_key),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_simplelb_style' => array(
				'label' 	=> __("Style", $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'light'		=> __('Light', $ml_key),
					'dark'		=> __('Dark', $ml_key), 
				),
				'note'		=> __('Select lightbox skin', $ml_key),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('simplelb')
				)
			),
			'ag_lb_col_style' => array(
				'label' 	=> __("Style", $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'1'		=> __('Skin', $ml_key) .' 1',
					'2'		=> __('Skin', $ml_key) .' 2',
					'3'		=> __('Skin', $ml_key) .' 3',
					'4'		=> __('Skin', $ml_key) .' 4',
					'5'		=> __('Skin', $ml_key) .' 5',
				),
				'note'		=> __('Select lightbox skin', $ml_key),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('colorbox')
				)
			),
			'ag_lb_opacity' => array(
				'label' 	=> __('Overlay opacity', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 100,	
				'step'		=> 5,
				'def'		=> 70,
				'value'		=> '%',
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'lightcase', 'fancybox', 'colorbox', 'prettyphoto', 'mag_popup', 'tosrus', 'simplelb', 'imagelb')
				)
			), 
			'ag_lb_ol_color' => array(
				'label' 	=> __("Overlay color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#333333',

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'lightcase', 'fancybox', 'mag_popup', 'tosrus', 'simplelb', 'imagelb')
				)
			), 
			'ag_lb_ol_pattern' => array(
				'type'		=> 'custom',
				'callback'	=> 'ag_lb_ol_pattern_f',
				'validation'=> array(
					array('index' => 'ag_lb_ol_pattern', 'label' => 'Lb overlay pattern'),
				),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'lightcase', 'mag_popup', 'simplelb', 'imagelb')
				)
			), 
			'ag_lb_max_w' => array(
				'label' 	=> __('Maximum width', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 20,
				'max_val'	=> 100,	
				'step'		=> 1,
				'def'		=> 85,
				'value'		=> '%',
				'note'		=> __("Set lightbox max width, in relation to browser's one", $ml_key),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'lightcase', 'fancybox', 'colorbox', 'prettyphoto', 'mag_popup', 'tosrus', 'simplelb', 'imagelb')
				)
			), 
			'ag_lb_max_h' => array(
				'label' 	=> __('Maximum height', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 20,
				'max_val'	=> 100,	
				'step'		=> 1,
				'def'		=> 85,
				'value'		=> '%',
				'note'		=> __("Set lightbox max height, in relation to browser's one", $ml_key),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'lightcase', 'colorbox', 'simplelb')
				)
			), 
			'ag_lb_padding' => array(
				'label' 	=> __('Contents padding', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 20,	
				'step'		=> 1,
				'def'		=> 0,
				'value'		=> 'px',

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'fancybox')
				)
			), 
			'ag_lb_border_w' => array(
				'label' 	=> __('Border width', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 20,	
				'step'		=> 1,
				'def'		=> 2,
				'value'		=> 'px',

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			), 
			'ag_lb_border_col' => array(
				'label' 	=> __("Border color", $ml_key),
				'type'		=> 'color',
				'def'		=> '#888888',

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			), 
			'ag_lb_radius' => array(
				'label' 	=> __('Border radius', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 1,
				'max_val'	=> 25,	
				'step'		=> 1,
				'def'		=> 2,
				'value'		=> 'px',

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'simplelb')
				)
			),
			'ag_lb_use_shadow' => array(
				'label' => __('Outer shadow?', $ml_key),
				'type'	=> 'checkbox',

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			), 
			'ag_lcl_txt_pos' => array(
				'label' 	=> __("Text position", $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'under'	=> __('Under image', $ml_key),
					'over'	=> __('Over image', $ml_key), 
					'rside'	=> __('On right side', $ml_key),
					'lside'	=> __('On left side', $ml_key), 
				),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_txt_treshold' => array(
				'label' 	=> __('Text visibility treshold', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 200,
				'max_val'	=> 2500,	
				'step'		=> 100,
				'def'		=> 600,
				'value'		=> 'px',
				'note'		=> __("Screens smaller than this value have text hidden by default", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_cmd_pos' => array(
				'label' 	=> __("Commands position", $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'inner'	=> __('Inner', $ml_key),
					'outer'	=> __('Outer', $ml_key), 
				),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_corner_close' => array(
				'label' => __('Closing button in corner position?', $ml_key),
				'type'	=> 'checkbox',

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			), 
			'ag_lb_middle_nav_pos' => array(
				'label' => __('Navigation buttons in middle position?', $ml_key),
				'type'	=> 'checkbox',

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			), 
			
			'ag_lb_txt_pos' => array(
				'label' 	=> __("Text position", $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'standard'	=> __('Under image', $ml_key),
					'over'		=> __('Over image', $ml_key),  
				),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('fancybox')
				)
			),
			
			'ag_lb_thumbs' => array(
				'label' => __('Use thumbnails navigation?', $ml_key),
				'type'	=> 'checkbox',

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'prettyphoto', 'tosrus', 'photobox')
				)
			),
			'ag_lb_tn_treshold' => array(
				'label' 	=> __('Thumbnails visibility treshold', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 200,
				'max_val'	=> 2500,	
				'step'		=> 100,
				'def'		=> 600,
				'value'		=> 'px',
				'note'		=> __("Screens smaller than this value have thumbnails hidden by default", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_thumb_sizes' => array(
				'label' 	=> __("Thumbnail sizes", $ml_key),
				'type'		=> 'custom',
				'callback'	=> 'ag_w_h_fields',
				'note'		=> __('Set lightbox thumbnail sizes (width x height)', $ml_key),
				'validation'=> array(
					array('index' => 'ag_lb_thumb_w', 'label' => __('Lightbox thumbnails width', $ml_key), 'type' => 'int'),
					array('index' => 'ag_lb_thumb_h', 'label' => __('Lightbox thumbnails height', $ml_key), 'type' => 'int'),
				),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_thumbs_full_img' => array(
				'label' => __('Use full images in thumbnails navigation?', $ml_key),
				'type'	=> 'checkbox',
				'note'		=> __("If checked, fills thumbnails with full-size images (<strong>use ONLY if you note thumbs loading issues</strong>)", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_fullscreen' => array(
				'label' => __('Enable fullscreen?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, allows to use the fullscreen mode", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'tosrus')
				)
			),
			'ag_lb_fs_treshold' => array(
				'label' 	=> __('Fullscreen treshold', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 200,
				'max_val'	=> 2500,	
				'step'		=> 100,
				'def'		=> 600,
				'value'		=> 'px',
				'note'		=> __("Lightbox will be only in fullscreen mode for screens smaller than this value", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_deeplink' => array(
				'label' => __('Enable deeplink?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, enables images deeplinking", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_download' => array(
				'label' => __('Download button?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, enables images download button (only for self hosted images)", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_zoom' => array(
				'label' => __('Images zoom?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, enables images zooming system", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_photobox_zoom' => array(
				'label' => __('Images zoom?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, enables images zooming system with mousewheel", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('photobox')
				)
			),
			'ag_lb_counter' => array(
				'label' => __('Show counter?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, displays images count and progress", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_socials' => array(
				'label' => __('Enable socials?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, enables social share", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'prettyphoto')
				)
			),
			'ag_lb_lcl_direct_fb' => array(
				'label' => __('Direct Facebook contents sharing?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("Allows direct photo + contents sharing on Facebook. <font style='color: #D54E21;'><strong>Requires a valid App ID linked to this domain</strong></font>", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
				
			'ag_lb_lcl_comments' => array(
				'label' 	=> __("Comments system", $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					''			=> __('None', $ml_key),
					'disqus'	=> __('Disqus', $ml_key),
					'fb' 		=> __('Facebook Comments', $ml_key), 
				),
				'note'	=> __("<font style='color: #D54E21;'>Comments are applied only using texts on right or left side. Facebook moderation requires a valid App ID</font>", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			
			'ag_lcl_disqus_shortname' => array(
				'label' => __('Disqus shortname', $ml_key),
				'type'	=> 'text',
				'note'	=> __("Required to use Disqus comments. <a href='https://help.disqus.com/customer/portal/articles/466208' target='_blank'>Get one</a>", $ml_key),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),				
			'ag_lcl_fb_appid' => array(
				'label' => __('Facebook App ID', $ml_key),
				'type'	=> 'text',
				'note'	=> __("Required to use FB direct contents share and moderate FB comments. <a href='https://developers.facebook.com/docs/apps/register' target='_blank'>Create an app</a>", $ml_key),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),		
					
					
			'ag_lb_slideshow' => array(
				'label' => __('Auto start slideshow?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, starts slideshow on lightbox opening", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'lightcase', 'colorbox', 'prettyphoto', 'photobox')
				)
			),
			'ag_lb_progressbar' => array(
				'label' => __('Show progressbar?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, shows a progressbar during slideshow", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			
			'ag_lb_oc_time' => array(
				'label' 	=> __('Open / Close timing', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 2000,	
				'step'		=> 50,
				'def'		=> 500,
				'value'		=> 'ms',
				'note'		=> __("Set open/close lightbox timing (in milliseconds - default: 500)", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb')
				)
			),
			'ag_lb_time' => array(
				'label' 	=> __('Sliding animation timing', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 0,
				'max_val'	=> 2000,	
				'step'		=> 50,
				'def'		=> 500,
				'value'		=> 'ms',
				'note'		=> __("Set minimum time to switch from an image to another (in milliseconds - default: 400)", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'lightcase', 'simplelb', 'imagelb', 'fancybox', 'colorbox', 'prettyphoto')
				)
			),
			'ag_lb_ss_time' => array(
				'label' 	=> __('Slideshow interval', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 2000,
				'max_val'	=> 15000,	
				'step'		=> 200,
				'def'		=> 5000,
				'value'		=> 'ms',
				'note'		=> __("Set slideshow interval's time in milliseconds (default 5000)", $ml_key),

				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('lcweb', 'lightcase', 'fancybox', 'colorbox', 'prettyphoto', 'simplelb', 'imagelb')
				)
			),
			'ag_lb_anim_behav' => array(
				'label' 	=> __("Animation's behavior", $ml_key),
				'type'		=> 'select',
				'val' 		=> array(
					'slide'	=> __('Slide', $ml_key),
					'fade'	=> __('Fade', $ml_key), 
				),
				'note'		=> __("Select animation's behavior navigating through images", $ml_key),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('tosrus', 'simplelb')
				)
			),
			'ag_lb_anim_behav' => array(
				'label' 	=> __("Animation's behavior", $ml_key),
				'type'		=> 'select',
				'val' 		=> ag_lightcase_trans_styles(),
				'note'		=> __("Select animation's behavior navigating through images", $ml_key),
				
				'js_vis' => array(
					'linked_field' 	=> 'ag_lightbox',
					'condition'		=> array('ag_lightcase_anim_behav')
				)
			),
			
		),
	),	
);
	
	
	
	
	
####################################
########### WATERMARK ##############
####################################		
$structure['watermark'] = array(
	
	'watermark_block' => array(
		'sect_name'	=>  __('Watermark Settings', $ml_key),
		'fields' 	=> array(
			
			'ag_uwm_block' => array(
				'label' 	=> 'upper watermark block',
				'type'		=> 'custom',
				'callback'	=> 'ag_u_wm_fields',
				'validation'=> array(
					array('index' => 'ag_watermark_img', 'label' => __( 'Watermark Image', $ml_key), 'type'=>'url'),
					array('index' => 'ag_watermark_pos', 'label' => 'Watermark Position')
				)
			),
			'ag_wm_margin_f' => array(
				'label' 	=> __("Watermark's margin", $ml_key),
				'type'		=> 'custom',
				'callback'	=> 'ag_size_type_fields',
				'note'		=> __("Sets margin from image edges (percentage value is related to image sizes)", $ml_key),
				'validation'=> array(
					array('index' => 'ag_wm_margin', 'label' => __("Watermark's margin", $ml_key), 'type' => 'int', 'required' => true),
					array('index' => 'ag_wm_margin_type', 'label' => 'watermark margin type'),
				)
			),
			'ag_wm_proport' => array(
				'label' => __('Proportional size?', $ml_key),
				'type'	=> 'checkbox',
				'note'	=> __("If checked, watermark will be resized basing on image sizes", $ml_key),
			),
			'ag_wm_prop_val' => array(
				'label' 	=> __("Proportional sizes", $ml_key),
				'type'		=> '2_numbers',
				'min_val'	=> 1,
				'max_val'	=> 100,	
				'value'		=> '%',
				'def'		=> array(10, 10),
				'note'		=> __('Sets watermark proportional sizes related to images one (horizontally / vertically)', $ml_key),
			),	
			 
			'ag_watermark_opacity' => array(
				'label' 	=> __('Watermark opacity', $ml_key),
				'type'		=> 'slider',
				'min_val'	=> 5,
				'max_val'	=> 100,	
				'step'		=> 5,
				'def'		=> 100,
				'value'		=> '%',
			),
			
			'spcr1' => array(
				'type' => 'spacer',
			),    
			'ag_lwm_block' => array(
				'label' 	=> 'upper watermark block',
				'type'		=> 'custom',
				'callback'	=> 'ag_l_wm_fields'
			),
		),
	),
	
	
	
	
	
	//array('index' => 'ag_watermark_opacity', 'label' => __('Watermark Opacity', $ml_key), 'type' => 'int', 'required' => true),
);
	





####################################
########### CUSTOM CSS #############
####################################		
$structure['cust_css'] = array(	
	'custom_css_wrap' => array(
		'sect_name'	=>  __('High-priority code - applied to Avator Gallery elements', $ml_key),
		'fields' 	=> array(
		
			'ag_custom_css' => array(
				'label' 	=> __('Custom CSS', $ml_key),
				'type'		=> 'code_editor',
				'language'	=> 'css',
			),
		),
	),
);






// No overlay manager? add an advertising!
if(!defined('AGOM_DIR')) {
	$structure['main_opts']['def_gall_sett']['fields']['agom_adv_spacer'] = array(
		'type' => 'spacer',
	);
	$structure['main_opts']['def_gall_sett']['fields']['agom_adv'] = array(
		'type'		=> 'message',
		'content'	=> '
			<style type="text/css">
			.ag_agom_adv td {
				background: #629c2c url("'. AG_URL .'/img/lc_pattern.png") repeat scroll left 5px;	
				border-bottom: none;
			}
			.ag_agom_adv td h3 {
				margin: 7px 0;
				font-size: 16px;
				text-shadow: 0 0 2px rgba(0,0 ,0,0.15);
				letter-spacing: 0.05px;
			}
			.ag_agom_adv td a, .ag_agom_adv td a:hover {
				color: #fff;	
			}
			.ag_agom_adv td a:focus {
				box-shadow: none;	
			}
			.ag_agom_adv td span {
				display: inline-block;
				border: 2px solid #fff;
				padding: 6px 10px;
				position: relative;
				bottom: 0px;
				left: 15px;
				font-size: 14px;
				border-radius: 1px;
				
				-webkit-transition: all .2s ease; 
				-ms-transition: 	all .2s ease; 
				transition: 		all .2s ease; 
			}
			.ag_agom_adv td a:hover span {
				border-color: transparent;
				background: #fff;
				color: #629c2c;
				text-shadow: none;
			}
			</style>
		
			<h3><a href="https://lcweb.it/global-gallery/overlay-manager-add-on?ref=mgs" target="_blank">Need more? Give an unique touch to your grids with the Overlay Manager add-on <span>check it!</span></a></h3>
		',
	);	
}


// AG-FILTER - manipulate settings structure
$GLOBALS['ag_settings_structure'] = apply_filters('ag_settings_structure', $structure);
