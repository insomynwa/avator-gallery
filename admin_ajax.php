<?php

////////////////////////////////////////////////
////// SHOW CONNECTIONS HUB WIZARD /////////////
////////////////////////////////////////////////

function ag_connect_wizard_show() {
	include_once(AG_DIR . '/classes/ag_connections_hub.php');

	if(!isset($_POST['gallery_id'])) {die('missing data');}
	$gid = $_POST['gallery_id'];
	
	if(!isset($_POST['ag_type'])) {die('missing data');}
	$type = $_POST['ag_type'];
	
	
	$conn_hub = new ag_connection_hub($gid, $type);
	echo $conn_hub->wizard();
	
	die();
}
add_action('wp_ajax_ag_connect_wizard_show', 'ag_connect_wizard_show');




////////////////////////////////////////////////
////// RELOAD TYPE CONNECTIONS DROPDOWN ////////
////////////////////////////////////////////////

function ag_connect_dd_reload() {
	include_once(AG_DIR . '/classes/ag_connections_hub.php');

	if(!isset($_POST['gallery_id'])) {die('missing data');}
	$gid = $_POST['gallery_id'];
	
	if(!isset($_POST['ag_type'])) {die('missing data');}
	$type = $_POST['ag_type'];

	$conn_hub = new ag_connection_hub($gid, $type);
	echo $conn_hub->src_connections_dd();
	
	die();
}
add_action('wp_ajax_ag_connect_dd_reload', 'ag_connect_dd_reload');




////////////////////////////////////////////////
////// SAVE TYPE CONNECTION - CONNECTION HUB ///
////////////////////////////////////////////////

function ag_save_type_connect() {
	include_once(AG_DIR . '/classes/ag_connections_hub.php');
	
	/* // debug
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL); */
	
	if(!isset($_POST['gallery_id'])) {die('missing data');}
	$gid = $_POST['gallery_id'];
	
	if(!isset($_POST['ag_type'])) {die('missing data');}
	$type = $_POST['ag_type'];
	
	$conn_hub = new ag_connection_hub($gid, $type);
	echo $conn_hub->setup_connection();
	
	die();
}
add_action('wp_ajax_ag_save_type_connect', 'ag_save_type_connect');




////////////////////////////////////////////////
////// DELETE TYPE CONNECTION //////////////////
////////////////////////////////////////////////

function ag_remove_connection() {
	include_once(AG_DIR . '/classes/ag_connections_hub.php');
	include_once(AG_DIR . '/functions.php');

	if(!isset($_POST['conn_id']) || !filter_var($_POST['conn_id'], FILTER_VALIDATE_INT)) {die('missing data');}
	$conn_id = (int)$_POST['conn_id'];
	
	
	// TO CHECK
	/*** OPERATIONS TO PERFORM BEFORE DELETION ***/
	$term = get_term($conn_id, 'ag_connect_hub');
	if(is_object($term)) {
		$data = unserialize(base64_decode($term->description));

		// google+ - remove from tokens database
		if(isset($data['gplus_user'])) {
			$stored = get_option('ag_gplus_base_tokens_db', array());
			
			if(isset($stored[ $data['gplus_user'] ])) {
				unset($stored[ $data['gplus_user'] ]);
				update_option('ag_gplus_base_tokens_db', $stored);	
			}
		}
		
		// google drive - remove from tokens database
		if(isset($data['gdrive_user'])) {
			$stored = get_option('ag_gdrive_base_tokens_db', array());
			
			if(isset($stored[ $data['gdrive_user'] ])) {
				unset($stored[ $data['gdrive_user'] ]);
				update_option('ag_gdrive_base_tokens_db', $stored);	
			}
		}
		
		// OneDrive - remove from tokens database
		if(isset($data['onedrive_user'])) {
			$stored = get_option('ag_onedrive_base_tokens_db', array());
			
			if(isset($stored[ $data['onedrive_user'] ])) {
				unset($stored[ $data['onedrive_user'] ]);
				update_option('ag_onedrive_base_tokens_db', $stored);	
			}
		}
	}

	$response = wp_delete_term($conn_id, 'ag_connect_hub');
	echo (is_wp_error($response)) ? $response->get_error_message() : 'success';
	
	die();
}
add_action('wp_ajax_ag_remove_connection', 'ag_remove_connection');






//////////////////////////////////////////////////////////////////////////////////////////





////////////////////////////////////////////////
////// GALLERY SETTINGS LOAD ///////////////////
////////////////////////////////////////////////

function ag_load_settings() {
	include_once(AG_DIR . '/classes/ag_builder_sources_hub.php');

	if(!isset($_POST['gallery_id'])) {die('missing data');}
	$gid = $_POST['gallery_id'];
	
	if(!isset($_POST['ag_type'])) {die('missing data');}
	$type = $_POST['ag_type'];
	
	if(!isset($_POST['ag_username'])) {die('missing data');}
	$username = $_POST['ag_username'];
	
	if(!isset($_POST['ag_psw'])) {die('missing data');}
	$psw = $_POST['ag_psw'];
	
	if(!isset($_POST['ag_connect_id'])) {die('missing data');}
	$connect_id = $_POST['ag_connect_id'];
	
	
	// specific options
	$hub = new ag_builder_hub($gid, $type);
	echo $hub->spec_opt(array('username' => $username, 'psw' => $psw, 'connect_id' => $connect_id));
	die();
}
add_action('wp_ajax_ag_load_settings', 'ag_load_settings');




///////////////////////////////////////
////// MEDIA IMAGE PICKER /////////////
///////////////////////////////////////

function ag_img_picker() {	
	include_once(AG_DIR . '/classes/ag_img_fetcher.php');
	include_once(AG_DIR . '/functions.php');
	$tt_path = AG_TT_URL; 
	
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);	
	
	
	// get vars
	if(!isset($_POST['gallery_id'])) {die('missing data');}
	$gid = $_POST['gallery_id'];
	
	if(!isset($_POST['ag_type'])) {die('missing data');}
	$type = $_POST['ag_type'];
	
	if(!isset($_POST['page'])) {$page = 1;}
	else {$page = (int)addslashes($_POST['page']);}
	
	if(!isset($_POST['per_page'])) {$per_page = 26;}
	else {$per_page = (int)addslashes($_POST['per_page']);}

	$search = (!isset($_POST['ag_search'])) ? '' : $_POST['ag_search'];
	$extra = (!isset($_POST['ag_extra'])) ? array() : $_POST['ag_extra'];
	
	// images fetcher 
	$fetcher = new ag_img_fetcher($gid, $type, $page, $per_page, $search, $extra);
	$img_data = $fetcher->get;
	
	
	// print code
	echo '<ul>';
	
	if($img_data['tot'] == 0) {
		die('<p>'. __('No images found', 'ag_ml') .' .. </p>');
	}
	else {
		foreach($img_data['img'] as $true_img_id => $img) {
			$img_id = str_replace('.', '', uniqid('', true));
			
			if(in_array($type, array('wp', 'wp_cat', 'cpt_tax', 'rml'))) {
				$img_src = $img['id'];
			} 
			elseif($type == 'ag_album' || $type == 'ngg') {
				$img_src = $img['path'];
			}
			else {
				$img_src = $img['url'];
			}
			
			
			$img_full_src = ag_img_src_on_type($img_src, $type);
			$thumb_url = (!get_option('ag_use_admin_thumbs')) ? $img['url'] : ag_thumb_src($img_full_src, $width = 90, $height = 90, $quality = 90);
			
			// add link to post if WP taxonomy
			$link = (($type == 'wp_cat' || $type == 'cpt_tax') && isset($img['link']) && !empty($img['link']) && get_option('ag_wp_term_autolink')) ? $img['link'] : '';

			echo '
			<li class="ag_sel_status ag_img_not_sel" id="sel-'.$img_id.'">
				<figure style="background-image: url('.$thumb_url.');" id="'.$img_id.'"
					img_src="'.esc_attr($img_src).'" img_full_src="'.esc_attr($img_full_src).'" fullurl="'.$img['url'].'"
					class="ag_all_img" title="'.esc_attr($img['title']).'" alt="'.esc_attr($img['descr']).'" author="'.esc_attr($img['author']).'" link="'.esc_attr($link).'"
				></figure>
				
			  <div class="ag_zoom_img"></div>
			</li>';	
		}
	}
	
	echo '
	</ul>
	<br class="lcwp_clear" />
	<table cellspacing="0" cellpadding="5" border="0" width="100%">
		<tr>
			<td style="width: 35%;">';			
			if($page > 1)  {
				echo '<input type="button" class="ag_img_pick_back button-secondary" id="slp_'. ($page - 1) .'" name="mgslp_p" value="&laquo; ' . __('Previous images', 'ag_ml') . '" />';
			}
			
		echo '</td><td style="width: 30%; text-align: center;">';
		
			if($img_data['tot'] > 0 && $img_data['tot_pag'] > 1) {
				echo '<em>page '.$img_data['pag'].' of '.$img_data['tot_pag'].'</em> - <input type="text" size="2" name="mgslp_num" id="ag_img_pick_pp" value="'.$per_page.'" autocomplete="off" /> <em>' . __('images per page', 'ag_ml') . '</em>';	
			}
			else { echo '<input type="text" size="2" name="mgslp_num" id="ag_img_pick_pp" value="'.$per_page.'" autocomplete="off" /> <em>' . __('images per page', 'ag_ml') . '</em>';	}
			
		echo '</td><td style="width: 35%; text-align: right;">';
			if($img_data['more'] != false)  {
				echo '<input type="button" class="ag_img_pick_next button-secondary" id="slp_'. ($page + 1) .'" name="mgslp_n" value="' . __('Next images', 'ag_ml') . ' &raquo;" />';
			}
		echo '</td>
		</tr>
	</table>';
	
	if($img_data['tot'] > 0) {
		echo'
		<script type="text/javascript">
		jQuery("#ag_total_img_num").text("('.$img_data['tot'].')")
		</script>';
	}
	die();
}
add_action('wp_ajax_ag_img_picker', 'ag_img_picker');




////////////////////////////////////////////////
////// GALLERY AUTO POPULATION /////////////////
////////////////////////////////////////////////

function ag_make_autopop() {
	include_once(AG_DIR . '/classes/ag_img_fetcher.php');
	require_once(AG_DIR . '/functions.php');
	$tt_path = AG_TT_URL; 
	
	if(!isset($_POST['gallery_id'])) {die('missing data');}
	$gid = $_POST['gallery_id'];
	
	if(!isset($_POST['ag_type'])) {die('missing data');}
	$type = $_POST['ag_type'];
	
	if(!isset($_POST['ag_max_img']) || !is_int((int)$_POST['ag_max_img']) ) {die('missing data');}
	$o_max_img = (int)$_POST['ag_max_img'];
	$max_img = (int)$_POST['ag_max_img'];
	
	if(!isset($_POST['ag_random_img'])) {die('missing data');}
	$random = $_POST['ag_random_img'];

	$erase_past = (!isset($_POST['ag_erase_past'])) ? false : $_POST['ag_erase_past'];
	$extra 		= (!isset($_POST['ag_extra'])) ? array() : $_POST['ag_extra'];
	
	
	// unified function - use update autopop cache
	$attr = array(
		'type'		=> $type,
		'max_images'=> $max_img,
		'random'	=> $random,
		'extra'		=> $extra,
		'erase_past'=> $erase_past
	);
	$to_save = ag_autopop_update_cache($gid, $attr);
	
	// display
	if(!count($to_save)) {die('<em>' . __('No images found', 'ag_ml') .' .. </em>');}
	echo '<ul id="ag_fb_builder" class="ag_autopop_gallery">';
	
	// display
	foreach($to_save as $img) {
		$img_full_src = ag_img_src_on_type($img['img_src'], $type);
		$thumb_url = (!get_option('ag_use_admin_thumbs')) ? $img['url'] : ag_thumb_src($img_full_src, $width = 400, $height = 220, 85, 'c', 3);	
		
		echo '<li>
			<div class="ag_builder_img_wrap">
				<figure style="background-image: url('.$thumb_url.');" class="ag_builder_img" fullurl="'.$img['url'].'" title="'. __("click to enlarge", 'ag_ml') .'"></figure>
			</div>	
			<div>
				<table>
				  <tr>
					<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/photo_author.png" title="photo author" /></td>
					<td>'.$img['author'].'</td>
				  </tr>
				  <tr>
					<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/photo_title.png" title="photo title" /></td>
					<td>'.$img['title'].'</td>
				  </tr>
				  <tr>
					<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/photo_descr.png" title="photo description" /></td>
					<td>'.$img['descr'].'</td>
				  </tr>';
				  
				  if(($type == 'wp_cat' || $type == 'cpt_tax') && isset($img['link']) && !empty($img['link'])) {
					echo '
					<tr>
					  <td class="ag_img_data_icon"><img src="'.AG_URL.'/img/link_icon.png" title="photo link" /></td>
					  <td style="position: relative;"><a href="'. $img['link'] .'" target="_blank">'. $img['link'] .'</a></td>
					</tr>';
				  }
				
				echo '
				</table>
			</div>
		</li>';		
	}
	
	echo '</ul>';
	die();
}
add_action('wp_ajax_ag_make_autopop', 'ag_make_autopop');




////////////////////////////////////////////////
////// CPT TAXONOMY - CHANGE TAXONOMY //////////
////////////////////////////////////////////////

function ag_cpt_tax_change() {
	if(!isset($_POST['cpt_tax'])) {die('missing data');}
	$cpt_tax = $_POST['cpt_tax'];
	
	require_once(AG_DIR . '/functions.php');
	
	echo ag_get_taxonomy_terms($cpt_tax);
	die();
}
add_action('wp_ajax_ag_cpt_tax_change', 'ag_cpt_tax_change');




///////////////////////////////////////////////////////////////////////////////




////////////////////////////////////////////////
////// SET PREDEFINED STYLES ///////////////////
////////////////////////////////////////////////

function ag_set_predefined_style() {
	if(!isset($_POST['lcwp_nonce']) || !wp_verify_nonce($_POST['lcwp_nonce'], 'lcwp_nonce')) {die('Cheating?');}
	if(!isset($_POST['style'])) {die('data is missing');}

	require_once(AG_DIR .'/settings/preset_styles.php');
	require_once(AG_DIR .'/functions.php');
	
	$style_data = ag_preset_styles_data($_POST['style']);
	if(empty($style_data)) {die('Style not found');}
	
	
	// override values
	foreach($style_data as $key => $val) {
		update_option($key, $val);		
	}


	// if is not forcing inline CSS - create static file
	if(!get_option('mg_inline_css')) {
		ag_create_frontend_css();
	}
	
	die('success');
}
add_action('wp_ajax_ag_set_predefined_style', 'ag_set_predefined_style');




////////////////////////////////////////////////
////// CREATE WATERMARK CACHE //////////////////
////////////////////////////////////////////////

function ag_create_wm_cache() {
	require_once(AG_DIR .'/functions.php');
	
	// force PHP debug mode
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);	
	
	
	// specific gallery or any gallery?
	$gid = (isset($_POST['gid'])) ? addslashes($_POST['gid']) : false;
	
	if(!$gid) {
		$args = array(
			'post_type' => 'ag_galleries',
			'numberposts' => -1,
			'post_status' => 'publish'
		);
		$galleries = get_posts($args);
	}
	else {
		$galleries = array( get_post($gid));	
	}
	
	
	// perform
	foreach($galleries as $gallery) {
		$gid = $gallery->ID;
		$type = get_post_meta($gid, 'ag_type', true);
		$images = ag_gall_data_get($gid);
	
		if(is_array($images)) {
			foreach($images as $img) { 
				$full_src = ag_img_src_on_type($img['img_src'], $type);
				ag_watermark($full_src);
			}
		}	
	}

	die('success');
}
add_action('wp_ajax_ag_create_wm_cache', 'ag_create_wm_cache');




////////////////////////////////////////////////
////// CLEAN WATERMARK CACHE ///////////////////
////////////////////////////////////////////////

function ag_clean_wm_cache() {
	require_once(AG_DIR . '/functions.php');
	
	$wp_dirs = wp_upload_dir();
	$cache_dir = trailingslashit($wp_dirs['basedir']) . 'ag_watermarked';
	
	// folder exists?
	if(!@file_exists($cache_dir)) {
		die('success');	
	}
	
	// clean
	foreach(scandir($cache_dir) as $file) {
		$ext = ag_stringToExt($file);
		$accepted = array('.jpg', '.jpeg', '.gif', '.png');
		
		if(in_array($ext, $accepted) && file_exists($cache_dir.'/'.$file)) {
			unlink($cache_dir.'/'.$file);
		}	
	}

	die('success');
}
add_action('wp_ajax_ag_clean_wm_cache', 'ag_clean_wm_cache');




//////////////////////////////////////////////////////////////////////////////




////////////////////////////////////////////////
////// ADD COLLECTION TERM /////////////////////
////////////////////////////////////////////////

function ag_add_coll() {
	if(!isset($_POST['coll_name'])) {die('missing data');}
	$name = $_POST['coll_name'];
	
	$resp = wp_insert_term( $name, 'ag_collections', array( 'slug'=>sanitize_title($name)) );
	
	if(is_array($resp)) {die('success');}
	else {
		$err_mes = $resp->errors['term_exists'][0];
		die($err_mes);
	}
}
add_action('wp_ajax_ag_add_coll', 'ag_add_coll');



////////////////////////////////////////////////
////// LOAD COLLECTIONS LIST ///////////////////
////////////////////////////////////////////////

function ag_coll_list() {
	if(!isset($_POST['coll_page']) || !filter_var($_POST['coll_page'], FILTER_VALIDATE_INT)) {$pag = 1;}
	$pag = (int)$_POST['coll_page'];
	
	$per_page = 10;
	
	// get all terms 
	$colls = get_terms( 'ag_collections', 'hide_empty=0' );
	$total = count($colls);
	
	$tot_pag = ceil( $total / $per_page );
	
	if($pag > $tot_pag) {$pag = $tot_pag;}
	$offset = ($pag - 1) * $per_page;
	
	// get page terms
	$args =  array(
		'number' => $per_page,
		'offset' => $offset,
		'hide_empty' => 0
	 );
	$colls = get_terms( 'ag_collections', $args);

	// clean term array
	$clean_colls = array();
	
	foreach ( $colls as $coll ) {
		$clean_colls[] = array('id' => $coll->term_id, 'name' => $coll->name);
	}
	
	$to_return = array(
		'colls' => $clean_colls,
		'pag' => $pag, 
		'tot_pag' => $tot_pag
	);
    
	echo json_encode($to_return);
	die();
}
add_action('wp_ajax_ag_get_colls', 'ag_coll_list');




////////////////////////////////////////////////
////// DELETE THE COLLECTION TERM //////////////
////////////////////////////////////////////////

function ag_del_coll() {
	if(!isset($_POST['coll_id'])) {die('missing data');}
	$id = addslashes($_POST['coll_id']);
	
	$resp = wp_delete_term( $id, 'ag_collections');

	if($resp == '1') {die('success');}
	else {die('error during the collection deletion');}
}
add_action('wp_ajax_ag_del_coll', 'ag_del_coll');




////////////////////////////////////////////////
////// DISPLAY COLLECTION BUILDER //////////////
////////////////////////////////////////////////

function ag_coll_builder() {
	require_once(AG_DIR . '/functions.php');

	if(!isset($_POST['coll_id'])) {die('missing data');}
	$coll_id = addslashes($_POST['coll_id']);
			
	// item categories list
	$item_cats = get_terms( 'ag_gall_categories', 'hide_empty=0' );
	
	// cat and page selector
	?>
    <h2></h2>
    
    <div id="ag_grid_builder_cat" class="postbox" style="min-width: 630px;">
      <h3 class="hndle"><?php _e("Add Collection Galleries", 'ag_ml'); ?></h3>
      <div class="inside">
    
        <div class="lcwp_mainbox_meta">
          <table class="widefat lcwp_table lcwp_metabox_table" style="border: none;">
            <tr>
              <td>
                  <label style="width: 145px;"><?php _e("Gallery Categories", 'ag_ml'); ?></label>
                  
                  <select data-placeholder="<?php _e("Select gallery categories", 'ag_ml'); ?> .." name="ag_gall_cats" id="ag_gall_cats" class="lcweb-chosen" style="width: 314px;" autocomplete="off">
                    <option value="all"><?php _e('Any category', 'ag_ml') ?></option>
                      
                    <?php 
                    foreach($item_cats as $cat) {
                        // WPML fix - get original ID
                        if (function_exists('icl_object_id') && isset($GLOBALS['sitpress'])) {
                            global $sitepress;
							$term_id = icl_object_id($cat->term_id, 'ag_gall_categories', true, $sitepress->get_default_language());
                        }
                        else {$term_id = $cat->term_id;}
                            
                        echo '<option value="'.$term_id.'">'.$cat->name.'</option>';
                    }
                    ?>
                </select>
              </td>
            </tr>
            <tr>
              <td style="padding-bottom: 0 !important;">
                <div>
                    <label style="width: 145px;"><?php _e("Select galleries", 'ag_ml'); ?></label>
                    <input type="text" name="ag_coll_gall_search" id="ag_coll_gall_search" style="width: 314px; padding-right: 28px;" placeholder="<?php _e('search galleries', 'ag_ml') ?>" autocomplete="off" />
                    
                    <i class="ag_cgs_mag" title="<?php _e('search', 'ag_ml') ?>"></i>
                    <i class="ag_cgs_del" title="<?php _e('cancel', 'ag_ml') ?>"></i>
                    
                    <a href="javascript:void(0)" class="ag_cgs_show_all">(<?php _e('expand', 'ag_ml') ?>)</a>
                </div>
                
                <ul id="ag_coll_gall_picker">
                    <?php 
                    $post_list = ag_cat_galleries_code('all'); 
                    
                    if(!$post_list) {echo '<span>'. __('No galleries found', 'ag_ml') .' ..</span>';}
                    else {echo $post_list;}
                    ?>
                </ul>
              </td>
            </tr>
          </table>  
        <div>  
      </div>
	</div>
    </div>
    </div>
    
    <div class="postbox" style="min-width: 630px;">
      <h3 class="hndle"><?php _e("Collection Builder", 'ag_ml'); ?></h3>
      <div class="inside">
      
		<div id="visual_builder_wrap">
        
		<table id="ag_coll_builder">
          <?php
          $coll_data = get_term($coll_id, 'ag_collections');
		  $coll_composition = unserialize($coll_data->description);
		  $coll_galleries = $coll_composition['galleries'];
		  
          if(is_array( $coll_galleries) && count( $coll_galleries) > 0) {
			
			$a = 0;  
            foreach( $coll_galleries as $gdata) {
			  $gid = $gdata['id'];
			  $gall_img = ag_get_gall_first_img($gid);	
				
			  if(get_post_status($gid) == 'publish' && $gall_img) {

				  $rand_check 	= (isset($gdata['rand']) && $gdata['rand'] != 0) ? 'checked="checked"' : '';
				  $wmark_check 	= (isset($gdata['wmark']) && $gdata['wmark'] != 0) ? 'checked="checked"' : '';  
				  $filter_check	= (isset($gdata['filters']) && $gdata['filters'] != 0) ? 'checked="checked"' : ''; 
				  	
				  $link_subj 	= (isset($gdata['link_subj'])) ? $gdata['link_subj'] : 'none'; 
				  $link_val 	= (isset($gdata['link_val'])) ? $gdata['link_val'] : '';
				  $descr 		= (isset($gdata['descr'])) ? $gdata['descr'] : ''; 
				  
				  // custom image
				  if(isset($gdata['cust_img']) && $gdata['cust_img']) {
					$cust_img = ag_thumb_src($gdata['cust_img'], 500, 500, 70);
					$cust_img_id = $gdata['cust_img'];
					$ci_icon_sel_class = 'ag_coll_cust_img_sel';   
				  } 
				  else {
					$cust_img = '';
					$cust_img_id = '';
					$ci_icon_sel_class = '';  
				  }
				 
				  $orig_thumb = ag_thumb_src($gall_img, 500, 500, 70);
				  $thumb_to_use = (empty($cust_img)) ? $orig_thumb : $cust_img;
				  
				  
				  // categories
				  $gall_cats = ag_gallery_cats($gid, 'list', ', ');
				  $gall_cats = (empty($gall_cats)) ? '<em>'. __('No associated categories', 'ag_ml') .' ..</em>' : '<em class="dashicons dashicons-tag" title="'. esc_attr(__('Categories', 'ag_ml')) .'" style="padding-right: 3px; font-size: 16px; line-height: 23px;"></em> '.$gall_cats;

				  echo '
				  <tr class="coll_component" id="ag_coll_'.$gid.'">
					<td class="ag_coll_gall_imgbox" style="width: 230px; vertical-align: top; background-image: url('. $thumb_to_use .');">
						
						<div class="lcwp_del_row ag_del_gall"></div>
						<div class="lcwp_move_row"></div>
						
						<div class="ag_coll_cust_img_btn '. $ci_icon_sel_class .'" title="'. esc_attr(__('Manage custom main image', 'ag_ml')) .'">
							<i class="fa fa-camera" aria-hidden="true"></i>
							<input type="hidden" name="ag_coll_cust_img" value="'. $cust_img_id .'" class="ag_coll_cust_img" />
							
							<div class="ag_coll_del_cust_img_btn" title="'. esc_attr(__('Remove custom main image', 'ag_ml')) .'" orig-img="'. $orig_thumb .'">
								<i class="fa fa-camera" aria-hidden="true"></i>
							</div>
						</div>

						<div class="ag_coll_gall_cats">
							<span>'. $gall_cats .'</span>
						</div>
					</td>
					<td class="ag_coll_gall_inner" style="vertical-align: top;">
						<div>
							<h2>
								<a href="'.get_admin_url().'post.php?post='.$gid.'&action=edit" target="_blank" title="'. __('edit gallery', 'ag_ml').'">'.get_the_title($gid).'</a>
							</h2>
							<br/>
							
							<div style="width: 12.3%; margin-right: 4%;">
								<p>'.__('Random display?', 'ag_ml').'</p>
								<input type="checkbox" name="random" class="ip-checkbox" value="1" '.$rand_check.' />
							</div>
							<div style="width: 12.3%; margin-right: 4%;">
								<p>'.__('Use tags filter?', 'ag_ml').'</p>
								<input type="checkbox" name="tags_filter" class="ip-checkbox" value="1" '.$filter_check.' />
							</div>
							<div style="width: 12.3%; margin-right: 4%;">
								<p>'.__('Use watermark?', 'ag_ml').'</p>
								<input type="checkbox" name="watermark" class="ip-checkbox" value="1" '.$wmark_check.' />
							</div>
							<div style="width: 50%;">
								<p>'.__('Image link', 'ag_ml').'</p>
								<select name="ag_linking_dd" class="ag_linking_dd">
									<option value="none">'. __('No link', 'ag_ml') .'</option>
									<option value="page" '; if($link_subj == 'page') {echo 'selected="selected"';} echo '>'. __('To a page', 'ag_ml') .'</option>
									<option value="custom" '; if($link_subj == 'custom') {echo 'selected="selected"';} echo '>'. __('Custom link', 'ag_ml') .'</option>
								</select>
								<div class="ag_link_wrap">'. ag_link_field($link_subj, $link_val) .'</div>
							</div>
							
							<div>
								<textarea name="coll_descr" class="coll_descr" placeholder="'. esc_attr(__('Gallery description - supports %IMG-NUM% placeholder', 'ag_ml')) .'">'.$descr.'</textarea>
							</div>	
							
						</div>
					</td>
				  </tr>
				  ';
			  }
			  $a++;
            }
          }
		  else {echo '<tr><td colspan="5"><p>'.__('No selected galleries', 'ag_ml').' ..</p></td></tr>';}
          ?>

       </table>
       </div> 
         
	</div>
    </div>
    </div>
	<?php
	die();
}
add_action('wp_ajax_ag_coll_builder', 'ag_coll_builder');




////////////////////////////////////////////////
////// GET GALLERIES FOR A CATEGORY ////////////
////////////////////////////////////////////////

function ag_cat_galleries_code($fnc_cat = false) {	
	include_once(AG_DIR . '/functions.php');
	$cat = $fnc_cat;
	$code = '';
	
	// if is not called directly
	if(!$cat) {
		if(!isset($_POST['gallery_cat'])) {die('missing data');}
		$cat = $_POST['gallery_cat'];
	}

	$post_list = ag_cat_galleries($cat);	
	if(!$post_list) {return false;}
	
	foreach($post_list as $post) {
		$code .= '
		<li style="background-image: url('.ag_thumb_src($post['img'], 200, 170, 70).');" rel="'.$post['id'].'" title="'. __('add to collection', 'ag_ml') .'" ag-cats="'.$post['cats'].'" ag-img="'.ag_thumb_src($post['img'], 500, 500, 70).'">
			<div title="'.$post['title'].'">'.$post['title'].'</div>
		</li>';
	}

	
	if($fnc_cat == false) {die( $code );}
	else {return $code;}
}
add_action('wp_ajax_ag_cat_galleries_code', 'ag_cat_galleries_code');



////////////////////////////////////////////
////// SAVE COLLECTION CONTENTS ////////////
////////////////////////////////////////////

function ag_save_coll() {	
	require_once(AG_DIR . '/functions.php');
	
	if(!isset($_POST['coll_id'])) {die('missing data');}
	$coll_id = addslashes($_POST['coll_id']);
	
	if(!isset($_POST['gall_list'])) {$gall_list = '';}
	else {$gall_list = $_POST['gall_list'];}
	
	if(!isset($_POST['cust_img'])) {$cust_img = '';}
	else {$cust_img = $_POST['cust_img'];}
	
	if(!isset($_POST['random_flag'])) {$random_flag = '';}
	else {$random_flag = $_POST['random_flag'];}
	
	if(!isset($_POST['filters_flag'])) {$filters_flag = '';}
	else {$filters_flag = $_POST['filters_flag'];}
	
	if(!isset($_POST['wmark_flag'])) {$wmark_flag = '';}
	else {$wmark_flag = $_POST['wmark_flag'];}
	
	if(!isset($_POST['link_subj']) ) {$link_subj = '';}
	else {$link_subj = $_POST['link_subj'];}
	
	if(!isset($_POST['link_val'])) {$link_val = '';}
	else {$link_val = $_POST['link_val'];}
	
	if(!isset($_POST['coll_descr'])) {$descr = '';}
	else {$descr = $_POST['coll_descr'];}
	
	// create the categories array
	$terms_array = array();
	if(is_array($gall_list)) {
		foreach($gall_list as $post_id) {
			$pid_terms = wp_get_post_terms($post_id, 'ag_gall_categories', array("fields" => "ids"));
			foreach($pid_terms as $pid_term) { $terms_array[] = $pid_term; }	
		}
		$terms_array = array_unique($terms_array);
	}
	
	// create the galleries array
	$galleries = array();
	if(is_array($gall_list)) {
		$a = 0;
		foreach($gall_list as $gid) {
			$galleries[] = array(
				'id' 		=> $gid,
				'cust_img'	=> $cust_img[$a],
				'rand'		=> $random_flag[$a],
				'filters'	=> $filters_flag[$a],
				'wmark' 	=> $wmark_flag[$a],
				'link_subj' => $link_subj[$a],
				'link_val' 	=> ag_sanitize_input(stripslashes($link_val[$a])),
				'descr'		=> ag_sanitize_input(stripslashes($descr[$a])) 
			);	
			$a++;
		}
	}

	// final array
	$coll_arr = array(
		'galleries' => $galleries,
		'categories' => $terms_array
	);
	
	// update the collection term
	$result = wp_update_term($coll_id, 'ag_collections', array(
	  'slug' => uniqid(),
	  'description' => serialize($coll_arr)
	));
	
	
	if(is_wp_error($result)) {echo 'error';}
	else {echo 'success';}	

	die();
}
add_action('wp_ajax_ag_save_coll', 'ag_save_coll');

