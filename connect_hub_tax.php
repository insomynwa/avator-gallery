<?php
// custom taxonomy used to store external connections data
//// if missing - creates main terms (connections will be sub-terms)


// register taxonomy
add_action('init', 'ag_conn_taxonomy');
function ag_conn_taxonomy() {
		
	$labels = array( 
        'name' => __('Connections', 'ag_ml'),
        'singular_name' => __( 'Connection', 'ag_ml' ),
        'search_items' => __( 'Search Connections', 'ag_ml' ),
        'popular_items' => NULL,
        'all_items' => __( 'All Connections', 'ag_ml' ),
        'parent_item' => __( 'Parent Connection', 'ag_ml' ),
        'parent_item_colon' => __( 'Parent Connection:', 'ag_ml' ),
        'edit_item' => __( 'Edit Connection', 'ag_ml' ),
        'update_item' => __( 'Update Connection', 'ag_ml' ),
        'add_new_item' => __( 'Add New Connection', 'ag_ml' ),
        'new_item_name' => __( 'New Connection', 'ag_ml' ),
        'separate_items_with_commas' => __( 'Separate item categories with commas', 'ag_ml' ),
        'add_or_remove_items' => __( 'Add or remove Connections', 'ag_ml' ),
        'choose_from_most_used' => __( 'Choose from most used Connections', 'ag_ml' ),
        'menu_name' => __( 'Connections', 'ag_ml' ),
    );

    $args = array( 
        'labels' => $labels,
        'public' => false,
        'show_in_nav_menus' => false,
        'show_ui' => false,
        'show_tagcloud' => false,
        'hierarchical' => true,
        'rewrite' => false,
        'query_var' => false
    );
    register_taxonomy('ag_connect_hub', null, $args);
}


