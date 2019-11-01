<?php

// add the order field
add_action('ag_gall_categories_add_form_fields','ag_cat_order_field', 10, 2 );
add_action('ag_gall_categories_edit_form_fields' , "ag_cat_order_field", 10, 2);

function ag_cat_order_field($tax_data) {
   //check for existing taxonomy meta for term ID
   if(is_object($tax_data)) {
	  $term_id = $tax_data->term_id;
	  $icon = get_option("ag_cat_".$term_id."_icon");
	  $order = (int)get_option("ag_cat_".$term_id."_order");
	}
	else {
		$icon = '';
		$order = 0;
	}
	
	// creator layout
	if(!is_object($tax_data)) :
?>
		<div class="form-field">
            <label><?php _e('Icon', 'mg_ml') ?></label>
            <div class="ag_gall_cat_icon_trigger">
            	<i class="fa <?php echo $icon ?>" title="set category icon" style="display: inline-block;"></i>
                <input type="hidden" name="ag_cat_icon" value="<?php echo $icon ?>" /> 
            </div>
            <p><?php _e('Category icon, placed before category name', 'ag_ml') ?></p>
        </div>
		<div class="form-field">
            <label><?php _e('Order', 'ag_ml') ?></label>
           	<input type="text" name="ag_cat_order" value="<?php echo $order ?>" maxlength="3" style="width: 35px;" /> 
            <p><?php _e('The category order that will be used for the grid filter', 'ag_ml') ?></p>
        </div>
	<?php
	else:
	?>
    <tr class="form-field">
      <th scope="row" valign="top"><label><?php _e('Icon', 'ag_ml') ?></label></th>
      <td>
        <div class="ag_gall_cat_icon_trigger">
            <i class="fa <?php echo $icon ?>" title="set category icon" style="display: inline-block;"></i>
            <input type="hidden" name="ag_cat_icon" value="<?php echo $icon ?>" /> 
        </div>
        <p class="description"><?php _e('Category icon, placed before category name', 'mg_ml') ?></p>
      </td>
    </tr>
	<tr class="form-field">
      <th scope="row" valign="top"><label><?php _e('Order', 'ag_ml') ?></label></th>
      <td>
        <input type="text" name="ag_cat_order" value="<?php echo $order ?>" maxlength="3" style="width: 35px;" />
        <p class="description"><?php _e('The category order that will be used for the grid filter', 'ag_ml') ?></p>
      </td>
	</tr>
<?php
	endif;
}



///////////////////////////////
// save fields
add_action('created_ag_gall_categories', 'save_ag_cat_order_field', 10, 2);
add_action('edited_ag_gall_categories', 'save_ag_cat_order_field', 10, 2);

function save_ag_cat_order_field( $term_id ) {
	 if ( isset($_POST['ag_cat_icon']) ) {
        update_option("ag_cat_".$term_id."_icon", $_POST['ag_cat_icon']); 
    }
	else {delete_option("ag_cat_".$term_id."_icon");}
	
	
    if ( isset($_POST['ag_cat_order']) ) {
        update_option("ag_cat_".$term_id."_order", (int)$_POST['ag_cat_order']); 
    }
	else {delete_option("ag_cat_".$term_id."_order");}
}


// clean options if a cat is deleted
add_action('delete_term', 'ag_clean_term_options', 10, 4);

function ag_clean_term_options($term, $tt_id, $taxonomy, $deleted_term) {
	delete_option("ag_cat_".$tt_id."_icon");
	delete_option("ag_cat_".$tt_id."_order");
}



/////////////////////////////
// manage taxonomy table
add_filter( 'manage_edit-ag_gall_categories_columns', 'ag_cat_order_column_headers', 10, 1);
add_filter( 'manage_ag_gall_categories_custom_column', 'ag_cat_order_column_row', 10, 3);


// add the table column
function ag_cat_order_column_headers($columns) {
	$prepend_cols = array();
	$append_cols = array();
	
	$prepend_cols['icon'] = __("Icon", 'ag_ml');
    $append_cols['order'] = __("Order", 'ag_ml');
	
	if(count($prepend_cols) > 0) {
		$columns = array_slice($columns, 0, 1, true) + $prepend_cols + array_slice($columns, 1, count($columns)-1, true);
	}
    return array_merge($columns, $append_cols);
}


// fill custom column row
function ag_cat_order_column_row( $row_content, $column_name, $term_id){
	
	if($column_name == 'icon') {
		return '<i class="fa fa-lg '.get_option("ag_cat_".$term_id."_icon").'"></i>';	
	}
	elseif($column_name == 'order') {
		return (int)get_option("ag_cat_".$term_id."_order");
	}
	else {return '&nbsp;';}
}


/////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////
// REMOVE PARENT FIELD FOR CUSTOM TAXONOMY
// ADD ICON MANAGEMENT SYSTEM
function ag_remove_cat_parent(){
	global $current_screen;

	// remove parent field
	if($current_screen->id == 'edit-ag_gall_categories') {
		?>
		<script type="text/javascript">
		jQuery(document).ready( function($) {
			jQuery('#parent').parents('.form-field').remove();
		});
		</script>
		<?php
	}
	
	
	// icon wizard
	if($current_screen->id == 'edit-ag_gall_categories') {
		include_once(AG_DIR . '/classes/lc_font_awesome_helper.php');
		$fa = new lc_fontawesome_helper;
		
		echo '
		<div id="ag_icons_list" style="display: none;">
			<div class="ag_lb_icons_wizard">
				<p rel="" class="agtoi_no_icon"><a>'. __('no icon', 'ag_ml') .'</a></p>';
			
				foreach($fa->sorted_icons as $cat => $icons) {
					echo '<h4>'. $cat .'</h4>';
					
					foreach($icons as $iid => $unicode) {
						$idata = $fa->icons[$iid];
						echo '<i rel="'.$idata->class.'" class="fa '.$idata->class.'" title="'.$idata->name.'"></i>';
					}
				}
		
		echo '
			</div>
		</div>';
		?>
		
		<script type="text/javascript">
		// launch option icon wizard
		var $sel_type_opt = false;
		jQuery('body').delegate('.ag_gall_cat_icon_trigger i', "click", function() {
			$sel_type_opt = jQuery(this).parent();
			
			tb_show('Items Category - choose an icon' , '#TB_inline?inlineId=ag_icons_list');
			setTimeout(function() {
				jQuery('#TB_ajaxContent').css('width', 'auto');
				jQuery('#TB_ajaxContent').css('height', (jQuery('#TB_window').height() - 47) );
			}, 50);
		});
		jQuery(window).resize(function() {
			if( jQuery('#TB_ajaxContent .ag_lb_icons_wizard').size() > 0 ) {
				jQuery('#TB_ajaxContent').css('height', (jQuery('#TB_window').height() - 47) );	
			}
		});
		
		
		// select icon
		jQuery('body').delegate('#TB_ajaxContent .ag_lb_icons_wizard > *', "click", function() {
			var val = jQuery(this).attr('rel');
			
			$sel_type_opt.find('input').val(val);
			$sel_type_opt.find('i').attr('class', 'fa '+val);
			
			tb_remove();
			$sel_type_opt = false;
		});	
		</script>
		<?php
	}
}
add_action('admin_footer-edit-tags.php', 'ag_remove_cat_parent');
add_action('admin_footer-term.php', 'ag_remove_cat_parent');

