<?php

/////////////////////////////////////////////////////
// [g-collection]
function ag_collection_shortcode( $atts, $content = null ) {
	include_once(AG_DIR . '/functions.php');
	include_once(AG_DIR . '/classes/ag_overlay_manager.php');
	
	extract( shortcode_atts( array(
		'cid' => '',
		'filter' => 0,
		'random' => 0,
		'overlay' => 'default'
	), $atts ) );

	if($cid == '') {return '';}
	
	// init
	$collection = '';
	
	$thumb_q = (int)get_option('ag_thumb_q', 90);
	$timestamp = current_time('timestamp');
	$unique_id = uniqid();
	
	$basewidth = get_option('ag_masonry_basewidth', 960);
	$thumb_w = (int)get_option('ag_coll_thumb_max_w', 300);
	
	// find out thumb sizes - calculate height if is percentage based
	$thumb_h_val = (int)get_option('ag_coll_thumb_h', 140);
	$thumb_h_type = get_option('ag_coll_thumb_h_type', 'px');
	$thumb_h = ($thumb_h_type == 'px') ? $thumb_h_val : ($thumb_w * ($thumb_h_val / 100));
	
	// collection elements
	$coll_data = get_term($cid, 'ag_collections');
	$coll_composition = unserialize($coll_data->description);
	
	$coll_galleries = $coll_composition['galleries'];
	$coll_cats = $coll_composition['categories'];
	
	
	// fetch galleries elements
	$galleries = array();
	if(is_array($coll_galleries)) {
		foreach($coll_galleries as $gdata) {
			$gid = $gdata['id'];
			
			if(isset($gdata['cust_img']) && !empty($gdata['cust_img'])) {
				$img_data = array(
					'src' => ag_img_src_on_type($gdata['cust_img'], 'wp'),
					'align' => 'c'
				);	
			}
			else {
				$img_data = ag_get_gall_first_img($gid, 'full');
			}
			
			if($img_data) {
				if($gdata['wmark'] && filter_var(get_option('ag_watermark_img'), FILTER_VALIDATE_URL)) {
					$new_paths = ag_watermark($img_data['src']);	
					$img_data['src'] = $new_paths['path'];
				}
				
				$galleries[] = array(
					'id'		=> (int)$gid, 
					'thumb'		=> ag_thumb_src($img_data['src'], ($thumb_w + 2), $thumb_h, $thumb_q, $img_data['align']),
					'full_url'	=> $img_data['src'],
					'title'		=> get_the_title($gid), 
					
					'rand'		=> (int)$gdata['rand'],
					'filters'	=> (isset($gdata['filters'])) ? (int)$gdata['filters'] : 0,
					'wmark'		=> (int)$gdata['wmark'],
					
					'link_subj' => (isset($gdata['link_subj'])) ? $gdata['link_subj'] : 'none',
					'link_val'	=> (isset($gdata['link_val'])) ? $gdata['link_val'] : '',
					'descr'		=> (isset($gdata['descr'])) ? $gdata['descr'] : ''
				);	
			}
		}
	}
	
	// check for existing galleries
	if(count($galleries) == 0) {return '';}	
		
	// randomize images 
	if((int)$random == 1) {shuffle($galleries);}
	
	// image overlay code 
	$ol_man = new ag_overlay_manager($overlay, false, 'coll');
	
	// overlay att value
	$overlay_att = $overlay; 
	if((!$overlay_att || $overlay_att == 'default') && defined('AGOM_DIR') && get_option('ag_coll_default_overlay')) {
		$overlay_att = get_option('ag_coll_default_overlay');
	}


	// build
	$collection .= '
	<div id="'.$unique_id.'" class="ag_gallery_wrap ag_collection_wrap cid_'.$cid.'" rel="'.$cid.'" '.$ol_man->img_fx_attr.'>';
      
	  // table structure start
	  $collection .= '
	  <table class="ag_coll_table">
	  	<tr><td class="ag_coll_table_cell ag_coll_table_first_cell">';
	  
	  
		  // filter
		  if($filter) {
			  $filter_code = ag_coll_filter_code($coll_cats);
			  
			  if($filter_code) {
				  $filter_type = (get_option('ag_use_old_filters')) ? 'ag_textual_filters' : 'ag_btn_filters';
				  $collection .= '<div id="agf_'.$cid.'" class="ag_filter '.$filter_type.'">'.$filter_code.'</div>';
			  
				  // mobile dropdown 
				  if(get_option('mg_dd_mobile_filter')) {
					  $filter_code = ag_coll_filter_code($coll_cats, 'dropdown');
					  
					  if($filter_code) {
						  $collection .= '<div id="agmf_'.$cid.'" class="ag_mobile_filter">'. $filter_code .'<i></i></div>';
					  }
				  }
			  }
		  }
		  
	  
		  // collection container 
		  $collection .= '<div id="agco_'.$cid.'" class="ag_coll_outer_container '.$ol_man->txt_vis_class.' '.$ol_man->ol_wrap_class.'" data-ag_ol="'.$overlay_att.'"><div class="ag_container ag_coll_container">'.ag_preloader();
		  $ol_type = get_option('ag_overlay_type');
		  
		  foreach($galleries as $gal) {
			  $gall_cats = ag_gallery_cats($gal['id'], $return = 'class_list');
			  $gall_cats_list = (is_array($gall_cats)) ? '' : $gall_cats;
			 
			  // image link codes
			  if(isset($gal['link_subj']) && trim($gal['link_subj']) != 'none') {
				  if($gal['link_subj'] == 'page') {$thumb_link = get_permalink($gal['link_val']);}
				  else {$thumb_link = $gal['link_val'];}
				  
				  $link_tag = 'data-ag-link="'.$thumb_link.'"';
				  $add_class = "ag_linked_img";
			  } else {
				  $link_tag = '';
				  $add_class = '';
			  }
			 
			 
			 // replace %IMG-NUM% placeholder
			 $gal['descr'] = str_replace('%IMG-NUM%', (int)get_post_meta($gal['id'], 'ag_img_count', true), $gal['descr']);
			 $gal['descr'] = str_replace(array('&apos;', '&quot;', '&lt;', '&gt;', '&amp;'), array('\'', '"', '<', '>', '&'), $gal['descr']); // allow HTML execution
			 
			  // title overlay position switch
			  if(get_option('ag_coll_title_under')) {
				$descr = (!empty($gal['descr'])) ? '<div class="ag_img_descr_under">'.$gal['descr'].'</div>' : '';
				$outer_ol = '<div class="ag_main_overlay_under"><div class="ag_img_title_under">'.$gal['title'].'</div>'.$descr.'</div>';  
				
				$inner_ol = $ol_man->get_img_ol('', '', '', $gal['thumb']);
			  } else {
				  $inner_ol = $ol_man->get_img_ol($gal['title'], $gal['descr'], '', $gal['thumb']);
				  $outer_ol = ''; 
			  }
			  
			  // SEO noscript part for full-res image
			  $noscript = '<noscript><img src="'.$gal['full_url'].'" alt="'.ag_sanitize_input($gal['title']).'" /></noscript>';
			  
			  
			  // data array to recall gallery through ajax
			  $gall_data = base64_encode( json_encode(array(
			  	'id' 		=> $gal['id'],
				'rand' 		=> $gal['rand'],
				'filters' 	=> $gal['filters'],
				'wmark'		=> $gal['wmark']
			  )));
			  
			  $collection .= '
			  <div class="ag_coll_img_wrap '.$gall_cats_list.'">
				  <div class="ag_img ag_coll_img '.$add_class.'" rel="'.$gal['id'].'" data-gall-data="'.$gall_data.'" style="padding-bottom: '.$thumb_h_val.$thumb_h_type.'" '.$link_tag.'>
					  <div class="ag_main_img_wrap">
						  <div class="ag_img_wrap_inner">
							  <img src="" data-ag-lazy-src="'.$gal['thumb'].'" alt="'.ag_sanitize_input($gal['title']).'" class="ag_photo ag_main_thumb" />
							  '.$noscript.'
						  </div>
					  </div>
					  <div class="ag_overlays">'.$inner_ol.'</div>
				  </div>
				  '.$outer_ol.'
			  </div>';	
		  }

		  // container - outer-container closing
		  $collection .= '</div></div>'; 
		  
	// end collection cell and start gallery one
	$collection .= '</td><td class="ag_coll_table_cell">';  
		
		// "back to" elements
		$back_to_str = get_option('ag_coll_back_to');
		if(empty($back_to_str)) {$back_to_str = '<i class="fa fa-caret-left" aria-hidden="true"></i> '.__('Back to collection', 'ag_ml');}
		$btn_style = (get_option('ag_use_old_filters')) ? '' : 'ag_coll_back_to_new_style';
		   
		// gallery container
		$collection .= '	
		<div class="ag_coll_gallery_container">
		   <span id="ag_cgb_'.$unique_id.'" class="ag_coll_go_back '.$btn_style.'">'.$back_to_str.'</span>
		   <div class="ag_gallery_wrap"></div>
		</div>';
	
	// close table and the main wrapper
	$collection .= '</td></tr></table>
		<div style="clear: both;"></div>
	</div>'; // collection wrap closing
	
	
	
	// js - init collection ajax suppport
	$collection .= '
	<script type="text/javascript"> 
	jQuery(document).ready(function($) { 
		if(typeof(ag_galleries_init) == "function") {
			ag_galleries_init("'.$unique_id.'");
		}
	});
	</script>';
	
		
	$collection = str_replace(array("\r", "\n", "\t", "\v"), '', $collection);
	return $collection;
}
add_shortcode('g-collection', 'ag_collection_shortcode');

