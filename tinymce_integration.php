<?php
// IMPLEMENTING TINYMCE LIGHTBOX
	
function ag_action_admin_init() {
	if( !current_user_can('edit_posts') && !current_user_can('edit_pages') )
		return;

	if(get_user_option('rich_editing') == 'true') {
		add_filter('mce_external_plugins', 'ag_filter_mce_plugin');
		add_filter('mce_buttons', 'ag_filter_mce_button');
	}
}
add_action('admin_init', 'ag_action_admin_init');

	
function ag_filter_mce_button( $buttons ) {
	array_push( $buttons, 'ag_btn');
	return $buttons;
}

function ag_filter_mce_plugin( $plugins ) {
	$plugins['agallery'] = AG_URL . '/js/tinymce_btn.js';
	return $plugins;
}




function ag_editor_btn_content() {
	if(strpos($_SERVER['REQUEST_URI'], 'post.php') === false && strpos($_SERVER['REQUEST_URI'], 'post-new.php') === false && !isset($GLOBALS['ag_tinymce_editor'])) {
		return false;
	}


	// get galleries
	$args = array(
		'post_type' => 'ag_galleries',
		'numberposts' => -1,
		'post_status' => 'publish',
		'fields' => 'ids'
	);
	
	$gall_ids = get_posts( $args );
	$galleries = array();
	
	foreach($gall_ids as $id) {
		$galleries[ $id ] = get_the_title($id);	
	}
	
	
	// get collections
	$collections = get_terms('ag_collections', 'hide_empty=0');
	
	
	////////////////////////////////////////////////////////////
	// OVERLAY MANAGER ADD-ON - variable containing dropdown
	if(defined('AGOM_DIR')) {
		$agom_block = '
		<li class="lcwp_scw_field ag_scw_field">
			<label>'. __('Custom Overlay', 'ag_ml') .'</label>
		  
			<select data-placeholder="'. __('Select an overlay', 'ag_ml') .'.." name="ag_custom_overlay" class="lcweb-chosen" autocomplete="off">
				<option value="">('. __('default one', 'ag_ml') .')</option>';
		
			   $overlays = get_terms('agom_overlays', 'hide_empty=0&orderby=name');
			   foreach($overlays as $ol) {
				  $agom_block .= '<option value="'.$ol->term_id.'">'.$ol->name.'</option>'; 
			   }
		
		$agom_block .= '
			</select>
		</li>';  
	}
	else {$agom_block = '';}
	////////////////////////////////////////////////////////////
?>


	<div id="agallery_sc_wizard" style="display:none;">
    	<div class="lcwp_scw_choser_wrap ag_scw_choser_wrap">
            <select name="ag_scw_choser" class="lcwp_scw_choser ag_scw_choser" autocomplete="off">
                <option value="#ag_sc_gall" selected="selected"><?php _e('Gallery', 'ag_ml') ?></option>
                <option value="#ag_sc_itg"><?php _e('Image-to-Gallery', 'ag_ml') ?></option>
                <option value="#ag_sc_coll"><?php _e('Collection', 'ag_ml') ?></option>	
                <option value="#ag_sc_slider"><?php _e('Slider', 'ag_ml') ?></option>	
                <option value="#ag_sc_car"><?php _e('Carousel', 'ag_ml') ?></option>	
            </select>	
        </div>
        
        
        
		<div id="ag_sc_gall" class="lcwp_scw_block ag_scw_block"> 
            <ul>
                <li class="lcwp_scw_field ag_scw_field">
                	<label><?php _e('Which gallery?', 'ag_ml') ?></label>
               		<select id="ag_gall_choose" data-placeholder="<?php _e('Select gallery', 'ag_ml') ?> .." name="ag_gall_choose" class="lcweb-chosen" autocomplete="off">
						<?php
						foreach($galleries as $gid => $g_tit) {
							echo '<option value="'.$gid.'">'.$g_tit.'</option>';	
						}
                        ?>
                	</select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Random display?', 'ag_ml') ?></label>
                    <input type="checkbox" id="ag_random" name="ag_random" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Use watermark?', 'ag_ml') ?> <em>(<?php _e('where available', 'ag_ml') ?>)</em></label>
                    <input type="checkbox" id="ag_watermark" name="ag_watermark" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Use tags filter?', 'ag_ml') ?></label>
                    <input type="checkbox" id="ag_tag_filter" name="ag_tag_filter" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Pagination system', 'ag_ml') ?></label>
               		<select id="ag_gall_pagination" data-placeholder="<?php _e('Select an option', 'ag_ml') ?> .." name="ag_gall_pagination" class="lcweb-chosen" autocomplete="off">
						
                        <option value=""><?php _e('Auto - follow global settings', 'ag_ml') ?></option>
                        <option value="standard"><?php _e('Standard', 'ag_ml') ?></option>
                        <option value="inf_scroll"><?php _e('Infinite scroll', 'ag_ml') ?></option>
                	</select>
                </li>
                <?php echo $agom_block; ?>
                <li class="lcwp_scw_field ag_scw_field">
                	<input type="button" value="<?php _e('Insert Gallery', 'ag_ml') ?>" name="ag_insert_gallery" id="ag_insert_gallery" class="button-primary" />
                </li>
			</ul>
		</div>  
        
        
        
        <div id="ag_sc_itg" class="lcwp_scw_block ag_scw_block"> 
            <ul>
                <li class="lcwp_scw_field ag_scw_field">
                	<label><?php _e('Which gallery?', 'ag_ml') ?></label>
               		<select data-placeholder="<?php _e('Select gallery', 'ag_ml') ?> .." name="ag_itg_gall" class="lcweb-chosen" autocomplete="off">
						<?php
						foreach($galleries as $gid => $g_tit) {
							echo '<option value="'.$gid.'">'.$g_tit.'</option>';	
						}
                        ?>
                	</select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e("Box width", 'ag_ml') ?></label>
                    <input type="number" name="ag_itg_w" value="100" style="width: 70px; text-align: center;" maxlength="4" autocomplete="off" />
                    
                    <select name="ag_itg_w_type" style="width: 50px; min-width: 0px; position: relative; top: -3px;" autocomplete="off">
                    	<option value="%">%</option>
                        <option value="px">px</option>
                    </select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e("Images height", 'ag_ml') ?> <em>(<?php _e('% is proportional to width', 'ag_ml') ?>)</em></label>
                    
                    <input type="number" name="ag_itg_h" value="75" style="width: 70px; text-align: center;" maxlength="4" autocomplete="off" />
                    
                    <select name="ag_itg_h_type" style="width: 50px; min-width: 0px; position: relative; top: -3px;" autocomplete="off">
                    	<option value="%">%</option>
                        <option value="px">px</option>
                    </select>
                </li>
                <li class="lcwp_scw_field ag_scw_field">
                	<label><?php _e("Layout", 'ag_ml') ?></label>

                    <select name="ag_itg_layout" class="lcweb-chosen" autocomplete="off">
                    	<option value="">(<?php _e('default one', 'ag_ml') ?>)</option>
                       	<?php
						include_once(AG_DIR .'/settings/field_options.php');
						foreach(ag_itg_layouts() as $key => $val) {
							echo '<option value="'. $key .'">'. $val .'</option>';	
						}
						?>
                    </select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e("How many images to display?", 'ag_ml') ?> <span class="dashicons dashicons-info" title="<?php echo esc_attr(__('This will be ignored if chosen layout is "main image + two on sides"', 'ag_ml')) ?>" style="cursor: help; opacity: 0.3;"></span></label>

                    <select name="ag_itg_img_num" style="width: 50px; min-width: 0px;" autocomplete="off">
                    	<option value="1">1</option>
                        <option value="2">2</option>
                        <option value="4">4</option>
                    </select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e("Custom font size", 'ag_ml') ?> <span class="dashicons dashicons-info" title="<?php echo esc_attr(__('Leave empty to use default one', 'ag_ml')) ?>" style="cursor: help; opacity: 0.3;"></span></label>

                     <input type="number" name="ag_itg_font_size" value="" max="5" min="0.1" step="0.1" style="width: 70px; text-align: center;" maxlength="4" autocomplete="off" /> rem
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Random images?', 'ag_ml') ?> <span class="dashicons dashicons-info" title="<?php echo esc_attr(__('Check to randomly pick images randomly from gallery', 'ag_ml')) ?>" style="cursor: help; opacity: 0.3;"></span></label>
                    <input type="checkbox" name="ag_itg_random" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Use watermark?', 'ag_ml') ?> <em>(<?php _e('where available', 'ag_ml') ?>)</em></label>
                    <input type="checkbox" name="ag_itg_watermark" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field ag_itg_img_num">
                	<label><?php _e("Custom overlay text", 'ag_ml') ?> <span class="dashicons dashicons-info" title="<?php echo esc_attr(__('Remember you can use placeholders and FontAwesome icons as explained in settings', 'ag_ml')) ?>" style="cursor: help; opacity: 0.3;"></span></label>
                    
                	<textarea name="ag_itg_cust_txt"></textarea>
				</li>

                <li class="lcwp_scw_field ag_scw_field">
                	<input type="button" value="<?php _e('Insert Image', 'ag_ml') ?>" name="ag_insert_itg" id="ag_insert_itg" class="button-primary" />
                </li>
			</ul>
		</div>  
		
        
        
        <div id="ag_sc_coll" class="lcwp_scw_block ag_scw_block"> 
            <ul>
                <li class="lcwp_scw_field ag_scw_field">
                	<label><?php _e('Which collection?', 'ag_ml') ?></label>
               		<select id="ag_collection_choose" name="ag_collection_choose" data-placeholder="<?php _e('Select gallery', 'ag_ml') ?> .." class="lcweb-chosen" autocomplete="off">
						<?php
						foreach ( $collections as $collection ) {
							echo '<option value="'.$collection->term_id.'">'.$collection->name.'</option>';
						}
                        ?>
                	</select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Allow filters?', 'ag_ml') ?></label>
                    <input type="checkbox" id="ag_coll_filter" name="ag_coll_filter" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Random display?', 'ag_ml') ?></label>
                    <input type="checkbox" id="ag_coll_random" name="ag_coll_random" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <?php echo $agom_block; ?>
                <li class="lcwp_scw_field ag_scw_field">
                	<input type="button" value="<?php _e('Insert Collection', 'ag_ml') ?>" name="ag_insert_collection" id="ag_insert_collection" class="button-primary" />
                </li>
			</ul>
		</div>  
        
        
        
        <div id="ag_sc_slider" class="lcwp_scw_block ag_scw_block"> 
            <ul>
                <li class="lcwp_scw_field ag_scw_field">
                	<label><?php _e('Images source', 'ag_ml') ?></label>
               		<select name="ag_slider_gallery" id="ag_slider_gallery" data-placeholder="<?php _e('Select gallery', 'ag_ml') ?> .." class="lcweb-chosen" autocomplete="off">
						<?php
						foreach($galleries as $gid => $g_tit) {
							echo '<option value="'.$gid.'">'.$g_tit.'</option>';	
						}
                        ?>
                	</select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e("Slider's width", 'ag_ml') ?></label>
                    
                    <input type="number" name="ag_slider_w" value="" id="ag_slider_w" style="width: 70px; text-align: center;" maxlength="4" autocomplete="off" />
                    
                    <select name="ag_slider_w_type"  id="ag_slider_w_type" style="width: 50px; min-width: 0px; position: relative; top: -3px;" autocomplete="off">
                    	<option value="%">%</option>
                        <option value="px">px</option>
                    </select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e("Slider's height", 'ag_ml') ?> <em>(<?php _e('% is proportional to width', 'ag_ml') ?>)</em></label>
                    
                    <input type="number" name="ag_slider_h" value="" id="ag_slider_h" style="width: 70px; text-align: center;" maxlength="4" autocomplete="off" />
                    
                    <select name="ag_slider_h_type"  id="ag_slider_h_type" style="width: 50px; min-width: 0px; position: relative; top: -3px;" autocomplete="off">
                    	<option value="%">%</option>
                        <option value="px">px</option>
                    </select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Random display?', 'ag_ml') ?></label>
                    <input type="checkbox" id="ag_slider_random" name="ag_slider_random" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Use watermark?', 'ag_ml') ?> <em>(<?php _e('where available', 'ag_ml') ?>)</em></label>
                    <input type="checkbox" id="ag_slider_watermark" name="ag_slider_watermark" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field">
                	<label><?php _e('Autoplay slider?', 'ag_ml') ?></label>
               		<select id="ag_slider_autop" data-placeholder="<?php _e('Select an option', 'ag_ml') ?> .." name="ag_slider_autop" class="lcweb-chosen" autocomplete="off">
						
                        <option value="auto">(<?php _e('as default', 'ag_ml') ?>)</option>
						<option value="1"><?php _e('Yes', 'ag_ml') ?></option>
                      	<option value="0"><?php _e('No', 'ag_ml') ?></option>
                	</select>
                </li>
                <li class="lcwp_scw_field ag_scw_field">
                	<input type="button" value="<?php _e('Insert Slider', 'ag_ml') ?>" name="ag_insert_slider" id="ag_insert_slider" class="button-primary" />
                </li>
			</ul>
		</div>  
        
        
        
        <div id="ag_sc_car" class="lcwp_scw_block ag_scw_block"> 
            <ul>
                <li class="lcwp_scw_field ag_scw_field">
                	<label><?php _e('Which gallery?', 'ag_ml') ?></label>
               		<select name="ag_car_gallery" id="ag_car_gallery" data-placeholder="<?php _e('Select gallery', 'ag_ml') ?> .." class="lcweb-chosen" autocomplete="off">
						<?php
						foreach($galleries as $gid => $g_tit) {
							echo '<option value="'.$gid.'">'.$g_tit.'</option>';	
						}
                        ?>
                	</select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Images max width', 'ag_ml') ?></label>
                    <input type="number" name="ag_car_max_w" value="" min="20" id="ag_car_max_w" style="width: 70px; text-align: center;" maxlength="4" autocomplete="off" /> px
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Images height', 'ag_ml') ?></label>
                    <input type="number" name="ag_car_h" value="" id="ag_car_h" style="width: 70px; text-align: center;" maxlength="4" autocomplete="off" /> 
                    
                    <select name="ag_car_h_type" id="ag_car_h_type" autocomplete="off" style="width: 50px; min-width: 0px; position: relative; top: -3px;">
                        <option value="px">px</option>
                        <option value="%">%</option>
                    </select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Rows', 'ag_ml') ?></label>
                    
                    <select id="ag_car_rows" data-placeholder="<?php _e('Select an option', 'ag_ml') ?> .." name="ag_car_rows" style="width: 70px;" autocomplete="off">
						<?php
                        for($a=1; $a<=10; $a++) {
                        	echo '<option value="'.$a.'">'.$a.'</option>';  
                        }
                        ?>
					</select>
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Multi-scroll?', 'ag_ml') ?></label>
                    <input type="checkbox" id="ag_car_multiscroll" name="ag_car_multiscroll" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Center display mode?', 'ag_ml') ?></label>
                    <input type="checkbox" id="ag_car_center_mode" name="ag_car_center_mode" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Avoid images crop?', 'ag_ml') ?></label>
                    <input type="checkbox" id="ag_car_nocrop" name="ag_car_nocrop" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Static mode?', 'ag_ml') ?> <span class="dashicons dashicons-info" title="<?php echo esc_attr(__('Disables overlay and lightbox', 'ag_ml')) ?>" style="cursor: help; opacity: 0.3;"></span></label>
                    <input type="checkbox" id="ag_car_static" name="ag_car_static" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Random display?', 'ag_ml') ?></label>
                    <input type="checkbox" id="ag_car_random" name="ag_car_random" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Use watermark?', 'ag_ml') ?> <em>(<?php _e('where available', 'ag_ml') ?>)</em></label>
                    <input type="checkbox" id="ag_car_watermark" name="ag_car_watermark" value="1" class="ip-checkbox" autocomplete="off" />
                </li>
                <li class="lcwp_scw_field ag_scw_field lcwp_scwf_half">
                	<label><?php _e('Autoplay carousel?', 'ag_ml') ?></label>
               		<select id="ag_car_autop" data-placeholder="<?php _e('Select an option', 'ag_ml') ?> .." name="ag_car_autop" class="lcweb-chosen" autocomplete="off">
						
                        <option value="auto">(<?php _e('as default', 'ag_ml') ?>)</option>
						<option value="1"><?php _e('Yes', 'ag_ml') ?></option>
                      	<option value="0"><?php _e('No', 'ag_ml') ?></option>
                	</select>
                </li>
                <?php echo $agom_block; ?>
                <li class="lcwp_scw_field ag_scw_field">
                	<input type="button" value="<?php _e('Insert Carousel', 'ag_ml') ?>" name="ag_insert_carousel" id="ag_insert_carousel" class="button-primary" />
                </li>
			</ul>
    	</div> 
	</div> 
   
   
   
	<?php // SCRIPTS ?>
    <link rel="stylesheet" href="<?php echo AG_URL; ?>/js/lightboxes/magnific-popup/magnific-popup.css" media="all" />
    <script src="<?php echo AG_URL; ?>/js/lightboxes/magnific-popup/magnific-popup.pckg.js" type="text/javascript"></script>
	
    <script src="<?php echo AG_URL; ?>/js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo AG_URL; ?>/js/lc-switch/lc_switch.min.js" type="text/javascript"></script>
    
    <script src="<?php echo AG_URL; ?>/js/functions.js" type="text/javascript"></script>
    <script src="<?php echo AG_URL; ?>/js/tinymce_btn.js" type="text/javascript"></script>
<?php    
}
add_action('admin_footer', 'ag_editor_btn_content');
