<?php
// loader class in footer
function ag_loader_class() {
	?>
    <script type="text/javascript">
    if(	navigator.appVersion.indexOf("MSIE 8.") != -1 || navigator.appVersion.indexOf("MSIE 9.") != -1 ) {
		document.body.className += ' ag_old_loader';
	} else {
		document.body.className += ' ag_new_loader';
	}
	</script>
    <?php	
}
add_action('wp_footer', 'ag_loader_class', 1);




// FLAGS - as first head element
function ag_head_js_flags() {
	// galleries / collections flags 
	?>
	<script type="text/javascript">
	ag_rtl = <?php echo (get_option('ag_rtl_mode') == '1') ? 'true' : 'false'; ?>;
	ag_columnized_max_w = <?php echo (int)get_option('ag_cl_thumb_max_w', 260) ?>;
	ag_masonry_min_w = <?php echo (int)get_option('ag_masonry_min_width', 150) ?>;
	ag_phosostr_min_w = <?php echo get_option('ag_photostring_min_width', 120) ?>; 
	ag_coll_max_w = <?php echo (int)get_option('ag_coll_thumb_max_w', 260) ?>;
	
	ag_preload_hires_img 	= <?php echo (get_option('ag_preload_hires_img') == '1') ? 'true' : 'false'; ?>; 
	ag_use_deeplink 		= <?php echo (get_option('ag_disable_dl') == '1') ? 'false' : 'true'; ?>;
	ag_monopage_filter 		= <?php echo (get_option('ag_monopage_filter') == '1') ? 'true' : 'false'; ?>;
	ag_back_to_gall_scroll 	= <?php echo (get_option('ag_coll_back_to_scroll') == '1') ? 'true' : 'false'; ?>;

	<?php
	$fx = get_option('ag_slider_fx', 'fadeslide');
	$fx_time = get_option('ag_slider_fx_time', 400);
	$crop = get_option('ag_slider_crop', 'true');
	$delayed_fx = (get_option('ag_delayed_fx')) ? 'false' : 'true';
	?>
	// global vars
	ag_galleria_toggle_info = <?php echo (get_option('ag_slider_tgl_info')) ? 'true' : 'false'; ?>;
	ag_galleria_fx = '<?php echo $fx ?>';
	ag_galleria_fx_time = <?php echo $fx_time ?>; 
	ag_galleria_img_crop = <?php echo ($crop=='true' || $crop=='false') ? $crop : '"'.$crop.'"' ?>;
	ag_galleria_autoplay = <?php echo (get_option('ag_slider_autoplay')) ? 'true' : 'false'; ?>;
	ag_galleria_interval = <?php echo get_option('ag_slider_interval', 3000) ?>;
	ag_delayed_fx = <?php echo $delayed_fx ?>;
	</script>
    <?php
}
add_action('wp_head', 'ag_head_js_flags', 5); 



// TEMP LOADER / RIGHT CLICK / LCL FB comments - in head 
function ag_head_js_elements() {
    // linked images function ?>
	<script type="text/javascript">
	jQuery(document).delegate('.ag_linked_img', 'click', function() {
		var link = jQuery(this).data('ag-link');
		window.open(link ,'<?php echo get_option('ag_link_target', '_top') ?>');
	});
	</script>
	
	<?php
	// if prevent right click
	if(get_option('ag_disable_rclick')) {
		?>
        <script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('body').delegate('.ag_gallery_wrap *, .ag_galleria_slider_wrap *, #lcl_wrapper *', "contextmenu", function(e) {
                e.preventDefault();
            });
		});
		</script>
        <?php	
	}
	
	// LC LIGHTBOX - FACEBOOK COMMENTS
	if(get_option('ag_lightbox', 'lcweb') == 'lcweb' && get_option('ag_lb_lcl_comments') == 'fb') {
		?>
        <meta property="fb:app_id" content="<?php echo get_option('ag_lcl_fb_appid') ?>" />
        <?php	
	}
}
add_action('wp_head', 'ag_head_js_elements', 999);



// SLIDER INIT / EDIT GALLERY HELPER / LCL facebook  - in footer 
function ag_footer_js_elements() {
	?>
	<script type="text/javascript">
	<?php
	// logged users helper - direct link to edit items
	if(current_user_can('edit_posts'))  : ?>
	jQuery(document).ready(function() {
		jQuery(document).delegate('.ag_galleria_slider_wrap, .ag_gallery_wrap:not(.ag_collection_wrap)', 'mouseenter', function() {  //==>> mouseenter mouseleave implementation
			var gid = jQuery(this).attr('rel');
			if(jQuery('#ag_quick_edit_btn.agqeb_'+gid).length) {return false;}
			
			if(typeof(ag_remove_qeb) != 'undefined') {clearTimeout(ag_remove_qeb);}
			if(jQuery('#ag_quick_edit_btn').length) {jQuery('#ag_quick_edit_btn').remove();}
			
			var item_pos = jQuery(this).offset();
			var item_padding = parseInt( jQuery(this).css('padding-top'));
			var css_pos = 'style="top: '+ (item_pos.top + item_padding) +'px; left: '+ (item_pos.left + item_padding) +'px;"';
			
			var link = "<?php echo admin_url() ?>post.php?post="+ gid +"&action=edit";
			var icon = '<i class="fa fa-pencil" aria-hidden="true"></i>';
			
			jQuery('body').append('<a id="ag_quick_edit_btn" class="agqeb_'+gid+'" href="'+ link +'" target="_blank" title="<?php _e('edit', 'ag_ml') ?>" '+css_pos+'>'+ icon +'</>');		
		});
		jQuery(document).delegate('.ag_galleria_slider_wrap, .ag_gallery_wrap', 'mouseleave', function() {
			if(typeof(ag_remove_qeb) != 'undefined') {clearTimeout(ag_remove_qeb);}
			ag_remove_qeb = setTimeout(function() {
				if(jQuery('#ag_quick_edit_btn').length) {jQuery('#ag_quick_edit_btn').remove();}
			}, 700);
		});
	});
	<?php endif; ?>
	</script>
	<?php
	
	
	// LC LIGHTBOX - FACEBOOK COMMENTS
	if(get_option('ag_lightbox', 'lcweb') == 'lcweb' && (get_option('ag_lb_lcl_comments') == 'fb' || get_option('ag_lb_lcl_direct_fb'))) {
		?>
        <div id="fb-root"></div>
        
		<script type="text/javascript">
		(function(d, s, id) {
		    var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "https://connect.facebook.net/<?php echo get_locale() ?>/sdk.js#xfbml=1&version=v2.12&appId=<?php echo get_option('ag_lcl_fb_appid') ?>";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
        </script>
        <?php
	}
}
add_action('wp_footer', 'ag_footer_js_elements', 999);




// right click - CSS code for iphone in head
function ag_head_elements() {
	if(get_option('ag_disable_rclick')) {
		?>
        <style type="text/css">
		.ag_gallery_wrap *, .ag_galleria_slider_wrap *, #lcl_wrapper * {
			-webkit-touch-callout: none; 
			-webkit-user-select: none;
		}
		</style>
        <?php	
	}
}
add_action('wp_head', 'ag_head_elements', 999);
