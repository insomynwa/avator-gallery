<?php
// overwrite the page content to display gallery/collection preview

add_filter('the_content', 'ag_manage_preview', 9999);

function ag_manage_preview($the_content) {
	$target_page = (int)get_option('ag_preview_pag');
	$curr_page_id = (int)get_the_ID();
	
	if($target_page == $curr_page_id && is_user_logged_in() && (isset($_REQUEST['ag_gid']) || isset($_REQUEST['ag_cid']))) {
			
		// gallery preview	
		if(isset($_REQUEST['ag_gid'])) {			
			$content = do_shortcode('[g-gallery gid="'.(int)$_REQUEST['ag_gid'].'" random="0"]');
		}
		
		// collection
		else {
			$content = do_shortcode('[g-collection cid="'. (int)$_REQUEST['ag_cid'] .'" filter="1" random="0"]');
		}
		
		
		return $content;
	}	
	else {return $the_content;}
}
