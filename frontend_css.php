<?php
////////////////////////////////////
// DYNAMICALLY CREATE THE CSS //////
////////////////////////////////////
require_once(AG_DIR. '/functions.php');
include_once(AG_DIR . '/classes/loaders_switch.php');


// slider style
$slider_style = (get_option('ag_slider_old_cmd')) ? '' : '_minimal';

// is RTL?
$rtl = (get_option('ag_rtl_mode') == '1') ? true : false;
?>


/* preloader */
<?php ag_loaders_switch() ?>

<?php if(get_option('ag_img_shadow') == 'outshadow') : ?>
.ag_gallery_wrap:not(.ag_collection_wrap), 
.ag_itg_container,
.ag_coll_outer_container {
	padding: 3px;
}
<?php elseif(get_option('ag_img_shadow') == 'outline') : ?>
.ag_gallery_wrap:not(.ag_collection_wrap), 
.ag_itg_container,
.ag_coll_outer_container {
	padding: 1px;
}
<?php endif; ?>


/* image border, radius and shadow */
.ag_standard_gallery .ag_img,
.ag_columnized_gallery .ag_img,
.ag_masonry_gallery .ag_img_inner,
.ag_string_gallery .ag_img,
.ag_itg_container .ag_img,
.ag_coll_img {
	<?php
	$border = get_option('ag_img_border');
	if(!$border || $border == 0) {
		echo 'border: none';
	}
	else {
		echo 'border: '.$border.'px solid '.get_option('ag_img_border_color', '#444').';';
		echo 'background-color: '.get_option('ag_img_border_color', '#444').';';
	}
	?>
    
    <?php 
	$radius = get_option('ag_img_radius');
	if($radius && (int)$radius  > 0) {
		echo 'border-radius: '.$radius.'px;';	
	}
	?>
	
	<?php 
	// soft shadow or outline
	$shadow_outline = get_option('ag_img_shadow');
	
	if($shadow_outline == 'outshadow') {
		echo 'box-shadow: 0 0 2px rgba(25,25,25,0.4);';
	}
	elseif($shadow_outline == 'outline') {
		echo 'box-shadow: 0 0 0 1px '. get_option('ag_img_outline_color', '#777777') .';';
	}
	?>
}


<?php 
/* OVERLAYS */
$overlay_type = get_option('ag_overlay_type'); 
$ol_alpha = ((int)get_option('ag_main_ol_opacity', 70)) / 100;


// color
$bg_color = ag_hex2rgb(get_option('ag_main_ol_color', 'rgb(245,245,245)'));
$txt_color = get_option('ag_main_ol_txt_color', '#222');
	
if(!empty($overlay_type)) : 
?>
/* main overlay */
.ag_gallery_wrap .ag_img .ag_main_overlay {
	<?php
	echo '
	color: '.$txt_color.';
	background: '.$bg_color.';
	text-shadow: 0 0 0 '. ag_hex2rgba($txt_color, 0.85) .';
	';  
	
	if($ol_alpha) {
		echo 'background: '. ag_hex2rgba($bg_color, $ol_alpha) .';'; 
	}
	?>
}
<?php
endif;


/* fullimage title layer */
?>
.ag_ol_full_mode .ag_img_title {
	border-color: <?php echo ag_hex2rgba($txt_color, 0.15) ?>; 
	background: <?php echo ag_hex2rgba($bg_color, round($ol_alpha * 0.5, 1)) ?>;

	<?php if(!$ol_alpha) : ?>
    box-shadow: none;
    <?php endif; ?>	
}
<?php



/* secondary overlay */
if($overlay_type == 'both') : ?>

.ag_gallery_wrap div.ag_img:not(.ag_coll_img):not(.ag_linked_img) .ag_sec_overlay span:before {
<?php
switch(get_option('ag_sec_ol_icon', 'eye')) {
	case 'eye': default :
		?>content: "\e604";<?php
		break;
		
	case 'camera':
		?>content: "\e90f"; font-size: 15px;<?php
		break;
		
	case 'magnifier':
		?>content: "\e601"; font-size: 18px;<?php
		break;
		
	case 'image':
		?>content: "\e90d"; font-size: 15px;<?php
		break;				
}
?>
}
.ag_both_ol .ag_sec_overlay {
	background: <?php echo get_option('ag_sec_ol_color', '#eee'); ?>;
}
.ag_gallery_wrap .ag_img .ag_sec_overlay span {
	color: <?php echo get_option('ag_icons_col', '#fcfcfc') ?>;
}
<?php 
endif; // overlays end



/* slow image zoom on hover */
if(get_option('ag_slowzoom_ol')) : ?>
[data-ag_ol="default"]:not(.ag_car_nocrop) .ag_main_img_wrap,
.ag_itg_wrap .ag_main_img_wrap {
	transition:	transform .5s ease-out;	
	transform: scale(1) translateZ(0); /* set default state to avoid bad "jumps" on state change */
}
[data-ag_ol="default"]:not(.ag_car_nocrop) .ag_img:hover .ag_main_img_wrap,
[data-ag_ol="default"]:not(.ag_car_nocrop) .ag_img.ag_touch_on .ag_main_img_wrap,
.ag_itg_wrap .ag_img:hover .ag_main_img_wrap {
	transform: scale(1.03);
	transition-duration: 4s;
}
.ag_itg_monoimage .ag_img:nth-child(2):hover .ag_main_img_wrap,
.ag_itg_monoimage .ag_img:nth-child(3):hover .ag_main_img_wrap {
	transform: scale(1) !important;
}
<?php endif; ?>



/* collections - texts under images */
.ag_coll_img .ag_main_overlay_under .ag_img_title_under {
	color: <?php echo get_option('ag_txt_u_title_color', '#444444') ?>;
}
.ag_coll_img .ag_main_overlay_under .ag_img_descr_under {
	color: <?php echo get_option('ag_txt_u_descr_color', '#555555') ?>;
}



<?php /* tags + collection filters + back to collection button */ ?>
.ag_filter,
.ag_tags_wrap {
	text-align: <?php echo get_option('ag_filters_align', 'left'); ?>; 
}
.ag_filter a.agf,
.ag_tag,
.ag_coll_back_to_new_style {	
	color: <?php echo get_option('ag_filters_txt_color', '#444444'); ?>;
    font-size: <?php echo get_option('ag_filters_font_size', 15) ?>px;
    
    <?php $fp = get_option('ag_filters_padding', array(6, 12)) ?>
    padding: <?php echo $fp[0] ?>px <?php echo $fp[1] ?>px;
}
.ag_filter a.agf:hover,
.ag_tag:hover,
.ag_coll_back_to_new_style:hover {	
	color: <?php echo get_option('ag_filters_txt_color_h', '#666666'); ?> !important;
}
.ag_filter a.agf.ag_cats_selected,
.ag_filter a.agf.ag_cats_selected:hover,
.ag_tag.ag_tag_sel,
.ag_tag.ag_tag_sel:hover {	
	color: <?php echo get_option('ag_filters_txt_color_sel', '#333'); ?> !important;
}
.ag_textual_filters .ag_cats_selected:after,
.ag_textual_filters .ag_tag_sel {
	text-shadow: 0 0.01em 0 <?php echo get_option('ag_filters_txt_color_sel', '#333'); ?>;
}

.ag_btn_filters a.agf,
.ag_btn_filters .ag_tag,
.ag_coll_back_to_new_style,
select.ag_mobile_filter_dd {	
	background-color: <?php echo get_option('ag_filters_bg_color', '#ffffff'); ?>;
    border: <?php echo (int)get_option('ag_filters_border_w', 1) ?>px solid <?php echo get_option('ag_filters_border_color', '#999999') ?>;
    border-radius: <?php echo (int)get_option('ag_filters_radius', 2) ?>px;
}
.ag_btn_filters a.agf:hover,
.ag_btn_filters .ag_tag:hover,
.ag_coll_back_to_new_style:hover {	
	background-color: <?php echo get_option('ag_filters_bg_color_h', '#ffffff') ?>;
    border-color: <?php echo get_option('ag_filters_border_color_h', '#666666') ?>;
}
.ag_btn_filters a.agf.ag_cats_selected,
.ag_btn_filters a.agf.ag_cats_selected:hover,
.ag_btn_filters .ag_tag.ag_tag_sel,
.ag_btn_filters .ag_tag.ag_tag_sel:hover  {	
	background-color: <?php echo get_option('ag_filters_bg_color_sel', '#ffffff'); ?>;
    border-color: <?php echo get_option('ag_filters_border_color_sel', '#555555'); ?>;
}
.ag_textual_filters .agf:after,
.ag_textual_filters .ag_tag:after {
	background-color: <?php echo get_option('ag_filters_bg_color_sel', '#ffffff'); ?>;
}


<?php 
// responsive part for dropdown filters
if(get_option('ag_dd_mobile_filter')) :
?>
@media screen and (max-width: 760px) { 
	.ag_filter,
    .ag_tag {
    	display: none !important;
    }
    .ag_mobile_filter_dd {
    	display: block !important;
    }
}
<?php endif; ?>




<?php /* pagination elements */ ?>
.ag_standard_pag i,
.ag_num_btns_wrap > div {
	color: <?php echo get_option('ag_pag_txt_col', '#707070') ?>;
}
.ag_standard_pag:not(.ag_detach_arr),
.ag_standard_pag.ag_detach_arr > div,
.ag_infinite_scroll,
.ag_num_btns_wrap > div {
	border: 1px solid <?php echo get_option('ag_pag_border_col', '#f5f5f5') ?>;
	background: <?php echo get_option('ag_pag_bg_col', '#f5f5f5') ?>;
}
.ag_standard_pag:not(.ag_only_arr) .ag_nav_mid,
.ag_infinite_scroll div {
	color: <?php echo get_option('ag_pag_txt_col', '#707070') ?>;
}
.ag_standard_pag.ag_monoblock:before {
	background: <?php echo get_option('ag_pag_border_col', '#f5f5f5') ?>;
}
.ag_dots_pag_wrap > div {
	background: <?php echo get_option('ag_pag_bg_col', '#f5f5f5') ?>;
}

.ag_standard_pag:not(.ag_only_arr) > div:not(.ag_nav_mid):not(.ag_pag_disabled):hover,
.ag_infinite_scroll:hover,
.ag_standard_pag.ag_only_arr > div:not(.ag_pag_disabled):hover,
.ag_num_btns_wrap > div:hover,
.ag_num_btns_wrap .ag_pag_disabled {
	background: <?php echo get_option('ag_pag_bg_col_h', '#f0f0f0') ?>;
}
.ag_standard_pag:not(.ag_only_arr) > div:not(.ag_nav_mid):not(.ag_pag_disabled):hover i,
.ag_standard_pag.ag_only_arr > div:not(.ag_pag_disabled):hover i,
.ag_infinite_scroll:hover span,
.ag_num_btns_wrap > div:hover,
.ag_num_btns_wrap .ag_pag_disabled  {
	color: <?php echo get_option('ag_pag_txt_col_h', '#5e5e5e') ?>;	
}
.ag_infinite_scroll:hover,
.ag_standard_pag.ag_only_arr > div:not(.ag_pag_disabled):hover,
.ag_monoblock > div:not(.ag_pag_disabled):hover,
.ag_infinite_scroll:hover,
.ag_num_btns_wrap > div:hover, 
.ag_num_btns_wrap .ag_pag_disabled {
	border: 1px solid  <?php echo get_option('ag_pag_border_col_h', '#999999') ?>;	
}
.ag_dots_pag_wrap > div:hover,
.ag_dots_pag_wrap > div.ag_pag_disabled {	
	box-shadow: 0 -13px 0 0 <?php echo get_option('ag_pag_bg_col_h', '#f0f0f0') ?> inset;
}



/* ************************************************** */


/* standard gallery images */
.ag_standard_gallery .ag_container {
	width: calc(100% + <?php echo (int)get_option('ag_standard_hor_margin', 10) ?>px);
}
.ag_standard_gallery .ag_img {
	margin-right: <?php echo (int)get_option('ag_standard_hor_margin', 10) ?>px;
    margin-bottom: <?php echo (int)get_option('ag_standard_ver_margin', 10) ?>px;
}
<?php if($rtl) : ?>
.ag_standard_gallery .ag_container {
	left: <?php echo (int)get_option('ag_standard_hor_margin', 10) ?>px; 
}
<?php endif;?>

/* columnized gallery images */
.ag_columnized_gallery .ag_container {
	width: calc(100% + <?php echo (int)get_option('ag_colnzd_hor_margin', 10) ?>px);
}
.ag_columnized_gallery .ag_img {
	margin-right: <?php echo (int)get_option('ag_colnzd_hor_margin', 10) ?>px;
    margin-bottom: <?php echo (int)get_option('ag_colnzd_ver_margin', 10) ?>px;
}
<?php if($rtl) : ?>
.ag_columnized_gallery .ag_container  {
	left: <?php echo (int)get_option('ag_colnzd_hor_margin', 10) ?>px; 
}
<?php endif;?>

/* masonry gallery images */
.ag_masonry_gallery .ag_container {
	width: calc(100% + <?php echo (int)get_option('ag_masonry_margin', 10) ?>px);
}
.ag_masonry_gallery .ag_img {
    padding-right: <?php echo (int)get_option('ag_masonry_margin', 10) ?>px;
    margin-bottom: <?php echo (int)get_option('ag_masonry_margin', 10) ?>px;
}
<?php if($rtl) : ?>
.ag_masonry_gallery .ag_container {
	-webkit-transform: 	translateX(<?php echo (int)get_option('ag_masonry_margin', 10) - 1 ?>px); 
	-ms-transform: 		translateX(<?php echo (int)get_option('ag_masonry_margin', 10) - 1 ?>px); 
	transform: 			translateX(<?php echo (int)get_option('ag_masonry_margin', 10) - 1 ?>px); 
}
<?php endif;?>

/* photostring gallery images */
.ag_string_gallery .ag_container {
	width: calc(100% + <?php echo (int)get_option('ag_photostring_margin', 10) ?>px + 1px); /* 1px = security addition */
}
.ag_string_gallery .ag_img {
	margin-right: <?php echo get_option('ag_photostring_margin', 10) ?>px;
    margin-bottom: <?php echo get_option('ag_photostring_margin', 10) ?>px;
}
<?php if($rtl) : ?>
.ag_string_gallery .ag_container {
	left: <?php echo (int)get_option('ag_photostring_margin', 10) ?>px; 
}
<?php endif;?>

/* image-to-gallery images */
.ag_itg_wrap:not(.ag_itg_monoimage) .ag_itg_img:nth-child(odd) {
	width: calc(50% - <?php echo ceil((int)get_option('ag_itg_margin', 10) / 2) ?>px);
    margin-right: <?php echo (int)get_option('ag_itg_margin', 10) ?>px;
}
.ag_itg_wrap:not(.ag_itg_monoimage) .ag_itg_img:nth-child(even) {
	width: calc(50% - <?php echo floor((int)get_option('ag_itg_margin', 10) / 2) ?>px);
}
.ag_itg_wrap .ag_itg_img:nth-of-type(3),
.ag_itg_wrap .ag_itg_img:nth-of-type(4) {
	margin-top: <?php echo (int)get_option('ag_itg_margin', 10) ?>px;
}
.ag_itg_wrap .ag_itg_ol_inner {
	color: <?php echo get_option('ag_itg_txt_color', '#fefefe') ?>;
}
.ag_itg_corner_txt .ag_itg_ol_inner,
.ag_itg_main_n_sides .ag_itg_ol_inner {
	background-color: <?php echo ag_hex2rgba(get_option('ag_itg_bg_color', '#333333'), '0.75') ?>;
}
.ag_itg_50_op_ol .ag_itg_ol {
	background-color: <?php echo ag_hex2rgba(get_option('ag_itg_bg_color', '#333333'), '0.5') ?>;
}
.ag_itg_100_op_ol .ag_itg_ol,
.ag_itg_block_over .ag_itg_ol_inner { 
    background-color: <?php echo get_option('ag_itg_bg_color', '#333333') ?>;
}




/* COLLECTION IMAGES */
.ag_coll_container {
	width: calc(100% + <?php echo (int)get_option('ag_coll_hor_margin', 15) ?>px);
}
.ag_coll_img_wrap {
	margin-bottom: <?php echo (int)get_option('ag_coll_ver_margin', 15) ?>px;
	padding-right: <?php echo (int)get_option('ag_coll_hor_margin', 15) ?>px;
}
<?php if($rtl) : ?>
.ag_coll_gallery_container .ag_gallery_wrap {
	direction: RTL;
}
<?php endif;?>



/* CAROUSEL */
.ag_car_item_wrap {
	padding-right: <?php echo floor((int)get_option('ag_car_hor_margin') / 2) ?>px;
	padding-left: <?php echo ceil((int)get_option('ag_car_hor_margin') / 2) ?>px;
    padding-top: <?php echo floor((int)get_option('ag_car_ver_margin') / 2) ?>px;
	padding-bottom: <?php echo ceil((int)get_option('ag_car_ver_margin') / 2) ?>px;
}
<?php if(!in_array('dots', (array)get_option('ag_car_hide_nav_elem', array()))) : ?>
.ag_carousel_wrap.slick-slider {
	margin-bottom: 55px;
}
<?php endif; ?>

/* pagination button alignment */
.ag_paginate {
	text-align: <?php echo get_option('ag_pag_align', 'center') ?>;
}


/* ************************************************** */


<?php 
// slider thumbs toggle visibility
$thumbs_visibility = get_option('ag_slider_thumbs', 'yes');
if($thumbs_visibility == 'always' || $thumbs_visibility == 'never') : 
?>
.ag_galleria_slider_wrap .galleria-ag-toggle-thumb {
	display: none !important;
}
<?php endif; ?>
<?php if($thumbs_visibility == 'no') :  ?>
.ag_galleria_slider_wrap .galleria-thumbnails-container {
	opacity: 0;
    filter: alpha(opacity=0);
}
<?php endif; ?>

<?php
// slider elements to hide
$to_hide = get_option('ag_slider_to_hide');
if(is_array($to_hide) && count($to_hide) > 0) {
	$elems = array();
	
	if(in_array('play', $to_hide)) 		{$elems[] = '.ag_galleria_slider_wrap .galleria-ag-play , .ag_galleria_slider_wrap .galleria-ag-pause';}
	if(in_array('lightbox', $to_hide)) 	{$elems[] = '.ag_galleria_slider_wrap .galleria-ag-lightbox';}
	if(in_array('info', $to_hide)) 		{$elems[] = '.ag_galleria_slider_wrap .galleria-ag-info-link, .ag_galleria_slider_wrap .galleria-info-text';}
	if(in_array('counter', $to_hide)) 	{$elems[] = '.ag_galleria_slider_wrap .galleria-counter';}
	
	echo implode(' , ', $elems) . '{display: none !important;}';
}

// slider - if thumbs always hidden
if(get_option('ag_slider_thumbs', 'yes') == 'never') {
	echo '
	.ag_galleria_slider_wrap .galleria-thumbnails-container {
		display: none !important;
	}
	';	
}

// slider thumbs size
$s_thumb_h = (int)get_option('ag_slider_thumb_h', 40); 
echo '
.ag_galleria_slider_wrap .galleria-thumbnails .galleria-image {
	width: '. get_option('ag_slider_thumb_w', 60) .'px !important;
}
.ag_galleria_slider_wrap .galleria-thumbnails .galleria-image,
.ag_galleria_slider_wrap .galleria-thumbnails-container {
     height: '.$s_thumb_h.'px !important;
}
.ag_galleria_slider_wrap.ag_galleria_slider_show_thumbs {
	padding-bottom: '. ($s_thumb_h + 2 + 12) .'px !important;	
}
.ag_galleria_slider_show_thumbs .galleria-thumbnails-container {
	bottom: -'. ($s_thumb_h + 2 + 10) .'px !important;		
}
';




// LC Lightbox - openclose effect
if(get_option('ag_lightbox') == 'lcweb') : ?>
/* ************************************************** */
	
    <?php
	switch(get_option('ag_lb_lcl_openclose', 'lcl_zoomin_oc')) {
		
		case 'lcl_fade_oc' :
			?>
            .lcl_fade_oc.lcl_pre_show #lcl_overlay,
            .lcl_fade_oc.lcl_pre_show #lcl_window,
            .lcl_fade_oc.lcl_is_closing #lcl_overlay,
            .lcl_fade_oc.lcl_is_closing #lcl_window {
                opacity: 0 !important;
            }
            .lcl_fade_oc.lcl_is_closing #lcl_overlay {
            	-webkit-transition-delay: .15s !important; 
                transition-delay: .15s !important;
            }
            <?php
			break;
		
		case 'lcl_zoomin_oc' :
			?>
            .lcl_zoomin_oc.lcl_pre_show #lcl_window,
            .lcl_zoomin_oc.lcl_is_closing #lcl_window {
                opacity: 0 !important;
                
                -webkit-transform: scale(0.05) translateZ(0) !important;
                transform: scale(0.05) translateZ(0) !important;		
            }
            .lcl_zoomin_oc.lcl_is_closing #lcl_overlay {
                opacity: 0 !important;
            }
            <?php
			break;
		
		case 'lcl_bottop_oc' :
			?>
            .lcl_bottop_oc.lcl_pre_show #lcl_overlay,
            .lcl_bottop_oc.lcl_is_closing #lcl_overlay {
                opacity: 0 !important;
            }
            .lcl_bottop_oc.lcl_pre_show #lcl_window {
                transition-property: transform !important;
                opacity: 1 !important;
                
                -webkit-transform: translate3d(0, 100vh, 0);
                transform: translate3d(0, 100vh, 0);
            }
            .lcl_bottop_oc.lcl_is_closing #lcl_window {
                -webkit-transform: translate3d(0, -100vh, 0);
                transform: translate3d(0, -100vh, 0);
            }
            <?php
			break;
		
		case 'lcl_bottop_v2_oc' :
			?>
            .lcl_bottop_v2_oc.lcl_pre_show #lcl_window,
            .lcl_bottop_v2_oc.lcl_is_closing #lcl_window {
                opacity: 0 !important;
                
                -webkit-transition-timing-function: ease;
                transition-timing-function: ease;
            }
            .lcl_bottop_v2_oc.lcl_pre_show #lcl_window {
                top: 40vh;
            }
            .lcl_bottop_v2_oc.lcl_is_closing #lcl_window {
                top: -40vh;
                
                 -webkit-transform: scale(0.8) translateZ(0);
                transform: scale(0.8) translateZ(0);
                
                -webkit-transition-delay: 0s !important; 
                transition-delay: 0s !important;	
            }
            .lcl_bottop_v2_oc.lcl_pre_show #lcl_overlay {
                top: 100vh;	
            }
            .lcl_bottop_v2_oc.lcl_is_closing #lcl_overlay {
                top: -100vh;	
            }
            <?php
			break;
		
		case 'lcl_rtl_oc' :
			?>
            .lcl_rtl_oc.lcl_pre_show #lcl_overlay,
            .lcl_rtl_oc.lcl_is_closing #lcl_overlay {
                opacity: 0 !important;
            }
            .lcl_rtl_oc.lcl_pre_show #lcl_window,
            .lcl_rtl_oc.lcl_is_closing #lcl_window {
             	opacity: 1 !important;
                -webkit-transform: scale(.8) translateZ(0);
                transform: scale(.8) translateZ(0);	
            }
            
            .lcl_rtl_oc.lcl_pre_show #lcl_window {
                left: -100vw;
            }
            .lcl_rtl_oc.lcl_is_closing #lcl_window {
                left: 100vw;  
            }
            .lcl_rtl_oc.lcl_is_closing #lcl_overlay {
            	-webkit-transition-delay: .2s !important; 
                transition-delay: .2s !important;
            }
            <?php
			break;
		
		case 'lcl_horiz_flip_oc' :
			?>
            .lcl_horiz_flip_oc.lcl_pre_show #lcl_overlay,
            .lcl_horiz_flip_oc.lcl_is_closing #lcl_overlay {
                opacity: 0 !important;
            }
            .lcl_horiz_flip_oc.lcl_pre_show #lcl_window,
            .lcl_horiz_flip_oc.lcl_is_closing #lcl_window {
                -webkit-backface-visibility: hidden;
                backface-visibility: hidden;
                
                -webkit-transition-timing-function: ease;
                transition-timing-function: ease;
            }
            .lcl_horiz_flip_oc.lcl_pre_show #lcl_window {
                -webkit-transform: rotateY(90deg) perspective(800px);
                transform: rotateY(90deg) perspective(800px);	
            }
            .lcl_horiz_flip_oc.lcl_is_closing #lcl_window {
                -webkit-transform: rotateY(-90deg) scale(.8) perspective(800px);
                transform: rotateY(-90deg) scale(.8) perspective(800px);	
            }
            .lcl_horiz_flip_oc.lcl_is_closing #lcl_overlay {
            	-webkit-transition-delay: .2s !important; 
                transition-delay: .2s !important;
            }
            <?php
			break;
		
		case 'lcl_vert_flip_oc' :
			?>
            .lcl_vert_flip_oc.lcl_pre_show #lcl_overlay,
            .lcl_vert_flip_oc.lcl_is_closing #lcl_overlay {
                opacity: 0 !important;
            }
            .lcl_vert_flip_oc.lcl_pre_show #lcl_window,
            .lcl_vert_flip_oc.lcl_is_closing #lcl_window {
                -webkit-backface-visibility: hidden;
                backface-visibility: hidden;
                
                -webkit-transition-timing-function: ease;
                transition-timing-function: ease;
            }
            .lcl_vert_flip_oc.lcl_pre_show #lcl_window {
                -webkit-transform: rotateX(-90deg) perspective(1000px);
                transform: rotateX(-90deg) perspective(1000px);	
            }
            .lcl_vert_flip_oc.lcl_is_closing #lcl_window {
                opacity: .5 !important;
                
                -webkit-transform: rotateX(90deg) scale(.6) perspective(1000px);
                transform: rotateX(90deg) scale(.6) perspective(1000px);	
            }
            .lcl_vert_flip_oc.lcl_is_closing #lcl_overlay {
            	-webkit-transition-delay: .2s !important; 
                transition-delay: .2s !important;
            }
            
			<?php
			break;
	}


// lightcase lightbox 
elseif(get_option('ag_lightbox') == 'lightcase') : ?>

/* ************************************************** */

.ag_lc_ol#lightcase-overlay {
	background-color: <?php echo get_option('ag_lb_ol_color', '#111'); ?> !important;
    
    <?php 
	if(get_option('ag_lb_ol_pattern', 'none') != 'none') {
    	echo '
		background-image: url('.AG_URL.'/js/lightboxes/lc-lightbox/img/patterns/'.get_option('ag_lb_ol_pattern', 'none').'.png) !important;
		background-position: top left !important;
		background-repeat: repeat !important;
		';
	} 
	?>
}


<?php // magnific popup 
elseif(get_option('ag_lightbox') == 'mag_popup') : ?>
/* ************************************************** */

.ag_mp .mfp-arrow-left:before, .mfp-arrow-left .mfp-b,
.ag_mp .mfp-arrow-right:before, .mfp-arrow-right .mfp-b  {
	border-color: transparent !important; 
}
.ag_mp.mfp-bg {
	<?php $opacity = ((int)get_option('ag_lb_opacity', 75) == 0) ? 5 : (int)get_option('ag_lb_opacity', 75); ?>
    opacity: <?php echo $opacity / 100 ?>;
    filter: alpha(opacity=<?php echo $opacity ?>);
	background-color: <?php echo get_option('ag_lb_ol_color', '#111'); ?> !important;
    
    <?php 
	if(get_option('ag_lb_ol_pattern', 'none') != 'none') {
    	echo '
		background-image: url('.AG_URL.'/js/lightboxes/lc-lightbox/img/patterns/'.get_option('ag_lb_ol_pattern', 'none').'.png) !important;
		background-position: top left !important;
		background-repeat: repeat !important;
		';
	} 
	?>
}
.ag_mp .mfp-image-holder .mfp-content {
    max-width: <?php echo get_option('ag_lb_max_w', 90) ?>% !important;
}
.ag_mp button:hover, .ag_mp button:active, .ag_mp button:focus {
	background: none !important;
    box-shadow: none !important;
    border: none !important;
    padding: none !important;
}
.ag_mag_popup_loader {
    display: inline-block;
    width: 30px;
    height: 30px;
    background: url(<?php echo AG_URL.'/js/lightboxes/magnific-popup/mp_loader.gif'; ?>) no-repeat center center transparent;
}
.ag_mp .mfp-bottom-bar {
	text-shadow: 1px 1px 0px rgba(0, 0, 0, 0.2);
    margin-top: -40px;   
    padding: 13px;
    background: url(<?php echo AG_URL.'/js/lightboxes/magnific-popup/txt_bg.png'; ?>) repeat center center transparent;
}
.ag_mp .mfp-counter {
    right: 13px;
    top: 13px;
}
.ag_mp .mfp-figure {
	display: none;
}
.ag_mp .mfp-figure small {
    color: #D7D7D7;
    line-height: 18px;
}
.ag_mp .mfp-figure small p {
	display: inline;
}
.ag_mp .mfp-title span:first-child {
	font-size: 13px;
    font-style: italic;
}


<?php // imageLightbox
elseif(get_option('ag_lightbox') == 'imagelb') : ?>
/* ************************************************** */

#imagelightbox-overlay {
	<?php $opacity = ((int)get_option('ag_lb_opacity', 75) == 0) ? 5 : (int)get_option('ag_lb_opacity', 75); ?>
    opacity: <?php echo $opacity / 100 ?>;
    filter: alpha(opacity=<?php echo $opacity ?>);
	background-color: <?php echo get_option('ag_lb_ol_color', '#111'); ?> !important;
    
    <?php 
	if(get_option('ag_lb_ol_pattern', 'none') != 'none') {
    	echo '
		background-image: url('.AG_URL.'/js/lightboxes/lc-lightbox/img/patterns/'.get_option('ag_lb_ol_pattern', 'none').'.png) !important;
		background-position: top left !important;
		background-repeat: repeat !important;
		';
	} 
	?>
}


<?php // Simple Lightbox lightbox 
elseif(get_option('ag_lightbox') == 'simplelb') : ?>
/* ************************************************** */

.ag_simplelb.sl-overlay {
	background-color: <?php echo get_option('ag_lb_ol_color', '#111') ?> !important;
    opacity: <?php echo (int)get_option('ag_lb_opacity', 75) / 100; ?> !important;
    filter: alpha(opacity=<?php echo get_option('ag_lb_opacity', 75); ?>) !important;
    
    <?php 
	if(get_option('ag_lb_ol_pattern', 'none') != 'none') {
    	echo '
		background-image: url('.AG_URL.'/js/lightboxes/lc-lightbox/img/patterns/'.get_option('ag_lb_ol_pattern', 'none').'.png) !important;
		background-position: top left !important;
		background-repeat: repeat !important;
		';
	} 
	?>
}
.ag_simplelb .sl-navigation button{
	font-size: 36px;
}
.ag_simplelb .sl-close {
	font-size: 34px;
}
.ag_simplelb button:hover,
.ag_simplelb button:focus,
.ag_simplelb button:active {
	background: transparent !important;
    border: none !important;
    padding: 0;
}
.ag_simplelb .sl-image {
	border-radius: <?php echo (int)get_option('ag_lb_radius') ?>px;
    overflow: hidden;
    box-shadow: 0 10px 11px rgba(20, 20, 20, 0.25);
}

  /* styles */
  <?php if(get_option('ag_simplelb_style', 'light') == 'light') : ?>
  .ag_simplelb button {
      color: #5a5a5a;
  }
  .ag_simplelb .sl-spinner {
      border-color: #444;
  }
  .ag_simplelb .sl-caption {
      color: #1a1a1a !important;
      background: #fefefe !important;
  }
  <?php else : ?>
  .ag_simplelb button {
      color: #fdfdfd;
  }
  .ag_simplelb .sl-spinner {
      border-color: #fdfdfd;
  }
  .ag_simplelb .sl-caption {
      color: #fff !important;
      background: #0f0f0f !important;
  }
  <?php endif; ?> 
    



<?php // tosrus lightbox 
elseif(get_option('ag_lightbox') == 'tosrus') : ?>
/* ************************************************** */

<?php $bg_color = ag_hex_to_rgb(get_option('ag_lb_ol_color', '#111'), get_option('ag_lb_opacity', 75)); ?>
.ag_tosrus.tos-wrapper {
	background-color: <?php echo $bg_color ?> !important;
}


.tosrus_ie8.tos-wrapper.tos-fixed {
    background: url(<?php echo AG_URL.'/js/lightboxes/jQuery.TosRUs/over_bg_d.png'; ?>) repeat center center transparent !important;
}
.tosrus_ie8 .tos-prev span, .tosrus_ie8 .tos-prev span:before, 
.tosrus_ie8 .tos-next span, .tosrus_ie8 .tos-next span:before,
.tosrus_ie8 .tos-close span, .tosrus_ie8 .tos-close span:before, .tosrus_ie8 .tos-close span:after {
	font-family: 'avatorgallery';
	speak: none;
	font-style: normal;
	font-weight: normal;
	font-variant: normal;
	text-transform: none;
	line-height: 1;
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: grayscale;
    
    color: #fff;
    border: none !important;
}
.tosrus_ie8 .tos-prev span:before {
    margin-left: -3px !important;
	content: "\e608";	
} 
.tosrus_ie8 .tos-next span:before {
    margin-right: -7px;
	content: "\e605";
}
.tosrus_ie8 .tos-close {
	background-image: url(<?php echo AG_URL.'/js/lightboxes/jQuery.TosRUs/close_icon.png'; ?>) !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
}


<?php // Light Gallery lightbox 
elseif(get_option('ag_lightbox') == 'lightgall') : ?>
/* ************************************************** */

.ag_lightgall #lightGallery-close {top: 13px !important;}
#lightGallery-close:after {top: 2px !important;}
#lightGallery-action a#lightGallery-prev:before, #lightGallery-action a#lightGallery-next:after {bottom: 2px !important;}
#lightGallery-action a.cLthumb:after {bottom: 3px !important;}
#lightGallery-Gallery .thumb_cont .thumb_info .close {margin-top: -1px !important;}
#lightGallery-Gallery .thumb_cont .thumb_info .close i:after {top: 2px !important;}

.ag_lightgall .title small {
	font-size: 12px;
    font-style: italic;
    font-weight: normal;
}

<?php $bg_color = ag_hex_to_rgb(get_option('ag_lb_ol_color', '#111'), get_option('ag_lb_opacity', 75)); ?>
.ag_lightgall, .ag_lightgall .info.group {
	background-color: <?php echo $bg_color ?> !important;
}

<?php if((int)get_option('ag_lb_opacity', 75) < 100) { ?>
.lightgall_ie8, .lightgall_ie8 .info.group {
    background: url(<?php echo AG_URL.'/js/lightboxes/jQuery.TosRUs/over_bg_d.png'; ?>) repeat center center transparent !important;
}
<?php } ?>
<?php endif; ?>


<?php 
// custom CSS
echo get_option('ag_custom_css');
