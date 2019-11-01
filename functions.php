<?php

// get the current URL
function ag_curr_url() {
	$pageURL = 'http';
	
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://" . $_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];

	return $pageURL;
}
	

// get file extension from a filename
function ag_stringToExt($string) {
	// remove url parameters
	if(strpos($string, '?') !== false) {
		$arr = explode('?', $string);
		$string = $arr[0];	
	}
	
	$pos = strrpos($string, '.');
	$ext = strtolower(substr($string,$pos));
	return $ext;	
}


// get filename without extension
function ag_stringToFilename($string, $raw_name = false) {
	$pos = strrpos($string, '.');
	$name = substr($string,0 ,$pos);
	if(!$raw_name) {$name = ucwords(str_replace('_', ' ', $name));}
	return $name;	
}


// normalize a url string
function ag_urlToName($string) {
	$string = ucwords(str_replace('_', ' ', $string));
	return $string;	
}


// remove a folder and its contents
function ag_remove_folder($path) {
	if($objs = @glob($path."/*")){
		foreach($objs as $obj) {
			@is_dir($obj)? ag_remove_folder($obj) : @unlink($obj);
		}
	 }
	@rmdir($path);
	return true;
}


// checkbox checked attribute
function ag_checkbox_check($val) {
	return ($val == 1) ? 'checked="checked"' : '';	
}


// sanitize input field values
function ag_sanitize_input($val) {	
	global $wp_version;
	
	// not sanitize quotes  in WP 4.3 and newer
	$val = ($wp_version >= 4.3) ? esc_attr($val) : str_replace(array('\'', '"', '<', '>', '&'), array('&apos;', '&quot;', '&lt;', '&gt;', '&amp;'), (string)$val);
	return trim($val);
}


// know if server supports cURL followlocation command
function ag_followlocation() {
	return (!ini_get('open_basedir') && !ini_get('safe_mode')) ? true : false; 	
}


// convert HEX to RGB
function ag_hex2rgb($hex) {
   	// if is RGB or transparent - return it
   	$pattern = '/^#[a-f0-9]{6}$/i';
	if(empty($hex) || $hex == 'transparent' || !preg_match($pattern, $hex)) {return $hex;}
  
	$hex = str_replace("#", "", $hex);
   	if(strlen($hex) == 3) {
		$r = hexdec(substr($hex,0,1).substr($hex,0,1));
		$g = hexdec(substr($hex,1,1).substr($hex,1,1));
		$b = hexdec(substr($hex,2,1).substr($hex,2,1));
	} else {
		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2));
	}
	$rgb = array($r, $g, $b);
  
	return 'rgb('. implode(",", $rgb) .')'; // returns the rgb values separated by commas
}


// hex color to RGBA
function ag_hex2rgba($hex, $alpha) {
	$rgba = str_replace(array('rgb', ')'), array('rgba', ', '.$alpha.')'), ag_hex2rgb($hex));
	return $rgba;	
}


// convert RGB to HEX
function ag_rgb2hex($rgb) {
   	// if is hex or transparent - return it
   	$pattern = '/^#[a-f0-9]{6}$/i';
	if(empty($rgb) || $rgb == 'transparent' || preg_match($pattern, $rgb)) {return $rgb;}

  	$rgb = explode(',', str_replace(array('rgb(', ')'), '', $rgb));
  	
	$hex = "#";
	$hex .= str_pad(dechex( trim($rgb[0]) ), 2, "0", STR_PAD_LEFT);
	$hex .= str_pad(dechex( trim($rgb[1]) ), 2, "0", STR_PAD_LEFT);
	$hex .= str_pad(dechex( trim($rgb[2]) ), 2, "0", STR_PAD_LEFT);

	return $hex; 
}


// add array index after or before another one
//// $to_inject = array(index => val)
//// $what  	= related array index
//// $where 	= before / after
function ag_inject_array_elem($to_inject, $array, $what, $where = 'after') {
	$tot_elems = count($array);
	if(!$tot_elems) {return $to_inject;}
	
	$keys = array_keys($array);
	$pos = array_search($what, $keys); 
	if($pos === false) {return false;}

	$a = 0;
	$new_arr = array(); 
	foreach($array as $index => $val) {
		if($a == $pos && $where == 'before') {
			$new_arr = $new_arr + $to_inject;	
		}
		
		$new_arr[$index] = $val;
		
		if($a == $pos && $where == 'after') {
			$new_arr = $new_arr + $to_inject;	
		}
		
		$a++;
	}
		
	return $new_arr;
}


// get the upload directory (for WP MU)
function ag_wpmu_upload_dir() {
	$dirs = wp_upload_dir();
	$basedir = $dirs['basedir'] . '/YEAR/MONTH';
	
	return $basedir;	
}


// image ID to path
function ag_img_id_to_path($img_src) {
	if(is_numeric($img_src)) {
		$wp_img_data = wp_get_attachment_metadata((int)$img_src);
		
		if(is_array($wp_img_data) && isset($wp_img_data['file'])) {
			$upload_dirs = wp_upload_dir();
			$img_src = $upload_dirs['basedir'] . '/' . $wp_img_data['file'];
		}
	}
	
	return $img_src;
}


// image ID to url
function ag_img_id_to_url($img_src) {
	if(is_numeric($img_src)) {
		$wp_img_data = wp_get_attachment_metadata((int)$img_src);
		if($wp_img_data) {
			$upload_dirs = wp_upload_dir();
			$img_src = $upload_dirs['baseurl'] . '/' . $wp_img_data['file'];
		}
	}
	
	return $img_src;
}


// thumbnail source switch between timthumb and ewpt
function ag_thumb_src($img_id, $width = false, $height = false, $quality = 80, $alignment = 'c', $resize = 1, $canvas_col = 'FFFFFF', $fx = array()) {
	if(!$img_id) {return false;}
	
	if(get_option('ag_use_timthumb')) {
		$thumb_url = AG_TT_URL.'?src='.ag_img_id_to_path($img_id).'&w='.$width.'&h='.$height.'&a='.$alignment.'&q='.$quality.'&zc='.$resize.'&cc='.$canvas_col;
	} else {
		$thumb_url = easy_wp_thumb($img_id, $width, $height, $quality, $alignment, $resize, $canvas_col , $fx);
	}	
	
	return $thumb_url;
}


// link field generator
function ag_link_field($src, $val = '') {
	if($src == 'page') {
		$code = '<select name="ag_item_link[]" class="ag_link_field">';
		
		foreach(get_pages() as $pag) {
			($val == $pag->ID) ? $selected = 'selected="selected"' : $selected = '';
			$code .= '<option value="'.$pag->ID.'" '.$selected.'>'.$pag->post_title.'</option>';
		}
		
		return $code . '</select>';
	}
	else if($src == 'custom') {
		return '<input type="text" name="ag_item_link[]" value="'.ag_sanitize_input($val).'" class="ag_link_field" />';
	}
	else {
		return '<input type="hidden" name="ag_item_link[]" value="" />';
	}
}


// giving a gallery ID returns the associated categories
function ag_gallery_cats($gid, $return = 'list', $list_divider = ', ') {
	$terms = wp_get_post_terms($gid, 'ag_gall_categories');	
	
	if(count($terms) == 0) {
		return ($return == 'list') ? '' : array();	
	}
	
	$to_return = array();
	foreach($terms as $term) {
		// WPML fix - get original ID
		if (function_exists('icl_object_id')) {
			global $sitepress;
			$term_id = icl_object_id($term->term_id, 'ag_gall_categories', true);
			$term = get_term($term_id, 'ag_gall_categories');
		}
		
		if($return == 'list') {$to_return[] = $term->name;}
		elseif($return == 'class_list') {$to_return[] = 'agc_'.$term->term_id;}
		else {$to_return[] = $term->term_id;}	
	}
	
	if($return == 'list') {return implode($list_divider, $to_return);}
	elseif($return == 'class_list') {return implode(' ', $to_return);}
	else {return $to_return;}	
}


// get the gallery first image
function ag_get_gall_first_img($gid, $return = 'img') {
	
	$first_imgs = (array)get_post_meta($gid, 'ag_first_imgs_data', true);		
	if(!count($first_imgs)) {
		return false;
	} 
	
	$type = get_post_meta($gid, 'ag_type', true);
	$img_src = ag_img_src_on_type($first_imgs[0]['img_src'], $type);
	
	if($return == 'img') {return $img_src;}
	else {
		return array(
			'src' 	=> $img_src,
			'align' => $first_imgs[0]['thumb']
		);		
	}
}



// giving a category, return the associated galleries (for collection builder)
function ag_cat_galleries($cat) {
	if(!$cat) {return false;}
	
	$args = array(
		'posts_per_page'  => -1,
		'post_type'       => 'ag_galleries',
		'post_status'     => 'publish'
	);
	
	if($cat != 'all') {
		$term_data = get_term_by( 'id', $cat, 'ag_gall_categories');	
		$args['ag_gall_categories'] = $term_data->slug;		
	}	
	$raw_galleries = get_posts($args);
	
	$galleries = array();
	foreach($raw_galleries as $gallery) {
		$gid = $gallery->ID;
		$img = ag_get_gall_first_img($gid);
		
		if($img) { 
			$galleries[] = array(  
				'id' =>	$gid,
				'title' => $gallery->post_title,
				'img' => $img,
				'cats' => ag_gallery_cats($gid)
			);
		}
	}
	
	
	if(count($galleries) > 0) {  
		return $galleries;
	} else { 
		return false; 
	}
}



// get all the custom post types
function ag_get_cpt() {
	$args = array(
		'public'   => true,
		'publicly_queryable' => true,
		'_builtin' => false
	);
	$cpt_obj = get_post_types($args, 'objects');
	
	if(count($cpt_obj) == 0) { return false;}
	else {
		$cpt = array();
		foreach($cpt_obj as $id => $obj) {
			$cpt[$id] = $obj->labels->name;	
		}
		
		return $cpt;
	}	
}


// get affected post types for WP gall management
function ag_affected_wp_gall_ct() {
	$basic = array('post','page');	
	$cpt = get_option('ag_extend_wp_gall'); 

	if(is_array($cpt)) {
		$pt = array_merge((array)$basic, (array)$cpt);	
	}
	else {$pt = $basic;}

	return $pt;
}


// return the gallery categories by the chosen order
function ag_order_coll_cats($terms) {
	$ordered = array();
	
	foreach($terms as $term_id) {
		$ord = (int)get_option("ag_cat_".$term_id."_order");
		
		// check the final order
		while( isset($ordered[$ord]) ) {
			$ord++;	
		}
		
		$ordered[$ord] = $term_id;
	}
	
	ksort($ordered, SORT_NUMERIC);
	return $ordered;	
}


// return the collections filter code
function ag_coll_filter_code($terms, $return = 'html') {
	if(!$terms) { return false; }
	else {
		$terms = ag_order_coll_cats($terms);
		$terms_data = array();
		
		$a = 0;
		foreach($terms as $term) {
			$term_data = get_term_by('id', $term, 'ag_gall_categories');
			
			// icon code
			$icon = get_option("mg_cat_".$term['id']."_icon");
			if(!empty($icon)) {
				$icon_code = '<i class="mg_cat_icon fa '.$icon.'"></i>';	
			} 
			else {$icon_code = '';}
			
			if(is_object($term_data)) {
				$icon = get_option("ag_cat_".$term."_icon");
				$icon = (!empty($icon)) ? '<i class="mg_cat_icon fa '.$icon.'"></i>' : ''; 
				
				$terms_data[$a] = array('id' => $term, 'name' => $term_data->name, 'slug' => $term_data->slug, 'icon' => $icon); 		
				$a++;
			}
		}
		
		
		// "ALL" text
		$all_txt = get_option('ag_filters_all_txt');
		if(empty($all_txt)) {$all_txt = __('All', 'ag_ml');}
		
		
		if($return == 'html') {
			$coll_terms_list = '<a class="ag_cats_selected agf agf_all" rel="*" href="javascript:void(0)">'. $all_txt .'</a>';
			$separator = (get_option('ag_use_old_filters')) ? '<span>'. get_option('ag_os_filters_separator', '/') .'</span>' : '';
			
			foreach($terms_data as $term) {
				$coll_terms_list .= $separator .
				'<a class="agf_id_'.$term['id'].' agf" rel="'.$term['id'].'" href="javascript:void(0)">'.$term['icon'] . $term['name'].'</a>';	
			}
			
			return $coll_terms_list;
		}
		
		elseif($return == 'dropdown') {
			$code = '
			<select class="ag_mobile_filter_dd" autocomplete="off">
				<option value="*">'. $all_txt .'</option>';	

				foreach($terms_data as $term) {
					$code .= '<option value="'.$term['id'].'">'.$term['name'].'</option>';	
				}
				
			return $code . '
			</select>';	
		}
	}
}


// clean emoticons from instagram texts
function ag_clean_emoticons($text) {
    $clean_text = "";

    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $text);

    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);

    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);

    return $clean_text;
}


// get RGB color from hex
function ag_hex_to_rgb($hex, $alpha = false) {
	if($alpha) {$alpha = (int)$alpha / 100;}
	
	$hex = str_replace("#", "", $hex);
	if(strlen($hex) == 3) {
	  $r = hexdec(substr($hex,0,1).substr($hex,0,1));
	  $g = hexdec(substr($hex,1,1).substr($hex,1,1));
	  $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	} else {
	  $r = hexdec(substr($hex,0,2));
	  $g = hexdec(substr($hex,2,2));
	  $b = hexdec(substr($hex,4,2));
	}
	
	$rgb = implode(', ', array($r, $g, $b));
	if($alpha) {$rgb .= ', '.$alpha;}
	
	return ($alpha) ? 'rgba('.$rgb.')' : 'rgb('.$rgb.')'; 
}


// img url grab from a string
function ag_string_to_url($string) {
	preg_match_all('/img[^>]*src *= *["\']?([^"\']*)/i', $string, $output, PREG_PATTERN_ORDER);
	if(isset($output[0][0])) {
		$raw_url = $output[0][0];	
		$url = substr($raw_url, 9);
		
		return $url;
	}
	else {return '';}
}


// preloader code
function ag_preloader() {
	return '
	<div class="ag_loader">
	  <div class="agl_1"></div>
	  <div class="agl_2"></div>
	  <div class="agl_3"></div>
	  <div class="agl_4"></div>
	</div>';	
}



// use cURL to get external url contents
function ag_curl_get_contents($url, $followlocation = false) {
	$data = wp_remote_get($url, array('timeout' => 8, 'redirection' => 3));

	// nothing got - use cURL 
	if(is_wp_error($data) || 200 != wp_remote_retrieve_response_code($data) || empty($data['body'])) {
		@ini_set( 'memory_limit', '256M');
		$ch = curl_init();
	
		//curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		
		// followlocation only if needed
		if($followlocation) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		}
		else {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, ag_followlocation());
		}
		
		$data = curl_exec($ch);
		
		// store last URL if followlocation has been performed
		if(strpos($url, 'pinterest.com') !== false || $followlocation) {
			$GLOBALS['ag_curl_true_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); 
		}
		
		curl_close($ch);
		return $data;
	}
	else {
		return $data['body'];	
	}
}


// check remote file existence
function ag_rm_file_exists($url) {
	$ch = curl_init();

	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, ag_followlocation());
	curl_setopt($ch, CURLOPT_URL, $url);
	
	curl_exec($ch);
	$answer = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	return ($answer != '200') ? false : true;
}


/////////////////////////////////////////////////////////////////////////////


// get imagesize with cURL
function ag_getimagesize($url) {
	@ini_set( 'memory_limit', '256M');
	$ext = ag_stringToExt($url);
	
	// ranges for img type
	switch($ext) {
		case '.jpg': case '.jpeg': $range = 32768; break;
		case '.png': $range = 24; break;
		case '.gif': $range = 10; break;
		default: $range = 32768; break; // efault use JPG
	}

	// without curl or for local images
	if(!function_exists('curl_init') || !filter_var($url, FILTER_VALIDATE_URL) || strpos($url, site_url()) !== false) {
		$data = @file_get_contents($url, 0, NULL, 0, $range);
	} 
	else {
		$curlOpt = array(
			CURLOPT_RETURNTRANSFER => true, 
			CURLOPT_HEADER	 => false, 
			CURLOPT_FOLLOWLOCATION => ag_followlocation(),
			CURLOPT_ENCODING => '', 
			CURLOPT_AUTOREFERER => true,
			CURLOPT_FAILONERROR	 => true,
			CURLOPT_CONNECTTIMEOUT => 2,
			CURLOPT_TIMEOUT => 2, 
			CURLOPT_MAXREDIRS => 3, 
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_RANGE => '0-'.$range
		);
		
		$ch = curl_init($url);
		curl_setopt_array($ch, $curlOpt);
		$data = curl_exec($ch);
		curl_close($ch);
	}
	if(strlen($data) == 0) {return false;}


	if($ext == '.png') {
		// avoid errors on tiny png
		if(strlen($data) < 24 || 1==1) {
			list($w, $h) = @getimagesize($url);
			return ($w && $h) ? array($w, $h) : false; 
		}
		
		// The identity for a PNG is 8Bytes (64bits)long
		$ident = unpack('Nupper/Nlower', $data);
		
		// Make sure we get PNG
		if($ident['upper'] !== 0x89504E47 || $ident['lower'] !== 0x0D0A1A0A) {
			return false;
		}

		// Grab the first chunk tag, should be IHDR
		$data = substr($data, 8);
		$chunk = unpack('Nlength/Ntype', $data);
		
		// IHDR must come first, if not we return false
		if($chunk['type'] === 0x49484452) {
			$data = substr($data, 8);
			$info = unpack('NX/NY', $data);
			
			$width = $info['X'];
			$height = $info['Y'];
		}
		else {return false;}
	}
	
	elseif($ext == '.gif') {
		// avoid errors on tiny png
		if(strlen($data) < 10) {
			list($w, $h) = @getimagesize($url);
			return ($w && $h) ? array($w, $h) : false; 
		}
		
		$ident = unpack('nupper/nmiddle/nlower', $data);
		
		// Make sure we get GIF 87a or 89a
		if($ident['upper'] !== 0x4749 || $ident['middle'] !== 0x4638 || ($ident['lower'] !== 0x3761 && $ident['lower'] !== 0x3961)) {
			return false;
		}
		
		$data = substr($data, 6);
		$info = unpack('vX/vY', $data);
		
		$width = $info['X'];
		$height = $info['Y'];
	}
	
	else {
		$im = @imagecreatefromstring($data); // use @ - is normal it returns warnings
		if(!$im) {return false;}
		
		$width = imagesx($im);
		$height = imagesy($im);		
		imagedestroy($im);
	}
			
	return ($width) ? array($width, $height) : false;	
}


/////////////////////////////////////////////////////////////////////////////


// gallery data compress and save
function ag_gall_data_save($gid, $data, $autopop = false, $wp_gall_hash = '') {
	$str = serialize($data);
	if(function_exists('gzcompress') && function_exists('gzuncompress')) {
		$str = gzcompress($str, 9);
	}
	$str = base64_encode($str);
	
	if($autopop){
		update_post_meta($gid, 'ag_autopop_cache', $str); 
	} else {
		update_post_meta($gid, 'ag_gallery'.$wp_gall_hash, $str); 
	}
	
	
	// AG v6 - save also utility meta
	if(empty($wp_gall_hash)) {
		update_post_meta($gid, 'ag_img_count', count($data)); // images count
		
		// first 4 images (URL + thumb center)
		$first_imgs_data = array();		
		for($a=0; $a<4; $a++) {
			
			if(isset($data[$a])) {
				$first_imgs_data[] = array(
					'img_src'	=> $data[$a]['img_src'],
					'thumb' 	=> (isset($data[$a]['thumb'])) ? $data[$a]['thumb'] : 'c',
				);
			}
		}
		update_post_meta($gid, 'ag_first_imgs_data', $first_imgs_data);
	}
	return true;
}


// gallery data uncompress and get 
function ag_gall_data_get($gid, $autopop = NULL, $wp_gall_hash = '') {
	// autopop or not? (if not already specified)
	if($autopop === NULL) {
		$autopop = get_post_meta($gid, 'ag_autopop', true);	
	}
	
	if(!$autopop){ $data = get_post_meta($gid, 'ag_gallery'.$wp_gall_hash, true); }
	else 		 { $data = get_post_meta($gid, 'ag_autopop_cache', true) ;}
	
	if(!is_array($data) && !empty($data)) {
		$string = base64_decode($data);
		if(function_exists('gzcompress') && function_exists('gzuncompress') && !empty($string)) {
			$string = gzuncompress($string);
		}
		$data = (array)unserialize($string);
	}
	
	if(!is_array($data) || (count($data) == 1 && !$data[0])) {$data = false;}
	return $data;
}


/////////////////////////////////////////////////////////////////////////////


// gallery types
function ag_types($type = false) {
	$types = array(
		'wp' 		=> __('Wordpress Library', 'ag_ml'),
		'wp_cat' 	=> __('Wordpress Category', 'ag_ml'),
		'cpt_tax' 	=> __('Custom post type Taxonomy', 'ag_ml'),
		'ag_album'	=> __('Avator Gallery Album', 'ag_ml'),
		'flickr'	=> __('Flickr Album / Photostream / Tag URL', 'ag_ml'),
		'instagram'	=> 'Instagram',
		'pinterest' => __('Pinterest Board', 'ag_ml'),
		'fb'		=> __('Facebook Page Album', 'ag_ml'),
		'picasa'	=> __('Google+ Album', 'ag_ml'),
		'g_drive'	=> 'Google Drive',
		//'onedrive'	=> 'Microsoft OneDrive',
		'twitter'	=> 'Twitter',
		'tumblr'	=> __('Tumblr Blog', 'ag_ml'),
		'rml'		=> 'Real Media Library',
		'ngg'		=> 'nextGEN Gallery',
		'rss'		=> __('RSS Feed', 'ag_ml'),
	);
	
	/*** remove sources if PHP version is old ***/
	$php_ver = (float)substr(PHP_VERSION, 0, 3);

	if($php_ver < 5.4) {unset($types['fb']);}	
	if($php_ver < 5.4) {unset($types['picasa']);}	
	if($php_ver < 5.4) {unset($types['g_drive']);}	
	if($php_ver < 5.6) {unset($types['onedrive']);}	

	return (empty($type)) ? $types : $types[$type];
}


// username field label depending on the type
function ag_username_label($type) {
	switch($type) {
		case 'flickr': 		return __('Set / Profile / Tag URL', 'ag_ml'); break; 
		case 'pinterest': 	return __('Board URL', 'ag_ml'); break;
		case 'instagram':	return __('Username', 'ag_ml'); break; //return __('Username or #hashtag', 'ag_ml'); break;
		case 'twitter':		return __('@Username or #hashtag', 'ag_ml'); break;
		case 'tumblr':		return __('Blog URL', 'ag_ml'); break;
		case 'rss':			return __('Feed URL', 'ag_ml'); break;
		default: 			return __('Username', 'ag_ml'); break;	
	}
}


// cache intervals
function ag_cache_intervals($time = false) {
	$times = array(
		'1' 	=> __('1 Hour', 'ag_ml'),
		'2' 	=> __('2 Hours', 'ag_ml'),
		'6'		=> __('6 Hours', 'ag_ml'),
		'12'	=> __('12 Hours', 'ag_ml'),
		'24'	=> __('1 Day', 'ag_ml'),
		'72'	=> __('3 Days', 'ag_ml'),
		'168'	=> __('One week', 'ag_ml'), 
		'none'	=> __('Never', 'ag_ml')
	);
	
	if($time === false) {return $times;}
	else {return $times[$time];}	
}



// get gallery value and, where needed, recall global value
function ag_check_default_val($gid, $key, $default = '', $prior_value = false) {
	$specific_val = get_post_meta($gid, $key, true);
	
	// if has to check another value (eg. specific gallery layout to determine specific sizes)
	if($prior_value !== false) {
		if((empty($prior_value) || $prior_value == 'default') && $prior_value !== '0') {
			$specific_val = false;	
		}
	}
	
	return ((empty($specific_val) || $specific_val == 'default') && $specific_val !== '0') ? get_option($key, $default) : $specific_val;
}


// create the frontend css and js
function ag_create_frontend_css() {	
	if(!ini_get('allow_url_fopen')) {return false;} // locked server
	
	ob_start();
	include_once(AG_DIR .'/frontend_css.php');
	
	$css = ob_get_clean();
	if(!empty($css)) {
		if(!@file_put_contents(AG_DIR.'/css/custom.css', $css, LOCK_EX)) {$error = true;}
	}
	else {
		if(file_exists(AG_DIR.'/css/custom.css')) {unlink(AG_DIR.'/css/custom.css');}
	}
	
	if(isset($error)) {return false;}
	else {return true;}
}



//////////////////////////////////////////////



// retrieve data for an hub connection
function ag_get_conn_hub_data($gid, $conn_id = false) {
	if(empty($conn_id)) {
		$conn_id = get_post_meta($gid, 'ag_connect_id', true);
		if(empty($conn_id)) {return false;}
	}
	
	$term = get_term($conn_id, 'ag_connect_hub');
	
	if(!is_object($term)) {return false;}
	return  unserialize(base64_decode($term->description));
}


// check if an array key exists and return its value or false
function ag_get_arr_key($array, $key) {
	return (empty($array) || !is_array($array) || !isset($array[$key]))	? false : $array[$key];
}


// get instagram user ID 
function ag_instagram_user_id($username, $token) {
	$api_url = 'https://api.instagram.com/v1/users/search/?q='.urlencode($username).'&access_token='.urlencode( trim($token));
	$json = ag_curl_get_contents($api_url);
	
	if($json === false ) {die( __('Error connecting to Instagram', 'ag_ml') .' ..');}
	$data = json_decode($json, true);

	if($data['meta']['code'] == 400) {return false;}
	else {
		$user_id = false;
		
		// search the exact username
		foreach($data['data'] as $user) {
			if(isset($user['id']) && strtolower($user['username']) == strtolower($username)) {
				$user_id = $user['id'];	
			}
		}
		
		if(!$user_id) {die( __('Username not found', 'ag_ml') .' ..');}
		return $user_id;
	}	
}


// get Flickr subject from given url
function ag_flickr_subj($url) {
	if(strpos($url, "flickr.com") === false) {return false;}	
	
	if		(strpos($url, "/sets/") !== false || strpos($url, "/albums/") !== false) {return 'set';}
	else if (strpos($url, "/tags/") !== false) {return 'tag';}
	else if (strpos($url, "/photos/") !== false) {return 'photostream';}
	else {return false;}
}


// get Flickr set ID or username or tag from url
function ag_flickr_subj_id($url) {
	$url_arr = explode('/', untrailingslashit($url));
	return end($url_arr);
}


// get AG albums subfolders
function ag_get_albums() {
	$albums = glob( get_option('ag_albums_basepath', AGA_DIR).DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
	
	if(!is_array($albums)) {return array();}
	else {
		$new_albums = array();
		foreach($albums as $album) {
			$arr = explode(DIRECTORY_SEPARATOR, $album);
			$folder = end($arr);
			$new_albums[$folder] = ucwords( str_replace(array('_', '-'), array(' ', ' '), $folder) );
		}
		return $new_albums;
	}
}


// get custom post types and taxonomies
function ag_get_cpt_with_tax() {
	$cpt = get_post_types(array('_builtin' => false), 'objects');
	$usable = array(); 
	
	foreach($cpt as $pt) {
		$tax = get_object_taxonomies($pt->name, 'objects');
		
		// add only if has a taxonomy
		if(is_array($tax) && !empty($tax)) {
			$tax_array = array();
			
			foreach($tax as $slug => $data) {
				$tax_array[$slug] = $data->labels->name;	
			}
			
			$usable[ $pt->name ] = array(
				'name' => $pt->labels->name,
				'tax' => $tax_array
			);		
		}
	}
	
	return (empty($usable)) ? array() : $usable;
}


// given cpt + taxonomy - get taxonomy terms in a select field
function ag_get_taxonomy_terms($cpt_tax, $sel_val = '') {
	$arr = explode('|||', $cpt_tax);
	$cats = get_terms($arr[1], 'orderby=name&hide_empty=0');

	$code = '
	<select data-placeholder="'. __('Select a term', 'ag_ml') .' .." name="ag_cpt_tax_term" id="ag_cpt_tax_term" class="lcweb-chosen">
		<option value="">'. __('all', 'ag_ml') .'</option>';
		
		if(is_array($cats)) {
			foreach($cats as $cat ) {
				$sel =  ($cat->term_id == $sel_val) ? 'selected="selected"' : '';
				$code .= '<option value="'.$cat->term_id.'" '.$sel.'>'.$cat->name.'</option>'; 
			}
		}

	return $code . '</select>'; 
}


// get nextGEN galleries
function ag_get_nag_galleries($gid = false) {
	global $wpdb;
	$table_name = $wpdb->prefix . "nag_gallery";	
	
	// check table existing
	if($wpdb->get_var("SHOW TABLES LIKE '". $table_name ."'") != $table_name) {
		die( __('nextGEN gallery plugin seems missing. No trace in the database', 'ag_ml') );	
	}
	
	// specific gallery path condition
	$search = ($gid) ? 'WHERE gid = '. (int)$gid : '';
	$query = $wpdb->get_results("SELECT gid, title, path FROM ". $table_name ." ".$search, ARRAY_A);

	if($gid) {
		// clean base to be usable with WP constants
		$base = $query[0]['path'];
		 
		if(substr($base, 0, 1) == DIRECTORY_SEPARATOR) {$base = substr($base, 1);}
		$base = explode(DIRECTORY_SEPARATOR, $base);
		unset($base[0]);
		
		return implode(DIRECTORY_SEPARATOR, $base);	
	} else {
		return $query;	
	}
}



// get Real Media Library folders
function ag_get_rml_galleries($parent_obj = false, $pre_name = '') {
	
	// check table existing
	if(!defined('RML_FILE')) {
		die( __('Real Media Library plugin must be enabled to use it as source', 'ag_ml') );	
	}
	
	$folders 	= array();
	$obj 		= ($parent_obj) ? $parent_obj->getChildren() : wp_rml_root_childs(); 
	$pre_name	.= ($parent_obj) ? '-' : '';
	
	foreach($obj as $folder) {
		$folders[ $folder->getId() ] = $pre_name .' '. $folder->getName();
		
		// check for childrens
		$childs = ag_get_rml_galleries($folder, $pre_name);
		
		if(!empty($childs)) {
			$folders += $childs;	
		}	
	}

	return $folders;
}



///////////////////////////////////////////////////////////////////



// given the gallery type - return the image path ready to be used
function ag_img_src_on_type($raw_src, $type) {
	
	if(in_array($type, array('wp', 'wp_cat', 'wp_gall', 'cpt_tax', 'rml'))) {
		$img_full_src = ag_img_id_to_path($raw_src);	
	} 
	elseif($type == 'ag_album') {
		$img_full_src = get_option('ag_albums_basepath', AGA_DIR) .'/'. $raw_src;	
	}
	elseif($type == 'ngg') {
		if(strpos($raw_src, WP_CONTENT_DIR) !== false) {$img_full_src = str_replace(WP_CONTENT_DIR.'/', '', $raw_src);} // fix old error in path calculation
		else {$img_full_src = $raw_src;}
		
		if(strpos($img_full_src, 'wp-content/') !== false) {$img_full_src = str_replace('wp-content/', '', $img_full_src);} // fix old error in path calculation
		else {$img_full_src = $img_full_src;}
		
		$img_full_src = (strpos($img_full_src, WP_CONTENT_DIR) === false) ? WP_CONTENT_DIR.'/'.$img_full_src : $img_full_src;	
	}
	else {$img_full_src = $raw_src;}	
	
	return str_replace(' ', '%20', $img_full_src);
}



// given the gallery type - return the image url ready to be used
function ag_img_url_on_type($raw_src, $type) {
	
	if(in_array($type, array('wp', 'wp_cat', 'wp_gall', 'rml'))) {
		$img_url = $src = wp_get_attachment_image_src($raw_src, 'full');
		$img_url = $img_url[0];
	} 
	elseif($type == 'ag_album') {
		$img_url = get_option('ag_albums_baseurl', AGA_URL) .'/'. $raw_src;	
	}
	elseif($type == 'ngg') {
		if(strpos($raw_src, WP_CONTENT_DIR) !== false) {$img_url = str_replace(WP_CONTENT_DIR.'/', '', $raw_src);} // fix old error in path calculation
		else {$img_url = $raw_src;}
		
		if(strpos($img_url, 'wp-content/') !== false) {$img_url = str_replace('wp-content/', '', $img_url);} // fix old error in path calculation
		else {$img_url = $raw_src;}
		
		$img_url = (strpos($img_url, WP_CONTENT_URL) === false) ? WP_CONTENT_URL.'/'.$img_url : $img_url;
	}
	else {$img_url = $raw_src;}	
	
	return str_replace(' ', '%20', $img_url);
}



// check for deleted images in a gallery
function ag_gallery_img_exists($images, $gall_type) {
	if(!is_array($images)) {return array();}
	
	$expired = array();
	foreach($images as $index => $val) {
		$img_src = ag_img_src_on_type($val['img_src'], $gall_type);
		
		if(!function_exists('curl_init') || !filter_var($img_src, FILTER_VALIDATE_URL)) {
			if(!@file_get_contents($img_src)) {$expired[] = $index;}
		}
		else {
			if(!ag_rm_file_exists($img_src)) {$expired[] = $index;}
		}
	}
	
	foreach($expired as $index) {
		unset($images[$index]);	
	}
	
	return $images;
}



// check for expired images in gallery
function ag_expir_img_check($gid, $images, $gall_type, $autopop = false) {
	return $images; // KEEP DISABLED FOR NOW	
	//////////////////
	
	$timestamp = current_time('timestamp');	
	$last_check = (int)get_post_meta($gid, 'ag_last_check', true);
	$check_interval = (int)get_option('ag_check_interval');
	
	if($check_interval != 'none' && ini_get('allow_url_fopen') && $timestamp - $last_check >= $check_interval) {
		$old_images = $images;
		$images = ag_gallery_img_exists($images, $type);
		
		// if there are differences - overwrite
		if(count($old_images) != count($images)) {
			if($autopop) {
				update_post_meta($gid, 'ag_autopop_time', $timestamp);
			}
			
			ag_gall_data_save($gid, $images); 	
		}
		
		update_post_meta($gid, 'ag_last_check', $timestamp);
		return $images;	
	}	
	else {
		return $images;	
	}
}



// update auto-population cache
function ag_autopop_update_cache($gid, $manual_attr = array()) {
	include_once(AG_DIR . '/classes/ag_img_fetcher.php');
	
	$type 		= (empty($manual_attr)) ? get_post_meta($gid, 'ag_type', true) : $manual_attr['type'];
	$o_max_img 	= (empty($manual_attr)) ? get_post_meta($gid, 'ag_max_images', true) : $manual_attr['max_images'];
	$max_img 	= (empty($manual_attr)) ? get_post_meta($gid, 'ag_max_images', true) : $manual_attr['max_images'];
	$random 	= (empty($manual_attr)) ? get_post_meta($gid, 'ag_auto_random', true) : $manual_attr['random'];

	// extra data
	if(empty($manual_attr)) {
		switch($type) {
			case 'wp_cat'	: $extra = get_post_meta($gid, 'ag_wp_cat', true); 	break;
			case 'cpt_tax'	: $extra = array('cpt_tax' => get_post_meta($gid, 'ag_cpt_tax', true), 'term' => get_post_meta($gid, 'ag_cpt_tax_term', true)); 	break;
			case 'ag_album'	: $extra = get_post_meta($gid, 'ag_album', true); 	break;
			case 'fb'		: $extra = get_post_meta($gid, 'ag_fb_album', true); 	break;
			case 'picasa'	: $extra = get_post_meta($gid, 'ag_picasa_album', true); break;
			case 'g_drive'	: $extra = get_post_meta($gid, 'ag_gdrive_album', true); break;
			case 'onedrive'	: $extra = get_post_meta($gid, 'ag_onedrive_album', true); break;
			case 'rml'		: $extra = get_post_meta($gid, 'ag_rml_folder', true); break;
			case 'ngg'		: $extra = get_post_meta($gid, 'ag_nag_gallery', true); break;
			default			: $extra = ''; break; 	
		}
	}
	else {$extra = $manual_attr['extra'];}

	// images fetcher 
	$fetcher = new ag_img_fetcher($gid, $type, $page = 1, $o_max_img, '', $extra);
	$img_data = $fetcher->get;
	
	$images = $img_data['img'];
	if($max_img >= count($images)) {$max_img = count($images);}
	
	if($random == '1') { 
		shuffle($images);
		
		$to_display = array();
		for($a=0; $a < $max_img; $a++) {
			$to_display[]	= $images[$a];
		}
	}
	else {
		$to_display = array();
		for($a=0; $a < $max_img; $a++) {
			if(isset($images[$a])) { $to_display[] = $images[$a]; }
		}
	}
	
	$to_save = array();
	foreach($to_display as $img) {
		if(in_array($type, array('wp', 'wp_cat', 'rml'))) {
			$img_src = $img['id'];
		} 
		elseif($type == 'ag_album' || $type == 'ngg') {
			$img_src = $img['path'];
		}
		else {
			$img_src = $img['url'];
		}

		$to_add = array( 
			'url' 		=> $img['url'],
			'img_src'	=> $img_src,
			'author'	=> $img['author'],
			'title'		=> $img['title'],
			'descr'		=> $img['descr']
		);	
		if(($type == 'wp_cat' || $type == 'cpt_tax') && isset($img['link'])) {
			$to_add['link_opt'] = 'custom';
			$to_add['link'] = $img['link'];
		}

		$to_save[] = $to_add;	
	}
	
	// if the maximum number is not reached, try to add the old images - only if erase past if false
	if(empty($manual_attr) || !$manual_attr['erase_past']) {
		if(count($to_save) < $o_max_img) {
			$old_img = ag_gall_data_get($gid, true);
			if(is_array($old_img)) {
					
				$a = 0;
				while($o_max_img > count($to_save) && isset($old_img[$a]))	 {
					$exists = false;
					foreach($to_save as $img) {
						if($old_img[$a]['img_src'] == $img['img_src']) {$exists = true;}
					}
					
					if(!$exists) {$to_save[] = $old_img[$a];}
					
					$a++;	
				}
			}
		}
	}

	// save the autopop cache
	ag_gall_data_save($gid, $to_save, true);

	// save creation time
	update_post_meta($gid, 'ag_autopop_time', current_time('timestamp'));
	
	return $to_save;
}



// check autopop creation time - if outdated refetch - and return the images array
function ag_autopop_expiry_check($gid) {
	$last_update = (int)get_post_meta($gid, 'ag_autopop_time', true);
	$update_interval = (float)get_post_meta($gid, 'ag_cache_interval', true) * 60 * 60;
	$timestamp = (int)current_time('timestamp');
	
	if($update_interval && $update_interval != 'none' && ($timestamp - $last_update) >= $update_interval) {
		$images = ag_autopop_update_cache($gid);
	}
	else {$images = ag_gall_data_get($gid, true);}
	
	return $images;
}



// returns image array ready to be used in frontend - checks autopopulation cache
function ag_frontend_img_prepare($gid, $type, $wp_gall_hash = false) {
	$autopop = get_post_meta($gid, 'ag_autopop', true);
	
	// manual - autopop switch
	$images = ($autopop) ? ag_autopop_expiry_check($gid) : ag_gall_data_get($gid, false, $wp_gall_hash);
	
	// check for expired images
	$checked = ag_expir_img_check($gid, $images, $type, $autopop);	
	
	// sorting system
	if(!$wp_gall_hash) {
		$sorting = get_post_meta($gid, 'ag_orderby', true); 
		
		if(
			empty($checked) || !$sorting || $sorting == 'default' || 
			(strpos($sorting, 'id_') !== false && !in_array($type, array('wp', 'wp_cat', 'cpt_tax', 'rml'))) // ID sorting only for WP images
		) {
			return $checked;	
		}
		
		else {
			$sort_params = explode('_', $sorting);
			
			if($sort_params[0] == 'id') { // WP id is just the img_src
				$sort_params[0] = 'img_src';
			}
			
			return ag_sort_images($checked, $sort_params[0], $sort_params[1]);
		}
	}
	else {
		return $checked;	
	}	
}



// returns gallery images applying custom sortings
function ag_sort_images($images, $key = 'title', $order = 'asc') {
	
	// set key as image array index
	$arr = array();
	foreach($images as $id => $data) {
		$arr[ strtolower($data[$key]) ] = $data;	
	}
	
	// sort
	ksort($arr);
	if($order == 'desc') {
		$arr = array_reverse($arr);
	}
	
	return array_values($arr);
}



// given images total number - returns a randomized indexes array
function ag_random_img_indexes($img_count) {
	$arr = array();
	for($a = 0; $a < $img_count; $a++) {
		$arr[] = $a;	
	}
	
	shuffle($arr);
	return $arr;
}



//// given the final image array - returns a part of them basing on their keys and applies watermark
// selection array -> (index start, images number to fetch)
function ag_frontend_img_split($gid, $images, $selection = 'all', $randomized_order = false, $watermark = false) {
	$to_return = array();
	$apply_watermark = ($watermark && filter_var(get_option('ag_watermark_img'), FILTER_VALIDATE_URL)) ? true : false;
	
	// if no randomized order - setup an array with increasing values
	if(!$randomized_order) {
		$randomized_order = array();
		for($a=0; $a<count($images); $a++) {$randomized_order[] = $a;}	
	}
	
	$start = (is_array($selection)) ? $selection[0] : 0;
	$end = (is_array($selection)) ? $selection[1] : count($images);
	if(!isset($randomized_order[$start])) {return $img_arr;}
	
	
	// no ag gallery post type - is a WP gallery
	if(get_post_type($gid) != 'ag_galleries') {
		$type 			= 'wp';
		$autopop 		= false;
		$show_authors 	= false;
		$show_titles 	= false;
		$show_descr 	= false;	
		$auto_link 		= false;		
	}
	else {
		$type 			= get_post_meta($gid, 'ag_type', true);
		$autopop 		= get_post_meta($gid, 'ag_autopop', true);
		$show_authors 	= get_post_meta($gid, 'ag_auto_author', true);
		$show_titles 	= get_post_meta($gid, 'ag_auto_title', true);
		$show_descr 	= get_post_meta($gid, 'ag_auto_descr', true);	
		$auto_link 		= get_post_meta($gid, 'ag_auto_link', true);	
	}
	
	for($a=$start; $a<$end; $a++) {
		if(!isset($randomized_order[$a])) {break;} // NOTE: if no radomized - the $randomized_order contains increasing values
		$img_index = $randomized_order[$a];

		if(!isset($images[$img_index])) {break;}
		$img = $images[$img_index];	
		
		
		// apply watermark	
		if($apply_watermark) {
			$full_src = ag_img_src_on_type($img['img_src'], $type);
			$new_paths = ag_watermark($full_src);
				
			$img['wm_url']  = (is_array($new_paths)) ? $new_paths['url'] : '';
			$img['wm_path'] = (is_array($new_paths)) ? $new_paths['path'] : '';
		}
		
			
		// prepare image's array
		if($autopop) {
			if(!$show_titles) 	{$img['title'] = '';}
			if(!$show_authors) 	{$img['author'] = '';}
			if(!$show_descr) 	{$img['descr'] = '';}  
			if(!$auto_link)		{$img['link'] = '';}  
	
			$img['thumb'] = 'c';	
		}
		
		if(isset($img['wm_path'])) {
			$img['path'] =  $img['wm_path'];
			$img['url'] =  $img['wm_url'];
		} else {
			$img['path'] = ag_img_src_on_type($img['img_src'], $type);
			$img['url'] = ag_img_url_on_type($img['img_src'], $type);
		}

		$to_return[$img_index] = $img;
	}
	return $to_return;	
}



// given the images array, returns a multidimensional array('tag' => array(img_indexes)) containing tags and associated images
function ag_get_gallery_tags($images) {
	$tags = array();
	
	foreach($images as $img_index => $img) {
		if(!isset($img['tags']) || empty($img['tags'])) {continue;}
		
		$img_tags = explode(',', $img['tags']);
		foreach($img_tags as $tag) {
			$tag = trim($tag);
			
			if(isset($tags[$tag])) {
				$tags[$tag][] = $img_index;
			} else {
				$tags[$tag] = array($img_index);	
			}
		}
	}
	
	return $tags;
}



// given gallery tags array, returns filters code
function ag_gallery_tags_code($uniqid, $gid, $tags) {
	$code = '';
	
	$tags_treshold 	= (int)get_option('ag_discard_tags', 1);
	$tags_sorting 	= get_option('ag_tags_sort', 'name');
	$show_counter 	= get_option('ag_show_tags_counter');
	
	$all_txt 		= get_option('ag_tags_all_txt');
	if(empty($all_txt)) {$all_txt = __('All', 'ag_ml');}
	
	
	// strip tags with only one attached image?
	if($tags_treshold > 1) {
		foreach($tags as $tag => $img_indexes) {
			if(count($img_indexes) < $tags_treshold) {
				unset($tags[$tag]);	
			}
		}
		if(empty($tags)) {return '';}
	}
	
	
	// tags sorting
	if($tags_sorting == 'name') {
		ksort($tags); 	
	} else {
		asort($tags);	
		$tags = array_reverse($tags);
	}
	
	
	$filter_type = (get_option('ag_use_old_filters')) ? 'ag_textual_filters' : 'ag_btn_filters';
	$separator = (get_option('ag_use_old_filters')) ?'<span>'. get_option('ag_os_filters_separator', '/') .'</span>' : '';
	
	$code .= '
	<div class="ag_tags_wrap ag_tag_gid_'. $gid .' '. $filter_type .'" data-gid="'. $uniqid .'">
		<div class="ag_tag ag_tag_sel"  data-tag="*">'. $all_txt .'</div>';
	
		foreach($tags as $tag => $img_indexes) {
			$counter = ($show_counter) ? '<span class="ag_tag_counter">'. count($img_indexes) .'</span>' : '';
			
			$code .= $separator .
			'<div class="ag_tag" data-gid="'. $uniqid .'" data-tag="'. sanitize_title($tag) .'" data-images="'. implode(',', $img_indexes) .'">'.
				$tag . $counter .
			'</div>';	
		}
		
		
		// needs also dropdown?
		if(get_option('ag_dd_mobile_filter')) {
			$code .= '
			<select class="ag_mobile_filter_dd ag_tags_dd" autocomplete="off">
				<option value="*">'. $all_txt .'</option>';	
	
				foreach($tags as $tag => $img_indexes) {
					$counter = ($show_counter) ? ' '. count($img_indexes) : '';
					$code .= '<option value="'. sanitize_title($tag) .'">'. $tag . $counter .'</option>';	
				}
				
			$code .= '
			</select>';	
		}
		
	return $code .'</div>'; 
}




///////////////////////////////////////////////////////////////////




// watermarker
function ag_watermark($img_src) {
	include_once(AG_DIR . '/classes/lc_wp_watermarker.php');
	@ini_set('memory_limit', '256M');

	
	// cached instance? use it!
	if(isset($GLOBALS['ag_wm_instance'])) {
		$lcww = $GLOBALS['ag_wm_instance'];	
	}
	else {
		
		// retrieve watermark image
		$wm_img = get_option('ag_watermark_img');
		if(!filter_var($wm_img, FILTER_VALIDATE_URL)) {
			die( __("Watermark image's url is wrong", 'ag_ml') );	
		}
		
		// setup class
		$wp_dirs = wp_upload_dir();
		$folder_name = 'ag_watermarked'; 
	
		$args = array(
			'cache_folder_dir' => trailingslashit($wp_dirs['basedir']) . $folder_name,
			'cache_folder_url' => trailingslashit($wp_dirs['baseurl']) . $folder_name,
			
			'quality' 		=> 95,
			'proportional' 	=> get_option('ag_wm_proport', false),
			'prop_sizes'	=> get_option('ag_wm_prop_val', array(15, 15)),
			'wm_pos'		=> strtolower(get_option('ag_watermark_pos', 'mm')),
			'wm_margin'		=> get_option('ag_wm_margin', 10),
			'wm_margin_type'=> get_option('ag_wm_margin_type', '%'),
			'wm_opacity'	=> get_option('ag_watermark_opacity', 1),
		);
		
		$lcww = new lc_wp_watermark($wm_img, $args);
		$GLOBALS['ag_wm_instance'] = $lcww;	
	}
	
	return $lcww->mark_it($img_src);
}

