<?php
// declaring menu, custom post type and taxonomy

///////////////////////////////////
// SETTINGS PAGE

function ag_settings_page() {	
	add_submenu_page('edit.php?post_type=ag_galleries', __('Collections', 'ag_ml'), __('Collections', 'ag_ml'), 'publish_posts', 'ag_collections', 'ag_collections');
	add_submenu_page('edit.php?post_type=ag_galleries', __('Settings', 'ag_ml'), __('Settings', 'ag_ml'), 'install_plugins', 'ag_settings', 'ag_settings');	
}
add_action('admin_menu', 'ag_settings_page');


function ag_collections() {
	include_once(AG_DIR . '/collections_manager.php');	
}
function ag_settings() {
	include_once(AG_DIR . '/settings/view.php');	
}


///////////////////////////////////////
// GALLERY CUSTOM POST TYPE & TAXONOMY

add_action( 'init', 'register_cpt_ag_gallery' );
function register_cpt_ag_gallery() {

    $labels = array( 
        'name' => __( 'Galleries', 'ag_ml'),
        'singular_name' => __( 'Gallery', 'ag_ml'),
        'add_new' => __( 'Add New Gallery', 'ag_ml'),
        'add_new_item' => __( 'Add New Gallery', 'ag_ml'),
        'edit_item' => __( 'Edit Gallery', 'ag_ml'),
        'new_item' => __( 'New Gallery', 'ag_ml'),
        'view_item' => __( 'View Gallery', 'ag_ml'),
        'search_items' => __( 'Search Galleries', 'ag_ml'),
        'not_found' => __( 'No galleries found', 'ag_galleries' ),
        'not_found_in_trash' => __( 'No galleries found in Trash', 'ag_ml'),
        'parent_item_colon' => __( 'Parent Gallery:', 'ag_ml'),
        'menu_name' => __( 'Avator Gallery', 'ag_ml'),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'supports' => array( 'title' ), 
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 100,
		'menu_icon' => AG_URL . '/img/ag_logo_menu.png',
        'show_in_nav_menus' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
        'capability_type' => 'post'
    );
    register_post_type('ag_galleries', $args);
	
	//////
	
	$labels = array( 
        'name' => __( 'Gallery Categories', 'ag_ml'),
        'singular_name' => __( 'Gallery Category', 'ag_ml' ),
        'search_items' => __( 'Search Gallery Categories', 'ag_ml' ),
        'popular_items' => NULL,
        'all_items' => __( 'All Gallery Categories', 'ag_ml' ),
        'parent_item' => __( 'Parent Gallery Category', 'ag_ml' ),
        'parent_item_colon' => __( 'Parent Gallery Category:', 'ag_ml' ),
        'edit_item' => __( 'Edit Gallery Category', 'ag_ml' ),
        'update_item' => __( 'Update Gallery Category', 'ag_ml' ),
        'add_new_item' => __( 'Add New Gallery Category', 'ag_ml' ),
        'new_item_name' => __( 'New Gallery Category', 'ag_ml' ),
        'separate_items_with_commas' => __( 'Separate item categories with commas', 'ag_ml' ),
        'add_or_remove_items' => __( 'Add or remove Gallery Categories', 'ag_ml' ),
        'choose_from_most_used' => __( 'Choose from most used Gallery Categories', 'ag_ml' ),
        'menu_name' => __( 'Gallery Categories', 'ag_ml' ),
    );

    $args = array( 
        'labels' => $labels,
        'public' => false,
        'show_in_nav_menus' => false,
        'show_ui' => true,
        'show_tagcloud' => false,
        'hierarchical' => true,
        'rewrite' => false,
        'query_var' => true
    );
    register_taxonomy('ag_gall_categories', array('ag_galleries'), $args);
}


////////////////////////
// COLLECTIONS TAXONOMY

add_action( 'init', 'register_taxonomy_ag_collections' );
function register_taxonomy_ag_collections() {
	
    $labels = array( 
        'name' => __( 'Collections', 'ag_ml'),
        'singular_name' => __( 'Collection', 'ag_ml'),
        'search_items' => __( 'Search Collections', 'ag_ml'),
        'popular_items' => __( 'Popular Collections', 'ag_ml'),
        'all_items' => __( 'All Collections', 'ag_ml'),
        'parent_item' => __( 'Parent Collection', 'ag_ml'),
        'parent_item_colon' => __( 'Parent Collection:', 'ag_ml'),
        'edit_item' => __( 'Edit Collection', 'ag_ml'),
        'update_item' => __( 'Update Collection', 'ag_ml'),
        'add_new_item' => __( 'Add New Collection', 'ag_ml'),
        'new_item_name' => __( 'New Collection', 'ag_ml'),
        'separate_items_with_commas' => __( 'Separate grids with commas', 'ag_ml'),
        'add_or_remove_items' => __( 'Add or remove Collections', 'ag_ml'),
        'choose_from_most_used' => __( 'Choose from most used Collections', 'ag_ml'),
        'menu_name' => __( 'Collections', 'ag_ml'),
    );

    $args = array( 
        'labels' => $labels,
        'public' => false,
        'show_in_nav_menus' => false,
        'show_ui' => false,
        'show_tagcloud' => false,
        'hierarchical' => false,
        'rewrite' => false,
        'query_var' => true
    );

    register_taxonomy('ag_collections', null, $args);
}



//////////////////////////////
// VIEW CUSTOMIZATORS

function ag_updated_messages( $messages ) {
  global $post;

  $messages['ag_galleries'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => __('Gallery updated', 'ag_ml'),
    2 => __('Gallery updated', 'ag_ml'),
    3 => __('Gallery deleted', 'ag_ml'),
    4 => __('Gallery updated', 'ag_ml'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Gallery restored to revision from %s', 'ag_ml'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => __('Gallery published', 'ag_ml'),
    7 => __('Gallery saved', 'ag_ml'),
    8 => __('Gallery submitted', 'ag_ml'),
    9 => sprintf( __('Gallery scheduled for: <strong>%1$s</strong>', 'ag_ml'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ))),
    10 => __('Gallery draft updated', 'ag_ml'),
  );

  return $messages;
}
add_filter('post_updated_messages', 'ag_updated_messages');



// edit submitbox - hide minor submit minor-publishing
add_action('admin_head', 'ag_galleries_custom_submitbox');
function ag_galleries_custom_submitbox() {
	global $post_type;

    if ($post_type == 'ag_galleries') {
		echo '<style type="text/css">
		#minor-publishing {
			display: none;	
		}
		#lcwp_slider_opt_box > .inside {
			padding: 0;	
		}
		#lcwp_slider_creator_box {
			background: none;
			border: none;	
		}
		#lcwp_slider_creator_box > .handlediv {
			display: none;	
		}
		#lcwp_slider_creator_box > h3.hndle {
			background: none;
			border: none;
			padding: 12px 0 6px 0;	
			font-size: 18px;
			border-radius: 0px 0px 0px 0px;
		}
		#add_slide {
			float: left;
			margin-top: -36px;
			margin-left: 132px;
			cursor: pointer;	
		}
		.slide_form_table {
			width: 100%;	
		}
		.slide_form_table td {
			vertical-align: top;	
		}
		.second_col {
			width: 50%;
			border-left: 1px solid #ccc; 
			padding-left: 30px;
		}
		</style>';
	}
}


// customize galleries CPT table
add_filter('manage_edit-ag_galleries_columns', 'ag_edit_pt_table_head', 10, 2);
function ag_edit_pt_table_head($columns) {
	$new_cols = array();
	
	$new_cols['cb'] = '<input type="checkbox" />';
	$new_cols['gid'] = 'ID';
	$new_cols['title'] = __('Title', 'column name');
	
	$new_cols['ag_type'] = __('Source', 'ag_ml');
	$new_cols['ag_layout'] = __('Layout', 'ag_ml');
	$new_cols['ag_pag'] = __('Pagination', 'ag_ml');
	$new_cols['ag_autopop'] = __('Auto Population', 'ag_ml');
	$new_cols['ag_img_num'] = __('Images', 'ag_ml');
	$new_cols['date'] = __('Date', 'column name');
	$new_cols['ag_preview'] = '';
	
	return $new_cols;
}

add_action('manage_ag_galleries_posts_custom_column', 'ag_edit_pt_table_body', 10, 2);
function ag_edit_pt_table_body($column_name, $id) {
	include_once(AG_DIR . '/functions.php');
	
	$type 		= get_post_meta($id, 'ag_type', true);
	$autopop 	= get_post_meta($id, 'ag_autopop', true);
	$layout		= get_post_meta($id, 'ag_layout', true);
	$paginate 	= get_post_meta($id, 'ag_paginate', true);
	$img_count 	= (int)get_post_meta($id, 'ag_img_count', true);
	$first_imgs = (array)get_post_meta($id, 'ag_first_imgs_data', true);

	switch ($column_name) {
		case 'ag_type' : 
			$type = ag_types( get_post_meta($id, 'ag_type', true) );		
			echo (is_array($type)) ? '' : $type;
			break;
		
		case 'gid' : echo $id;
			break;
		
		case 'ag_layout' : echo ($layout == 'string') ? 'PhotoString' : ucfirst($layout);
			break;
			
		case 'ag_pag' :
			if($paginate == '1') {_e('Yes');}
			elseif($paginate == '0') {_e('No');}
			else { echo 'Default'; }
			break;
		
		case 'ag_autopop' : echo ($autopop != '1') ? '' : '&#x2713;';
			break;
		
		case 'ag_img_num' : echo $img_count;
			break;
		
		case 'ag_preview' : 
			if(!is_array($first_imgs) || !count($first_imgs)) {
				echo '';	
			} else {
			
				$to_display = array();
				for($a=0; $a<4; $a++) {
					
					if(isset($first_imgs[$a])) { 
						$img_src = ag_img_src_on_type($first_imgs[$a]['img_src'], $type);	
						$to_display[] = '<img src="'. ag_thumb_src($img_src, $width = 55, $height = 55, 80, $first_imgs[$a]['thumb']) .'" height="55" width="55"/>';		
					}
					else {
						$to_display[] = '';
					}
				}
				
				echo '
				<table class="ag_gal_list_preview">
				  <tr><td>'.$to_display[0].'</td><td>'.$to_display[1].'</td></tr>
				  <tr><td>'.$to_display[2].'</td><td>'.$to_display[3].'</td></tr>
				</table>
				';
			}
			break;

		default:
			break;
	}
	return true;
}




/////////////////////////////////////////////////////////////////////////////////



// GALLERIES LIST - filter by gallery source
function ag_gall_list_type_filter(){
	if(!isset($_GET['post_type']) || $_GET['post_type'] != 'ag_galleries') {
		return false;
	}
   
	//change this to the list of values you want to show
	//in 'label' => 'value' format
	$values = array(
					'Cow' => 'Cow',
					'Goat' => 'Goat',
					'Sheep' => 'Sheep',
					'Cow-Goat' => 'Cow-Goat',
					'Cow-Sheep' => 'Cow-Sheep',
					'Cow-Goat-Sheep' => 'Cow-Goat-Sheep',
					'Goat-Sheep' => 'Goat-Sheep',
					'Water Buffalo' => 'Water Buffalo',
					'Mixed' => 'Mixed'
	);
	?>
	<select name="ag_gall_list_filter">
		<option value=""><?php _e('Any source', 'ag_ml'); ?></option>
		<?php
			$current_v = (isset($_GET['ag_gall_list_filter'])) ? $_GET['ag_gall_list_filter'] : '';
			foreach(ag_types() as $type => $name) {
				printf
					(
						'<option value="%s"%s>%s</option>',
						$type,
						$type == $current_v? ' selected="selected"':'',
						$name
					);
				}
		?>
	</select>
	<?php
}
add_action('restrict_manage_posts', 'ag_gall_list_type_filter');
 
 
// perform filter
function ag_gall_list_type_do_filter($query){
    global $pagenow;
	
	if(isset($_GET['post_type']) && $_GET['post_type'] == 'ag_galleries') {

		if(is_admin() && $pagenow == 'edit.php' && isset($_GET['ag_gall_list_filter']) && $_GET['ag_gall_list_filter'] !== '') {
			$query->query_vars['meta_key'] = 'ag_type';
			$query->query_vars['meta_value'] = $_GET['ag_gall_list_filter'];
		}
	}
	
	return $query;
}
add_filter('parse_query', 'ag_gall_list_type_do_filter');