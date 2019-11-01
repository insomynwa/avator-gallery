<?php

/////////////////////////////////////
////// PAGINATION ///////////////////
/////////////////////////////////////

function ag_pagination() {
	if(isset($_POST['ag_type']) && $_POST['ag_type'] == 'ag_pagination') {
		include_once(AG_DIR . '/functions.php');
		include_once(AG_DIR . '/classes/ag_overlay_manager.php');
		
		if(!isset($_POST['gid']) || !filter_var($_POST['gid'], FILTER_VALIDATE_INT)) {die('Gallery ID is missing');}
		$gid = (int)$_POST['gid'];
		
		if(!isset($_POST['ag_page'])) {die('wrong page number');}
		$page = (is_array($_POST['ag_page'])) ? $_POST['ag_page'] : (int)$_POST['ag_page'];
		
		// overlay
		if(!isset($_POST['ag_ol'])) {die('overlay is missing');}
		$overlay = $_POST['ag_ol'];
		
		
		// is applying a filter? handle matching image indexes
		$filtered_indexes = (isset($_POST['ag_filtered_imgs'])) ? (array)$_POST['ag_filtered_imgs'] : false;
		
				
		// randomized images list trail
		if(!isset($_POST['ag_pag_vars']) || !is_array($_POST['ag_pag_vars'])) {die('missing gallery infos');}
		$per_page			= (int)$_POST['ag_pag_vars']['per_page']; if(!$per_page) {$per_page = 15;}
		$watermark 			= $_POST['ag_pag_vars']['watermark'];
		$randomized_order	= $_POST['ag_pag_vars']['random_trail']; if(!empty($randomized_order)) {$randomized_order = json_decode($randomized_order);}
		$wp_gall_hash		= $_POST['ag_pag_vars']['wp_gall_hash'];

		// get the gallery data
		$type 		= get_post_meta($gid, 'ag_type', true);
		$raw_layout = get_post_meta($gid, 'ag_layout', true);
		$thumb_q 	= get_option('ag_thumb_q', 90);

		// WP gall pagination fix
		if(!$type) {$type = 'wp_gall';}


		// layout options
		$layout = ag_check_default_val($gid, 'ag_layout');
		if($layout == 'standard') {
			$thumb_w = ag_check_default_val($gid, 'ag_thumb_w', 150, $raw_layout);
			$thumb_h = ag_check_default_val($gid, 'ag_thumb_h', 150, $raw_layout);
		}
		elseif($layout == 'columnized') {
			$thumb_w = (int)ag_check_default_val($gid, 'ag_colnzd_thumb_max_w', 260, $raw_layout);

			$thumb_h_val  = (int)ag_check_default_val($gid, 'ag_colnzd_thumb_h', 120, $raw_layout);
			$thumb_h_type = ag_check_default_val($gid, 'ag_colnzd_thumb_h_type', 'px', $raw_layout);
			$thumb_h = ($thumb_h_type == 'px') ? $thumb_h_val : ($thumb_w * ($thumb_h_val / 100));
		}
		elseif($layout == 'masonry') { 
			$cols = ag_check_default_val($gid, 'ag_masonry_cols', 4, $raw_layout); 
			$default_w = (int)get_option('ag_masonry_basewidth', 960);
			$col_w = floor( $default_w / $cols );
		}
		else {
			$row_h = ag_check_default_val($gid, 'ag_photostring_h', 180, $raw_layout);
		}
		
			
		//// prepare images
		// get them
		$images = ag_frontend_img_prepare($gid, $type, $wp_gall_hash);
		if(!is_array($images) || !count($images)) {$images = array();}
		$gall_img_count = count($images);

		// images array to be used (eventually watermarked)
		//// consider also an array value - flagging an infinite scroll restore after a filter
		
		if(is_array($page)) {
			$last_elem = (int)end($page) * $per_page;
			$selection = array(0, $last_elem);
		}
		else {
			$start = ($page == 1) ? 0 : (($page - 1) * $per_page); 
			$selection = ($filtered_indexes) ? 'all' : array($start, ($page * $per_page));  
		}
		
		$images = ag_frontend_img_split($gid, $images, $selection, $randomized_order, $watermark);	// PASS ALSO WATERMARK FLAG!!!!
		if(!is_array($images) || !count($images)) {$images = array();}
	
	
		// pagination limit
		if($gall_img_count > $per_page) {
			$tot_pages = ceil($gall_img_count / $per_page );	
		}
		
		
		// if is filtering - return only them
		if($filtered_indexes) {
			$filtered = array();
			
			foreach($filtered_indexes as $fid) {
				if(isset($images[$fid])) {
					$filtered[] = $images[$fid];	
				}
			}
			
			$images = $filtered;
		}
		
		
		// image overlay code 
		$ol_man = new ag_overlay_manager($overlay, false, 'gall');
			
		// create new block of gallery HTML
		$gallery = '';
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
			
			// SEO noscript part for full-res image
		  	$noscript = '<noscript><img src="'.$img['url'].'" alt="'.ag_sanitize_input($img['title']).'" /></noscript>';
					
			// common attributes + classes
			$atts = 'class="ag_img '.$add_class.'" data-ag-url="'.$img['url'].'" data-ag-title="'.ag_sanitize_input($img['title']).'" data-ag-author="'.ag_sanitize_input($img['author']).'" data-ag-descr="'.ag_sanitize_input($img['descr']).'" data-img-id="'.$img_index.'" rel="'.$gid.'"';
			
			
			
			/////////////////////////
			// standard layout
			if($layout == 'standard') {	 
				
				$thumb = ag_thumb_src($img['path'], $thumb_w, $thumb_h, $thumb_q, $img['thumb']);
				$gallery .= '
				'.$open_tag.' '.$atts.'>
				  <div class="ag_img_inner">';
					
					$gallery .= '
					<div class="ag_main_img_wrap">
						<div class="ag_img_wrap_inner">
							<img src="" data-ag-lazy-src="'.$thumb.'" alt="'.ag_sanitize_input($img['title']).'" class="ag_photo ag_main_thumb" />
							'.$noscript.'
						</div>
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
				'.$open_tag.' '.$atts.'>
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
				'.$open_tag.' '.$atts.'>
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
				'.$open_tag.' '.$atts.'>
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
		
		$pag = array(
			'html' => $gallery,
			'more' => (is_array($page) || $gall_img_count > ($page * $per_page)) ? 1 : 0,
		);
		
		echo json_encode($pag);
		die();
	}
}
add_action('init', 'ag_pagination');




//////////////////////////////////////////////////////////////////////////////




////////////////////////////////////////////
////// LOAD GALLERY WITHIN COLLECTION //////
////////////////////////////////////////////

function ag_load_coll_gallery() {
	if(isset($_POST['ag_type']) && $_POST['ag_type'] == 'ag_load_coll_gallery') {
		
		if(!isset($_POST['gdata'])) {die('data is missing');}
		$gdata = json_decode(base64_decode($_POST['gdata']), true);
		
		$resp = '';
		if(get_option('ag_coll_show_gall_title')) {
			$resp .= '<h3 class="ag_coll_gall_title">'. get_the_title($gdata['id']) .'</h3>';
		}
		
		$resp .= '<div class="ag_coll_gall_topmargin"></div>';
		
		$resp .= do_shortcode('[g-gallery gid="'.$gdata['id'].'" random="'.$gdata['rand'].'" filters="'.$gdata['filters'].'" watermark="'.$gdata['wmark'].'"]');
		die($resp);
	}
}
add_action('init', 'ag_load_coll_gallery');

