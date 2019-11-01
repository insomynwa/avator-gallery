<?php
// METABOXES FOR THE GALLERIES

// register
function ag_gall_builder_metaboxes() {
	require_once(AG_DIR . '/functions.php');
	add_meta_box('submitdiv', __('Publish', 'ag_ml'), 'post_submit_meta_box', 'ag_galleries', 'side', 'high');

	add_meta_box('ag_gallery_type', __('Gallery Type', 'ag_ml'), 'ag_gallery_type', 'ag_galleries', 'side', 'core');
	add_meta_box('ag_main_settings', __('Main Settings', 'ag_ml') .'<span class="dashicons dashicons-info" title="'. esc_attr(__('Use "default" option or leave fields empty to use global setup', 'ag_ml')) .'" style="font-size: 19px; position: relative; left: 7px; top: 2px; color: #a5a5a5; cursor: help;"></span>', 'ag_main_settings', 'ag_galleries', 'side', 'core');

	if(filter_var( get_option('ag_watermark_img') , FILTER_VALIDATE_URL)) {
		add_meta_box('ag_create_gall_wmark_cache', __('Watermark Cache', 'ag_ml'), 'ag_create_gall_wmark_cache', 'ag_galleries', 'side', 'low');
	}

	add_meta_box('ag_specific_settings', __('Specific Settings', 'ag_ml'), 'ag_specific_settings', 'ag_galleries', 'normal', 'default');
	add_meta_box('ag_gallery_builder', __('Gallery Builder', 'ag_ml'), 'ag_gallery_builder', 'ag_galleries', 'normal', 'default');
}
add_action('admin_init', 'ag_gall_builder_metaboxes');




//////////////////////////
// GALLERY TYPE
function ag_gallery_type() {
	include_once(AG_DIR . '/classes/ag_connections_hub.php');
	include_once(AG_DIR . '/functions.php');
	global $post;

	$type = get_post_meta($post->ID, 'ag_type', true);
	$username = get_post_meta($post->ID, 'ag_username', true);
	$psw = get_post_meta($post->ID, 'ag_psw', true);
	
	$conn_hub = new ag_connection_hub($post->ID);
	
	// Instagram JULY 2016 - only able to fetch personal data - useless username
	$usern_vis 	= (!$type || in_array($type, array_merge($conn_hub->to_consider, array('wp', 'wp_cat', 'cpt_tax', 'ag_album', 'picasa', 'rml', 'ngg')))) ? 'style="display: none;"' : '';
	$psw_vis 	= ($type != 'instagram') ? 'style="display: none;"' : '';
	
	
	// dunno why but WP drag&drop overlay stucks in this page
	?>
	<style type="text/css">
    .uploader-window {
		display: none !important;	
	}
    </style>


    <div class="lcwp_sidebox_meta">
        <div class="misc-pub-section">
          <label><?php _e("Choose images source", 'ag_ml'); ?></label>
          <select data-placeholder="<?php _e('Select source', 'ag_ml') ?> .." name="ag_type" id="ag_type_dd" class="lcweb-chosen" autocomplete="off">
            <?php
			foreach(ag_types() as $id => $name) {
				$sel = ($id == $type) ? 'selected="selected"' : '';
				
				echo '<option value="'.$id.'" '.$sel.'>'.$name.'</option>';
			}
			?>
          </select>
        </div>


		<div class="misc-pub-section" id="ag_connect_id_wrap" <?php if(!in_array($type, $conn_hub->to_consider)) {echo 'style="display: none;"';} ?>>
            <?php echo $conn_hub->src_connections_dd(); ?>
        </div>
        

        <div class="misc-pub-section" id="ag_username_wrap" <?php echo $usern_vis ?>>
            <label><?php echo ag_username_label($type); ?></label>
            <input type="text" name="ag_username" value="<?php echo ag_sanitize_input($username); ?>" id="ag_username" />
        </div>


        <div class="misc-pub-section" id="ag_psw_wrap" <?php echo $psw_vis; ?>>
            <label><?php _e('Access Token', 'ag_ml') ?></label>
            <input type="text" name="ag_psw" value="<?php echo $psw; ?>" id="ag_psw" />

            <a href="https://instagram.com/oauth/authorize/?client_id=7fc8464dc65d41629ae0b7be0841c4fe&redirect_uri=http://www.lcweb.it&response_type=token" target="_blank" id="ag_instagram_get_token" <?php if($type != 'instagram') {echo 'style="display: none;"';} ?>><?php _e("Get your Instagram token", 'ag_ml'); ?> &raquo;</a>
        </div>

        <input type="button" name="ag_handle_user" value="Connect" id="ag_handle_user_btn" class="button-secondary" style="margin-top: 7px;
		<?php if(!$type || in_array($type, array('wp', 'wp_cat', 'cpt_tax', 'ag_album', 'rml', 'ngg'))) {echo 'display: none;';} ?>" />
    </div>
    
    
    <script type="text/javascript">
    // chosen and lc-lightbox enqueued later (around line 691) - as well as blocks visibility
	jQuery(document).ready(function($) {
		var gid = <?php echo $post->ID ?>;
		var ag_conn_hub_acting = false;
		var refresh_dd_on_close = false;
		
		
		// lightbox on wizard link click
		$(document).on('click', '#ag_launch_conn_wizard', function(e) {
			e.preventDefault();
			
			var obj = [{
				type	: 'html',
				src		: '#ag_conn_hub_wizard_outer_wrap'
			}];
	
			var instance = lc_lightbox(obj, {
				wrap_class	: 'lcl_zoomin_oc',
				max_width	: 450,
				modal		: true,
				
				ins_close_pos	: 'corner',
				open_close_time	: 250,
				fading_time		: 0,
				animation_time	: 0,
				
				// populate lightbox on open
				on_open	: function () {
					conn_hub_fill_lightbox();
				},
				
				// reload connections dropdown on close
				on_close : function() {
					if(ag_conn_hub_acting) {return false;}
					ag_conn_hub_acting = true;
					
					// if something changed - refresh
					if(refresh_dd_on_close) {
						ag_reload_conn_hub_dd();
					}
				}
			});
			lcl_open(instance, 0); // direct lightbox opening showing first element
		});
		
		
		// populate lightbox
		var conn_hub_fill_lightbox = function() {
			ag_conn_hub_acting = true;

			// populate with ajax
			$('.lcl_html_elem #ag_conn_hub_wizard_wrap').html('<div style="height: 90px; background: url(<?php echo AG_URL ?>/img/loader_big.gif) no-repeat center center transparent;"></div>');
	
			var data = {
				action: 'ag_connect_wizard_show',
				ag_type: $('#ag_type_dd').val(),
				gallery_id: gid
			};
			$.post(ajaxurl, data, function(response) {
				$('.lcl_html_elem #ag_conn_hub_wizard_wrap').html(response);
				ag_conn_hub_acting = false;
			});	
		};
		
		
		// load connections dropdown
		ag_reload_conn_hub_dd = function() {
			// reload connection's dropdown
			$('#ag_connect_id_wrap').html('<div style="width: 20px; height: 20px;" class="lcwp_loading"></div>');
	
			var data = {
				action: 'ag_connect_dd_reload',
				ag_type: $('#ag_type_dd').val(),
				gallery_id: gid
			};
			$.post(ajaxurl, data, function(response) {
				$('#ag_connect_id_wrap').html(response);
				
				ag_live_chosen();
				refresh_dd_on_close = false;
				ag_conn_hub_acting = false;
			});	
		};


		// submit connection trial
		$(document).delegate('#ag_conn_hub_submit', 'click', function() {
			var $subj = $('#ag_add_conn_form');
			$subj.find('section').empty();
			
			// check that every field has been filled up
			var js_check = true;
			$subj.find('input').each(function() {
                if(!$(this).val() && $(this).parents('p').is(':visible')) {
					$subj.find('section').html('<div class="gch_error"><?php _e('One or more fields are empty', 'ag_ml') ?></div>');
					js_check = false;
					return false;	
				}
            });
			
			if(!js_check || ag_conn_hub_acting) {return false;}
			ag_conn_hub_acting = true;
			
			// ajax submission
			$('#ag_conn_hub_submit').css('opacity', 0.5);

			var data = 'action=ag_save_type_connect&ag_type='+$('#ag_type_dd').val()+'&gallery_id='+gid+'&'+ $subj.serialize();
			$.post(ajaxurl, data, function(response) {
				$('#ag_conn_hub_submit').css('opacity', 1);
				
				if($.trim(response) != 'success') {
					$subj.find('section').html('<div class="gch_error">'+ response +'</div>');	
					ag_conn_hub_acting = false;
				}
				else {
					$subj.find('section').html('<div class="gch_success"><?php _e('Successfully connected!', 'ag_ml') ?></div>');
					refresh_dd_on_close = true;
					
					// successfully added - reload lightbox contents
					setTimeout(function() {
						conn_hub_fill_lightbox();	
					}, 1700);
				}
			}).fail(function() {
				// handle eventual 500 server errors (eg. dropbox on bad token)
				$subj.find('section').html("<div class='gch_error'><?php _e('Connection error - check credentials', 'ag_ml') ?></div>");	
				
				$('#ag_conn_hub_submit').css('opacity', 1);
				ag_conn_hub_acting = false;
			});
		});
		
		
		// delete connections
		$(document).on('click', '#ag_conn_hub_wizard_wrap .lcwp_del_row', function(e) {
			if(ag_conn_hub_acting) {return false;}
			var $subj = $(this).parents('tr');
			
			if(confirm("<?php _e("Do you really want to remove this connection?", 'ag_ml') ?>")) {
				ag_conn_hub_acting = true;
				$(this).parents('tr').fadeTo(100, 0.7);
			
				var data = {
					action: 'ag_remove_connection',
					conn_id: $subj.attr('rel')
				};
				$.post(ajaxurl, data, function(response) {
					if($.trim(response) == 'success') {
						$subj.slideUp();
						refresh_dd_on_close = true;	
					} 
					else {
						alert(response);	
					}
					
					ag_conn_hub_acting = false;
				});	
			}
		});
		
    });
    </script>
    <?php
	// create a custom nonce for submit verification later
    echo '<input type="hidden" name="ag_gallery_noncename" value="' . wp_create_nonce(__FILE__) . '" />';
	
	return true;
}





//////////////////////////
// GALLERY MAIN SETTINGS
function ag_main_settings() {
	include_once(AG_DIR . '/functions.php');
	global $post;

	$layout 		= get_post_meta($post->ID, 'ag_layout', true);
	$thumb_w 		= get_post_meta($post->ID, 'ag_thumb_w', true);
	$thumb_h 		= get_post_meta($post->ID, 'ag_thumb_h', true);
	
	$colnzd_max_w 	= get_post_meta($post->ID, 'ag_colnzd_thumb_max_w', true);
	$colnzd_h 		= get_post_meta($post->ID, 'ag_colnzd_thumb_h', true);
	$colnzd_h_type 	= get_post_meta($post->ID, 'ag_colnzd_thumb_h_type', true);

	$masonry_cols 	= get_post_meta($post->ID, 'ag_masonry_cols', true);
	$masonry_min_w 	= get_post_meta($post->ID, 'ag_masonry_min_width', true);;
	
	$ps_height 		= get_post_meta($post->ID, 'ag_photostring_h', true);
	$ps_min_w 		= get_post_meta($post->ID, 'ag_photostring_min_width', true);

	$paginate 		= get_post_meta($post->ID, 'ag_paginate', true);
	$per_page 		= get_post_meta($post->ID, 'ag_per_page', true);
	
	$orderby 		= get_post_meta($post->ID, 'ag_orderby', true);
	
	if(!$paginate || $paginate == 'default') {
		$per_page = get_option('ag_img_per_page');
	}

	// switches
	$hide = 'style="display: none;"';
	$standard_show 	= ($layout != 'standard') ? $hide : '';
	$columnized_show= ($layout != 'columnized') ? $hide : '';
	$masonry_show 	= ($layout != 'masonry') ? $hide : '';
	
	$ps_show 		= ($layout != 'string') ? $hide :  '';
	$per_page_show 	= ($paginate != '1') ? $hide : '';
	?>
    <div class="lcwp_sidebox_meta lcwp_form">
      <div class="misc-pub-section">
      	<div style="float: right; margin-top: -7px;">
        	<select data-placeholder="<?php _e('Select a layout', 'ag_ml') ?> .." name="ag_layout" id="ag_layout" autocomplete="off" tabindex="2" style="width: 122px; min-width: 0px;">
                <option value="default"><?php _e('As default', 'ag_ml') ?></option>
                <option value="standard" <?php selected($layout, 'standard') ?>>Standard</option>
                <option value="columnized" <?php selected($layout, 'columnized') ?>>Columnized</option>
                <option value="masonry" <?php selected($layout, 'masonry') ?>>Masonry</option>
                <option value="string" <?php selected($layout, 'string') ?>>PhotoString</option>
            </select>
        </div>
        
        <label><?php _e('Gallery layout', 'ag_ml') ?></label>
      </div>


      <div class="misc-pub-section" id="ag_tt_sizes" <?php echo $standard_show; ?>>
		<div style="float: right; margin-top: -5px;">
            <input type="text" name="ag_thumb_w" value="<?php echo $thumb_w ?>" maxlength="4" autocomplete="off" style="width: 42px; margin-right: 3px; text-align: center;" /> x
            <input type="text" name="ag_thumb_h" value="<?php echo $thumb_h ?>" maxlength="4" autocomplete="off" style="width: 42px; margin-left: 3px; text-align: center;" /> px
        </div>
        
        <label><?php _e('Thumbnail sizes', 'ag_ml') ?></label>
      </div>
      
      <div class="misc-pub-section ag_columnized_fields" <?php echo $columnized_show; ?>>
       <div style="float: right; margin-top: -5px; width: 123px;">
            <input type="number" name="ag_colnzd_thumb_max_w" value="<?php echo $colnzd_max_w ?>" maxlength="3" min="20" autocomplete="off" style="width: 65px; text-align: center;" /> px
        </div>
        
        <label><?php _e('Max thumbs width', 'ag_ml') ?></label>
      </div>
      <div class="misc-pub-section ag_columnized_fields" <?php echo $columnized_show; ?>>
        <div style="float: right; margin-top: -5px;">
            <input type="number" name="ag_colnzd_thumb_h" value="<?php echo $colnzd_h ?>" maxlength="4" min="20" autocomplete="off" style="width: 65px; margin-right: 3px; text-align: center;" />
            
            <select name="ag_colnzd_thumb_h_type" autocomplete="off" style="width: 50px; min-width: 0px; position: relative; top: -3px;">
                <option value="px">px</option>
                <option value="%" <?php selected($colnzd_h_type, '%') ?>>%</option>
            </select>
        </div>
        
        <label><?php _e('Thumbnails height', 'ag_ml') ?></label>
      </div>
      
      
      <div class="misc-pub-section ag_masonry_fields" <?php echo $masonry_show; ?>>
        <div style="float: right; margin-top: -3px;">
            <div style="float: right; margin-top: -5px;">
                <select name="ag_masonry_cols" autocomplete="off" style="width: 122px; min-width: 0px;">
                	<option value="default"><?php _e('As default', 'ag_ml') ?></option>
					<?php
					for($a=1; $a<=20; $a++) {
						echo '<option value="'.$a.'" '. selected((int)$masonry_cols, $a, false) .'>'.$a.'</option>';
					}
					?>
                </select>
            </div>
        </div>
        
        <label><?php _e('Image columns', 'ag_ml') ?></label>
      </div>
      
      <div class="misc-pub-section ag_masonry_fields" <?php echo $masonry_show; ?>>
        <div style="float: right; margin-top: -5px; width: 123px;">
            <input type="number" name="ag_masonry_min_width" value="<?php echo $masonry_min_w ?>" maxlength="3" min="40" autocomplete="off" style="width: 65px; text-align: center;" /> px
        </div>
        
        <label><?php _e('Min thumbs width', 'ag_ml') ?></label>
      </div>
      
      
      <div class="misc-pub-section ag_ps_fields" <?php echo $ps_show; ?>>
        <div style="float: right; margin-top: -3px; width: 123px;">
        	<input type="number" name="ag_photostring_h" value="<?php echo $ps_height ?>" maxlength="4" min="20" autocomplete="off" style="width: 65px; margin-right: 2px; text-align: center;" /> px
        </div>
        
        <label><?php _e('Thumbs height', 'ag_ml') ?></label>
      </div>
      
      <div class="misc-pub-section ag_ps_fields" <?php echo $ps_show; ?>>
        <div style="float: right; margin-top: -5px; width: 123px;">
            <input type="number" name="ag_photostring_min_width" value="<?php echo $ps_min_w ?>" maxlength="3" min="40" autocomplete="off" style="width: 65px; text-align: center;" /> px
        </div>
        
        <label><?php _e('Min thumbs width', 'ag_ml') ?></label>
      </div>
      

      <div class="misc-pub-section">
        <div style="float: right; margin-top: -5px;">
        	<select name="ag_paginate" id="ag_paginate" autocomplete="off" style="width: 122px;">
                <option value="default"><?php _e('As default', 'ag_ml') ?></option>
                <option value="1" <?php selected($paginate, '1') ?>><?php _e('Yes', 'ag_ml') ?></option>
                <option value="0" <?php selected($paginate, '0') ?>><?php _e('No', 'ag_ml') ?></option>
            </select>
        </div>
        
        <label><?php _e('Use pagination?', 'ag_ml') ?></label>
      </div>
      <div class="misc-pub-section-last" id="ag_per_page" <?php echo $per_page_show; ?>>
        <div style="float: right; margin-top: -3px;">
        	<input type="number" name="ag_per_page" value="<?php echo $per_page ?>" maxlength="4" autocomplete="off" style="width: 65px; margin-right: 56px; text-align: center;" />
        </div>
        
         <label><?php _e('Images per page', 'ag_ml') ?></label>
      </div>
      
      
      <div class="misc-pub-section">
        <div style="float: right; margin-top: -5px;">
        	<select name="ag_orderby" autocomplete="off" style="width: 122px;">
                <option value="default"><?php _e('As in builder', 'ag_ml') ?></option>
                <option value="title_asc" <?php selected($orderby, 'title_asc') ?>><?php _e('By title (A to Z)', 'ag_ml') ?></option>
                <option value="title_desc" <?php selected($orderby, 'title_desc') ?>><?php _e('By title (Z to A)', 'ag_ml') ?></option>
                <option value="author_asc" <?php selected($orderby, 'author_asc') ?>><?php _e('By author (A to Z)', 'ag_ml') ?></option>
                <option value="author_desc" <?php selected($orderby, 'author_desc') ?>><?php _e('By author (Z to A)', 'ag_ml') ?></option>
                <option value="id_asc" <?php selected($orderby, 'id_asc') ?>><?php _e('By date (A to Z)', 'ag_ml') ?></option>
                <option value="id_desc" <?php selected($orderby, 'id_desc') ?>><?php _e('By date (Z to A)', 'ag_ml') ?></option>
            </select>
        </div>
        
        <label><?php _e('Images sorting', 'ag_ml') ?></label>
      </div>
    </div>


	<script type="text/javascript">
	jQuery(document).ready(function($) {
        
		// main settings toggle
		$(document).delegate('#ag_layout', 'change', function() {
			var layout = $(this).val();

			if(layout == 'standard') {
				$('#ag_tt_sizes').show();
				$('.ag_columnized_fields, .ag_masonry_fields, .ag_ps_fields').hide();
			}
			else if (layout == 'columnized') {
				$('.ag_columnized_fields').show();
				$('#ag_tt_sizes, .ag_masonry_fields, .ag_ps_fields').hide();
			}
			else if (layout == 'masonry') {
				$('.ag_masonry_fields').show();
				$('.ag_columnized_fields, #ag_tt_sizes, .ag_ps_fields').hide();
			}
			else if (layout == 'string') {
				$('.ag_ps_fields').show();
				$('#ag_tt_sizes, .ag_masonry_fields').hide();
			}
			else { 
				$('#ag_tt_sizes, .ag_columnized_fields, .ag_masonry_fields, .ag_ps_fields').hide(); 
			}
		});

		
		// paginate toggle
		$(document).delegate('#ag_paginate', 'change', function() {
			
			($(this).val() == '1') ? $('#ag_per_page').fadeIn() : $('#ag_per_page').fadeOut();
		});
		
		
		// toggle WP-restricted sorting
		$(document).delegate('#ag_type_dd', 'change', function() {
			
			switch($(this).val()) {
				case 'wp' :
				case 'wp_cat' :
				case 'cpt_tax' :
				case 'rml' :
					$('select[name=ag_orderby]').find('option[value="id_asc"], option[value="id_desc"]').removeAttr('disabled');
					break;  
				
				default :
					$('select[name=ag_orderby]').find('option[value="id_asc"], option[value="id_desc"]').prop('disabled', 'disabled');
					break;  
			}
		});
		$('#ag_layout').trigger('change'); // on init
    });
	</script>
    <?php
	return true;
}



//////////////////////////
// CREATE WATERMARK CACHE
function ag_create_gall_wmark_cache() {
	global $post;
	if(ag_get_gall_first_img($post->ID, 'img')) :
	?>
    <div class="lcwp_mainbox_meta">
    	<div><a><i class="dashicons dashicons-shield"></i> <?php _e('Create watermark cache', 'ag_ml') ?></a> <span></span></div>
    </div>

    <script type="text/javascript">
	jQuery(document).ready(function($) {
		var $wmark_box = jQuery('#ag_create_gall_wmark_cache .lcwp_mainbox_meta > div');
		
		$('body').delegate('#ag_create_gall_wmark_cache .lcwp_mainbox_meta a', 'click', function() {
			
			$wmark_box.find('small, div').remove();
			
			//$wmark_box.find('div').remove(); // clean past results
			$wmark_box.find('span').html('<div style="width: 20px; height: 20px;" class="lcwp_loading"></div>');
			$wmark_box.append('<small style="padding-left: 15px;">(<?php echo ag_sanitize_input( __('might take very long if you have many images to manage', 'ag_ml')) ?>)</small>');

			var data = {
				action: 'ag_create_wm_cache',
				gid: <?php echo $post->ID ?>
			};
			$.post(ajaxurl, data, function(response) {
				var resp = $.trim(response);

				$wmark_box.find('span').empty();
				$wmark_box.find('small').remove();

				if(resp == 'success') {
					$wmark_box.append('<div><?php echo ag_sanitize_input( __('Cache created succesfully', 'ag_ml')) ?>!</div>');
				}
				else {
					console.error(resp);
					
					if(resp.indexOf("Maximum execution") != -1) {
						$wmark_box.append('<div><?php _e('Process took too much time for your server. Try again', 'ag_ml' ) ?></div>');
					}
					else if(resp.indexOf("bytes exhausted") != -1) {
						$wmark_box.append('<div><?php _e('The process requires too much memory for your server. Try using smaller images', 'ag_ml' ) ?></div>');
					}
					else {
						$wmark_box.append('<div><?php _e('Error during the cache creation', 'ag_ml' ) ?></div>');
					}
				}
			}).fail(function(xhr, status, error) {
        		console.error(error);
    		});;
		});
	});
	</script>

    <style type="text/css">
	#ag_create_gall_wmark_cache {display: block;}
	</style>
    <?php else : ?>

    <style type="text/css">
	#ag_create_gall_wmark_cache {display: none;}
	</style>

    <?php endif;
}



//////////////////////////
// GALLERY SPECIFIC SETTINGS
function ag_specific_settings() {
	include_once(AG_DIR . '/classes/ag_builder_sources_hub.php');
	global $post;
	
	
	include_once(AG_DIR . '/classes/source_helpers/onedrive_integration.php');
	$onedrive = new ag_onedrive_integration($post->ID);
	
	
	
	
	?>
    <div class="lcwp_mainbox_meta">
    	<div id="ag_settings_wrap">
			<?php
            $hub = new ag_builder_hub($post->ID, get_post_meta($post->ID, 'ag_type', true) );
            echo $hub->spec_opt();
            ?>
        </div>
    </div>
    <?php
}



//////////////////////////
// GALLERY BUILDER
function ag_gallery_builder() {
	include_once(AG_DIR . '/classes/ag_connections_hub.php');
	include_once(AG_DIR . '/functions.php');
	
	global $post;
	$conn_hub = new ag_connection_hub($post->ID);
	
	$type = get_post_meta($post->ID, 'ag_type', true);
	$autopop = get_post_meta($post->ID, 'ag_autopop', true);

	if( (float)substr(get_bloginfo('version'), 0, 3) >=  3.8) {
		echo '
		<style type="text/css">
		#ag_bulk_opt_wrap input {
			margin-top: 0px;
		}
		</style>';
	}
	?>

    <div class="lcwp_mainbox_meta">
    	<div id="ag_builder_wrap">
		<?php
		if($autopop != '1') {
			$gallery = ag_gall_data_get($post->ID);

			// picked images gallery
			if(!$gallery || !is_array($gallery) || (is_array($gallery) && count($gallery) == 0)) {echo '<em>'. __('Select images source', 'ag_ml') .'</em>';}
			else {
				echo '
				<table class="widefat lcwp_table lcwp_metabox_table">
				  <thead>
				  <tr>
					<th>
						<div id="ag_bulk_opt_wrap" style="display: none;"></div>
					</th>
				  </tr>
				  </thead>
				</table>
				<ul id="ag_fb_builder">';

				if(!$gallery || !is_array($gallery)) {$gallery = array();}
				foreach($gallery as $item) {
					$tags 		= (isset($item['tags'])) ? $item['tags'] : '';
					$link_opt 	= (isset($item['link_opt'])) ? $item['link_opt'] : 'none';
					$link_val 	= (isset($item['link'])) ? $item['link'] : '';

					$img_full_src = ag_img_src_on_type($item['img_src'], $type);
					$img_full_url = ag_img_url_on_type($item['img_src'], $type);

					$thumb = (!get_option('ag_use_admin_thumbs')) ? $img_full_url : ag_thumb_src($img_full_src, $width = 400, false, 85, $alignment = $item['thumb'], 3);
					echo '<li>
						<div class="ag_sm_handler lcwp_move_row"></div>
						<div class="ag_cmd_bar">
							<div class="lcwp_row_to_sel" title="'. __('select image', 'ag_ml') .'"></div>
							<div class="lcwp_del_row" title="'. __('remove image', 'ag_ml') .'"></div>
							<div class="ag_sel_thumb" title="'. __("set thumbnail's center", 'ag_ml') .'">
								<input type="hidden" name="ag_item_thumb[]" value="'.ag_sanitize_input($item['thumb']).'" class="ag_item_thumb" />
							</div>
							<div class="ag_enlarge_img" title="'. __('enlarge image', 'ag_ml') .'"></div>
						</div>
						<div class="ag_builder_img_wrap">
							<figure style="background-image: url('.$thumb.');" class="ag_builder_img" fullurl="'.ag_sanitize_input($img_full_url).'" title="'. __("click to enlarge", 'ag_ml') .'"></figure>
							<input type="hidden" name="ag_item_img_src[]" value="'.ag_sanitize_input($item['img_src']).'" class="ag_item_img_src" />
						</div>
						<div class="ag_img_texts">
							<table>
							  <tr>
								<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/photo_author.png" title="photo author" /></td>
								<td><input type="text" name="ag_item_author[]" value="'.ag_sanitize_input($item['author']).'" class="ag_item_author" autocomplete="off" /></td>
							  </tr>
							  <tr>
								<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/photo_title.png" title="photo title" /></td>
								<td><input type="text" name="ag_item_title[]" value="'.ag_sanitize_input($item['title']).'" class="ag_item_title" autocomplete="off" /></td>
							  </tr>
							  <tr>
								<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/photo_descr.png" title="photo description" /></td>
								<td><textarea name="ag_item_descr[]" class="ag_item_descr" autocomplete="off">'.ag_sanitize_input($item['descr']).'</textarea></td>
							  </tr>
							  <tr>
								<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/tags_icon.png" title="photo tags - comma split" /></td>
								<td><textarea name="ag_item_tags[]" class="ag_item_tags" autocomplete="off">'.ag_sanitize_input($tags).'</textarea></td>
							  </tr>
							  <tr>
								<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/link_icon.png" title="photo link" /></td>
								<td>
									<select name="ag_link_opt[]" class="ag_linking_dd" autocomplete="off">
										<option value="none">'. __('No link', 'ag_ml') .'</option>
										<option value="page" '; if($link_opt == 'page') {echo 'selected="selected"';} echo '>'. __('To a page', 'ag_ml') .'</option>
										<option value="custom" '; if($link_opt == 'custom') {echo 'selected="selected"';} echo '>'. __('Custom link', 'ag_ml') .'</option>
									</select>
									<div class="ag_link_wrap">'.ag_link_field($link_opt, $link_val).'</div>
								</td>
							  </tr>
							</table>
						</div>
					</li>';
				}

				echo '</ul>';
			}
		}

		// auto population gallery
		else {
			$gallery = ag_gall_data_get($post->ID, true);

			if(!is_array($gallery) || count($gallery) == 0) {echo '<em>'. __('No images found', 'ag_ml') .' .. </em>';}
			else {
				echo '<ul id="ag_fb_builder" class="ag_autopop_gallery">';

				foreach($gallery as $img) {
					$img_full_src = ag_img_src_on_type($img['img_src'], $type);
					$img_full_url = ag_img_url_on_type($img['img_src'], $type);

					$thumb = (!get_option('ag_use_admin_thumbs')) ? $img_full_url : ag_thumb_src($img_full_src, $width = 400, false, 'c', 3);
					echo '<li>
						<div class="ag_builder_img_wrap">
							<figure style="background-image: url('.$thumb.');" class="ag_builder_img" fullurl="'.ag_sanitize_input($img_full_url).'" title="'. __("click to enlarge", 'ag_ml') .'"></figure>
						</div>
						<div class="ag_img_texts">
							<table>
							  <tr>
								<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/photo_author.png" title="photo author" /></td>
								<td>'.ag_sanitize_input($img['author']).'</td>
							  </tr>
							  <tr>
								<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/photo_title.png" title="photo title" /></td>
								<td>'.ag_sanitize_input($img['title']).'</td>
							  </tr>
							  <tr>
								<td class="ag_img_data_icon"><img src="'.AG_URL.'/img/photo_descr.png" title="photo description" /></td>
								<td>'.ag_sanitize_input($img['descr']).'</td>
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
			}
		}
        ?>
        </div>
    </div>

    <?php // hidden code to set the thumbnail center ?>
    <div id="ag_set_thumb_center" style="display: none;">
    	<h4><?php _e('Select thumbnail center clicking on a cell', 'ag_ml') ?>:</h4>
        <table class="ag_sel_thumb_center">
            <tr>
                <td id="ag_tl"></td>
                <td id="ag_t"></td>
                <td id="ag_tr"></td>
            </tr>
            <tr>
                <td id="ag_l"></td>
                <td id="ag_c"></td>
                <td id="ag_r"></td>
            </tr>
            <tr>
                <td id="ag_bl"></td>
                <td id="ag_b"></td>
                <td id="ag_br"></td>
            </tr>
        </table>
    </div>

    <?php // ////////////////////// ?>

    <?php // SCRIPTS ?>
	<script src="<?php echo AG_URL; ?>/js/functions.js" type="text/javascript"></script>
    <script src="<?php echo AG_URL; ?>/js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo AG_URL; ?>/js/lc-switch/lc_switch.min.js" type="text/javascript"></script>
    <script src="<?php echo AG_URL; ?>/js/jquery.jstepper.min.js" type="text/javascript"></script>

    <script src="<?php echo AG_URL; ?>/js/jquery.event.drag-2.2/jquery.event.drag-2.2.js" type="text/javascript"></script>
    <script src="<?php echo AG_URL; ?>/js/jquery.event.drag-2.2/jquery.event.drag.live-2.2.js" type="text/javascript"></script>
    <script src="<?php echo AG_URL; ?>/js/jquery.event.drop-2.2/jquery.event.drop-2.2.js" type="text/javascript"></script>
    <script src="<?php echo AG_URL; ?>/js/jquery.event.drop-2.2/jquery.event.drop.live-2.2.js" type="text/javascript"></script>

	<link rel="stylesheet" href="<?php echo AG_URL; ?>/js/lightboxes/lc-lightbox/css/lc_lightbox.min.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?php echo AG_URL; ?>/js/lightboxes/lc-lightbox/skins/light.css" type="text/css" media="all" />
	<script src="<?php echo AG_URL; ?>/js/lightboxes/lc-lightbox/js/lc_lightbox.ag.min.js" type="text/javascript"></script>
	
	<style type="text/css">
	/* LC-LIGHTBOX - zoom-in effect */
	.lcl_zoomin_oc.lcl_pre_show #lcl_window,
	.lcl_zoomin_oc.lcl_is_closing #lcl_window {
		opacity: 0 !important;
		
		-webkit-transform: scale(0.05) translateZ(0) !important;
		transform: scale(0.05) translateZ(0) !important;		
	}
	.lcl_zoomin_oc.lcl_is_closing #lcl_overlay {
		opacity: 0 !important;
	}
	</style>
	
    
    
    <script type="text/javascript">
	$ = jQuery;
	
	// First init - gallery settings & builder load
	var gid = <?php echo $post->ID; ?>;
	var TT_url = '<?php echo AG_TT_URL ?>';
	var EWPT_url = '<?php echo AG_EWPT_URL ?>';
	var ag_use_tt = <?php echo (get_option('ag_use_timthumb')) ? 'true' : 'false'; ?>;
	var ag_erase_past = false; // flag reporting whether a gallery cleaning is needed (if source changes for example)

	// encapsulate ajax objects to abort them in case and save server resources 
	var spec_opt_ajax = false;
	var img_picker_ajax = false;
	
	
	// basic gallery data handle
	ag_basic_data = function() {
		$ = jQuery; // dunno why, but with some customers it's needed
		
		ag_type 	= $('#ag_type_dd').val();
		ag_username = $('#ag_username').val();
		ag_psw 		= $('#ag_psw').val();
	}
	
	
	// get the additional vars depending on the type
	get_type_extra = function() {
		if( ag_type == 'wp_cat') 		{return $('#ag_wp_cat').val();}
		else if( ag_type == 'cpt_tax')  {return { cpt_tax : $('#ag_cpt_tax').val(), term : $('#ag_cpt_tax_term').val() }; }
		else if( ag_type == 'ag_album') {return $('#ag_album').val();}
		else if( ag_type == 'fb') 		{return $('#ag_fb_album').val();}
		else if( ag_type == 'picasa') 	{return $('#ag_picasa_album').val();}
		else if( ag_type == 'g_drive') 	{return $('#ag_gdrive_album').val();}
		else if( ag_type == 'onedrive') {return $('#ag_onedrive_album').val();}
		else if( ag_type == 'rml') 		{return $('#ag_rml_folder').val();}
		else if( ag_type == 'ngg') 		{return $('#ag_nag_gallery').val();}
		else {return '';}
	}
	
	
	// init fetching data and starting images picker
	ag_gallery_init = function(on_builder_opening) {
		ag_basic_data();
		
		if(typeof(on_builder_opening) == 'undefined') {
			ag_load_settings();	
		}
		else {
			ag_load_img_picker(1);	
		}
	}
	

	// gallery settings display
	ag_load_settings = function() {
		if(spec_opt_ajax !== false) {spec_opt_ajax.abort();}
		$('#ag_settings_wrap').html('<div style="height: 30px;" class="lcwp_loading"></div>');

		var data = {
			action: 'ag_load_settings',
			gallery_id: gid,
			ag_type: ag_type,
			ag_username: ag_username,
			ag_connect_id: ($('#ag_connect_id').size()) ? $('#ag_connect_id').val() : false,
			ag_psw: ag_psw
		};

		spec_opt_ajax = $.post(ajaxurl, data, function(response) {
			$('#ag_settings_wrap').html(response);
			ag_numeric_fields();
			ag_live_chosen();
			ag_ip_checks();
			
			spec_opt_ajax = false;
			ag_load_img_picker(1);
		});

		return true;
	}
	
	
	// images picker
	ag_img_pp = 26;
	ag_sel_img = [];

	// load images picker
	ag_load_img_picker = function(page) {
		if(img_picker_ajax !== false) {img_picker_ajax.abort();}
		
		$('#ag_img_picker').html('<div style="height: 30px;" class="lcwp_loading"></div>');
		
		var data = {
			action: 'ag_img_picker',
			ag_type: ag_type,
			page: page,
			per_page: ag_img_pp,
			gallery_id: gid,
			ag_search: ($('.ag_img_search').length) ? $('.ag_img_search').val() : '',
			ag_extra: get_type_extra()
		};

		img_picker_ajax = $.post(ajaxurl, data, function(response) {
			$('#ag_img_picker').html(response);
			ag_sel_img_on_drag();
			ag_sel_picker_img_status();
			
			img_picker_ajax = false;
		});

		return true;
	}
	
	
	$(document).ready(function($) {
		ag_gallery_init(true);
		ag_count_gall_images();
		

		// update on gallery type change
		$(document).delegate('#ag_type_dd', 'change', function(e) {
			if( $('#ag_fb_builder').size() == 0 || ( $('#ag_fb_builder').size() && confirm("<?php _e('Current gallery will be erased. Continue?', 'ag_ml') ?>") ) ) {
				var ag_new_type = $(this).val();
				
				ag_erase_past = 1;
				ag_reset_gallery();
				
				// init gallery if source doesn't require config
				if( $.inArray(ag_new_type, ['wp', 'wp_cat', 'cpt_tax', 'ag_album', 'rml', 'ngg']) !== -1 ) {
					ag_gallery_init();
					
					$('#ag_connect_id_wrap, #ag_username_wrap, #ag_psw_wrap, #ag_handle_user_btn').slideUp();
					return false;
				}


				// connection hub toggle
				if($.inArray(ag_new_type, ['<?php echo implode("','", $conn_hub->to_consider) ?>']) !== -1) {
					$('#ag_connect_id_wrap').slideDown();
					ag_reload_conn_hub_dd();	
				}
				else {
					$('#ag_connect_id_wrap').slideUp();
				}
				
				////////////////////////////////////////////
				
								
				// change username label
				switch(ag_new_type) {
					case 'flickr'	: $('#ag_username_wrap label').text('<?php echo ag_sanitize_input( __('Set / Profile / Tag URL', 'ag_ml')) ?>'); break;
					case 'pinterest': $('#ag_username_wrap label').text('<?php echo ag_sanitize_input( __('Board URL', 'ag_ml')) ?>'); break;
					case 'fb'		: $('#ag_username_wrap label').text('<?php echo ag_sanitize_input( __('Page URL', 'ag_ml')) ?>'); break;
					case 'instagram': $('#ag_username_wrap label').text('<?php echo ag_sanitize_input( __('Username', 'ag_ml') /*__('Username or hashtag', 'ag_ml')*/) ?>'); break;
					case 'g_drive'	: $('#ag_username_wrap label').text('<?php echo ag_sanitize_input( __('Public folder URL', 'ag_ml')) ?>'); break;
					case 'twitter'	: $('#ag_username_wrap label').text('<?php echo ag_sanitize_input( __('@Username or #hashtag', 'ag_ml')) ?>'); break;
					case 'tumblr'	: $('#ag_username_wrap label').text('<?php echo ag_sanitize_input( __('Blog URL', 'ag_ml')) ?>'); break;
					case 'rss'		: $('#ag_username_wrap label').text('<?php echo ag_sanitize_input( __('Feed URL', 'ag_ml')) ?>'); break;
					default			: $('#ag_username_wrap label').text('<?php echo ag_sanitize_input( __('Username', 'ag_ml')) ?>'); break;
				}

				//// gallery type auth data toggle
				// reset fields
				if(ag_type != ag_new_type) { $('#ag_username_wrap input, #ag_psw_wrap input').val(''); }
				
				
				// password visibility (and instagram getToken - only field to use psw field)
				// JULY 2016 - only able to fetch personal data - useless username 
				if(ag_new_type == 'instagram') { 
					$('#ag_psw_wrap, #ag_instagram_get_token').slideDown(); 
				} else {
					$('#ag_psw_wrap, #ag_instagram_get_token').slideUp(); 
				}
				
				
				// username field visibility
				if( $.inArray(ag_new_type, ['<?php echo  implode("','", array_merge($conn_hub->to_consider, array('wp', 'wp_cat', 'cpt_tax', 'ag_album', 'rml', 'ngg'))) ?>']) === -1 ) {
					$('#ag_username_wrap').slideDown();
				} else {
					$('#ag_username_wrap').slideUp();
				}
				

				// connect button visibility
				if( $.inArray(ag_new_type, ['wp', 'wp_cat', 'cpt_tax', 'ag_album', 'rml', 'ngg']) === -1 ) {
					$('#ag_handle_user_btn').slideDown();
				} else {
					$('#ag_handle_user_btn').slideUp();
				}
			}
			else { return false; }
		});
		
		// start on "connect" button click
		$(document).delegate('#ag_handle_user_btn', 'click', function() {
			if( $('#ag_fb_builder').size() == 0 || ( $('#ag_fb_builder').size() && confirm("<?php echo _e('Current gallery will be erased. Continue?', 'ag_ml') ?>") ) ) {
			
				ag_erase_past = 1;
				ag_reset_gallery();
				ag_gallery_init();
			}
		});
	});
	
	
	// reset gallery
	ag_reset_gallery = function() {
		$('#ag_settings_wrap').html('<em><?php echo ag_sanitize_input( __('Select gallery type and fill in data to get images', 'ag_ml')) ?></em>');
		$('#ag_builder_wrap').html('<em><?php echo ag_sanitize_input( __('Select images source', 'ag_ml')) ?></em>');

		$('#ag_gallery_builder h3.hndle small').remove();
	}


	/////////////////////////////////////


	////////////////////////
	// gallery management

	// add selected images to the gallery
	$(document).delegate('#ag_add_img', 'click', function() {
		if( !$('#ag_builder_wrap > ul').length ) {
			$('#ag_builder_wrap').html('\
				<table class="widefat lcwp_table lcwp_metabox_table">\
				  <thead><tr>\
					  <th><div id="ag_bulk_opt_wrap" style="display: none;"></div></th>\
				  </tr></thead>\
				</table>\
				<ul id="ag_fb_builder"></ul>');
		}

		// revert array to add in right order
		ag_sel_img.reverse();

		$.each(ag_sel_img, function(index, value) {
			var img_id = value.substr(4);
			var $img = $('#'+img_id);
			
			var img_url = $img.attr('fullurl');

			var img_full_src = encodeURIComponent( $img.attr('img_full_src') );
			var img_src = $('#'+img_id).attr('img_src');

			var author = $img.attr('author');
			var title  = $img.attr('title');
			var descr  = $img.attr('alt');

			var base_script = (ag_use_tt) ? TT_url : EWPT_url;
			var thumb_url = <?php echo (!get_option('ag_use_admin_thumbs')) ? 'img_url' : "base_script +'?src='+img_full_src+'&w=400&h=&q=85&rs=3&zc=3'"; ?>;

			var new_tr = 
			'<li id="'+img_id+'">\
				<div class="ag_sm_handler lcwp_move_row"></div>\
				<div class="ag_cmd_bar">\
					<div class="lcwp_row_to_sel"></div>\
					<div class="lcwp_del_row"></div>\
					<div class="ag_sel_thumb" title="set the thumbnail center">\
						<input type="hidden" name="ag_item_thumb[]" value="c" class="ag_item_thumb" />\
					</div>\
					<div class="ag_enlarge_img" title="enlarge image"></div>\
				</div>\
				<div class="ag_builder_img_wrap">\
					<figure style="background-image: url('+ thumb_url +');" class="ag_builder_img" fullurl="'+img_url+'"></figure>\
					<input type="hidden" name="ag_item_img_src[]" value="'+img_src+'" class="ag_item_img_src" />\
				</div>\
				<div class="ag_img_texts">\
					<table>\
					  <tr>\
						<td class="ag_img_data_icon"><img src="<?php echo AG_URL ?>/img/photo_author.png" title="photo author" /></td>\
						<td><input type="text" name="ag_item_author[]" value="'+author+'" class="ag_item_author" autocomplete="off" /></td>\
					  </tr>\
					  <tr>\
						<td class="ag_img_data_icon"><img src="<?php echo AG_URL ?>/img/photo_title.png" title="photo title" /></td>\
						<td><input type="text" name="ag_item_title[]" value="'+title+'" class="ag_item_title" autocomplete="off" /></td>\
					  </tr>\
					  <tr>\
						<td class="ag_img_data_icon"><img src="<?php echo AG_URL ?>/img/photo_descr.png" title="photo description" /></td>\
						<td><textarea name="ag_item_descr[]" class="ag_item_descr" autocomplete="off">'+descr+'</textarea></td>\
					  </tr>\
					  <tr>\
						<td class="ag_img_data_icon"><img src="<?php echo AG_URL ?>/img/tags_icon.png" title="photo tags - comma split" /></td>\
						<td><textarea name="ag_item_tags[]" class="ag_item_tags" autocomplete="off"></textarea></td>\
					  </tr>\
					  <tr>\
						<td class="ag_img_data_icon"><img src="<?php echo AG_URL ?>/img/link_icon.png" title="photo link" /></td>\
						<td><select name="ag_link_opt[]" class="ag_linking_dd" autocomplete="off">\
								<option value="none"><?php echo ag_sanitize_input( __('No link', 'ag_ml')) ?></option>\
								<option value="page"><?php echo ag_sanitize_input( __('To a page', 'ag_ml')) ?></option>\
								<option value="custom"><?php echo ag_sanitize_input( __('Custom link', 'ag_ml')) ?></option>\
							</select>\
							<div class="ag_link_wrap"><?php echo ag_link_field('none') ?></div>\
						</td>\
					</table>\
				</div>\
			</li>';

			$('#ag_fb_builder').prepend( new_tr );
			
			
			// has got link? add it!
			if($img.attr('link')) {
				$('#ag_fb_builder #'+img_id +' .ag_linking_dd option[value="custom"]').attr('selected', 'selected');	
				$('#ag_fb_builder #'+img_id +' .ag_linking_dd').trigger('change');
				$('#ag_fb_builder #'+img_id +' .ag_link_field').val( $img.attr('link') );
			}
		});

		$('#ag_img_picker ul li.ag_img_sel').removeClass('ag_img_sel');
		$('#ag_img_picker ul li.selected').removeClass('selected');
		$('#ag_add_img').fadeOut();

		ag_sel_picker_img_status();
		ag_items_sort();
		ag_count_gall_images();
		ag_read_imgs_data();
	});


	// sortable images 
	ag_items_sort = function() {
		$('#ag_fb_builder').sortable({
			placeholder: {
				element: function(currentItem) {
					if(!ag_sort_mode_on) {
						return $('<li style="border-color: transparent; border-style: solid; border-width: 0 15px 20px; background-color: #97dd52; height: 350px; margin-bottom: -133px;"></li>')[0];
					} else {
						return $('<li id="ag_builder_sm_placeh"></li>')[0];
					}
				},
				update: function(container, p) {
					return;
				}
			},
			tolerance: 'intersect',
			handle: '.ag_builder_img, .ag_sm_handler',
			items: 'li',
			opacity: 0.9,
			scrollSensivity: 50,
			create: function() {
				$("#ag_fb_builder table input, #ag_fb_builder table textarea").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
				  e.stopImmediatePropagation();
				});
			},
			stop: function () {
				$("#ag_fb_builder table input, #ag_fb_builder table textarea").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
				  e.stopImmediatePropagation();
				});
			}
		});
	};
	$(document).ready(function($) {ag_items_sort();});


	// rows select
	$(document).delegate('.lcwp_row_to_sel', 'click', function() {
		$(this).toggleClass('lcwp_sel_row');
		$(this).parent().parent().toggleClass('selected');
		ag_bulk_opt();
	});
	
	// remove item
	$(document).delegate('.ag_cmd_bar .lcwp_del_row', 'click', function() {
		if(confirm("<?php _e('Remove image?', 'ag_ml') ?>")) {
			$(this).parent().parent().fadeOut(function() {
				$(this).remove();
				ag_sel_picker_img_status();
				ag_count_gall_images();
			});
		}
	});

	
	
	///////////////////////////////////////////////////
	
	
	
	// EASY SORTING MODE
	//// append button - wait a bit to let images counter and bulk selection to be added
	setTimeout(function() {
		$('#ag_gallery_builder h2.hndle').append('<a href="javascript:void(0)" id="ag_sort_mode"><?php echo ag_sanitize_input( __('Easy sorting', 'ag_ml')) ?> <span id="ag_sm_flag" class="off">OFF</span></a>');	
		if( $('#ag_autopop input').is(':checked') ) {
			$('#ag_sort_mode').hide();
		}
	}, 200);
	
	ag_sort_mode_on = false;
	
	// manage sorting mode toggle
	$(document).delegate('#ag_sort_mode', 'mousedown', function(e) {
		e.stopImmediatePropagation();
		
		if( !$(this).hasClass('ag_esm_on') ) {
			$('#ag_fb_builder').addClass('ag_is_sorting');
			$(this).addClass('ag_esm_on');
			
			$('#ag_sm_flag').text('ON');
			ag_sort_mode_on = true;
		}
		else {
			$('#ag_fb_builder').removeClass('ag_is_sorting');
			$(this).removeClass('ag_esm_on');
			
			$('#ag_sm_flag').text('OFF');
			ag_sort_mode_on = false;
		}
	});
	


	/////////////////////////////////////////////////////



	// BULK CONTROLS	
	//// append button - wait a bit to let images counter to act 
	setTimeout(function() {
		$('#ag_gallery_builder h2.hndle').append('<a href="javascript:void(0)" id="ag_select_all_img"><?php echo ag_sanitize_input( __('Select all', 'ag_ml')) ?></a>');	
		if( $('#ag_autopop input').is(':checked') ) {
			$('#ag_select_all_img').hide();
		}
	}, 250);
	
	//// track button click
	$(document).delegate('#ag_select_all_img', 'mousedown', function(e) {
		e.stopImmediatePropagation();
		
		if( $(this).hasClass('selected') ) {
			$(this).removeClass('selected').text('<?php echo ag_sanitize_input( __('Select all', 'ag_ml')) ?>');
			$('.lcwp_row_to_sel').each(function() {
				$(this).removeClass('lcwp_sel_row');
				$(this).parent().parent().removeClass('selected');
			});
			ag_bulk_opt();
		}
		else {
			if(!$('.lcwp_row_to_sel').length) {return false;}
			
			$(this).addClass('selected').text('<?php echo ag_sanitize_input( __('Deselect all', 'ag_ml')) ?>');
			$('.lcwp_row_to_sel').each(function() {
				$(this).addClass('lcwp_sel_row');
				$(this).parent().parent().addClass('selected');
			});
			ag_bulk_opt();
		}
	});
	
	// bulk options code
	ag_bulk_opt = function() {
		var bulk_opt_code = '\
		  <label style="padding-right: 5px; margin-bottom: -2px;"><?php echo ag_sanitize_input( __('Bulk Options', 'ag_ml')) ?></label>\
		  <select data-placeholder="<?php echo ag_sanitize_input( __('Select an option', 'ag_ml')) ?> .." id="ag_bulk_opt" class="lcweb-chosen" tabindex="2" style="width: 200px;">\
			<option value="remove"><?php echo ag_sanitize_input( __('Remove Images', 'ag_ml')) ?></option>\
			<option value="author"><?php echo ag_sanitize_input( __('Set Author', 'ag_ml')) ?></option>\
			<option value="title"><?php echo ag_sanitize_input( __('Set Title', 'ag_ml')) ?></option>\
			<option value="descr"><?php echo ag_sanitize_input( __('Set Description', 'ag_ml')) ?></option>\
			<option value="thumb"><?php echo ag_sanitize_input( __('Set Thumbnail Center', 'ag_ml')) ?></option>\
		  </select>\
		  \
		  <input type="text" value="" id="ag_bulk_val" style="margin-left: 15px; padding: 5px;position: relative;top: 2px; display: none;" />\
		  \
		  <span id="ag_bulk_thumb_wrap" style="padding-left: 20px; display: none;">\
		  <select data-placeholder="<?php echo ag_sanitize_input( __('Select an option', 'ag_ml')) ?> .." id="ag_bulk_thumb_val" class="lcweb-chosen" tabindex="2" style="width: 100px;">\
			<option value="tl"><?php echo ag_sanitize_input( __('Top-left', 'ag_ml')) ?></option>\
			<option value="t"><?php echo ag_sanitize_input( __('Top', 'ag_ml')) ?></option>\
			<option value="tr"><?php echo ag_sanitize_input( __('Top-right', 'ag_ml')) ?></option>\
			<option value="l"><?php echo ag_sanitize_input( __('Left', 'ag_ml')) ?></option>\
			<option value="c"><?php echo ag_sanitize_input( __('Center', 'ag_ml')) ?></option>\
			<option value="r"><?php echo ag_sanitize_input( __('Right', 'ag_ml')) ?></option>\
			<option value="bl"><?php echo ag_sanitize_input( __('Bottom-left', 'ag_ml')) ?></option>\
			<option value="b"><?php echo ag_sanitize_input( __('Bottom', 'ag_ml')) ?></option>\
			<option value="br"><?php echo ag_sanitize_input( __('Bottom-right', 'ag_ml')) ?></option>\
		  </select>\
		  </span>\
		  <input type="button" value="<?php echo ag_sanitize_input( __('Apply', 'ag_ml')) ?>" id="ag_bulk_perform" class="button-secondary" style="margin-left: 15px; padding: 0px 9px;" />\
		';

		if($('#ag_fb_builder li.selected').size() > 0) {
			$('#ag_bulk_opt_wrap').empty();
			$('#ag_bulk_opt_wrap').append(bulk_opt_code).fadeIn();
			ag_live_chosen();
			ag_bulk_opt_input_toggle();
		}
		else {
			$('#ag_bulk_opt_wrap').fadeOut(function() {
				$(this).empty();
			});
		}
	}

	// bulk opt input toggle
	ag_bulk_opt_input_toggle = function() {
		$(document).delegate('#ag_bulk_opt', 'change', function() {
			if( $(this).val() == 'remove') {$('#ag_bulk_val, #ag_bulk_thumb_wrap').fadeOut();}
			else if( $(this).val() == 'thumb') {
				$('#ag_bulk_val').val('').fadeOut();
				$('#ag_bulk_thumb_wrap').fadeIn();
			}
			else {
				$('#ag_bulk_val').val('').fadeIn();
				$('#ag_bulk_thumb_wrap').fadeOut();
			}
		});
	}

	// perform bulk opt
	$(document).delegate('#ag_bulk_perform', 'click', function() {
		var type = $('#ag_bulk_opt').val();
		var bulk_val = $('#ag_bulk_val').val();
		var new_center = $('#ag_bulk_thumb_val').val();

		if(type == 'remove') {
			if(confirm('<?php echo ag_sanitize_input( __('Remove selected images?', 'ag_ml')) ?>')) {
				$('#ag_fb_builder li.selected').fadeOut(function() {
					$(this).remove();
					ag_sel_picker_img_status();
					ag_count_gall_images();
				});
				ag_reset_selection();
			}
		}
		else if(type == 'thumb') {
			$('#ag_fb_builder li.selected').each(function() {
				$(this).find('.ag_item_thumb').val(new_center);

				var img_url =  $(this).find('.ag_builder_img').attr('fullurl');
				var new_thumb_url = TT_url+'?src='+img_url+'&w=400&h=190&q=90&a='+new_center;
				$(this).find('.ag_builder_img').attr('src', new_thumb_url);
			});

			ag_reset_selection();
		}
		else {
			if(type == 'author') {
				$('#ag_fb_builder li.selected .ag_item_author').val(bulk_val);
			}
			else if(type == 'title') {
				$('#ag_fb_builder li.selected .ag_item_title').val(bulk_val);
			}
			else if(type == 'descr') {
				$('#ag_fb_builder li.selected .ag_item_descr').val(bulk_val);
			}

			ag_reset_selection();
		}
	});

	// reset items selection
	ag_reset_selection = function() {
		$('.lcwp_sel_row').each(function() {
			$(this).removeClass('lcwp_sel_row');
			$(this).parent().parent().removeClass('selected');

			if($('#ag_select_all_img').hasClass('selected')) {
				$('#ag_select_all_img').removeClass('selected').text("<?php echo ag_sanitize_input( __('select all', 'ag_ml')) ?>");
			}
			ag_bulk_opt();
		});
	}

	// status updater for selected images
	ag_sel_picker_img_status = function() {
		var ag_gallery_img = $.makeArray();
		$('.ag_builder_img').each(function() {
			var img_url = $(this).attr('fullurl');
			ag_gallery_img.push(img_url);
		});

		$('.ag_all_img').each(function() {
			var img_url = $(this).attr('fullurl');
			if( $.inArray(img_url, ag_gallery_img) != -1) {
				$(this).parent().addClass('ag_img_inserted');
			}
			else { $(this).parent().removeClass('ag_img_inserted'); }
		});
	}

	// linking management
	$(document).delegate('.ag_img_texts select.ag_linking_dd', 'change', function() {
		var link_opt = $(this).val();

		if(link_opt == 'page') {
			var link_field = '<?php echo str_replace("'", "\'", ag_link_field('page')); ?>';
		}
		else if(link_opt == 'custom') {
			var link_field = '<?php echo ag_link_field('custom'); ?>';
		}
		else {
			var link_field = '<?php echo ag_link_field('none'); ?>';
		}

		$(this).parent().find('.ag_link_wrap').html(link_field);
	});



	///////////////////////////////////////////////////////////////////////////



	////////////////////////
	// automatic gallery population

	// autopop switch behaviours
	$(document).delegate('#ag_autopop input', 'lcs-statuschange', function() {
		if( $(this).is(':checked') ) {
			ag_autopop_make_cache();
			ag_sel_picker_img_status();

			// gallery auto-population toggle
			$('.ag_autopop_fields').slideDown();
			$('#ag_sort_mode, #ag_select_all_img, #ag_img_picker_area').fadeOut();
			
			if($('#ag_sort_mode').hasClass('ag_esm_on')) {$('#ag_sort_mode').trigger('mousedown');}
			if($('#ag_select_all_img').hasClass('selected')) {$('#ag_select_all_img').trigger('mousedown');}
		}
		else {
			$('#ag_builder_wrap').html('<em><?php echo ag_sanitize_input( __('Select images source', 'ag_ml')) ?></em>');
			ag_sel_picker_img_status();
			$('#ag_sort_mode, #ag_select_all_img, #ag_img_picker_area').fadeIn();

			// gallery auto-population toggle
			$('.ag_autopop_fields').slideUp();
		}
	});


	// re-load on click
	$(document).delegate('.ag_rebuild_cache', 'click', function() {
		ag_autopop_make_cache();
	});

	// create the cache and display images in the builder
	ag_autopop_make_cache = function() {
		var max_img = $('#ag_max_images').val();
		var random_img = ( $('#ag_auto_random input').is(':checked') ) ? 1 : 0;

		var data = {
			action: 'ag_make_autopop',
			ag_type: ag_type,
			gallery_id: gid,
			ag_extra: get_type_extra(),
			ag_max_img: max_img,
			ag_random_img: random_img,
			ag_erase_past: ag_erase_past
		};

		$('#ag_builder_wrap').html('<div style="height: 30px; margin: 7px 0 3px 15px;" class="lcwp_loading"></div>');

		$.post(ajaxurl, data, function(response) {
			$('#ag_builder_wrap').html(response);
			
			ag_count_gall_images();
			ag_read_imgs_data();

			if(ag_erase_past) {
				ag_erase_past = false;
			}
		});

		return true;
	}

	
	// display added images count 
	ag_count_gall_images = function() {
		if(!$('#ag_gallery_builder > h2  small').size()) {
			$('#ag_gallery_builder > h2').append('<small></small>');	
		}
		var $subj = $('#ag_gallery_builder > h2  small');
		var tot = $('#ag_fb_builder > li').size();
		
		
		if(!tot) {
			$subj.empty();	
		} else {
			$subj.html(' ('+ tot  +' <?php _e('images', 'ag_ml') ?>)');	
		}
	}



	/////////////////////////////////////////////////////////////////////////



	// change imges picker's page
	$('body').delegate('.ag_img_pick_back, .ag_img_pick_next', 'click', function() {
		var page = $(this).attr('id').substr(4);
		ag_load_img_picker(page);
	});

	// change images per page
	$('body').delegate('#ag_img_pick_pp', 'keyup', function() {
		
		if(typeof(ag_img_picker_tOut) != 'undefined') {
			clearTimeout( ag_img_picker_tOut );	
		}
		
		var pp = $(this).val();
		
		ag_img_picker_tOut = setTimeout(function() {
			if( pp.length >= 2 ) {
				if( parseInt(pp) < 10 ) {
					$('#ag_img_pick_pp').val(10);
					ag_img_pp = 10;
				}
				else {ag_img_pp = pp;}

				ag_load_img_picker(1);
			}
		}, 300);
	});

	
	// re-fetch images on search or enter button
	$(document).delegate('.ag_img_search_btn', 'click', function() {
		ag_load_img_picker(1);
	});
	
	$(document).delegate('.ag_img_search', 'keypress', function(e) {

		if(e.keyCode === 13){
			e.preventDefault();
			ag_load_img_picker(1);
			
			return false;
		}
		
		// fetch live
		else {
			if(typeof(ag_img_search_tout) != 'undefined') {clearTimeout(ag_img_search_tout);}
			
			ag_img_search_tout = setTimeout(function() {
				ag_load_img_picker(1);
			}, 400);
		}
   	});
	

	// img selection with mouse drag
	ag_sel_img_on_drag = function() {
		if($('.ag_all_img').size() > 2) {
			$('#ag_img_picker_area').drag("start",function( ev, dd ){
				return $('<div id="ag_drag_selection" />')
					.css('opacity', .45 )
					.appendTo( document.body );
			})
			.drag(function( ev, dd ){
				$( dd.proxy ).css({
					top: Math.min( ev.pageY, dd.startY ),
					left: Math.min( ev.pageX, dd.startX ),
					height: Math.abs( ev.pageY - dd.startY ),
					width: Math.abs( ev.pageX - dd.startX )
				});
			})
			.drag("end",function( ev, dd ){
				$( dd.proxy ).remove();
				ag_man_img_array();
			});

			$('#ag_img_picker ul li')
				.drop(function( ev, dd ){
					if(!$(this).hasClass('ag_img_inserted')) {
						$(this).toggleClass('ag_img_sel');
					}
				})
			$.drop({ multi: true });
		}
	}

	// "select all" action
	$(document).delegate('.ag_sel_all_btn', 'click', function() {
		$('#ag_img_picker ul li').not('.ag_img_inserted').each(function() {
			$(this).addClass('ag_img_sel');
		});
		ag_man_img_array();
	});

	// img selection with click
	$(document).delegate('#ag_img_picker ul li figure', 'click', function() {
		if(!$(this).parent().hasClass('ag_img_inserted')) {
			$(this).parent().toggleClass('ag_img_sel');
			ag_man_img_array();
		}
	});

	// dynamic selection button title
	$(document).delegate('#ag_img_picker ul li', 'hover', function() {
		if ( $(this).hasClass('ag_img_sel') ) { $(this).attr('title', 'Click to unselect'); }
		else if ( $(this).hasClass('ag_img_inserted') ) { $(this).attr('title', 'Image already in the gallery'); }
		else { $(this).attr('title', 'Click to select');}
	});

	// selected images array management
	function ag_man_img_array() {
		ag_sel_img = $.makeArray();
		$('.ag_img_sel').each(function() {
			ag_sel_img.push( $(this).attr('id') );
		});

		if( !ag_sel_img.length) { $('#ag_add_img').fadeOut(); }
		else { $('#ag_add_img').fadeIn(); }
	}


	// reload on category/album change - ask for confirmation
	$(document).delegate('#ag_wp_cat, #ag_cpt_tax_term, #ag_album, #ag_picasa_album, #ag_gdrive_album, #ag_onedrive_album, #ag_fb_album, #ag_rml_folder, #ag_nag_gallery', 'change', function() {
		
		if( !$('.ag_builder_img').length || confirm('<?php echo ag_sanitize_input( __('Current gallery will be erased. Continue?', 'ag_ml')) ?>')) {
			if( $('#ag_autopop input').is(':checked') ) {
				ag_erase_past = 1;
				ag_autopop_make_cache();
			}
			else {
				ag_load_img_picker(1);
				$('#ag_builder_wrap').html('<em><?php echo ag_sanitize_input( __('Select images source', 'ag_ml')) ?></em>');
			}
		} else {
			return false;
		}
	});


	// CPT taxonomy - change subject and reload terms erasing the gallery
	$(document).delegate('#ag_cpt_tax', 'change', function() {
		if( !$('.ag_builder_img').length || confirm('<?php echo ag_sanitize_input( __('Current gallery will be erased. Continue?', 'ag_ml')) ?>')) {
			var data = {
				action: 'ag_cpt_tax_change',
				cpt_tax: $('#ag_cpt_tax').val()
			};

			$('#ag_ctp_tax_term_wrap').html('<div style="height: 30px;" class="lcwp_loading"></div>');

			$.post(ajaxurl, data, function(response) {
				$('#ag_ctp_tax_term_wrap').html(response);
				ag_live_chosen();

				if( $('#ag_autopop input').is(':checked') ) {
					ag_erase_past = 1;
					ag_autopop_make_cache();
				}
				else {
					ag_load_img_picker(1);
					$('#ag_builder_wrap').html('<em><?php echo ag_sanitize_input( __('Select WP images or the images source', 'ag_ml')) ?></em>');
				}
			});
		} else {
			return false;
		}
	});



	//////////////////////////////////////////////////////////////////////////////


	// images info detection - pass through JS to not weight on server
	ag_read_imgs_data = function() {
		$('.ag_builder_img').each(function() {
            var $wrap = $(this).parents('li');
			
			if($wrap.find('.ag_img_info').length) {return true;}
			var img_url = $(this).attr('fullurl');

			// read weight and mime
			var blob = null;
			var xhr = new XMLHttpRequest();
			xhr.open("GET", img_url);
			xhr.responseType = "blob"; //force the HTTP response, response-type header to be blob
			xhr.onload = function() {
				blob = xhr.response; //xhr.response is now a blob object
				
				// image sizes
				var img = new Image();
				img.onload = function() {
					var sizes = this.width + ' x ' + this.height +'px';
					
					// detect mime
					switch(blob.type) {
						case 'image/png' : var type = 'png'; break;
						case 'image/gif' : var type = 'gif'; break;
						default 		 : var type = 'jpg'; break;	
					}
					
					
					// append img info block
					var $target = ($wrap.find('.ag_cmd_bar').length) ? $wrap.find('.ag_cmd_bar') : $wrap.find('.ag_builder_img'); 
					$target.after('<div class="ag_img_info">\
						<span>'+ bytes_to_human(blob.size) +'</span><span>'+ type +'</span><span>'+ sizes +'</span>\
					</div>');
				}
				img.src = img_url;
			};
			xhr.send();
        });	
	};

	var bytes_to_human = function(bytes) {
	   var sizes = ['bytes', 'kb', 'mb', 'gb'];
	   if (bytes == 0) return '0 Byte';
	   var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	   return Math.round(bytes / Math.pow(1024, i), 2) + sizes[i];
	};


	//////////////////////////////////////////////////////////////////////////////


	// custom file uploader for wp gallery
	ag_TB = 0;
	var file_frame = false;

	// open tb and hide tabs
	$(document).delegate('.ag_TB', 'click', function(e) {
		
		// If the media frame already exists, reopen it.
		if(file_frame){
		  file_frame.open();
		  return;
		}
	
		// Create the media frame
		file_frame = wp.media.frames.file_frame = wp.media({
		  title: "<?php _e('Avator Gallery - images management', 'ag_ml') ?>",
		  button: {
			text: "<?php _e('Back to builder', 'ag_ml') ?>",
		  },
		  library : {type : 'image'},
		  multiple: false
		});
		
		// if closed or selected - refresh picker
		file_frame.on('close select', function() {
			ag_load_img_picker(1);
			clearInterval(bb_builder_lb_intval);
		});
	
		// turn button into active in any case and simulate closing
		file_frame.on('open', function() {
			bb_builder_lb_intval = setInterval(function() {
				$('.media-button-select').removeAttr('disabled').addClass('bb_builder_lb_btn');
			}, 10)
		});
		$(document).on('click', '.bb_builder_lb_btn', function() {
			file_frame.close();
		});
	
		file_frame.open();
	});
	
	

	/////////////////////////////////////////////////////////////////////



	// open thumbnail's center wizard
	$(document).delegate('.ag_sel_thumb', 'click', function() {
		$sel = $(this).parents('li');
		var thumb_center = $(this).find('input').val();
		if(thumb_center.length == 0) { var thumb_center = 'c'; }

		var ag_H = 417;
		var ag_W = 480;
		tb_show( 'Thumbnail center', '#TB_inline?height='+ag_H+'&width='+ag_W+'&inlineId=ag_set_thumb_center' );

		$('#TB_ajaxContent .ag_sel_thumb_center td').removeClass('thumb_center');
		$('#TB_ajaxContent .ag_sel_thumb_center #ag_'+thumb_center).addClass('thumb_center');

		$('#TB_window').css("height", ag_H);
		$('#TB_window').css("width", ag_W);

		$('#TB_window').css("top", (($(window).height() - ag_H) / 4) + 'px');
		$('#TB_window').css("left", (($(window).width() - ag_W) / 4) + 'px');
		$('#TB_window').css("margin-top", (($(window).height() - ag_H) / 4) + 'px');
		$('#TB_window').css("margin-left", (($(window).width() - ag_W) / 4) + 'px');

	});

	// set the thumbnail center
	$(document).delegate('#TB_ajaxContent .ag_sel_thumb_center td', 'click', function() {
		var new_center = $(this).attr('id').substr(3);

		$('#TB_ajaxContent .ag_sel_thumb_center td').removeClass('thumb_center');
		$('#TB_ajaxContent .ag_sel_thumb_center #ag_'+new_center).addClass('thumb_center');

		$sel.find('.ag_item_thumb').val(new_center);

		<?php if(get_option('ag_use_admin_thumbs')) : ?>
		var img_src = encodeURIComponent( $sel.find('.ag_item_img_src').val() );
		var base_script = (ag_use_tt) ? TT_url : EWPT_url;
		var new_thumb_url = base_script +'?src='+img_src+'&w=400&h=220&q=85&rs=3&zc=3&a='+new_center;

		$sel.find('.ag_builder_img').attr('src', new_thumb_url);
		<?php endif; ?>
	});



	///////////////////////////////////////////////////////////////



	// live preview link
	<?php
	$preview_pag = get_option('ag_preview_pag');
	if($preview_pag && $gallery) :
		$link = get_permalink($preview_pag);
	?>
		var ag_live_preview = '<div class="misc-pub-section-last">\
			<a href="<?php echo $link; ?>?ag_gid=<?php echo $post->ID; ?>" target="_blank" id="ag_live_preview_link"><?php echo ag_sanitize_input( __('Go to gallery preview', 'ag_ml')) ?> &raquo;</a></div>';

		$('#submitpost').parent().append(ag_live_preview);
		$('#major-publishing-actions').addClass('misc-pub-section');
	<?php endif; ?>



	/////////////////////////////////////////////////////////////////
	
	
	// LC lightbox for images preview
	$(document).delegate(".ag_zoom_img, .ag_enlarge_img", 'click', function(e) {
		var obj = [{
			type	: 'image',
            src		: $(this).parents('li').find('figure').attr('fullurl')
		}];

		var instance = lc_lightbox(obj, {
			wrap_class		: 'lcl_zoomin_oc',
			max_width		: '85%', // Lightbox maximum width. Use a responsive percent value or an integer for static pixel value
			max_height		: '85%', 
			
			ins_close_pos	: 'corner',
			open_close_time	: 200,
			fading_time		: 0,
		});
    	lcl_open(instance, 0); // direct lightbox opening showing first element
	});
	

	/////////////////////////////////////


	$(document).ready(function($) {
		ag_read_imgs_data();

		// images block sizing - cycle to find proper size
		var opts_width = function() {
			var w = $('#ag_gallery_builder').width();
			
			for(a=7; a>0; a--) {
				if( ((w / a) + 15) > 300 || a == 1 ) {

					var w_code = 'width: '+ (100/a) +'%;';
					var how_many = a;
					break; 	
				}
			}
			
			var border_trick = (how_many > 1) ? '#ag_fb_builder li:not(:nth-child('+ how_many +'n)) {border-right-width: 0;}' : '';
			
			$('#ag_img_opts_width').remove();
			$('head').append('<style id="ag_img_opts_width" type="text/css">#ag_fb_builder li {'+ w_code +'} '+ border_trick +'</style>');
		}
		opts_width();
		
		$(window).resize(function() {
			fp_column_w_to = setTimeout(function() {
				if(typeof(fp_column_w_to) != 'undefined') {clearTimeout(fp_column_w_to);}
				opts_width();
			}, 50);
		});
	


		// numeric fields control
		ag_numeric_fields = function() {
			$('#ag_main_settings .lcwp_sidebox_meta input, #ag_max_images').jStepper({minLength:1, allowDecimals:false});
		}
		ag_numeric_fields();
	
		// live lcweb switch init
		ag_ip_checks = function() {
			$('.ip-checkbox').lc_switch('YES', 'NO');
		}
	
		// live chosen init
		ag_live_chosen = function() {
			$('.lcweb-chosen').each(function() {
				var w = $(this).css('width');
				$(this).chosen({width: w});
			});
			$(".lcweb-chosen-deselect").chosen({allow_single_deselect:true});
		}


		// fix for chosen overflow
		$('#wpbody').css('overflow', 'hidden');
	
		// fix for subcategories
		$('#ag_gall_categories-adder').remove();
	});
	</script>

    <?php
	return true;
}






///////////////////////////////////////////////////////
// SAVING METABOXES

function ag_gallery_meta_save($post_id) {
	if(isset($_POST['ag_gallery_noncename'])) {
		if (!wp_verify_nonce($_POST['ag_gallery_noncename'], __FILE__)) return $post_id;

		include_once(AG_DIR.'/functions.php');
		include_once(AG_DIR.'/classes/simple_form_validator.php');

		$validator = new simple_fv;
		$indexes = array();
		
		$to_save = array(
			'ag_type',
			'ag_username',
			'ag_psw',
			'ag_connect_id',
			
			'ag_wp_cat',
			'ag_cpt_tax',
			'ag_cpt_tax_term',
			'ag_album',
			'ag_fb_album',
			'ag_picasa_album',
			'ag_gdrive_album',
			'ag_onedrive_album',
			'ag_rml_folder',
			'ag_nag_gallery',
			
			'ag_layout',
			'ag_thumb_w',
			'ag_thumb_h',
			
			'ag_colnzd_thumb_max_w',
			'ag_colnzd_thumb_h',
			'ag_colnzd_thumb_h_type',
			
			'ag_masonry_cols',
			'ag_masonry_min_width',
			
			'ag_photostring_h',
			'ag_photostring_min_width',
			
			'ag_paginate',
			'ag_per_page',
			
			'ag_orderby',
		);
		foreach($to_save as $ts) {
			$indexes[] = array('index'=>$ts, 'label'=>'foo');	
		}
		


		$indexes[] = array('index'=>'ag_autopop', 'label'=>'Gallery auto population');
		$indexes[] = array('index'=>'ag_auto_author', 'label'=>'Catch authors');
		$indexes[] = array('index'=>'ag_auto_title', 'label'=>'Catch titles');
		$indexes[] = array('index'=>'ag_auto_descr', 'label'=>'Catch descriptions');
		$indexes[] = array('index'=>'ag_auto_link', 'label'=>'Auto link');
		$indexes[] = array('index'=>'ag_cache_interval', 'label'=>'Cache interval');
		$indexes[] = array('index'=>'ag_auto_random', 'label'=>'Random Catching');
		$indexes[] = array('index'=>'ag_max_images', 'label'=>'Max images in gallery');

		$indexes[] = array('index'=>'ag_item_img_src', 'label'=>'Item Image source');
		$indexes[] = array('index'=>'ag_item_thumb', 'label'=>'Item Thumb Center');
		$indexes[] = array('index'=>'ag_item_author', 'label'=>'Item Author');
		$indexes[] = array('index'=>'ag_item_title', 'label'=>'Item Title');
		$indexes[] = array('index'=>'ag_item_descr', 'label'=>'Item Description');
		$indexes[] = array('index'=>'ag_item_tags', 'label'=>'Item Tags');
		$indexes[] = array('index'=>'ag_link_opt', 'label'=>'Item Link option');
		$indexes[] = array('index'=>'ag_item_link', 'label'=>'Item Link source');

		$validator->formHandle($indexes);

		$fdata = $validator->form_val;
		$error = $validator->getErrors();

		// clean data
		foreach($fdata as $key=>$val) {
			if(!is_array($val)) {
				$fdata[$key] = stripslashes($val);
			}
			else {
				$fdata[$key] = array();
				foreach($val as $arr_val) {$fdata[$key][] = stripslashes($arr_val);}
			}
		}

		// gallery data array builder
		if(!$fdata['ag_item_img_src'] || !is_array($fdata['ag_item_img_src'])) {$fdata['ag_gallery'] = false;}
		else {
			$fdata['ag_gallery'] = array();

			for($a=0; $a < count($fdata['ag_item_img_src']); $a++) {
				if(!isset($fdata['ag_item_link'][$a]) || !$fdata['ag_item_link'][$a]) {$fdata['ag_link_opt'][$a] = 'none';}

				$fdata['ag_gallery'][] = array(
					'img_src'	=> $fdata['ag_item_img_src'][$a],
					'thumb' 	=> $fdata['ag_item_thumb'][$a],
					'author'	=> trim($fdata['ag_item_author'][$a]),
					'title'		=> trim($fdata['ag_item_title'][$a]),
					'descr'		=> trim($fdata['ag_item_descr'][$a]),
					'tags'		=> trim($fdata['ag_item_tags'][$a]),
					'link_opt'	=> $fdata['ag_link_opt'][$a],
					'link'		=> $fdata['ag_item_link'][$a]
				);
			}
		}

		$to_unset = array('ag_item_img_src', 'ag_item_thumb', 'ag_item_author', 'ag_item_title', 'ag_item_descr', 'ag_item_tags', 'ag_link_opt', 'ag_item_link');
		foreach($to_unset as $key) {
			if(isset($fdata[$key])) {unset($fdata[$key]);}
		}


		// save data
		foreach($fdata as $key => $val) {
			if($key == 'ag_gallery') {
				if(!$fdata['ag_autopop']) {
					ag_gall_data_save($post_id, $val);
				}
			} 
			else {
				update_post_meta($post_id, $key, $fdata[$key]);
			}
		}
	}

    return $post_id;
}
add_action('save_post','ag_gallery_meta_save');
