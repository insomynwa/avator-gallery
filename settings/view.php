<?php 
include_once(AG_DIR . '/functions.php');
$ml_key = 'ag_ml';


// framework engine
include_once(AG_DIR . '/classes/simple_form_validator.php');
include_once(AG_DIR . '/settings/settings_engine.php'); 
include_once(AG_DIR . '/settings/field_options.php'); 
include_once(AG_DIR . '/settings/custom_fields.php');
include_once(AG_DIR . '/settings/structure.php'); 
?>

<div class="wrap lcwp_settings_wrap" style="direction: LTR; overflow-x: hidden;">  
	<div class="icon32"><img src="<?php echo AG_URL.'/img/ag_logo.png'; ?>" alt="avatorgallery" /><br/></div>
    <h2 class="ag_page_title"><?php _e('Avator Gallery Settings', $ml_key) ?></h2>  
    
	<?php
    $engine = new ag_settings_engine('ag_settings', $GLOBALS['ag_settings_tabs'], $GLOBALS['ag_settings_structure']);
    
    // get fetched data and allow customizations
    if($engine->form_submitted()) {
        $fdata = $engine->form_data;
        $errors = (!empty($engine->errors)) ? $engine->errors : array();


        // AG-FILTER - manipulate setting errors - passes errors array and form values - error subject as index + error text as val
        $errors = apply_filters('ag_setting_errors', $errors, $fdata);	
        
        
        // save or print error
        if(empty($errors)) {
            
            // AG-FILTER - allow data manipulation (or custom actions) before settings save - passes form values
            $engine->form_data = apply_filters('ag_before_save_settings', $fdata); 
            
            
            // save
            $engine->save_data();

            // create custom style css file
          	if(!get_option('ag_inline_css')) {
				if(!ag_create_frontend_css()) {
					update_option('ag_inline_css', 1);	
					echo '<div class="updated"><p>'. __('An error occurred during dynamic CSS creation. The code will be used inline anyway', $ml_key) .'</p></div>';
					$noredirect = true;
				}
				else {delete_option('ag_inline_css');}
			}	   
			
			
			// refresh to allow saved values to be spread in structure
			if(!isset($noredirect)) {
				ob_end_clean(); // avoid issues with previously printed code
				wp_redirect( str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) . '&lcwp_sf_success');
				
  				exit;
			}
        }
        
        // compose and return errors
        else {
            $err_elems = array();
            foreach($errors as $i => $v) {
                if(is_numeric($i)) {
                    $err_elems[] = $v;	
                }
                else {
                    $err_elems[] = $i .' - '. $v;	
                }
            }
            
            echo '<div class="error lcwp_settings_result"><p>'. implode(' <br/> ', $err_elems) .'</p></div>';	
        }
    }
	
	
	// if successdully saved
	if(isset($_GET['lcwp_sf_success'])) {
		echo '<div class="updated lcwp_settings_result" style="display: none;"><p><strong>'. __('Options saved', $ml_key) .'</strong></p></div>';	
	}
	
	// print form code
    echo $engine->get_code();
    ?>
</div>



<?php // CUSTOM CSS ?>
<style type="text/css">
#item_atts_wrap {
	display: none;	
}
</style>



<?php // SCRIPTS ?>
<script src="<?php echo AG_URL; ?>/js/lc-switch/lc_switch.min.js" type="text/javascript"></script>
<script src="<?php echo AG_URL; ?>/js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
<script src="<?php echo AG_URL; ?>/js/colpick/js/colpick.min.js" type="text/javascript"></script>

<link rel="stylesheet" href="<?php echo AG_URL; ?>/js/codemirror/codemirror.css">
<script src="<?php echo AG_URL; ?>/js/codemirror/codemirror.min.js" type="text/javascript"></script>
<script src="<?php echo AG_URL; ?>/js/codemirror/languages/css.min.js" type="text/javascript"></script>

<script type="text/javascript" charset="utf8">
// codemirror - execute before tabbing
jQuery('.lcwp_sf_code_editor').each(function() {
	CodeMirror.fromTextArea( jQuery(this)[0] , {
		lineNumbers: true,
		mode: "css"
	});
});


jQuery(document).ready(function($) {
	var lcwp_nonce = '<?php echo wp_create_nonce('lcwp_ajax') ?>';
	
	
	// replacing jQuery UI tabs 
	jQuery('.lcwp_settings_tabs').each(function() {
    	var sel = '';
		var hash = window.location.hash;
		
		var $form = jQuery(".lcwp_settings_form");
		var form_act = $form.attr('action');

		// track URL on opening
		if(hash && jQuery(this).find('.nav-tab[href='+ hash +']').length) {
			jQuery(this).find('.nav-tab').removeClass('nav-tab-active');
			jQuery(this).find('.nav-tab[href='+ hash +']').addClass('nav-tab-active');	
			
			$form.attr('action', form_act + hash);
		}
		
		// if no active - set first as active
		if(!jQuery(this).find('.nav-tab-active').length) {
			jQuery(this).find('.nav-tab').first().addClass('nav-tab-active');	
		}
		
		// hide unselected
		jQuery(this).find('.nav-tab').each(function() {
            var id = jQuery(this).attr('href');
			
			if(jQuery(this).hasClass('nav-tab-active')) {
				sel = id
			}
			else {
				jQuery(id).hide();
			}
        });
		
		// scroll to top by default
		jQuery("html, body").animate({scrollTop: 0}, 0);
		
		// track clicks
		if(sel) {
			jQuery(this).find('.nav-tab').click(function(e) {
				e.preventDefault();
				if(jQuery(this).hasClass('nav-tab-active')) {return false;}
				
				var sel_id = jQuery(this).attr('href');
				window.location.hash = sel_id.replace('#', '');
				
				$form.attr('action', form_act + sel_id);
				
				// show selected and hide others
				jQuery(this).parents('.lcwp_settings_tabs').find('.nav-tab').each(function() {
                    var id = jQuery(this).attr('href');
					
					if(sel_id == id) {
						jQuery(this).addClass('nav-tab-active');
						jQuery(id).show();		
					}
					else {
						jQuery(this).removeClass('nav-tab-active');
						jQuery(id).hide();	
					}
                });
			});
		}
   });
   
  
   
	// sliders
	var lcwp_sf_slider_opt = function() {
		var a = 0; 
		$('.lcwp_sf_slider_wrap').each(function(idx, elm) {
			var sid = 'slider'+a;
			jQuery(this).attr('id', sid);	
		
			svalue = parseInt(jQuery("#"+sid).next('input').val());
			minv = parseInt(jQuery("#"+sid).attr('min'));
			maxv = parseInt(jQuery("#"+sid).attr('max'));
			stepv = parseInt(jQuery("#"+sid).attr('step'));
			
			jQuery('#' + sid).slider({
				range: "min",
				value: svalue,
				min: minv,
				max: maxv,
				step: stepv,
				slide: function(event, ui) {
					jQuery('#' + sid).next().val(ui.value);
				}
			});
			jQuery('#'+sid).next('input').change(function() {
				var val = parseInt(jQuery(this).val());
				var minv = parseInt(jQuery("#"+sid).attr('min'));
				var maxv = parseInt(jQuery("#"+sid).attr('max'));
				
				if(val <= maxv && val >= minv) {
					jQuery('#'+sid).slider('option', 'value', val);
				}
				else {
					if(val <= maxv) {jQuery('#'+sid).next('input').val(minv);}
					else {jQuery('#'+sid).next('input').val(maxv);}
				}
			});
			
			a = a + 1;
		});
	}
	lcwp_sf_slider_opt();
	
	
	// colorpicker
	var lcwp_sf_colpick = function () {
		jQuery('.lcwp_sf_colpick input').each(function() {

			var curr_col = jQuery(this).val().replace('#', '');
			jQuery(this).colpick({
				layout:'rgbhex',
				submit: 0,
				color: curr_col,
				onChange:function(hsb,hex,rgb, el, fromSetColor) {
					if(!fromSetColor){ 
						jQuery(el).val('#' + hex);
						jQuery(el).parents('.lcwp_sf_colpick').find('.lcwp_sf_colblock').css('background-color','#'+hex);
					}
				}
			}).keyup(function(){
				jQuery(this).colpickSetColor(this.value);
				jQuery(this).parents('.lcwp_sf_colpick').find('.lcwp_sf_colblock').css('background-color', this.value);
			});  
		});
	}
	lcwp_sf_colpick();
   
  
   // lc switch
	var lcwp_sf_live_checks = function() { 
		jQuery('.lcwp_sf_check').lc_switch('YES', 'NO');
	}
	lcwp_sf_live_checks();
	
	
	
	// chosen
	var lcwp_sf_live_chosen = function() { 
		jQuery('.lcwp_sf_chosen').each(function() {
			var w = jQuery(this).css('width');
			jQuery(this).chosen({width: w}); 
		});
		jQuery(".lcweb-chosen-deselect").chosen({allow_single_deselect:true});
	}
	lcwp_sf_live_chosen();
	
	
	
	//////////////////////////////////////////////////
	
	
	// fixed submit position
	var lcwp_sf_fixed_submit = function(btn_selector) {
		var $subj = jQuery(btn_selector);
		if(!$subj.length) {return false;}
		
		var clone = $subj.clone().wrap("<div />").parent().html();

		setInterval(function() {
			
			// if page has scrollers or scroll is far from bottom
			if((jQuery(document).height() > jQuery(window).height()) && (jQuery(document).height() - jQuery(window).height() - jQuery(window).scrollTop()) > 130) {
				if(!jQuery('.lcwp_settings_fixed_submit').length) {	
					$subj.after('<div class="lcwp_settings_fixed_submit">'+ clone +'</div>');
				}
			}
			else {
				if(jQuery('.lcwp_settings_fixed_submit').length) {	
					jQuery('.lcwp_settings_fixed_submit').remove();
				}
			}
		}, 50);
	};
	lcwp_sf_fixed_submit('.lcwp_settings_submit');
	
	
	//////////////////////////////////////////////////
	
	
	
	// toast message for better visibility
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
	
	
	
	// on settings submit
	if(jQuery('.lcwp_settings_result').length) {
		
		var $subj = jQuery('.lcwp_settings_result');
		var subj_txt = $subj.find('p').html();
	
		
		// if success - simply hide main one
		if($subj.hasClass('updated')) {
			jQuery('#lc_toast_mess').empty().html('<div class="lc_success"><p>'+ subj_txt +'</p><span></span></div>');	
			$subj.remove();	
			
			// remove &lcwp_sf_success
			history.replaceState(null, null, window.location.href.replace('&lcwp_sf_success', ''));
		}
		
		// show errors but keep them visible on top
		else {
			jQuery('#lc_toast_mess').empty().html('<div class="lc_error"><p><?php _e('One or more errors occurred', $ml_key) ?></p><span></span></div>');
			jQuery("html, body").animate({scrollTop: 0}, 0);		
		}
		
		// use a micro delay to let CSS animations act
		setTimeout(function() {
			jQuery('#lc_toast_mess').addClass('lc_tm_shown');
		}, 30);
		
		// auto-close after a while
		setTimeout(function() {
			jQuery('#lc_toast_mess.lc_tm_shown span').trigger('click');
		}, 2300);	
	}
		
});
</script>


<?php
// AG-ACTION - allow extra code printing in settings (for javascript/css)
do_action('ag_settings_extra_code');
?>
