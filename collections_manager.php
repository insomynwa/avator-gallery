<?php 
require_once(AG_DIR . '/functions.php');
?>

<style type="text/css">
#wpbody {overflow: hidden;}
</style>

<div class="wrap lcwp_form">  
	<div class="icon32"><img src="<?php echo AG_URL.'/img/ag_logo.png'; ?>" alt="avatorgallery" /><br/></div>
    <?php echo '<h2 class="lcwp_page_title" style="border: none;">' . __('Collections Manager', 'ag_ml') . '</h2>'; ?>  

    <div id="poststuff" class="metabox-holder has-right-sidebar" style="overflow: hidden;">
    	
        <?php // SIDEBAR ?>
        <div id="side-info-column" class="inner-sidebar">
          <form class="form-wrap">	
           
            <div id="add_coll_box" class="postbox lcwp_sidebox_meta">
            	<h3 class="hndle"><?php _e('Add Collection', 'ag_ml') ?></h3> 
				<div class="inside">
                  <div class="misc-pub-section-last">
					<label><?php _e('Collection Name', 'ag_ml') ?></label>
                	<input type="text" name="ag_coll_name" value="" id="add_coll" maxlenght="100" style="width: 180px;" />
                    <input type="button" name="add_coll_btn" id="add_coll_btn" value="<?php _e('Add', 'ag_ml') ?>" class="button-primary" style="margin-left: 5px;" />
                  </div>  
                </div>
            </div>
            
            <div id="man_coll_box" class="postbox lcwp_sidebox_meta">
            	<h3 class="hndle"><?php _e('Collections List', 'ag_ml') ?></h3> 
				<div class="inside"></div>
            </div>
            
            <div id="save_coll_box" class="postbox lcwp_sidebox_meta" style="display: none; background: none; border: none; box-shadow: none;">
            	<input type="button" name="save-coll" value="<?php _e('Save Collection', 'ag_ml') ?>" class="button-primary" />
                
                <?php if(get_option('ag_preview_pag')) : ?>
                <input type="button" name="preview-coll" value="<?php _e('Preview', 'ag_ml') ?>" baseurl="<?php echo get_permalink(get_option('ag_preview_pag')) ?>?ag_cid=" class="button-secondary" />
                <?php endif; ?>
				
                <div style="width: 30px; padding: 0 0 0 7px; float: right;"></div>
            </div>
          </form>	
            
        </div>
    	
        <?php // PAGE CONTENT ?>
        <form class="form-wrap" id="coll_items_list">  
          <div id="post-body">
          <div id="post-body-content" class="ag_coll_content">
              <p><?php _e('Select a collection', 'ag_ml') ?> ..</p>
          </div>
          </div>
        </form>
        
        <br class="clear">
    </div>
    
</div>  

<?php // SCRIPTS ?>
<script src="<?php echo AG_URL; ?>/js/functions.js" type="text/javascript"></script>
<script src="<?php echo AG_URL; ?>/js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
<script src="<?php echo AG_URL; ?>/js/lc-switch/lc_switch.min.js" type="text/javascript"></script>

<script type="text/javascript" charset="utf8" >
jQuery(document).ready(function($) {

	// selected coll var
	ag_sel_coll = 0;
	ag_coll_pag = 1;
	
	ag_load_colls();
	
	
	/////////////////////////////////////////////////////////////
	
	
	// custom main image - media image  manager 
	var file_frame = false;
	
	jQuery(document).delegate('.ag_coll_cust_img_btn', 'click', function(e) {
		if(jQuery(e.target).hasClass('ag_coll_del_cust_img_btn')) {return false;}
		
		var $wrap = jQuery(this).parents('td');
		var $btn = jQuery(this);
		
		// Create the media frame
		file_frame = wp.media.frames.file_frame = wp.media({
		  title: "Avator Gallery - <?php _e("custom gallery's main image", 'ag_ml') ?>",
		  button: {
			text: "<?php _e('Select') ?>",
		  },
		  library : {type : 'image'},
		  multiple: false
		});
	
		// When an image is selected, run a callback.
		file_frame.on('select', function() {
			var img_data = file_frame.state().get('selection').first().toJSON();
			
			$wrap.find('.ag_coll_cust_img').val(img_data.id);
			$wrap.css('background-image', 'url('+ img_data.url +')');
			$btn.addClass('ag_coll_cust_img_sel');
		});

		file_frame.open();
	});
	
	
	// custom main image removal
	jQuery(document).delegate('.ag_coll_del_cust_img_btn', 'click', function() {
		var $wrap = jQuery(this).parents('td');
		var $btn = jQuery(this).parents('.ag_coll_cust_img_btn');
		
		if(confirm("<?php echo addslashes(__("Remove custom gallery's main image?", 'ag_ml')) ?>")) {
			$wrap.find('.ag_coll_cust_img').val('');
			$btn.removeClass('ag_coll_cust_img_sel');
			$wrap.css('background-image', 'url('+ jQuery(this).attr('orig-img') +')');
		}
		
		return false;
	});
	
	
	/////////////////////////////////////////////////////////////
	

	// galleries search
	jQuery('body').on('keyup', "#ag_coll_gall_search", function() {
		if(typeof(ag_cgs_acting) != 'undefined') {clearTimeout(ag_cgs_acting);}
		ag_cgs_acting = setTimeout(function() {
			
			var src_string = jQuery.trim( jQuery("#ag_coll_gall_search").val() );
			src_string = src_string.replace(',', '').replace('.', '').replace('?', ''); 
			
			if(src_string.length > 2) {
				jQuery('.ag_cgs_del').fadeIn(200);
				
				var src_arr = src_string.split(' ');
				var matching = jQuery.makeArray();

				// cyle and check eac searched term 
				jQuery('#ag_coll_gall_picker li').each(function(i, elem) {
					jQuery.each(src_arr, function(i, word) {						
						
						if( jQuery(elem).find('div').attr('title').indexOf(word) !== -1 ) {
							jQuery(elem).show();
						} else {
							jQuery(elem).hide();
						}
					});
				});
			}
			else {
				jQuery('.ag_cgs_del').fadeOut(200);
				jQuery('#ag_coll_gall_picker li').show();
			}
		}, 300);
	});
	
	jQuery('body').on('click', '.ag_cgs_mag', function() {
		jQuery("#ag_coll_gall_search").trigger('keyup');
	});
	
	jQuery('body').on('click', '.ag_cgs_del', function() {
		jQuery("#ag_coll_gall_search").val('').trigger('keyup');
	});
	
	
	// galleries picker - expand/compress
	jQuery('body').delegate('.ag_cgs_show_all', "click", function() {	
		if(jQuery(this).hasClass('shown')) {
			jQuery(this).removeClass('shown').text("(<?php _e('expand', 'ag_ml') ?>)");
			
			jQuery('#ag_coll_gall_picker').css('max-height', '113px');
		}
		else {
			jQuery(this).addClass('shown').text("(<?php _e('collapse', 'ag_ml') ?>)");
			jQuery('#ag_coll_gall_picker').css('max-height', 'none');	
		}
	});
	
	
	
	////////////////////////////////////////////////////////////
	
	
	
	// galleries cat choose
	jQuery('body').delegate('#ag_gall_cats', "change", function() {
		var item_cats = jQuery(this).val();	
		var data = {
			action: 'ag_cat_galleries_code',
			gallery_cat: item_cats
		};
		
		jQuery('.ag_dd_galls_preview').remove();
		jQuery('#ag_coll_gall_picker').html('<div style="height: 30px; width: 30px;" class="lcwp_loading"></div>');
		
		jQuery.post(ajaxurl, data, function(response) {
			if(jQuery.trim(response)) {
				jQuery('#ag_coll_gall_picker').html(response);
			}
			else {
				jQuery('#ag_coll_gall_picker').html('<span><?php echo ag_sanitize_input( __('No galleries found', 'ag_ml')) ?> ..</span>');
			}
		});	
	});
	
	
	// add gallery
	jQuery('body').delegate('#ag_coll_gall_picker li', "click", function() {
		var gall_id = jQuery(this).attr('rel');	
		var gall_title = jQuery(this).find('div').text();	
		var gall_cats = jQuery.trim(jQuery(this).attr('ag-cats'));
		var gall_img = jQuery(this).attr('ag-img');
		
		// check for already existing galleries
		if(jQuery('#ag_coll_builder tr#ag_coll_'+gall_id).length) {
			ag_toast_message('error', gall_title +" - <?php echo ag_sanitize_input( __('gallery already in collection', 'ag_ml')) ?>");
		}
		else {
			if(!jQuery('#ag_coll_builder .ag_coll_gall_imgbox').length) {jQuery('#ag_coll_builder > tbody').empty();}
			
			gall_cats = (gall_cats) ? '<em class="dashicons dashicons-tag" title="<?php echo addslashes(__('Categories', 'ag_ml')) ?>" style="padding-right: 3px; font-size: 16px; line-height: 23px;"></em> '+gall_cats : '<em><?php echo esc_attr(__('No associated categories', 'ag_ml')) ?> ..</em>';
  
			
			jQuery('#ag_coll_builder > tbody').append(
			'<tr class="coll_component" id="ag_coll_'+gall_id+'">'+
			  '<td class="ag_coll_gall_imgbox" style="width: 230px; vertical-align: top; background-image: url('+ gall_img +');">'+
				  '<div class="lcwp_del_row ag_del_gall"></div>'+
				  '<div class="lcwp_move_row"></div>'+
				  '<div class="ag_coll_cust_img_btn" title="<?php echo addslashes(__('Manage custom main image', 'ag_ml')) ?>">'+
				  		
					  '<i class="fa fa-camera" aria-hidden="true"></i>'+
					  '<input type="hidden" name="ag_coll_cust_img" value="" class="ag_coll_cust_img" />'+
						
					  '<div class="ag_coll_del_cust_img_btn" title="<?php echo addslashes(__('Remove custom main image', 'ag_ml')) ?>" orig-img="'+ gall_img +'">'+
						  '<i class="fa fa-camera" aria-hidden="true"></i>'+
					  '</div>'+
				  '</div>'+

				  '<div class="ag_coll_gall_cats">'+
					  '<span>'+ gall_cats +'</span>'+
				  '</div>'+
			  '</td>'+
			  '<td class="ag_coll_gall_inner" style="vertical-align: top;">'+
				  '<div>'+
					  '<h2><a href="<?php echo get_admin_url() ?>post.php?post='+gall_id+'&action=edit" target="_blank" title="<?php echo addslashes(__('edit gallery', 'ag_ml')) ?>">'+ gall_title +'</a></h2><br/>'+

					  '<div style="width: 12.3%; margin-right: 4%;">'+
						  '<p><?php echo esc_attr(__('Random display?', 'ag_ml')) ?></p>'+
						  '<input type="checkbox" name="random" class="ip-checkbox" value="1" />'+
					  '</div>'+
					  '<div style="width: 12.3%; margin-right: 4%;">'+
						  '<p><?php echo esc_attr(__('Use tags filter?', 'ag_ml')) ?></p>'+
						  '<input type="checkbox" name="tags_filter" class="ip-checkbox" value="1" />'+
					  '</div>'+
					  '<div style="width: 12.3%; margin-right: 4%;">'+
						  '<p><?php echo esc_attr(__('Use watermark?', 'ag_ml')) ?></p>'+
						  '<input type="checkbox" name="watermark" class="ip-checkbox" value="1" />'+
					  '</div>'+
					  ' <div style="width: 50%;">'+
						  '<p><?php echo esc_attr(__('Image link', 'ag_ml')) ?></p>'+
						  '<select name="ag_linking_dd" class="ag_linking_dd">'+
							  '<option value="none"><?php echo esc_attr(__('No link', 'ag_ml')) ?></option>'+
							  '<option value="page"><?php echo esc_attr(__('To a page', 'ag_ml')) ?></option>'+
							  '<option value="custom"><?php echo esc_attr(__('Custom link', 'ag_ml')) ?></option>'+
						  '</select>'+
						  '<div class="ag_link_wrap"></div>'+
					  '</div>'+
					  '<div>'+
						  '<textarea name="coll_descr" class="coll_descr" placeholder="<?php echo addslashes(__('Gallery description - supports %IMG-NUM% placeholder', 'ag_ml')) ?>"></textarea>'+
					  '</div>'+
				  '</div>'+
			  '</td>'+
			'</tr>');
			
			ag_live_ip_checks();
		}
	});
	
	
	// coll images linking management
	jQuery(document).delegate('select.ag_linking_dd', 'change', function() {
		var link_opt = jQuery(this).val();
		
		if(link_opt == 'page') {
			var link_field = '<?php echo str_replace("'", "\'", ag_link_field('page')); ?>';
		}
		else if(link_opt == 'custom') {
			var link_field = '<?php echo ag_link_field('custom'); ?>';
		}
		else {
			var link_field = '<?php echo ag_link_field('none'); ?>';	
		}
		
		jQuery(this).parent().find('.ag_link_wrap').html(link_field);
	});

	
	// remove collection's gallery
	jQuery('body').delegate('.ag_del_gall', "click", function() {
		
		if(confirm("<?php echo esc_attr(__("Do you really want to remove this gallery?", 'ag_ml')) ?>")) {
			jQuery(this).parents('.coll_component').fadeOut(function() {
				
				jQuery(this).remove();
				if(jQuery('#ag_coll_builder img').size() == 0) {
					jQuery('#ag_coll_builder tbody').append('<tr><td colspan="5">No galleries selected ..</td></tr>');		
				}
			});
		}
	});
	
	
	// save collection
	jQuery('body').delegate('#save_coll_box input', 'click', function() {
		var gall_list 	= [];
		var cust_img	= [];
		var random_flag = [];
		var filters_flag= [];
		var wmark_flag 	= [];
		var link_subj 	= [];
		var link_val 	= [];
		var coll_descr 	= [];
		
		// catch data
		jQuery('#ag_coll_builder tr.coll_component').each(function() {
			var gid = jQuery(this).attr('id').substr(8);
           
		    gall_list.push(gid);
			cust_img.push( jQuery(this).find('.ag_coll_cust_img').val() )
			coll_descr.push( jQuery(this).find('.coll_descr').val() );
			
			var rand = (jQuery(this).find('input[name=random]').is(':checked')) ? 1 : 0; 
			random_flag.push(rand);
			
			var filters = (jQuery(this).find('input[name=tags_filter]').is(':checked')) ? 1 : 0; 
			filters_flag.push(filters);
			
			var wmark = (jQuery(this).find('input[name=watermark]').is(':checked')) ? 1 : 0; 
			wmark_flag.push(wmark);
			
			link_subj.push( jQuery(this).find('.ag_linking_dd').val() );
			link_val.push( (jQuery(this).find('.ag_linking_dd').val() != 'none') ? jQuery(this).find('.ag_link_field').val() : '' );
        });
		
		// ajax
		var data = {
			action: 		'ag_save_coll',
			coll_id: 		ag_sel_coll,
			gall_list: 		gall_list,
			cust_img:		cust_img,
			random_flag: 	random_flag,
			filters_flag: 	filters_flag,
			wmark_flag:		wmark_flag,
			link_subj: 		link_subj,
			link_val:		link_val,
			coll_descr:		coll_descr
		};
		
		jQuery('#save_coll_box div').html('<div style="height: 30px;" class="lcwp_loading"></div>');
		
		jQuery.post(ajaxurl, data, function(response) {
			var resp = jQuery.trim(response); 			
			jQuery('#save_coll_box div').empty();
			
			if(resp == 'success') {
				ag_toast_message('success', "<?php echo ag_sanitize_input( __('Collection saved', 'ag_ml')) ?>");
			} else {
				ag_toast_message('error', resp);
			}
		});	
	});
	
	
	// select the collection
	jQuery('body').delegate('#man_coll_box input[type=radio]', 'click', function() {
		ag_sel_coll = parseInt(jQuery(this).val());
		var coll_title = jQuery(this).parent().siblings('.ag_coll_tit').text();

		jQuery('.ag_coll_content').html('<div style="height: 30px;" class="lcwp_loading"></div>');

		var data = {
			action: 'ag_coll_builder',
			coll_id: ag_sel_coll 
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('.ag_coll_content').html(response);
			
			// add the title
			jQuery('.ag_coll_content > h2').html(coll_title);
			
			// save coll box
			jQuery('#save_coll_box').fadeIn();
			
			ag_live_chosen();
			ag_live_ip_checks();
			ag_live_sort();
			
			// overflow fix
			jQuery('#poststuff').css('overflow', 'visible');
		});	
	});
	
	
	// add collection
	jQuery('#add_coll_btn').click(function() {
		var coll_name = jQuery('#add_coll').val();
		
		if( jQuery.trim(coll_name) != '' ) {
			var data = {
				action: 'ag_add_coll',
				coll_name: coll_name
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				var resp = jQuery.trim(response); 
				
				if(resp == 'success') {
					ag_toast_message('success', "<?php echo ag_sanitize_input( __('Collection added', 'ag_ml')) ?>");
					jQuery('#add_coll').val('');
					
					ag_coll_pag = 1;
					ag_load_colls();
				}
				else {
					ag_toast_message('error', resp);
				}
			});	
		}
	});
	
	
	// manage colls pagination
	// prev
	jQuery('body').delegate('#ag_prev_colls', 'click', function() {
		ag_coll_pag = ag_coll_pag - 1;
		ag_load_colls();
	});
	// next
	jQuery('body').delegate('#ag_next_colls', 'click', function() {
		ag_coll_pag = ag_coll_pag + 1;
		ag_load_colls();
	});
	
	
	// load collection list
	function ag_load_colls() {
		jQuery('#man_coll_box .inside').html('<div style="height: 30px;" class="lcwp_loading"></div>');
		
		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			data: "action=ag_get_colls&coll_page="+ag_coll_pag,
			dataType: "json",
			success: function(response){	
				jQuery('#man_coll_box .inside').empty();
				
				// get elements
				ag_coll_pag = response.pag;
				var ag_coll_tot_pag = response.tot_pag;
				var ag_colls = response.colls;	

				var a = 0;
				jQuery.each(ag_colls, function(k, v) {	
					if( ag_sel_coll == v.id) {var sel = 'checked="checked"';}
					else {var sel = '';}
				
					jQuery('#man_coll_box .inside').append('<div class="misc-pub-section-last">\
						<span><input type="radio" name="gl" value="'+ v.id +'" '+ sel +' /></span>\
						<span class="ag_coll_tit" style="padding-left: 7px;" title="Collection #'+ v.id +'">'+ v.name +'</span>\
						<span class="ag_del_coll" id="gdel_'+ v.id +'"></span>\
					</div>');
					
					a = a + 1;
				});
				
				if(a == 0) {
					jQuery('#man_coll_box .inside').html('<p>No existing collections</p>');
					jQuery('#man_coll_box h3.hndle').html('Collections List');
				}
				else {
					// manage pagination elements
					jQuery('#man_coll_box h3.hndle').html('<?php echo ag_sanitize_input( __('Collections List', 'ag_ml')) ?> <small>(pag '+ag_coll_pag+' of '+ag_coll_tot_pag+')</small>'+
					'<span id="ag_next_colls">&raquo;</span><span id="ag_prev_colls">&laquo;</span>');
					
					
					// different cases
					if(ag_coll_pag <= 1) { jQuery('#ag_prev_colls').hide(); }
					if(ag_coll_pag >= ag_coll_tot_pag) {jQuery('#ag_next_colls').hide();}	
				}
			}
		});	
	}
	
	
	// delete collection
	jQuery('body').delegate('.ag_del_coll', 'click', function() {
		$target_coll_wrap = jQuery(this).parent(); 
		var coll_id  = jQuery(this).attr('id').substr(5);
		
		if(confirm('<?php echo ag_sanitize_input( __('Definitively delete collection?', 'ag_ml')) ?>')) {
			var data = {
				action: 'ag_del_coll',
				coll_id: coll_id
			};
			
			jQuery.post(ajaxurl, data, function(response) {
				var resp = jQuery.trim(response); 
				
				if(resp == 'success') {
					// if is this one opened
					if(ag_sel_coll == coll_id) {
						jQuery('.ag_coll_content').html('<p><?php echo ag_sanitize_input( __('Select a collection', 'ag_ml')) ?> ..</p>');
						ag_sel_coll = 0;
						
						// savecoll box
						jQuery('#save_coll_box').fadeOut();
					}
					
					$target_coll_wrap.slideUp(function() {
						jQuery(this).remove();
						
						if( jQuery('#man_coll_box .inside .misc-pub-section-last').size() == 0) {
							jQuery('#man_coll_box .inside').html('<p><?php echo ag_sanitize_input( __('No existing collections', 'ag_ml')) ?></p>');
						}
					});	
				}
				else {alert(resp);}
			});
		}
	});
	
	

	
	<!-- ######### UTILITIES ######### -->


	// collection preview
	jQuery(document).delegate('input[name=preview-coll]', 'click', function(e) {
		var url = jQuery(this).attr('baseurl') + ag_sel_coll;
		window.open(url, '_blank', null);
	});
	


	// keep sidebar visible
	jQuery(window).scroll(function() {
		var $subj = jQuery('#side-info-column');
		
		if($subj.find('.postbox').length) {
			var side_h = $subj.outerHeight();
			var top_pos = $subj.parent().offset().top;
			var top_scroll = jQuery(window).scrollTop();
			
			// if is higher that window - ignore
			if((top_pos + side_h + 44) >= jQuery(window).height() || top_scroll <= top_pos) {
				$subj.css('margin-top', 0);	
			}
			else {
				$subj.css('margin-top', (top_scroll - top_pos + 44)); 	
			}	
		}
		else {
			$subj.css('margin-top', 0);	
		}
	});

	
	// sort opt
	function ag_live_sort() {
		jQuery('#ag_coll_builder').children('tbody').sortable({ 
			handle: '.lcwp_move_row',
			items: "> tr",
			axis: "y",
			placeholder: "ag-coll-sort-placeholder"
		});
		jQuery('#ag_coll_builder').find('.lcwp_move_row').disableSelection();
	}
	
	// init chosen for live elements
	function ag_live_chosen() {
		jQuery('.lcweb-chosen').each(function() {
			var w = jQuery(this).css('width');
			jQuery(this).chosen({width: w}); 
		});
		jQuery(".lcweb-chosen-deselect").chosen({allow_single_deselect:true});
	}
	
	// dynamic lcweb switch
	function ag_live_ip_checks() {
		jQuery('.ip-checkbox').lc_switch('YES', 'NO');
	}
	
	
	// toast message for ajax operations
	ag_toast_message = function(type, text) {
		if(!jQuery('#lc_toast_mess').length) {
			jQuery('body').append('<div id="lc_toast_mess"></div>');
			
			jQuery('head').append(
			'<style type="text/css">' +
			'#lc_toast_mess,#lc_toast_mess *{-moz-box-sizing:border-box;box-sizing:border-box}#lc_toast_mess{background:rgba(20,20,20,.2);position:fixed;top:0;right:-9999px;width:100%;height:100%;margin:auto;z-index:99999;opacity:0;filter:alpha(opacity=0);-webkit-transition:opacity .15s ease-in-out .05s,right 0s linear .5s;-ms-transition:opacity .15s ease-in-out .05s,right 0s linear .5s;transition:opacity .15s ease-in-out .05s,right 0s linear .5s}#lc_toast_mess.lc_tm_shown{opacity:1;filter:alpha(opacity=100);right:0;-webkit-transition:opacity .3s ease-in-out 0s,right 0s linear 0s;-ms-transition:opacity .3s ease-in-out 0s,right 0s linear 0s;transition:opacity .3s ease-in-out 0s,right 0s linear 0s}#lc_toast_mess:before{content:"";display:inline-block;height:100%;vertical-align:middle}#lc_toast_mess>div{position:relative;padding:13px 16px!important;border-radius:2px;box-shadow:0 2px 17px rgba(20,20,20,.25);display:inline-block;width:310px;margin:0 0 0 50%!important;left:-155px;top:-13px;-webkit-transition:top .2s linear 0s;-ms-transition:top .2s linear 0s;transition:top .2s linear 0s}#lc_toast_mess.lc_tm_shown>div{top:0;-webkit-transition:top .15s linear .1s;-ms-transition:top .15s linear .1s;transition:top .15s linear .1s}#lc_toast_mess>div>span:after{font-family:dashicons;background:#fff;border-radius:50%;color:#d1d1d1;content:"";cursor:pointer;font-size:23px;height:15px;padding:5px 9px 7px 2px;position:absolute;right:-7px;top:-7px;width:15px}#lc_toast_mess>div:hover>span:after{color:#bbb}#lc_toast_mess .lc_error{background:#fff;border-left:4px solid #dd3d36}#lc_toast_mess .lc_success{background:#fff;border-left:4px solid #7ad03a}' +
			'</style>');	
			
			// close toast message
			jQuery(document.body).off('click tap', '#lc_toast_mess');
			jQuery(document.body).on('click tap', '#lc_toast_mess', function() {
				jQuery('#lc_toast_mess').removeClass('lc_tm_shown');
			});
		}
		
		// setup
		if(type == 'error') {
			jQuery('#lc_toast_mess').empty().html('<div class="lc_error"><p>'+ text +'</p><span></span></div>');	
		} else {
			jQuery('#lc_toast_mess').empty().html('<div class="lc_success"><p>'+ text +'</p><span></span></div>');	
			
			setTimeout(function() {
				jQuery('#lc_toast_mess.lc_tm_shown span').trigger('click');
			}, 2150);	
		}
		
		// use a micro delay to let CSS animations act
		setTimeout(function() {
			jQuery('#lc_toast_mess').addClass('lc_tm_shown');
		}, 30);	
	}
	
});
</script>