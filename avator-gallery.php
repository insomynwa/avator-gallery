<?php
/**
 * Plugin Name: Avator Gallery
 * Description: Easily display photos on your website with style
 * Author: Mr.Lorem
 * Version: 6.531
 *
 * Text Domain: avator-gallery
 */


/////////////////////////////////////////////
/////// MAIN DEFINES ////////////////////////
/////////////////////////////////////////////

// plugin path
$wp_plugin_dir = substr(plugin_dir_path(__FILE__), 0, -1);
define('AG_DIR', $wp_plugin_dir);

// plugin url
$wp_plugin_url = substr(plugin_dir_url(__FILE__), 0, -1);
define('AG_URL', $wp_plugin_url);


// timthumb url - also for MU
if(is_multisite()){ define('AG_TT_URL', AG_URL . '/classes/timthumb_MU.php'); }
else { define('AG_TT_URL', AG_URL . '/classes/timthumb.php'); }


// Avator Gallery albums basepath
$path = $wp_plugin_dir . '/albums';
define('AGA_DIR', $path);

// Avator Gallery albums baseurl
$url = $wp_plugin_url . '/albums';
define('AGA_URL', $url);


// plugin version
define('AG_VER', 6.531);




/////////////////////////////////////////////
/////// FORCING DEBUG ///////////////////////
/////////////////////////////////////////////

if(isset($_REQUEST['ag_php_debug'])) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);	
}




/////////////////////////////////////////////
/////// MULTILANGUAGE SUPPORT ///////////////
/////////////////////////////////////////////

function ag_multilanguage() {
  $param_array = explode(DIRECTORY_SEPARATOR, AG_DIR);
  $folder_name = end($param_array);
  
  if(is_admin()) {
	 load_plugin_textdomain( 'ag_ml', false, $folder_name . '/lang_admin');  
  }
  else {
	 load_plugin_textdomain( 'ag_ml', false, $folder_name . '/languages');  
  }
}
add_action('init', 'ag_multilanguage', 1);




/////////////////////////////////////////////
/////// MAIN SCRIPT & CSS INCLUDES //////////
/////////////////////////////////////////////


// global script enqueuing
function ag_admin_scripts() {
	wp_enqueue_style('ag_admin', AG_URL . '/css/admin.css', 999, AG_VER);
	wp_enqueue_style('ag_settings', AG_URL . '/settings/settings_style.css', 999, AG_VER);	
	
	// chosen
	wp_enqueue_style( 'lcwp-chosen-style', AG_URL.'/js/chosen/chosen.css', 999);
	
	// lcweb switch
	wp_enqueue_style( 'lc-switch', AG_URL.'/js/lc-switch/lc_switch.css', 999);
	
	// colorpicker
	wp_enqueue_style( 'ag-colpick', AG_URL.'/js/colpick/css/colpick.css', 999);
	
	
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('jquery-ui-slider');
	
	
	// lightbox and thickbox
	if(function_exists('wp_enqueue_media')) {
		wp_enqueue_media();	
	}
	wp_enqueue_style('thickbox');
	wp_enqueue_script('thickbox');
	
	wp_enqueue_style('ag_fontawesome', AG_URL . '/css/font-awesome/css/font-awesome.min.css', 999, '4.7.0');
}
add_action('admin_enqueue_scripts', 'ag_admin_scripts');


function ag_global_scripts() {
	wp_enqueue_script('jquery');
	wp_enqueue_style('ag_fontawesome', AG_URL . '/css/font-awesome/css/font-awesome.min.css', 999, '4.7.0');
	
	if(!is_admin()) {

		// frontent JS on header or footer
		if(!get_option('ag_js_head')) {
			wp_enqueue_script('ag-frontend-js', AG_URL . '/js/frontend.js', 999, AG_VER, true);
		}
		else { wp_enqueue_script('ag-frontend-js', AG_URL . '/js/frontend.js', 99, AG_VER); }
		
		
		// frontend css
		wp_enqueue_style('ag-frontend-css', AG_URL. '/css/frontend.min.css', 90, AG_VER);	
		wp_enqueue_style('ag-slider-css', AG_URL. '/js/jquery.galleria/themes/agallery/galleria.agallery_minimal.css', 92, AG_VER);
		
		// custom CSS
		if(!get_option('ag_inline_css') && !get_option('ag_force_inline_css')) {
			wp_enqueue_style('ag-custom-css', AG_URL. '/css/custom.css', 100, AG_VER);	
		}
		else {add_action('wp_head', 'ag_inline_css', 999);}
	}
}
add_action('wp_enqueue_scripts', 'ag_global_scripts', 900);



// USE FRONTEND CSS INLINE
function ag_inline_css(){
	echo '<style type="text/css">';
	require_once(AG_DIR.'/frontend_css.php');
	echo '</style>';
}



/////////////////////////////////////////////
/////// MAIN INCLUDES ///////////////////////
/////////////////////////////////////////////

// admin menu and cpt and taxonomy
include_once(AG_DIR . '/admin_menu.php');

// gallery taxonomy options
include_once(AG_DIR . '/taxonomy_options.php');

// connection hub taxonomy
include_once(AG_DIR . '/connect_hub_tax.php');

// gallery builder
include_once(AG_DIR . '/gallery_builder.php');

// wp galleries management
include_once(AG_DIR . '/wp_gallery_manag.php');

// tinymce integration
include_once(AG_DIR . '/tinymce_integration.php');

// admin ajax
include_once(AG_DIR . '/admin_ajax.php');

// direct image share hack 
include_once(AG_DIR . '/classes/lc_social_img_share_metas.php');

// frontend ajax
include_once(AG_DIR . '/front_ajax.php');

// dynamic footer javascript
include_once(AG_DIR . '/dynamic_js.php');

// gallery previews
include_once(AG_DIR . '/gallery_preview.php');

// lightboxes switch
include_once(AG_DIR . '/lightboxes.php');


// SHORTCODES
include_once(AG_DIR . '/shortcodes/gallery.php');
include_once(AG_DIR . '/shortcodes/image-to-gallery.php');
include_once(AG_DIR . '/shortcodes/collection.php');
include_once(AG_DIR . '/shortcodes/slider.php');
include_once(AG_DIR . '/shortcodes/carousel.php');



// visual composer integration
include_once(AG_DIR . '/builders_integration/visual_composer.php');

// cornerstone integration
include_once(AG_DIR . '/builders_integration/cornerstone.php');

// elementor integration
include_once(AG_DIR . '/builders_integration/elementor.php');





////////////
// AVOID issues with bad servers in settings redirect
function ag_settings_redirect_trick() {
	ob_start();
}
add_action('admin_init', 'ag_settings_redirect_trick', 1);
////////////





////////////
// EASY WP THUMBS + forcing system
function ag_ewpt() {
	if(get_option('ag_ewpt_force')) {
		$_REQUEST['ewpt_force'] = true;
		define('AG_EWPT_URL', AG_URL . '/classes/easy_wp_thumbs_force.php');
	} else {
		define('AG_EWPT_URL', AG_URL . '/classes/easy_wp_thumbs.php');
	}
	
	include_once(AG_DIR . '/classes/easy_wp_thumbs.php');	
}
add_action('init', 'ag_ewpt', 1);
////////////






////////////
// DOCUMENTATION'S LINK	
function ag_doc_link($links, $file) {
	if($file == plugin_basename(__FILE__)) {	
		$links['lc_doc_link'] = '<a href="https://doc.lcweb.it/global_gallery" target="_blank">'. __('Documentation', 'ag_ml') .'</a>';
	}
	
	return $links;
}
// add_filter('plugin_row_meta', 'ag_doc_link', 50, 2);
////////////



////////////
// AUTO UPDATE DELIVER
include_once(AG_DIR . '/classes/lc_plugin_auto_updater.php');
function ag_auto_updates() {
	if(!get_option('ag_no_auto_upd')) {
		$upd = new lc_wp_autoupdate(__FILE__, 'http://updates.lcweb.it', 'lc_updates', 'ag_init_custom_css', true);
	}
}
add_action('admin_init', 'ag_auto_updates', 1);
////////////







/////////////////////////////////////////////
////// ACTIONS ON PLUGIN ACTIVATION /////////
/////////////////////////////////////////////

function ag_init_custom_css() {
	include_once(AG_DIR . '/functions.php');
	
	// create custom CSS
	if(!ag_create_frontend_css()) {
		if(!get_option('ag_inline_css')) {update_option('ag_inline_css', 1);}
	}
	else {delete_option('ag_inline_css');}
	
	
	// update galleries (for versions < 2.0)
	ag_update_galleries_structure_v2();
	
	// update galleries (for versions < 3.0)
	ag_update_galleries_structure_v3();
	
	// connections creation (for versions < 5.0)
	ag_setup_connections_v5();
	
	// setup galleries utility metas and clean old watermark cache in V6 
	ag_v6_setup();
	
	
	// update 5.3 - check old inf_scroll option
	if(get_option('ag_infinite_scroll')) {
		update_option('ag_pag_system', 'inf_scroll');
		delete_option('ag_infinite_scroll');
	}
}
register_activation_hook(__FILE__, 'ag_init_custom_css');



// update the galleries structure to v2.0
function ag_update_galleries_structure_v2() {
	if(!get_option('ag_v2_update_done')) {
		global $wpdb;
		
		// retrieve all galleries
		$args = array(
			'numberposts' => -1, 
			'post_type' => 'ag_galleries',
		);
		$posts_array = get_posts($args);
		
		if(is_array($posts_array)) {
			foreach($posts_array as $post) {
				$gall_type = get_post_meta($post->ID, 'ag_type', true);
				$autopop = get_post_meta($post->ID, 'ag_autopop', true);
				
				if(!$autopop) { $images = get_post_meta($post->ID, 'ag_gallery', true); }
				else { $images = get_post_meta($post->ID, 'ag_autopop_cache', true); }
				
				if(is_array($images) && count($images) > 0 && !isset($images[0]['img_src'])) {
					
					$new_structure = array();
					foreach($images as $img_data) {
						$temp_data = $img_data;
						
						// retrieve image source
						if($gall_type == 'wp' || $gall_type == 'wp_cat') {
							$query = "SELECT ID FROM ".$wpdb->posts." WHERE guid='".addslashes($temp_data['url'])."'";
							$id = (int)$wpdb->get_var($query);
						
							if(!$id || !is_int($id)) {
								// image not found in the DB - remove from the gallery
								////var_dump($id); die(' error during the galleries database update');
								$temp_data['img_src'] = 'to_remove';
							} 
							else {$temp_data['img_src'] = $id;}
						}
						elseif($gall_type == 'ag_album') {
							$temp_data['img_src'] = str_replace(AG_URL, '', $temp_data['url']);
						}
						else {
							$temp_data['img_src'] = $temp_data['url'];
						}
						
						unset($temp_data['url']);
						if(isset($temp_data['path'])) {unset($temp_data['path']);}
						
						if($temp_data['img_src'] != 'to_remove') {
							$new_structure[] = $temp_data;
						}
					}
					
					// update
					delete_post_meta($post->ID, 'ag_autopop_cache');
					delete_post_meta($post->ID, 'ag_gallery');
					
					if(!$autopop) {
						add_post_meta($post->ID, 'ag_gallery', $new_structure, true);
					} else {
						add_post_meta($post->ID, 'ag_autopop_cache', $new_structure, true);	
					}	
				}
			}
			
			update_option('ag_v2_update_done', 1);
		}
	}
	
	return true;
}


// update the galleries structure to v3.0
function ag_update_galleries_structure_v3() {
	if(!get_option('ag_v3_update_done')) {
		
		// retrieve all galleries
		$args = array(
			'numberposts' => -1, 
			'post_type' => 'ag_galleries',
		);
		$posts_array = get_posts($args);
		
		if(is_array($posts_array)) {
			foreach($posts_array as $post) {
				$gall_type = get_post_meta($post->ID, 'ag_type', true);
				$autopop = get_post_meta($post->ID, 'ag_autopop', true);
				
				if(!$autopop) { $images = get_post_meta($post->ID, 'ag_gallery', true); }
				else { $images = get_post_meta($post->ID, 'ag_autopop_cache', true); }
				
				if(is_array($images) && count($images) > 0) {
					
					$new_structure = array();
					foreach($images as $img_data) {
						$temp_data = $img_data;
						
						// retrieve image source
						if($gall_type == 'ag_album') {
							// remove the /album/ base to be compatible with custom paths
							$temp_data['img_src'] = str_replace('/albums/', '', $temp_data['img_src']);
						}

						$new_structure[] = $temp_data;
					}
					
					// update
					delete_post_meta($post->ID, 'ag_autopop_cache');
					delete_post_meta($post->ID, 'ag_gallery');
					
					if(!$autopop) {
						add_post_meta($post->ID, 'ag_gallery', $new_structure, true);
					} else {
						add_post_meta($post->ID, 'ag_autopop_cache', $new_structure, true);	
					}	
				}
			}
			
			update_option('ag_v3_update_done', 1);
		}
	}
	
	return true;
}


// get galleries data to setup connections for v5.0
function ag_setup_connections_v5() {
	if(!get_option('ag_v5_update_done')) {
		include_once(AG_DIR .'/classes/ag_connections_hub.php');
		
		// retrieve all galleries
		$args = array(
			'numberposts' => -1, 
			'post_type' => 'ag_galleries',
		);
		$posts_array = get_posts($args);
		
		if(is_array($posts_array)) {
			ag_conn_taxonomy(); // be sure taxonomy is registered
			
			foreach($posts_array as $post) {
				$gid = $post->ID;
				
				$ch = new ag_connection_hub($gid);
				if($ch->src == 'g_drive' || !in_array($ch->src, $ch->to_consider)) {continue;}
			
				switch($ch->src) {
					case 'fb' :
						$page_url = get_post_meta($gid, 'ag_username', true);
						
						$ch->ajax_data = array(
							'conn_name'		=> $page_url,
							'fb_src_switch'	=> 'page',
							'fb_page_url' 	=> $page_url
						);
						if($ch->test_connection() !== true) {break;}
						break;	
					
					
					case 'picasa' :
						$username = get_post_meta($gid, 'ag_username', true);
						
						$ch->ajax_data = array(
							'conn_name'		=> $username,
							'gplus_user'	=> $username
						);
						
						$stored = (array)get_option('ag_gplus_base_tokens_db', array());
						if(!isset($stored[$username])) {
							$force_continue = true;	
						}
						break;	
					
				}
				if(isset($force_continue) && $force_continue) {
					$force_continue = false;
					continue;		
				}
				
				
				// create connection
				if(!empty($ch->ajax_data)) {
					// check against already created connections for this source
					if($term = get_term_by('name', $ch->ajax_data['conn_name'], 'ag_connect_hub')) {
						update_post_meta($gid, 'ag_connect_id', $term->term_id);
					}
					else {
						$result = $ch->save_connection();
						if($result === true) {
							update_post_meta($gid, 'ag_connect_id', $ch->connect_id);	
						}
					}
				}
				$ch->ajax_data = array(); // reset
			}
		}
		
		update_option('ag_v5_update_done', 1);
	}
}


// setup galleries utility metas and clean old watermark cache in V6 
function ag_v6_setup() {
	
	if(!get_option('ag_v6_update_done')) {
		include_once(AG_DIR . '/functions.php');
		
		// retrieve all galleries
		$args = array(
			'numberposts' 	=> -1, 
			'post_type' 	=> 'ag_galleries',
			'post_status'	=> 'any',
			'fields' 		=> 'ids'
		);
		$gallery_ids = get_posts($args);
		
		if(is_array($gallery_ids)) {
			
			foreach($gallery_ids as $gid) {
				$autopop = get_post_meta($gid, 'ag_autopop', true);
				
				$images = ag_gall_data_get($gid, $autopop);	
				ag_gall_data_save($gid, $images ,$autopop);
			}
		}
		
		
		// remove old watermarked images from cache folder
		$cache_dir = AG_DIR .'/cache';
		
		foreach(scandir($cache_dir) as $file) {
			if(strpos($file, 'ag_watermark') !== false) {
				@unlink($cache_dir.'/'.$file);	
			}
		}

		update_option('ag_v6_update_done', 1);
	}
}


			