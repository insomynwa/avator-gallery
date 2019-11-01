<?php

///////////////////////////////////
// ADMIN IMPLEMENTATION


function ag_wp_gall_metabox() {
	// add a meta box for affected post types
	include_once(AG_DIR . '/functions.php');
	foreach(ag_affected_wp_gall_ct() as $type){
		add_meta_box('ag_wp_gall_settings', __('Avator Gallery', 'ag_ml'), 'ag_wp_gall_settings', $type, 'normal', 'default');
	}  
}
add_action('admin_init', 'ag_wp_gall_metabox');


// create metabox
function ag_wp_gall_settings() {
	include_once(AG_DIR . '/functions.php');
	global $post;
	
	$use_it 		= get_post_meta($post->ID, 'ag_affect_wp_gall', true);
	$show_as 		= get_post_meta($post->ID, 'ag_layout', true); if(!$show_as) {$show_as = 'default';}
	
	
	$thumb_w 		= get_post_meta($post->ID, 'ag_thumb_w', true);
	$thumb_h 		= get_post_meta($post->ID, 'ag_thumb_h', true);
	
	$colnzd_max_w 	= get_post_meta($post->ID, 'ag_colnzd_thumb_max_w', true);
	$colnzd_h 		= get_post_meta($post->ID, 'ag_colnzd_thumb_h', true);
	$colnzd_h_type 	= get_post_meta($post->ID, 'ag_colnzd_thumb_h_type', true);

	$masonry_cols 	= get_post_meta($post->ID, 'ag_masonry_cols', true);
	$masonry_min_w 	= get_post_meta($post->ID, 'ag_masonry_min_width', true);;
	
	$ps_height 		= get_post_meta($post->ID, 'ag_photostring_h', true);
	$ps_min_w 		= get_post_meta($post->ID, 'ag_photostring_min_width', true);
	
	$slider_autop 	= get_post_meta($post->ID, 'ag_slider_autoplay', true);
	
	$car_img_h		= get_post_meta($post->ID, 'ag_car_img_h', true);
	$car_cols		= get_post_meta($post->ID, 'ag_car_cols', true); if(!$car_cols) {$car_cols = 3;}
	$car_rows		= get_post_meta($post->ID, 'ag_car_rows', true); if(!$car_rows) {$car_rows = 1;}
	$car_nocrop 	= get_post_meta($post->ID, 'ag_car_nocrop', true);
	
	$itg_w			= (int)get_post_meta($post->ID, 'ag_itg_w', true); if(!$itg_w) {$itg_w = 100;}
	$itg_w_type 	= get_post_meta($post->ID, 'ag_itg_w_type', true);
	$itg_h			= (int)get_post_meta($post->ID, 'ag_itg_h', true); if(!$itg_h) {$itg_h = 75;}
	$itg_h_type 	= get_post_meta($post->ID, 'ag_itg_h_type', true);
	$itg_layout 	= get_post_meta($post->ID, 'ag_itg_layout', true);
	$itg_img_num 	= get_post_meta($post->ID, 'ag_itg_img_num', true);
	$itg_font_size 	= get_post_meta($post->ID, 'ag_itg_font_size', true);
	$itg_cust_txt	= get_post_meta($post->ID, 'ag_itg_cust_txt', true);
	
	$paginate 		= get_post_meta($post->ID, 'ag_paginate', true); if(!$paginate) {$paginate = 'default';}
	$per_page 		= get_post_meta($post->ID, 'ag_per_page', true);
	$pag_system 	= get_post_meta($post->ID, 'ag_pag_system', true);
	$custom_ol 		= get_post_meta($post->ID, 'ag_custom_overlay', true);
	
	
	// retrocompatibility
	if(get_post_meta($post->ID, 'ag_use_slider', true)) {$show_as = 'slider';}
	
	if($paginate == 'default') {
		$per_page = get_option('ag_img_per_page', 10);	
	}
	
	
	// switches
	$hide = 'style="display: none;"';
	$standard_show 	= ($show_as != 'standard') ? $hide : '';
	$colnzd_show 	= ($show_as != 'columnized') ? $hide : '';
	$masonry_show 	= ($show_as != 'masonry') ? $hide : '';
	$ps_show 		= ($show_as != 'string') ? $hide : '';
	
	$slider_show 	= ($show_as != 'slider') ? $hide : '';
	$carousel_show 	= ($show_as != 'carousel') ? $hide : '';
	$itg_show		= ($show_as != 'itg') ? $hide : '';
	
	$pag_show 		= (in_array($show_as, array('slider', 'carousel', 'itg'))) ? $hide : '';
	$per_page_show 	= ($paginate != '1') ? $hide : '';
	$agom_show 		= (in_array($show_as, array('slider', 'itg'))) ? $hide : '';
	
	
	// info icon
	$info_icon = '<span class="dashicons dashicons-info" title="'. esc_attr(__('Leave fields empty to use global setup', 'ag_ml')) .'" style="font-size: 16px; position: relative; left: 2px; top: 4px; color: #a5a5a5; cursor: help;"></span>';
	?>
    <div class="lcwp_mainbox_meta">
        <table class="widefat lcwp_table lcwp_metabox_table" style="margin-bottom: 10px;">
          <tr>
            <td class="lcwp_label_td"><?php _e("Use Avator Gallery with wordpress galleries in this page?", 'ag_ml'); ?></td>
            <td class="lcwp_field_td">
                <select data-placeholder="<?php _e('Select an option', 'ag_ml') ?> .." name="ag_affect_wp_gall" id="ag_affect_wp_gall" class="lcweb-chosen" autocomplete="off">
                  <option value="default"><?php _e('As default', 'ag_ml') ?></option>
                  <option value="1" <?php selected($use_it, '1') ?>><?php _e('Yes', 'ag_ml') ?></option>
                  <option value="0" <?php selected($use_it, '0') ?>><?php _e('No', 'ag_ml') ?></option>
                </select>
            </td>    
          </tr>
			
          <tbody id="ag_wp_gall_opts" class="lcwp_form" <?php if($use_it == '0') {echo $hide;} ?>>
            <tr><td class="lcwp_field_td" colspan="2">
                <div>
                    <label><?php _e('Display as', 'ag_ml') ?></label>
                    
                    <select data-placeholder="<?php _e('Select an option', 'ag_ml') ?> .." name="ag_layout" id="ag_show_as" class="lcweb-chosen" autocomplete="off">
                        <option value="default"><?php _e('Gallery', 'ag_ml') ?> - <?php _e('Default layout', 'ag_ml') ?></option>  
                        <option value="standard" <?php selected($show_as, 'standard') ?>><?php _e('Gallery', 'ag_ml') ?> - Standard layout</option> 
                        <option value="columnized" <?php selected($show_as, 'columnized') ?>><?php _e('Gallery', 'ag_ml') ?> - Columnized layout</option>   
                        <option value="masonry" <?php selected($show_as, 'masonry') ?>><?php _e('Gallery', 'ag_ml') ?> -  Masonry layout</option>
                        <option value="string" <?php selected($show_as, 'string') ?>><?php _e('Gallery', 'ag_ml') ?> -  PhotoString layout</option>  
                        
                        <option value="slider" <?php selected($show_as, 'slider') ?>><?php _e('Slider', 'ag_ml') ?></option> 
                        <option value="carousel" <?php selected($show_as, 'carousel') ?>><?php _e('Carousel', 'ag_ml') ?></option>   
                        <option value="itg" <?php selected($show_as, 'itg') ?>><?php _e('Image-to-gallery', 'ag_ml') ?></option>   
                    </select>
                </div>
                
                
                
                <div id="ag_tt_sizes" <?php echo $standard_show; ?>>
                    <label><?php _e('Images size', 'ag_ml') ?> <?php echo $info_icon ?></label>
                   
                    <input type="text" name="ag_thumb_w" value="<?php echo $thumb_w ?>" maxlength="4" style="width: 45px; margin-right: 3px; text-align: center;" /> x 
                    <input type="text" name="ag_thumb_h" value="<?php echo $thumb_h ?>" maxlength="4" style="width: 45px; margin-left: 3px; text-align: center;" /> px
                </div>    
                 
                
                
                <div class="ag_colnzd_fields" <?php echo $colnzd_show; ?>>
                    <label><?php _e('Maximum thumbnails width', 'ag_ml') ?> <?php echo $info_icon ?></label>
                    
                    <div class="lcwp_slider" step="20" max="2000" min="40"></div>
                    <input type="text" value="<?php echo $colnzd_max_w ?>" name="ag_colnzd_thumb_max_w" class="lcwp_slider_input" />
                    <span>px</span>
                </div>
                <div class="ag_colnzd_fields" <?php echo $colnzd_show; ?>>
                    <label><?php _e('Thumbnails height', 'ag_ml') ?> <?php echo $info_icon ?></label>
                    
                    <input type="number" name="ag_colnzd_thumb_h" value="<?php echo $colnzd_h ?>" maxlength="4" min="20" autocomplete="off" style="width: 65px; margin-right: 3px; text-align: center;" />
            
                    <select name="ag_colnzd_thumb_h_type" autocomplete="off" style="width: 50px; min-width: 0px; position: relative; top: -3px;">
                        <option value="px">px</option>
                        <option value="%" <?php if($colnzd_h_type == '%') {echo 'selected="selected"';} ?>>%</option>
                    </select>
                </div>
            
            
            
                <div class="ag_masonry_fields" <?php echo $masonry_show; ?>>
                    <label><?php _e('How many columns?', 'ag_ml') ?> <?php echo $info_icon ?></label>
                    
                    <div class="lcwp_slider" step="1" max="30" min="1"></div>
                    <input type="text" value="<?php echo $masonry_cols ?>" name="ag_masonry_cols" class="lcwp_slider_input" />
                    <span></span>
                </div>
                <div class="ag_masonry_fields" <?php echo $masonry_show; ?>>
                    <label><?php _e('Minimum images width', 'ag_ml') ?></label>
                    
                    <div class="lcwp_slider" step="20" max="2000" min="40"></div>
                    <input type="text" value="<?php echo $masonry_min_w ?>" name="ag_masonry_min_width" class="lcwp_slider_input" />
                    <span>px</span>
                </div>
                
                
                
                <div class="ag_ps_fields" <?php echo $ps_show; ?>>
                    <label><?php _e('Images height', 'ag_ml') ?> <?php echo $info_icon ?></label>
                    
                    <div class="lcwp_slider" step="5" max="500" min="20"></div>
                    <input type="text" value="<?php echo $ps_height ?>" name="ag_photostring_h" class="lcwp_slider_input" />
                    <span>px</span>
                </div>
                <div class="ag_ps_fields" <?php echo $ps_show; ?>>
                    <label><?php _e('Minimum thumbnails width', 'ag_ml') ?></label>
                    
                    <div class="lcwp_slider" step="20" max="700" min="20"></div>
                    <input type="text" value="<?php echo $ps_min_w ?>" name="ag_photostring_min_width" class="lcwp_slider_input" />
                    <span>px</span>
                </div>
                
                
                
                <div class="ag_slider_opts" <?php echo $slider_show; ?>>
                    <label><?php _e('Slider width', 'ag_ml') ?></label>
                    
                    <input type="text" name="ag_slider_w" value="<?php echo get_post_meta($post->ID, 'ag_slider_w', true) ?>" style="width: 50px; text-align: center;" maxlength="4" /> 
                    <select name="ag_slider_w_type" style="width: 50px; height: 28px; margin: -6px 0 0 -5px;" autocomplete="off">
                        <option value="%">%</option>
                        <option value="px" <?php if(get_post_meta($post->ID, 'ag_slider_w_type', true) == 'px') {echo 'selected="selected"';} ?>>px</option>
                    </select>
                </div>     
                <div class="ag_slider_opts" <?php echo $slider_show; ?>>
                    <label><?php _e('Slider height', 'ag_ml') ?></label>
                    
                    <input type="text" name="ag_slider_h" value="<?php echo get_post_meta($post->ID, 'ag_slider_h', true) ?>" style="width: 50px; text-align: center;" maxlength="4" /> 
                    <select name="ag_slider_h_type" style="width: 50px; height: 28px; margin: -6px 0 0 -5px;" autocomplete="off">
                        <option value="%">%</option>
                        <option value="px" <?php if(get_post_meta($post->ID, 'ag_slider_h_type', true) == 'px') {echo 'selected="selected"';} ?>>px</option>
                    </select>
                </div>
                <div class="ag_slider_opts" <?php echo $slider_show; ?>>
					<label><?php _e("Autoplay slideshow?", 'ag_ml'); ?></label> 
                    
                    <select data-placeholder="<?php _e('Select an option', 'ag_ml') ?> .." name="ag_slider_autoplay" class="lcweb-chosen" autocomplete="off">
                      <option value="auto"><?php _e('As default', 'ag_ml') ?></option>
                      <option value="1" <?php selected($slider_autop, '1') ?>><?php _e('Yes', 'ag_ml') ?></option>
                      <option value="0" <?php selected($slider_autop, '0') ?>><?php _e('No', 'ag_ml') ?></option>
                    </select>
                </div>
                
                
                
                <div class="ag_carousel_opts" <?php echo $carousel_show; ?>>
                    <label><?php _e('Images height', 'ag_ml') ?></label>
                    
                    <div class="lcwp_slider" step="5" max="500" min="20"></div>
                    <input type="text" value="<?php echo (int)$car_img_h ?>" name="ag_car_img_h" class="lcwp_slider_input" />
                    <span>px</span>
                </div>
                <div class="ag_carousel_opts" <?php echo $carousel_show; ?>>
                    <label><?php _e('Images per time', 'ag_ml') ?></label>
                    
                    <div class="lcwp_slider" step="1" max="10" min="1"></div>
                    <input type="text" value="<?php echo (int)$car_cols ?>" name="ag_car_cols" class="lcwp_slider_input" />
                    <span></span>
                </div>
                <div class="ag_carousel_opts" <?php echo $carousel_show; ?>>
                    <label><?php _e('Rows', 'ag_ml') ?></label>
                    
                    <div class="lcwp_slider" step="1" max="4" min="1"></div>
                    <input type="text" value="<?php echo (int)$car_rows ?>" name="ag_car_rows" class="lcwp_slider_input" />
                    <span></span>
                </div>
                <div class="ag_carousel_opts" <?php echo $carousel_show; ?>>
					<label><?php _e("Use multiscroll?", 'ag_ml'); ?></label> 
                    <?php  $sel = (get_post_meta($post->ID, 'ag_car_multiscroll', true) == 1) ? 'checked="checked"' : ''; ?>
                    <input type="checkbox" value="1" name="ag_car_multiscroll" class="ip-checkbox" <?php echo $sel; ?> />
                </div>   
                <div class="ag_carousel_opts" <?php echo $carousel_show; ?>>
					<label><?php _e("Center mode?", 'ag_ml'); ?></label> 
                    <?php  $sel = (get_post_meta($post->ID, 'ag_car_centermode', true) == 1) ? 'checked="checked"' : ''; ?>
                    <input type="checkbox" value="1" name="ag_car_centermode" class="ip-checkbox" <?php echo $sel; ?> />
                </div> 
                <div class="ag_carousel_opts" <?php echo $carousel_show; ?>>
					<label><?php _e("Avoid images crop?", 'ag_ml'); ?></label> 
                    <?php  $sel = (get_post_meta($post->ID, 'ag_car_nocrop', true) == 1) ? 'checked="checked"' : ''; ?>
                    <input type="checkbox" value="1" name="ag_car_nocrop" class="ip-checkbox" <?php echo $sel; ?> />
                </div>  
                              
                            
                
                <div class="ag_itg_opts" <?php echo $itg_show; ?>>
               		<label><?php _e("Box width", 'ag_ml') ?></label>
                    <input type="number" name="ag_itg_w" value="<?php echo $itg_w ?>" style="width: 70px; text-align: center;" maxlength="4" autocomplete="off" />
                    
                    <select name="ag_itg_w_type" style="width: 50px; min-width: 0px; position: relative; top: -3px;" autocomplete="off">
                    	<option value="%">%</option>
                        <option value="px" <?php selected($itg_w_type, 'px') ?>>px</option>
                    </select>
                </div>
                <div class="ag_itg_opts" <?php echo $itg_show; ?>>
                    <label><?php _e("Images height", 'ag_ml') ?> <em>(<?php _e('% is proportional to width', 'ag_ml') ?>)</em></label>
                    
                    <input type="number" name="ag_itg_h" value="75" style="width: 70px; text-align: center;" maxlength="4" autocomplete="off" />
                    
                    <select name="ag_itg_h_type" style="width: 50px; min-width: 0px; position: relative; top: -3px;" autocomplete="off">
                    	<option value="%">%</option>
                        <option value="px" <?php selected($itg_h_type, 'px') ?>>px</option>
                    </select>
                </div>
                <div class="ag_itg_opts" <?php echo $itg_show; ?>>
                    <label><?php _e("Layout", 'ag_ml') ?></label>

                    <select name="ag_itg_layout" class="lcweb-chosen" autocomplete="off">
                    	<option value="">(<?php _e('default one', 'ag_ml') ?>)</option>
                       	<?php
						include_once(AG_DIR .'/settings/field_options.php');
						foreach(ag_itg_layouts() as $key => $val) {
							echo '<option value="'. $key .'" '. selected($itg_layout, $key, false) .'>'. $val .'</option>';	
						}
						?>
                    </select>
                </div>
                <div class="ag_itg_opts" <?php echo $itg_show; ?>>
                    <label><?php _e("How many images to display?", 'ag_ml') ?> <span class="dashicons dashicons-info" title="<?php echo esc_attr(__('This will be ignored if chosen layout is "main image + two on sides"', 'ag_ml')) ?>" style="cursor: help; opacity: 0.3;"></span></label>

                    <select name="ag_itg_img_num" style="width: 50px; min-width: 0px;" autocomplete="off">
                    	<option value="1">1</option>
                        <option value="2" <?php selected($itg_img_num, '2') ?>>2</option>
                        <option value="4" <?php selected($itg_img_num, '4') ?>>4</option>
                    </select>
                </div>
                <div class="ag_itg_opts" <?php echo $itg_show; ?>>
					<label><?php _e("Custom font size", 'ag_ml') ?> <span class="dashicons dashicons-info" title="<?php echo esc_attr(__('Leave empty to use default one', 'ag_ml')) ?>" style="cursor: help; opacity: 0.3;"></span></label>

                     <input type="number" name="ag_itg_font_size" value="<?php echo $itg_font_size ?>" max="5" min="0.1" step="0.1" style="width: 70px; text-align: center;" maxlength="4" autocomplete="off" /> rem
                </div>   
                <div class="ag_itg_opts" <?php echo $itg_show; ?>>
					<label><?php _e("Custom overlay text", 'ag_ml') ?> <span class="dashicons dashicons-info" title="<?php echo esc_attr(__('Remember you can use placeholders and FontAwesome icons as explained in settings', 'ag_ml')) ?>" style="cursor: help; opacity: 0.3;"></span></label>
                    
                	<textarea name="ag_itg_cust_txt" style="width: 100%;"><?php echo $itg_cust_txt ?></textarea>
                </div>  
            </td>
            </tr>
            
            
            
            <tr id="ag_pagination"><td class="lcwp_field_td" colspan="2" <?php echo $pag_show ?>>
                <div>
                    <label><?php _e('Use pagination?', 'ag_ml') ?></label>
                    <select data-placeholder="<?php _e('Select an option', 'ag_ml') ?> .." name="ag_paginate" id="ag_paginate" autocomplete="off" class="lcweb-chosen">
                        <option value="default"><?php _e('As Default', 'ag_ml') ?></option>  
                        <option value="1" <?php selected($paginate, '1') ?>><?php _e('Yes', 'ag_ml') ?></option>  
                        <option value="0" <?php selected($paginate, '0') ?>><?php _e('No', 'ag_ml') ?></option>  
                    </select>
                </div>     
                <div id="ag_per_page" <?php echo $per_page_show; ?>>
                    <label><?php _e('Images per page', 'ag_ml') ?> <?php echo $info_icon ?></label>
                    
                    <div class="lcwp_slider" step="1" max="100" min="2"></div>
                    <input type="text" value="<?php echo $per_page ?>" name="ag_per_page" class="lcwp_slider_input" />
                    <span></span>
                </div>
            	<div>
                    <label><?php _e('Pagination System', 'ag_ml') ?></label>
                    <select data-placeholder="<?php _e('Select an option', 'ag_ml') ?> .." name="ag_pag_system" id="ag_pag_system" class="lcweb-chosen" autocomplete="off">
                        <option value=""><?php _e('auto - follow global settings', 'ag_ml') ?></option>
                        <option value="standard" <?php selected($pag_system, 'standard') ?>><?php _e('standard', 'ag_ml') ?></option>
                        <option value="inf_scroll" <?php selected($pag_system, 'inf_scroll') ?>><?php _e('infinite scroll', 'ag_ml') ?></option>
                    </select>
                </div>
            </td>
            </tr>


            <?php 
			///// OVERLAY MANAGER ADD-ON ///////////
			////////////////////////////////////////
			if(defined('AGOM_DIR')) : ?>
      		<tr id="ag_cust_ol" <?php echo $agom_show ?>>
            	<td class="lcwp_field_td" colspan="2">
                    <div>
                      <label><?php _e('Custom Overlay', 'ag_ml') ?></label>
                      <select data-placeholder="<?php _e('Select an overlay', 'ag_ml') ?> .." name="ag_custom_overlay" class="lcweb-chosen">
                          <option value="">(<?php _e('default one', 'ag_ml') ?>)</option>				
                          <?php
                          $overlays = get_terms('agom_overlays', 'hide_empty=0');
                          foreach($overlays as $ol) {
                                $sel = ($ol->term_id == $custom_ol) ? 'selected="selected"' : '';
                                echo '<option value="'.$ol->term_id.'" '.$sel.'>'.$ol->name.'</option>'; 
                          }
                          ?>
                      </select>
                    </div>
            	</td>
            </tr> 
            <?php endif; ?>
            
            
            <tr>
              <td colspan="2">
			  	<div>
					<label><?php _e("Use watermark?", 'ag_ml'); ?></label> 
                    <?php  $sel = (get_post_meta($post->ID, 'ag_watermark', true) == 1) ? 'checked="checked"' : ''; ?>
                    <input type="checkbox" value="1" name="ag_watermark" class="ip-checkbox" <?php echo $sel; ?> />
                </div>    
              </td>   
            </tr>
          </tbody>
        </table>  
        
        <input type="hidden" name="lcwp_nonce" value="<?php echo wp_create_nonce('lcwp') ?>" />
    </div>
    
    <?php // SCRIPTS ?>
    <script src="<?php echo AG_URL; ?>/js/functions.js" type="text/javascript"></script>
    <script src="<?php echo AG_URL; ?>/js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo AG_URL; ?>/js/lc-switch/lc_switch.min.js" type="text/javascript"></script>
    
    <script type="text/javascript">
	jQuery(document).ready(function($) {
		// settings toggle
		jQuery(document).delegate('#ag_affect_wp_gall', 'change', function() {
			var use_it = jQuery(this).val();
			
			if(use_it != '0') { jQuery('#ag_wp_gall_opts').slideDown(); }
			else { jQuery('#ag_wp_gall_opts').slideUp(); }		
		});
		
		jQuery(document).delegate('#ag_show_as', 'change', function() {
			var sa = jQuery(this).val();
			jQuery('#ag_tt_sizes, .ag_colnzd_fields, .ag_masonry_fields, .ag_ps_fields, .ag_slider_opts, .ag_carousel_opts').hide();
			
			if(sa == 'standard') {
				jQuery('#ag_tt_sizes').show();
			}
			else if (sa == 'columnized') {
				jQuery('.ag_colnzd_fields').show();
			}
			else if (sa == 'masonry') {
				jQuery('.ag_masonry_fields').show();
			}
			else if (sa == 'string') {
				jQuery('.ag_ps_fields').show();
			}
			else if (sa == 'slider') {
				jQuery('.ag_slider_opts').show();
			}
			else if (sa == 'carousel') {
				jQuery('.ag_carousel_opts').show();
			}
			else if (sa == 'itg') {
				jQuery('.ag_itg_opts').show();
			}
			
			
			if (sa == 'slider' || sa == 'carousel' || sa == 'itg') {
				jQuery('#ag_pagination').hide();
			} else {
				jQuery('#ag_pagination').show();	
			}
			
			
			if (sa == 'slider' || sa == 'itg') {
				jQuery('#ag_cust_ol').hide();
			} else {
				jQuery('#ag_cust_ol').show();	
			}
		});
		
		jQuery(document).delegate('#ag_paginate', 'change', function() {
			var paginate = jQuery(this).val();
			
			if(paginate == '1') { jQuery('#ag_per_page').show(); }
			else { jQuery('#ag_per_page').hide(); }		
		});
	});
	</script>
    <?php
}


// save metabox
function ag_wp_gall_meta_save($post_id) {
	if(isset($_POST['ag_affect_wp_gall'])) {
		// authentication checks
		if (!wp_verify_nonce($_POST['lcwp_nonce'], 'lcwp')) return $post_id;

		include_once(AG_DIR.'/functions.php');
		include_once(AG_DIR.'/classes/simple_form_validator.php');
				
		$validator = new simple_fv;
		$indexes = array();
		
		$indexes[] = array('index'=>'ag_affect_wp_gall', 'label'=>'Affect WP galleries');
		
		
		$to_save = array(
			'ag_affect_wp_gall',
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
		);
		foreach($to_save as $ts) {
			$indexes[] = array('index'=>$ts, 'label'=>'foo');	
		}
		
		$indexes[] = array('index'=>'ag_pag_system', 'label'=>'pagination system');
		
		$indexes[] = array('index'=>'ag_slider_w', 'label'=>'slider width');
		$indexes[] = array('index'=>'ag_slider_w_type', 'label'=>'slider width type');
		$indexes[] = array('index'=>'ag_slider_h', 'label'=>'slider height');
		$indexes[] = array('index'=>'ag_slider_h_type', 'label'=>'slider height type');
		$indexes[] = array('index'=>'ag_slider_autoplay', 'label'=>'autoplay slider');
		
		$indexes[] = array('index'=>'ag_car_img_h', 'label'=>'carousel image height');
		$indexes[] = array('index'=>'ag_car_cols', 'label'=>'carousel columns');
		$indexes[] = array('index'=>'ag_car_rows', 'label'=>'carousel rows');
		$indexes[] = array('index'=>'ag_car_multiscroll', 'label'=>'carousel multiscroll');
		$indexes[] = array('index'=>'ag_car_centermode', 'label'=>'carousel center mode');
		$indexes[] = array('index'=>'ag_car_nocrop', 'label'=>'no images crop mode');
		
		$indexes[] = array('index'=>'ag_itg_w', 'label'=>'');
		$indexes[] = array('index'=>'ag_itg_w_type', 'label'=>'');
		$indexes[] = array('index'=>'ag_itg_h', 'label'=>'');
		$indexes[] = array('index'=>'ag_itg_h_type', 'label'=>'');
		$indexes[] = array('index'=>'ag_itg_layout', 'label'=>'');
		$indexes[] = array('index'=>'ag_itg_img_num', 'label'=>'');
		$indexes[] = array('index'=>'ag_itg_font_size', 'label'=>'');
		$indexes[] = array('index'=>'ag_itg_cust_txt', 'label'=>'');
		
		$indexes[] = array('index'=>'ag_watermark', 'label'=>'use watermark');
		$indexes[] = array('index'=>'ag_custom_overlay', 'label'=>'custom overlay');
		
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

		// save data
		foreach($fdata as $key=>$val) {
			update_post_meta($post_id, $key, $fdata[$key]); 
		}
		
		// be sure old meta is deleted
		delete_post_meta($post_id, 'ag_use_slider');
	}

    return $post_id;
}
add_action('save_post','ag_wp_gall_meta_save');



/**************************************************************/




///////////////////////////////////
// FRONTEND IMPLEMENTATION

function ag_wp_gallery_manag_frontend($foo, $atts) {
	include_once(AG_DIR . '/functions.php');
	global $post;
	
	extract( shortcode_atts( array(
		'ids' => '',
		'orderby' => ''
	), $atts ) );
	
	$use_it 	= ag_check_default_val($post->ID, 'ag_affect_wp_gall', false);
	$random 	= ($orderby == 'rand') ? '1' : 0; 
	$wm 		= (get_post_meta($post->ID, 'ag_watermark', true)) ? '1' : '0';

	if($use_it && !empty($ids)) {
		ag_wp_gall_images($post->ID, $ids); // get and cache
		$layout = get_post_meta($post->ID, 'ag_layout', true);
		
		
		// slider
		if($layout == 'slider') {
			$w = (int)get_post_meta($post->ID, 'ag_slider_w', true) . get_post_meta($post->ID, 'ag_slider_w_type', true);
			$h = (int)get_post_meta($post->ID, 'ag_slider_h', true) . get_post_meta($post->ID, 'ag_slider_h_type', true);
			$autop = get_post_meta($post->ID, 'ag_slider_autoplay', true);
			
			$code = do_shortcode('[g-slider gid="'.$post->ID.'" width="'.$w.'" height="'.$h.'" random="'.$random.'" autoplay="'.$autop.'" watermark="'.$wm.'" wp_gall_hash="'.'-'.md5($ids).'"]');	
		}
		
		
		// carousel
		else if($layout == 'carousel') {
			$h = get_post_meta($post->ID, 'ag_car_img_h', true);
			$cols = get_post_meta($post->ID, 'ag_car_cols', true);
			$rows = get_post_meta($post->ID, 'ag_car_rows', true);
			$ms = get_post_meta($post->ID, 'ag_car_multiscroll', true);
			$center = get_post_meta($post->ID, 'ag_car_centermode', true);
			$nocrop = (int)get_post_meta($post->ID, 'ag_car_nocrop', true);

			$code = do_shortcode('[g-carousel gid="'.$post->ID.'" height="'.$h.'" per_time="'.$cols.'" rows="'.$rows.'" multiscroll="'.$ms.'" center="'.$center.'" 
									nocrop="'.$nocrop.'" random="'.$random.'" watermark="'.$wm.'" wp_gall_hash="'.'-'.md5($ids).'"]');	
		}
		
		
		// image-to-gallery
		else if($layout == 'itg') {
			$itg_w			= (int)get_post_meta($post->ID, 'ag_itg_w', true); if(!$itg_w) {$itg_w = 100;}
			$itg_w_type 	= get_post_meta($post->ID, 'ag_itg_w_type', true);
			$itg_h			= (int)get_post_meta($post->ID, 'ag_itg_h', true); if(!$itg_h) {$itg_h = 75;}
			$itg_h_type 	= get_post_meta($post->ID, 'ag_itg_h_type', true);
			$itg_layout 	= get_post_meta($post->ID, 'ag_itg_layout', true);
			$itg_img_num 	= get_post_meta($post->ID, 'ag_itg_img_num', true);
			$itg_font_size 	= get_post_meta($post->ID, 'ag_itg_font_size', true);
			$itg_cust_txt	= get_post_meta($post->ID, 'ag_itg_cust_txt', true);

			$code = do_shortcode('[g-itg gid="'.$post->ID.'" width="'.$itg_w.$itg_w_type.'" img_h="'.$itg_h.$itg_h_type.'" layout="'.$itg_layout.'" img_num="'.$itg_img_num.'" 
									font_size="'.$itg_font_size.'" random="'.$random.'" watermark="'.$wm.'" wp_gall_hash="'.'-'.md5($ids).'"]'. $itg_cust_txt .'[/g-itg]');	
		}
		
		
		// gallery
		else {
			$pag_system = get_post_meta($post->ID, 'ag_pag_system', true);
			$overlay = (defined('AGOM_DIR')) ? get_post_meta($post->ID, 'ag_custom_overlay', true) : '';
			
			$code = do_shortcode('[g-gallery gid="'.$post->ID.'" random="'.$random.'" watermark="'.$wm.'" pagination="'.$pag_system.'" overlay="'.$overlay.'" wp_gall_hash="'.'-'.md5($ids).'"]');
		}
		
		return $code;
	}
	else {return '';}
}
add_filter('post_gallery', 'ag_wp_gallery_manag_frontend', 999, 2);



// Wordpress gallery images - get and cache
function ag_wp_gall_images($post_id, $img_list, $use_captions = false) {
	$new_gall_hash = '-'.md5($img_list); 
	$cached_list = get_post_meta($post_id, 'ag_new_wp_gall_img_list'.$new_gall_hash, true); 
	
	// if equal to the cached - do anything
	if($img_list == $cached_list) {return true;}
	
	
	// otherwise fetch everything and compose the gallery array
	else {
		$args = array(
			'post_type' => 'attachment', 
			'post_mime_type' =>'image', 
			'post_status' => 'inherit', 
			'posts_per_page' => -1,
			'orderby' => 'post__in',
			'post__in' => explode(',', $img_list)
		);
		$query = new WP_query($args);

		$images = array();
		foreach($query->posts as $image) {
			if(trim($image->guid) != '') {
				$images[] = array(
					'img_src'	=> $image->ID,
					'thumb' 	=> 'c',
					'author'	=> '',  
					'title'		=> $image->post_title,
					'descr'		=> $image->post_content,
					'link_opt'	=> '', 
					'link'		=> ''
				);
			}
		} 
	
		ag_gall_data_save($post_id, $images, $autopop = false, $new_gall_hash);
		update_post_meta($post_id, 'ag_new_wp_gall_img_list'.$new_gall_hash, $img_list); 
	}
	
	return true;
}





// WP 5 - Gutenberg needs contents to be scanned in order to create a workaround
function ag_on_guten_gallery($contents) {
	include_once(AG_DIR.'/functions.php');	
		
	// only for WP5 >		
	if( (float)substr(get_bloginfo('version'), 0, 3) < 5.0 || !has_blocks()) {
		return $contents;	
	}
	
	// is this post affected?
	global $post;
	if(!ag_check_default_val($post->ID, 'ag_affect_wp_gall', false)) {
		return $contents;		
	}
	
	
	$blocks = parse_blocks($contents); 
	if(!is_array($blocks)) {
		return $contents;	
	}
	

	foreach($blocks as $block) {
		if($block['blockName'] != 'core/gallery') {continue;}
		
		// replace box text with the shortcode
		$sc = '[gallery ids="'. implode(',', $block['attrs']['ids']) .'"]';
		$contents = str_replace($block['innerContent'][0], $sc, $contents);	
	}

	return $contents;
}
add_filter('the_content', 'ag_on_guten_gallery', 1);




