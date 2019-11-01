<?php

/////////////////////////////////////////////////////
// [g-gallery]
function ag_gallery_shortcode( $atts, $content = null ) {
	include_once(AG_DIR . '/functions.php');
	include_once(AG_DIR . '/classes/ag_overlay_manager.php');
	
	extract( shortcode_atts( array(
		'gid' 			=> '',
		'random' 		=> 0,
		'watermark' 	=> 0,
		'filters'		=> 0,
		'pagination' 	=> '',
		'overlay' 		=> 'default',
		'wp_gall_hash' 	=> '' // hidden parameter for WP galleries - images list hash
	), $atts ) );

	if($gid == '') {return '';}
	
	// init
	$gallery 	= '';
	$type 		= (!empty($wp_gall_hash)) ? 'wp_gall' : get_post_meta($gid, 'ag_type', true);
	
	$thumb_q 	= get_option('ag_thumb_q', 90);
	$timestamp 	= current_time('timestamp');
	$unique_id 	= uniqid();
	
	$raw_layout = get_post_meta($gid, 'ag_layout', true);
	$layout 	= ag_check_default_val($gid, 'ag_layout');
	
	// layout options
	if($layout == 'standard') {
		$thumb_w = (int)ag_check_default_val($gid, 'ag_thumb_w', 150, $raw_layout);
		$thumb_h = (int)ag_check_default_val($gid, 'ag_thumb_h', 150, $raw_layout);
	}
	elseif($layout == 'columnized') {
		$thumb_w = (int)ag_check_default_val($gid, 'ag_colnzd_thumb_max_w', 260, $raw_layout);

		$thumb_h_val  = (int)ag_check_default_val($gid, 'ag_colnzd_thumb_h', 120, $raw_layout);
		$thumb_h_type = ag_check_default_val($gid, 'ag_colnzd_thumb_h_type', 'px', $raw_layout);
		$thumb_h = ($thumb_h_type == 'px') ? $thumb_h_val : ($thumb_w * ($thumb_h_val / 100));
	}
	elseif($layout == 'masonry') { 
		$cols = (int)ag_check_default_val($gid, 'ag_masonry_cols', 4, $raw_layout); 
		$default_w = (int)get_option('ag_masonry_basewidth', 960);
		
		$min_w = get_option('ag_masonry_min_width', 150);
		$col_w = floor( $default_w / $cols );
		if($col_w < $min_w) {$col_w = $min_w;}
	}
	else { 
		$row_h = ag_check_default_val($gid, 'ag_photostring_h', 180, $raw_layout); 
	}
	
	
	//// prepare images
	// get them
	$images = ag_frontend_img_prepare($gid, $type, $wp_gall_hash);
	if(!is_array($images) || !count($images)) {return '';}
	$gall_img_count = count($images);
	
	
	// gallery tags
	if(!empty($filters)) {
		$tags = ag_get_gallery_tags($images);	
		$gallery .= ag_gallery_tags_code($unique_id, $gid, $tags);
	}
	
	
	// paginate?
	$raw_paginate 	= get_post_meta($gid, 'ag_paginate', true);
	$paginate 		= ag_check_default_val($gid, 'ag_paginate');
	$per_page 		= (int)ag_check_default_val($gid, 'ag_per_page', 15, $raw_paginate);

	// randomize images 
	$randomized_order = ((int)$random) ? ag_random_img_indexes(count($images)) : false;

	// images array to be used (eventually watermarked)
	$selection = (!$paginate) ? 'all' : array(0, $per_page);  
	$images = ag_frontend_img_split($gid, $images, $selection, $randomized_order, $watermark);	
	if(!is_array($images) || !count($images)) {return '';}

	// pagination limit
	if($paginate && $gall_img_count > $per_page) {
		$tot_pages = ceil($gall_img_count / $per_page );	
	}
		
	// additional parameters
	switch($layout) {
		case 'columnized' :
			$add_param = 'data-col-maxw="'.$thumb_w.'"';
			break;
		
		case 'masonry' :
			$add_param = 'data-col-num="'.$cols.'"';
			
			if((int)get_post_meta($gid, 'ag_masonry_min_width', true)) {
				$add_param .= ' data-minw="'. (int)get_post_meta($gid, 'ag_masonry_min_width', true) .'"';	
			}
			break;
			
		case 'string' :
			$add_param = 'data-row-h="'.$row_h.'"';
			
			if((int)get_post_meta($gid, 'ag_photostring_min_width', true)) {
				$add_param .= ' data-minw="'. (int)get_post_meta($gid, 'ag_photostring_min_width', true) .'"';	
			}
			break;	
		
		default :
			$add_param = '';
			break;
	}
	
	
	// image overlay code 
	$ol_man = new ag_overlay_manager($overlay, false, 'gall');
	
	// overlay att value
	$overlay_att = $overlay; 
	if((!$overlay_att || $overlay_att == 'default') && defined('AGOM_DIR') && get_option('ag_gall_default_overlay')) {
		$overlay_att = get_option('ag_gall_default_overlay');
	}
	

	// build
	$gallery .= '
	<div id="'.$unique_id.'" class="ag_gallery_wrap ag_'.$layout.'_gallery gid_'.$gid.' '.$ol_man->ol_wrap_class.' '.$ol_man->txt_vis_class.'" data-ag_ol="'.$overlay_att.'" '.$add_param.' '.$ol_man->img_fx_attr.' rel="'.$gid.'" data-nores-txt="'. esc_attr(__('No images found in this page', 'ag_ml')) .'">
      '.ag_preloader().'
	  <div class="ag_container">';	
	    
	  foreach($images as $img_index => $img) {
		  
		// image link codes
		if(isset($img['link']) && trim($img['link']) != '') {
			if($img['link_opt'] == 'page') {$thumb_link = get_permalink($img['link']);}
			else {$thumb_link = $img['link'];}
			
			$open_tag = '<div data-ag-link="'.$thumb_link.'"';
			$add_class = "ag_linked_img";
			$close_tag = '</div>';
		} else {
			$open_tag = '<div';
			$add_class = "";
			$close_tag = '</div>';
		}
		
		
		// dunno why, but wp gall managed src must be managed // TODO
		if($wp_gall_hash) {
		  $img['url'] = ag_img_id_to_url($img['url']);  
		}
		
		
		// SEO noscript part for full-res image
		$noscript = '<noscript><img src="'.$img['url'].'" alt="'.ag_sanitize_input($img['title']).'" /></noscript>';
		
		// common attributes + classes
		$atts = 'class="ag_img '.$add_class.'" data-ag-url="'.$img['url'].'" data-ag-title="'.ag_sanitize_input($img['title']).'" data-ag-author="'.ag_sanitize_input($img['author']).'" data-ag-descr="'.ag_sanitize_input($img['descr']).'" data-img-id="'.$img_index.'" rel="'.$gid.'"';
		
		
		
		/////////////////////////
		// standard layout
		if($layout == 'standard') {	 
			
			$thumb = ag_thumb_src($img['path'], $thumb_w, $thumb_h, $thumb_q, $img['thumb']);
			$gallery .= '
			'. $open_tag .' '. $atts .'>
			  <div class="ag_img_inner">';
				
				$gallery .= '
				<div class="ag_main_img_wrap">
					<img src="" data-ag-lazy-src="'.$thumb.'" alt="'.ag_sanitize_input($img['title']).'" class="ag_photo ag_main_thumb" />
					'.$noscript.'
				</div>';	
				
				$gallery .= '
				<div class="ag_overlays">'. $ol_man->get_img_ol($img['title'], $img['descr'], $img['author'], $img['url']) .'</div>';	
				
			$gallery .= '</div>' . $close_tag;
		}
		
		
		/////////////////////////
		// columnized layout
		else if($layout == 'columnized') {
			
			$thumb = ag_thumb_src($img['path'], $thumb_w, $thumb_h, $thumb_q, $img['thumb']);	
			$gallery .= '
			'. $open_tag .' '. $atts .'>
			  <div class="ag_img_inner" style="padding-bottom: '.$thumb_h_val.$thumb_h_type.'">
				<div class="ag_main_img_wrap">
					<div class="ag_img_wrap_inner">
						<img src="" data-ag-lazy-src="'.$thumb.'" alt="'.ag_sanitize_input($img['title']).'" class="ag_photo ag_main_thumb" />
						'.$noscript.'	
					</div>
				</div>
				<div class="ag_overlays">'. $ol_man->get_img_ol($img['title'], $img['descr'], $img['author'], $img['url']) .'</div>	
			</div>'.$close_tag;  
		}
		
		
		/////////////////////////
		// masonry layout
		else if($layout == 'masonry') {
			
			$thumb = ag_thumb_src($img['path'], ($col_w + 40), false, $thumb_q, $img['thumb']);	
			$gallery .= '
			'. $open_tag .' '. $atts .'>
			  <div class="ag_img_inner">
				<div class="ag_main_img_wrap">
					<div class="ag_img_wrap_inner">
						<img src="" data-ag-lazy-src="'.$thumb.'" alt="'.ag_sanitize_input($img['title']).'" class="ag_photo ag_main_thumb" />
						'.$noscript.'	
					</div>
				</div>
				<div class="ag_overlays">'. $ol_man->get_img_ol($img['title'], $img['descr'], $img['author'], $img['url']) .'</div>	
			</div>'.$close_tag;  
		}
		
		  
		/////////////////////////
		// photostring layout
		else {

			$thumb = ag_thumb_src($img['path'], false, $row_h, $thumb_q, $img['thumb']);
			$gallery .= '
			'. $open_tag .' '. $atts .'>
			  <div class="ag_img_inner" style="height: '.$row_h.'px;">
			  	<div class="ag_main_img_wrap">
					<div class="ag_img_wrap_inner">
						<img src="" data-ag-lazy-src="'.$thumb.'" alt="'.ag_sanitize_input($img['title']).'" class="ag_photo ag_main_thumb" />	
						'.$noscript.'
					</div>
				</div>
				<div class="ag_overlays">'. $ol_man->get_img_ol($img['title'], $img['descr'], $img['author'], $img['url']) .'</div>	
			</div>'.$close_tag;  
		}	
	}
	  
	// container height trick for photostring
	if($layout == 'string') {$gallery .= '<div class="ag_string_clear_both" style="clear: both;"></div>';}

	// container closing
	$gallery .= '</div>'; 
	
	
	/////////////////////////
	// pagination
	if($paginate && $gall_img_count > $per_page) {		
		$gallery .= '<div class="ag_paginate ag_pag_'.get_option('ag_pag_style', 'light').'" ag-random="'.$random.'" data-ag-totpages="'.$tot_pages.'">';
		
		// pagination system
		$pag_system = get_option('ag_pag_system', 'standard');
		if($pagination) {$pag_system = $pagination;}
		
		// classic pagination
		if($pag_system == 'standard') {
			$pag_layout = get_option('ag_pag_layout', 'standard'); 
			$pl_class = '';
			
			if($pag_layout == 'only_num') {$pl_class .= 'ag_pag_onlynum';}
			if($pag_layout == 'only_arr_mb' || $pag_layout == 'only_arr') {
				$pl_class .= 'ag_only_arr';
				$pl_class .= ($pag_layout == 'only_arr_mb') ? ' ag_monoblock' : ' ag_detach_arr';
			}
			
			// mid nav - layout code
			if($pag_layout == 'standard') {
				$mid_code = '<div class="ag_nav_mid"><div>'. __('page', 'ag_ml') .' <span>1</span> '. __('of', 'ag_ml') .' '.$tot_pages.'</div></div>';	
			}
			elseif($pag_layout == 'only_num') {
				$mid_code = '<div class="ag_nav_mid"><div><span>1</span> <font>/</font> '.$tot_pages.'</div></div>';	
			}
			else {
				$mid_code = '<div class="ag_nav_mid" style="display: none;"><div><span>1</span> <font>-</font> '.$tot_pages.'</div></div>';
			}
			
			$gallery .= '
			<div class="ag_standard_pag '.$pl_class.'">
				<div class="ag_nav_left ag_prev_page ag_pag_disabled"><i></i></div>
				'.$mid_code.'
				<div class="ag_nav_right ag_next_page"><i></i></div>
			</div>';		
		}
		
		// infinite scroll
		else if($pag_system == 'inf_scroll') {
			$gallery .= '
			<div class="ag_infinite_scroll">
				<div class="ag_nav_left"></div>
				<div class="ag_nav_mid"><span>'. __('show more', 'ag_ml') .'</span></div>
				<div class="ag_nav_right"></div>
			</div>';
		}
		
		// numbered buttons
		else if($pag_system == 'num_btns') {
			$gallery .= '<div class="ag_num_btns_wrap">';
				for($a=1; $a<=$tot_pages; $a++) {
					$disabled = ($a==1) ? 'ag_pag_disabled' : '';
					$gallery .= '<div class="ag_pagenum '.$disabled.'" title="'. __('go to page', 'ag_ml') .' '.$a.'" rel="'.$a.'">'.$a.'</div>';
				}
			$gallery .= '</div>';
		}
		
		// dots
		else {
			$gallery .= '<div class="ag_dots_pag_wrap">';
				for($a=1; $a<=$tot_pages; $a++) {
					$disabled = ($a==1) ? 'ag_pag_disabled' : '';
					$gallery .= '<div class="ag_pag_dot '.$disabled.'" title="'. __('go to page', 'ag_ml') .' '.$a.'" rel="'.$a.'"></div>';
				}
			$gallery .= '</div>';
		}
		
		$gallery .= '</div>';
	}
	
	$gallery .= '<div style="clear: both;"></div>
	</div>'; // gallery wrap closing
	
	
	// pagination JS vars (WP-gall imgages - watermark flag - random order trail)
	if($paginate && $gall_img_count > $per_page) {	
		$random = (!empty($random)) ? json_encode($randomized_order) : 'false';
		
		$gallery .= '
		<script type="text/javascript"> 
		if(typeof(ag_pag_vars) == "undefined") {ag_pag_vars = {};}
		ag_pag_vars["'.$unique_id.'"] = {
			per_page		: '.$per_page.',
			watermark 		: '.(int)$watermark.',
			random_trail 	: "'. $random .'",
			wp_gall_hash	: "'. $wp_gall_hash .'"
		};
		</script>';
	}
	
	
	
	// js - init gallery
	$gallery .= '
	<script type="text/javascript"> 
	jQuery(document).ready(function($) { 
		if(typeof(ag_galleries_init) == "function") {
			ag_galleries_init("'.$unique_id.'"); 
		}
	});
	</script>';


	$gallery = str_replace(array("\r", "\n", "\t", "\v"), '', $gallery);
	return $gallery;
}
add_shortcode('g-gallery', 'ag_gallery_shortcode');

