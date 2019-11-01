<?php 

// width/height values with 2 distinct keys - to avoid excessive codes changes maintaining retrocompatibility 
function ag_w_h_fields($field_id, $field, $value, $all_vals) {
	
	switch($field_id) {
		case 'ag_st_sizes' : 
			$f_names = array('ag_slider_thumb_w', 'ag_slider_thumb_h'); 
			$vals = array(
				get_option('ag_slider_thumb_w', 60),
				get_option('ag_slider_thumb_h', 40),
			);
			break;	
		
		case 'ag_slayout_sizes' : 
			$f_names = array('ag_thumb_w', 'ag_thumb_h'); 
			$vals = array(
				get_option('ag_thumb_w', 220),
				get_option('ag_thumb_h', 280),
			);
			break;	
			
		case 'ag_lb_thumb_sizes' : 
			$f_names = array('ag_lb_thumb_w', 'ag_lb_thumb_h'); 
			$vals = array(
				get_option('ag_lb_thumb_w', 100),
				get_option('ag_lb_thumb_h', 100),
			);
			break;	
	}
	
	?>
	<tr class="<?php echo $field_id ?>">
    	<td class="lcwp_sf_label">
        	<label><?php echo $field['label'] ?></label>
        </td>
        <td class="lcwp_sf_field">
        	<input name="<?php echo $f_names[0] ?>" value="<?php echo $vals[0] ?>" maxlength="3" style="text-align: center; width: 54px; display: inline-block;" autocomplete="off" type="text" />
            <input name="<?php echo $f_names[1] ?>" value="<?php echo $vals[1] ?>" maxlength="3" style="text-align: center; width: 54px; display: inline-block;" autocomplete="off" type="text" />
            <span>px</span>
		</td>
		<td>
        	<span class="lcwp_sf_note"><?php echo $field['note'] ?></span>
        </td>
    </tr>
    <?php
}





// Easy WP thumbs - status
function ag_ewpt_status($field_id, $field, $value, $all_vals) {
	?>
	<table id="ag_ewpt_status_wrap" class="widefat lcwp_settings_table">
		<tr class="mg_<?php echo $field_id ?>">
			<td>
            	<input type="hidden" name="ewpt_status_f" value="" /> <?php //JS vis trick ?>
				<?php ewpt_wpf_form(); ?>
			</td>
		</tr>	
	</table>
    
    <script type="text/javascript">
    jQuery(document).ready(function() {
		jQuery(document).on('lcs-statuschange', 'input[name=ag_use_timthumb]', function(e) { 	
			(jQuery(this).is(':checked')) ? jQuery('#mg_ewpt_status_wrap').hide() : jQuery('#mg_ewpt_status_wrap').show();
		});
            
		// trigger on page's opening
		jQuery('input[name=ag_use_timthumb]').trigger('change').trigger('lcs-statuschange');
	});
	</script>
    <?php
}





// collections thumbs height (value + measure)
function ag_size_type_fields($field_id, $field, $value, $all_vals) {
	
	switch($field_id) {
		
		case 'ag_colnzd_thumbs_h_f' : 
			$f_names = array('ag_colnzd_thumb_h', 'ag_colnzd_thumb_h_type'); 
			$vals = array(
				get_option('ag_colnzd_thumb_h', 140),
				get_option('ag_colnzd_thumb_h_type', 'px'),
			);
			break;	
		
		case 'ag_coll_thumbs_h_f' : 
			$f_names = array('ag_coll_thumb_h', 'ag_coll_thumb_h_type'); 
			$vals = array(
				get_option('ag_coll_thumb_h', 140),
				get_option('ag_coll_thumb_h_type', 'px'),
			);
			break;	
			
		case 'ag_wm_margin_f' : 
			$f_names = array('ag_wm_margin', 'ag_wm_margin_type'); 
			$vals = array(
				get_option('ag_wm_margin', 10),
				get_option('ag_wm_margin_type', '%'),
			);
			break;			
	}
	
	?>
	<tr class="<?php echo $field_id ?>">
    	<td class="lcwp_sf_label">
        	<label><?php echo $field['label'] ?></label>
        </td>
        <td class="lcwp_sf_field">
        	<input name="<?php echo $f_names[0] ?>" value="<?php echo $vals[0] ?>" maxlength="3" style="text-align: center; width: 54px; display: inline-block;" autocomplete="off" type="text" />
            <select name="<?php echo $f_names[1] ?>" style="width: 50px; position: relative; top: -3px;" autocomplete="off">
            	<option value="px">px</option>
                <option value="%" <?php if($vals[1] == '%') {echo 'selected="selected"';} ?>>%</option>
            </select>
		</td>
		<td>
        	<span class="lcwp_sf_note"><?php echo $field['note'] ?></span>
        </td>
    </tr>
    <?php
}






// preset styles preview and setter 
function ag_preset_styles($field_id, $field, $value, $all_vals) {
	
	// build code
	echo '
	<table id="ag_preset_styles_cmd_wrap" class="widefat lcwp_settings_table">
		<tr class="mg_'. $field_id .'">
			<td class="lcwp_sf_label"><label>'. __('Choose style', 'ag_ml') .'</label></td>
			<td class="lcwp_sf_field">
				<select name="'. $field_id .'" id="ag_pred_styles" class="lcwp_sf_chosen ag_pred_styles_cf_select" autocomplete="off">
					<option value=""></option>';
				
					foreach(ag_preset_style_names() as $id => $name) {
						echo '<option value="'.$id.'">'.$name.'</option>';	
					}
		echo '
				</select>   
			</td>
			<td style="width: 50px;">
				<input name="mg_set_style" id="ag_set_style" value="'. __('Set', 'ag_ml') .'" class="button-secondary" type="button" />
			</td>
			<td><p class="lcwp_sf_note">'. __('Overrides styling options and applies preset styles', 'ag_ml') .'. '. __('Once applied, <strong>page will be reloaded</strong> with changed options', 'ag_ml') .'</p></td>
		</tr>
		<tr style="display: none;">
			<td class="lcwp_sf_label"><label>'. __('Preview', 'ag_ml') .'</label></td>
			<td colspan="3" id="ag_preset_styles_preview"></td>
		</tr>
	</table>';
	
	?>
    <script type="text/javascript">
    jQuery(document).ready(function(e) {
		
		// predefined style - preview toggle
		jQuery(document).delegate('#ag_pred_styles', "change", function() {
			var sel = jQuery('#ag_pred_styles').val();
			
			if(!sel) {
				jQuery('#ag_preset_styles_preview').empty();	
			}
			else {
				jQuery('#ag_preset_styles_cmd_wrap tr').last().show();
				
				var img_url = '<?php echo AG_URL ?>/img/pred_styles_demo/'+ sel +'.jpg';
				jQuery('#ag_preset_styles_preview').html('<img src="'+ img_url +'" />');		
			}
		});
		
		
		// set predefined style 
		jQuery(document).delegate('#ag_set_style', 'click', function() {
			var sel_style = jQuery('#ag_pred_styles').val();
			if(!sel_style) {return false;}
			
			if(confirm('<?php _e('This will overwrite your current settings, continue?', 'ag_ml') ?>')) {
				jQuery(this).replaceWith('<div style="width: 30px; height: 30px;" class="lcwp_loading"></div>');
				
				var data = {
					action: 'ag_set_predefined_style',
					style: sel_style,
					lcwp_nonce: '<?php echo wp_create_nonce('lcwp_nonce') ?>'
				};
				jQuery.post(ajaxurl, data, function(response) {
					if(jQuery.trim(response) == 'success') {
						jQuery('#lc_toast_mess').empty().html('<div class="lc_success"><p><?php echo esc_attr( __('Style successfully applied!', 'ag_ml')) ?></p><span></span></div>');	
						jQuery('#lc_toast_mess').addClass('lc_tm_shown');
						
						setTimeout(function() {
							window.location.reload();	
						}, 1500);
					}
					else {
						alert(response);	
					}
				});	
			}
		});
    });
    </script>
    <?php
}





// Lightbox overlay pattern
function ag_lb_ol_pattern_f($field_id, $field, $value, $all_vals) {
	$no_pattern_sel = (!$value || $value == 'none') ? 'ag_pattern_sel' : '';
	
	echo '
	<tr class="ag_'. $field_id .'">
		<td class="lcwp_sf_label"><label>'. __("Overlay's pattern", 'mg_ml') .'</label></td>
		<td class="lcwp_sf_field" colspan="2" style="padding-bottom: 0;">
			<input type="hidden" value="'. $value .'" name="ag_lb_ol_pattern" id="ag_lb_ol_pattern" />
			
			<div class="ag_setting_pattern '.$no_pattern_sel.'" rel="none"> no pattern </div>';
			
			foreach(ag_lcl_patterns_list() as $pattern) {
				$sel = ($value == $pattern) ? 'ag_pattern_sel' : '';  
				echo '<div class="ag_setting_pattern '.$sel.'" rel="'.$pattern.'" style="background: url('.AG_URL.'/js/lightboxes/lc-lightbox/img/patterns/'.$pattern.'.png) repeat top left transparent;"></div>';		
			}
	
	echo '
		</td>
	</tr>';
	
	?>
	<script type="text/javascript">
    jQuery(document).ready(function() {
		jQuery('body').delegate('.ag_setting_pattern', 'click', function() { // select a pattern
			jQuery('.ag_setting_pattern').removeClass('ag_pattern_sel');
			jQuery(this).addClass('ag_pattern_sel'); 
			
			jQuery('#ag_lb_ol_pattern').val( jQuery(this).attr('rel') );
		});
	});
	</script>
    <?php	
}






// upper watermark block (JS is in lower part)
function ag_u_wm_fields($field_id, $field, $value, $all_vals) {
	
	if(!isset($all_vals['ag_watermark_pos']) || empty($all_vals['ag_watermark_pos'])) {$all_vals['ag_watermark_pos'] = 'MM';}
	?>
    
    <table class="widefat lcwp_settings_table mg_settings_block">
      <tbody>
		<tr class="ag_wm_url">
        	<td class="lcwp_sf_label">
            	<label><?php _e('Watermark image', 'ag_ml') ?></label>
            </td> 
  			<td class="lcwp_sf_field" colspan="2">
                <div style="display: inline-block; width: 450px; vertical-align: top; margin: 15px 0;">
                
                    <input name="ag_watermark_img" id="ag_watermark_img" value="<?php echo esc_attr($all_vals['ag_watermark_img']) ?>" placeholder="<?php echo esc_attr( __("use wizard or paste image's URL", 'ag_ml')) ?>" style="margin-bottom: 10px;" autocomplete="off" type="text" />
                    <br/>
                    <input value="<?php echo esc_attr( __("Select image or upload a new one", 'ag_ml')) ?>" id="ag_wm_media_manag" class="button-secondary" style="width: 84%;" type="button" />	
                </div>

            	<div class="lcwp_upload_imgwrap" style="display: inline-block; bottom: -8px;">
				  <?php
                  if( !empty($all_vals['ag_watermark_img']) && preg_match( '/(^.*\.jpg|jpeg|png|gif*)/i', strtolower($all_vals['ag_watermark_img']))) {
					  echo '
					  <img src="'. esc_attr($all_vals['ag_watermark_img']) .'" />
					  <span class="lcwp_del_ul_img" title="'. esc_attr( __('remove image', 'ag_ml')) .'"></span>';
				  }
				  else {echo '<div class="no_image"></div>';}
                  ?>
            	</div>      
            </td>
		</tr>     
        <tr class="ag_wm_pos">
        	<td class="lcwp_sf_label">
            	<label><?php _e('Watermark position', 'ag_ml') ?></label>
            </td> 
  			<td class="lcwp_sf_field" colspan="2">
            	<input value="<?php echo $all_vals['ag_watermark_pos'] ?>" name="ag_watermark_pos" id="ag_watermark_pos" type="hidden">
                
                <table class="ag_sel_thumb_center">
					<tr>
                        <td id="ag_LT"></td>
                        <td id="ag_MT"></td>
                        <td id="ag_RT"></td>
                    </tr>
                    <tr>
                        <td id="ag_LM"></td>
                        <td id="ag_MM" class="thumb_center"></td>
                        <td id="ag_RM"></td>
                    </tr>
                    <tr>
                        <td id="ag_LB"></td>
                        <td id="ag_MB"></td>
                        <td id="ag_RB"></td>
                    </tr>
                </table>
            
            </td>
      	</tr>
    <?php
}


// lower watermark block
function ag_l_wm_fields($field_id, $field, $value, $all_vals) {
	
		/*
		 <tr>
        	<td class="lcwp_sf_label">
            	<label><?php _e('Create complete watermark cache', 'ag_ml') ?><br/>
                <small>(<?php _e('be sure settings are saved before using this', 'ag_ml') ?>)</small></label>
            </td>
            <td class="lcwp_sf_field">
				<input value="<?php echo esc_attr( __('Create', 'ag_ml')) ?>" id="ag_create_cache" class="button-secondary" style="width: auto;" type="button" />
			</td>
			<td>
            	<span class="ag_wm_create_status info" style="color: #222;"></span>
            </td>
        </tr>*/
		?>
        
        <tr>
        	<td class="lcwp_sf_label">
            	<label><?php _e('Clean watermark cache', 'ag_ml') ?></label>
            </td>
            <td class="lcwp_sf_field">
				<input value="<?php echo esc_attr( __('Clean', 'ag_ml')) ?>" id="ag_clean_cache" class="button-secondary" style="width: auto;" type="button">
			</td>
			<td>
            	<span class="ag_wm_clean_status info" style="display: block; color: #222;"></span>
            </td>
        </tr>
        
      </tbody>
    </table>
    
     <script type="text/javascript">
	jQuery(document).ready(function($) {
		
		// watermark - media image  manager 
		var file_frame = false;
		
		jQuery(document).delegate('#ag_wm_media_manag', 'click', function(e) {
			
			// If the media frame already exists, reopen it.
			if(file_frame){
			  file_frame.open();
			  return;
			}
		
			// Create the media frame
			file_frame = wp.media.frames.file_frame = wp.media({
			  title: "<?php _e('Avator Gallery - watermark selection', 'ag_ml') ?>",
			  button: {
				text: "<?php _e('Select') ?>",
			  },
			  library : {type : 'image'},
			  multiple: false
			});
		
			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				var img_data = file_frame.state().get('selection').first().toJSON();
				
				jQuery('#ag_watermark_img').val(img_data.url);
				jQuery('.lcwp_upload_imgwrap').html('<img src="'+ img_data.url +'" /><span class="lcwp_del_ul_img" title="remove image"></span>');
			});
	
			file_frame.open();
		});
		
		
		// watermark removal
		jQuery(document).delegate('.lcwp_del_ul_img', 'click', function() {
			jQuery('#ag_watermark_img').val('');
			jQuery('.lcwp_upload_imgwrap').html('<div class="no_image"></div>');
		});
		
	
	
		// create watermark cache
		jQuery('body').delegate('#ag_create_cache', 'click', function() {
			var wm_img = '<?php echo $all_vals['ag_watermark_img'] ?>';
			
			if(wm_img == '') { 
				alert("<?php _e("Watermark image hasn't been set", 'ag_ml' ) ?>");
				return false;
			}
			else {
				if(!confirm("<?php echo addslashes(__("ATTENTION: this affects every image in existing galleries and might slow down your server for a while. Continue?", 'ag_ml')) ?>")) {
					return false;	
				}
				
				jQuery('.ag_wm_create_status').html('<div style="width: 30px; height: 30px;" class="lcwp_loading"></div>\
				<small style="padding-left: 15px;">(<?php _e('might take very long having many images to manage', 'ag_ml' ) ?>)</small>');
				
				var data = {action: 'ag_create_wm_cache'};
				jQuery.post(ajaxurl, data, function(response) {
					var resp = jQuery.trim(response);
					
					if(resp == 'success') { jQuery('.ag_wm_create_status').html('<?php _e('Cache succesfully created', 'ag_ml' ) ?>!'); }
					else {
						if(resp.indexOf("Maximum execution") != -1) {
							jQuery('.ag_wm_create_status').html('<?php _e('Process took too much time for your server. Try creating the cache again', 'ag_ml' ) ?>'); 
						}
						else if(resp.indexOf("bytes exhausted") != -1) {
							jQuery('.ag_wm_create_status').html('<?php _e('The process requires too much memory for your server. Try applying it to smaller images', 'ag_ml' ) ?>'); 	
						}
						else {
							jQuery('.ag_wm_create_status').html('<?php _e('Error during cache creation', 'ag_ml' ) ?>'); 
						}
					}
				});	
			}
		});
		
		// clean watermark cache
		jQuery('body').delegate('#ag_clean_cache', 'click', function() {
			if( confirm("<?php echo addslashes(__("Every cached image will be deleted. Continue?", 'ag_ml')) ?>") ) {
				jQuery('.ag_wm_clean_status').html('<div style="width: 30px; height: 30px;" class="lcwp_loading"></div>');
				
				var data = {action: 'ag_clean_wm_cache'};
				jQuery.post(ajaxurl, data, function(response) {
					var resp = jQuery.trim(response);
					
					if(resp == 'success') { jQuery('.ag_wm_clean_status').html('<?php _e('Cache cleaned succesfully', 'ag_ml' ) ?>!'); }
					else { jQuery('.ag_wm_clean_status').html('<?php _e('Error during the cache deletion', 'ag_ml' ) ?>'); }
				});	
			}
		});
		
		// set watermark position
		function ag_watermark_position(position) {
			jQuery('.ag_sel_thumb_center td').removeClass('thumb_center');
			jQuery('.ag_sel_thumb_center #ag_'+position).addClass('thumb_center');
			
			jQuery('#ag_watermark_pos').val(position);	
		}
		ag_watermark_position( jQuery('#ag_watermark_pos').val() );
		
		jQuery('body').delegate('.ag_sel_thumb_center td', 'click', function() {
			var new_position = jQuery(this).attr('id').substr(3);
			ag_watermark_position(new_position);
		}); 
	});
	</script>
    <?php
}



