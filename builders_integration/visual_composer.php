<?php
//  visual composer integration


function ag_on_visual_composer() {
    include_once(AG_DIR .'/admin_menu.php'); // be sure tax are registered
	register_cpt_ag_gallery();
	register_taxonomy_ag_collections();
	

	// galleries array
	$args = array(
		'post_type' => 'ag_galleries',
		'numberposts' => -1,
		'post_status' => 'publish'
	);
	$galleries = get_posts($args);
	
	$galls_arr = array(); 
	foreach($galleries as $gallery) {
    	$galls_arr[ $gallery->post_title ] = $gallery->ID;
    }
	
	
	// collections array array (use full list for now)
	$collections = get_terms('ag_collections', 'hide_empty=0');
	
	$colls_arr = array(); 
	foreach($collections as $collection) {
    	$colls_arr[ $collection->name ] = $collection->term_id;
    }
	
	
	///// OVERLAY MANAGER ADD-ON ///////////
	if(defined('AGOM_DIR')) {
		register_taxonomy_agom(); // be sure tax are registered
		$overlays = get_terms('agom_overlays', 'hide_empty=0');
		
		$ol_arr = array(
			__('default one', 'ag_ml') => ''
		);
		foreach($overlays as $ol) {
			$ol_arr[ $ol->name ] = $ol->term_id;	
		}
		
		$agom_param = array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Custom Overlay', 'ag_ml'),
			'param_name' 	=> 'overlay',
			'admin_label' 	=> true,
			'value' 		=> $ol_arr,
		);
	}
	///////////////////////////////////////
	
	
	
	/**********************************************************************************************************/
	
	
	#########################################
	######## GALLERY SHORTCODE ##############
	#########################################
	
	// parameters
	$params = array(
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Gallery', 'ag_ml'),
			'param_name' 	=> 'gid',
			'admin_label' 	=> true,
			'value' 		=> $galls_arr,
			'description'	=> __('Select a gallery', 'ag_ml'),
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'random',
			'value' 		=> array(
				'<strong>'. __('Random display?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Display images randomly', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'filters',
			'value' 		=> array(
				'<strong>'. __('Use tags filter?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> '',
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Pagination System', 'ag_ml'),
			'param_name' 	=> 'pagination',
			'admin_label' 	=> true,
			'value' 		=> array(
				__('Auto - follow global settings', 'ag_ml') => '',
				__('Standard', 'ag_ml') => 'standard',
				__('Infinite scroll', 'ag_ml') => 'inf_scroll',
			),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'watermark',
			'value' 		=> array(
				'<strong>'. __('Use Watermark?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Apply watermark to images (if available)', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
	);
	
	if(isset($agom_param)) {
		$params[] = $agom_param;	
	}  
	
	// compile
	vc_map(
        array(
            'name' 			=> 'AG - '. __('Gallery', 'ag_ml'),
			'description'	=> __("Displays a gallery", 'ag_ml'),
            'base' 			=> 'g-gallery',
            'category' 		=> "Avator Gallery",
			'icon'			=> AG_URL .'/img/vc_icon.png',
            'params' 		=> $params,
        )
    );
	
	
	
	
	
	#########################################
	###### IMAGE-TO-GALLERY SHORTCODE #######
	#########################################
	
	// parameters
	$params = array(
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Gallery', 'ag_ml'),
			'param_name' 	=> 'gid',
			'admin_label' 	=> true,
			'value' 		=> $galls_arr,
			'description'	=> __('Select a gallery', 'ag_ml'),
		),
		array(
			'type' 			=> 'ag_num_unit',
			'heading' 		=> __('Box width', 'ag_ml'),
			'param_name' 	=> 'width',
			'value' 		=> '100%',
			'description'	=> '',
			'admin_label' 	=> true,
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'ag_num_unit',
			'heading' 		=> __('Images height', 'ag_ml'),
			'param_name' 	=> 'img_h',
			'value' 		=> '75%',
			'admin_label' 	=> true,
			'description'	=> __("percentage value is related to width", 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Autoplay', 'ag_ml'),
			'param_name' 	=> 'layout',
			'admin_label' 	=> true,
			'value' 		=> array(
				__('(as default)', 'ag_ml') => '',
				__('Bottom-right corner overlay on last image', 'ag_ml') 	=> 'corner_txt',
				__('100% opaque - full overlay on last image', 'ag_ml') 	=> '100_op_ol',
				__('50% opaque - full overlay on last image', 'ag_ml') 		=> '50_op_ol',
				__('0% opaque - full overlay on last image', 'ag_ml') 		=> '0_op_ol',
				__('Centered text block over images', 'ag_ml') 				=> 'block_over',	
				__('Main image with central overlay + two smaller on sides', 'ag_ml') => 'main_n_sides',	
			),
		),
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('How many images to display?', 'ag_ml'),
			'param_name' 	=> 'layout',
			'admin_label' 	=> true,
			'value' 		=> array(
				1 => 1,
				2 => 2,
				4 => 4,
			),
			'description'	=> __('This will be ignored if chosen layout is "main image + two on sides"', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'textfield',
			'heading' 		=> __('Custom font size', 'ag_ml'),
			'param_name' 	=> 'font_size',
			'admin_label' 	=> true,
			'description'	=> __('Use a float number (min 0.1 - max 3). Leave empty to use default value', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'random',
			'value' 		=> array(
				'<strong>'. __('Random display?', 'ag_ml') .'</strong>' => 1
			),
			'admin_label' 	=> true,
			'description'	=> __('Display images randomly', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'watermark',
			'value' 		=> array(
				'<strong>'. __('Use Watermark?', 'ag_ml') .'</strong>' => 1
			),
			'admin_label' 	=> true,
			'description'	=> __('Apply watermark to images (if available)', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'textarea',
			'heading' 		=> __('Custom overlay text', 'ag_ml'),
			"param_name" 	=> "content",
			'description'	=> __('Remember you can use placeholders and FontAwesome icons as explained in settings', 'ag_ml'),
		),
	);
  
	// compile
	vc_map(
        array(
            'name' 			=> 'AG - '. __('Image-to-Lightbox', 'ag_ml'),
			'description'	=> __("Displays one/more images showing the full gallery through lightbox", 'ag_ml'),
            'base' 			=> 'g-itg',
            'category' 		=> "Avator Gallery",
			'class'			=> 'ag_itg_sc',
			'icon'			=> AG_URL .'/img/vc_icon.png',
            'params' 		=> $params,
        )
    );
	
	
	
	
	
	#########################################
	####### COLLECTION SHORTCODE ############
	#########################################
	
	// parameters
	$params = array(
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Collection', 'ag_ml'),
			'param_name' 	=> 'cid',
			'admin_label' 	=> true,
			'value' 		=> $colls_arr,
			'description'	=> __('Select a collection', 'ag_ml'),
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'filter',
			'value' 		=> array(
				'<strong>'. __('Allow Filters?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Allow galleries filtering by category', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'random',
			'value' 		=> array(
				'<strong>'. __('Random display?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Display galleries randomly', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
	);
	
	if(isset($agom_param)) {
		$params[] = $agom_param;	
	}
		  
	// compile
	vc_map(
        array(
            'name' 			=> 'AG - '. __('Collection', 'ag_ml'),
			'description'	=> __("Displays a galleries collection", 'ag_ml'),
            'base' 			=> 'g-collection',
            'category' 		=> "Avator Gallery",
			'icon'			=> AG_URL .'/img/vc_icon.png',
            'params' 		=> $params,
        )
    );
	
	
	
	
	
	
	#########################################
	######## SLIDER SHORTCODE ###############
	#########################################
	
	// parameters
	$params = array(
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Gallery', 'ag_ml'),
			'param_name' 	=> 'gid',
			'admin_label' 	=> true,
			'value' 		=> $galls_arr,
			'description'	=> __('Select a gallery', 'ag_ml'),
		),
		array(
			'type' 			=> 'ag_num_unit',
			'heading' 		=> __('Width', 'ag_ml'),
			'param_name' 	=> 'width',
			'value' 		=> '100%',
			'description'	=> __("Define slider's width", 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-5 vc_column',
		),
		array(
			'type' 			=> 'ag_num_unit',
			'heading' 		=> __('Height', 'ag_ml'),
			'param_name' 	=> 'height',
			'value' 		=> '55%',
			'description'	=> __("Define slider's height (percentage is related to width)", 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-7 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'random',
			'value' 		=> array(
				'<strong>'. __('Random display?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Display images randomly', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'watermark',
			'value' 		=> array(
				'<strong>'. __('Use Watermark?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Apply watermark to images (if available)', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Autoplay', 'ag_ml'),
			'param_name' 	=> 'autoplay',
			'admin_label' 	=> true,
			'value' 		=> array(
				__('(as default)', 'ag_ml') => 'auto',
				__('Yes', 'ag_ml') => 1,
				__('No', 'ag_ml') => 0,
			),
		),
	);
  
	// compile
	vc_map(
        array(
            'name' 			=> 'AG - '. __('Slider', 'ag_ml'),
			'description'	=> __("Displays an image slider", 'ag_ml'),
            'base' 			=> 'g-slider',
            'category' 		=> "Avator Gallery",
			'class'			=> 'ag_slider_sc',
			'icon'			=> AG_URL .'/img/vc_icon.png',
            'params' 		=> $params,
        )
    );
	
	
	
	
	
	
	#########################################
	####### CAROUSEL SHORTCODE ##############
	#########################################
	
	// image rows
	$img_rows = array();
	for($a=1; $a<=10; $a++) {
		$img_rows[$a] = $a;	 
	}
	
	// parameters
	$params = array(
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Gallery', 'ag_ml'),
			'param_name' 	=> 'gid',
			'admin_label' 	=> true,
			'value' 		=> $galls_arr,
			'description'	=> __('Select a gallery', 'ag_ml'),
		),
		array(
			'type' 			=> 'textfield',
			'heading' 		=> __('Images max width', 'ag_ml'),
			'param_name' 	=> 'img_max_w',
			'admin_label' 	=> true,
			'value' 		=> '180',
			'description'	=> __("Carousel images height in pixels", 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'textfield',
			'heading' 		=> __('Images height', 'ag_ml'),
			'param_name' 	=> 'height',
			'admin_label' 	=> true,
			'value' 		=> '200',
			'description'	=> __("Carousel images height in pixels", 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Height type', 'ag_ml'),
			'param_name' 	=> 'h_type',
			'admin_label' 	=> true,
			'value' 		=> array(
				'px' => 'px',
				'%'	 => '%'
			),
			'description'	=> __("Choose how many images to show per time", 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Rows', 'ag_ml'),
			'param_name' 	=> 'rows',
			'admin_label' 	=> true,
			'value' 		=> $img_rows,
			'description'	=> __("Choose how many image rows to use", 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'multiscroll',
			'value' 		=> array(
				'<strong>'. __('Multiple scroll?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Slides multiple images per time', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'center',
			'value' 		=> array(
				'<strong>'. __('Center mode?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Enables center display mode', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'nocrop',
			'value' 		=> array(
				'<strong>'. __('Avoid images crop?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Just downscales images', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'static',
			'value' 		=> array(
				'<strong>'. __('Static mode?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Disables overlay and lightbox', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'random',
			'value' 		=> array(
				'<strong>'. __('Random display?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Display images randomly', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'checkbox',
			'param_name' 	=> 'watermark',
			'value' 		=> array(
				'<strong>'. __('Use watermark?', 'ag_ml') .'</strong>' => 1
			),
			'description'	=> __('Apply watermark to images (if available)', 'ag_ml'),
			'edit_field_class' => 'vc_col-sm-6 vc_column',
		),
		array(
			'type' 			=> 'dropdown',
			'heading' 		=> __('Autoplay', 'ag_ml'),
			'param_name' 	=> 'autoplay',
			'admin_label' 	=> true,
			'value' 		=> array(
				__('(as default)', 'ag_ml') => 'auto',
				__('Yes', 'ag_ml') => 1,
				__('No', 'ag_ml') => 0,
			),
		),
	);
	
	if(isset($agom_param)) {
		$params[] = $agom_param;	
	}
  
	// compile
	vc_map(
        array(
            'name' 			=> 'AG - '. __('Carousel', 'ag_ml'),
			'description'	=> __("Displays an image carousel", 'ag_ml'),
            'base' 			=> 'g-carousel',
            'category' 		=> "Avator Gallery",
			'class'			=> 'ag_slider_sc',
			'icon'			=> AG_URL .'/img/vc_icon.png',
            'params' 		=> $params,
        )
    );
	
	

	
	/**********************************************************************************************************/
	
	
	// add new field type
	function ag_vc_num_unit_field($settings, $value) {
		$px_sel = (!empty($value) && strpos($value, 'px') !== false) ? 'selected="selected"' : '';
	  	
		$min = (isset($settings['min_val'])) ? 'min="'. (int)$settings['min_val'] .'"' : '';
		$max = (isset($settings['max_val'])) ? 'max="'. (int)$settings['max_val'] .'"' : '';
		
		
	  	return 
		'<div class="ag_num_unit_wrap">
			<input name="'. esc_attr( $settings['param_name'] ) .'" type="hidden" class="wpb_vc_param_value '. esc_attr( $settings['param_name'] ) .'" value="'. esc_attr($value) .'" /> 
			
			<input name="'. esc_attr( $settings['param_name'] ) .'_val" class="wpb-textinput '. esc_attr( $settings['param_name'] ) .'" type="number" value="' . (int)str_replace(array('px', '%'), '', $value) . '" style="width: 100px;" '.$min.' '.$max.' />
				 
			<select name="'. esc_attr( $settings['param_name'] ) .'_unit" style="height: 28px; padding-bottom: 2px; padding-top: 2px; position: relative; top: -2px; width: 55px;">
				<option value="%">%</option>
				<option value="px" '. $px_sel .'>px</option>
			</select>
			
			
		</div>';
	}
	vc_add_shortcode_param('ag_num_unit', 'ag_vc_num_unit_field', AG_URL.'/js/vc_custom_field.js');
}
add_action('vc_before_init', 'ag_on_visual_composer');


