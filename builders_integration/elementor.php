<?php
// Elementor integration
include_once( ABSPATH . 'wp-admin/includes/plugin.php');
if(is_plugin_active('elementor/elementor.php') ) {
if(!defined('ABSPATH')) exit;



class ag_on_elementor {
	private $widgets_basepath = '';

	
	public function __construct() {
		/*** store common arrays into globals ***/
		
		// galleries array
		$args = array(
			'post_type' => 'ag_galleries',
			'numberposts' => -1,
			'post_status' => 'publish'
		);
		$galleries = get_posts($args);
		
		$galls_arr = array(); 
		foreach($galleries as $gallery) {
			$galls_arr[ $gallery->ID ] = $gallery->post_title;
		}
		$GLOBALS['ag_emtr_galls'] = $galls_arr;
		
		
		// AGOM - overlays array
		if(defined('AGOM_DIR')) {
			register_taxonomy_agom(); // be sure tax are registered
			$overlays = get_terms('agom_overlays', 'hide_empty=0');
			
			$ol_arr = array(
				'default' => __('default one', 'ag_ml')
			);
			foreach($overlays as $ol) {
				$ol_arr[ $ol->term_id ] = $ol->name;	
			}
			$GLOBALS['ag_emtr_overlays'] = $ol_arr;
		}
		
		
		/*** enqueue ***/
		$this->widgets_basepath = AG_DIR .'/builders_integration/elementor_elements';
		
		add_action('elementor/widgets/widgets_registered', array( $this, 'register_ag_gallery'));
		add_action('elementor/widgets/widgets_registered', array( $this, 'register_ag_itg'));
		add_action('elementor/widgets/widgets_registered', array( $this, 'register_ag_collection'));
		add_action('elementor/widgets/widgets_registered', array( $this, 'register_ag_slider'));
		add_action('elementor/widgets/widgets_registered', array( $this, 'register_ag_carousel'));
	}
		 
		 
	
	// gallery
	public function register_ag_gallery() {
		include_once($this->widgets_basepath .'/gallery.php');
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new ag_gallery_on_elementor() );
	}
	
	// image-to-gallery
	public function register_ag_itg() {
		include_once($this->widgets_basepath .'/itg.php');
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new ag_itg_on_elementor() );
	}
	
	// collection
	public function register_ag_collection() {
		include_once($this->widgets_basepath .'/collection.php');
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new ag_collection_on_elementor() );
	}
	
	// slider
	public function register_ag_slider() {
		include_once($this->widgets_basepath .'/slider.php');
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new ag_slider_on_elementor() );
	}
	
	// carousel
	public function register_ag_carousel() {
		include_once($this->widgets_basepath .'/carousel.php');
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new ag_carousel_on_elementor() );
	}
}
add_action('wp_loaded', function() {
	new ag_on_elementor();
}, 1);




// add Avator Gallery section
add_action('elementor/init', function() {
   \Elementor\Plugin::$instance->elements_manager->add_category( 
   	'global-gallery',
   	array(
   		'title' => 'Avator Gallery',
   		'icon' => 'fa fa-plug',
   	),
	3
   );
});



// style needed for LCweb icons
add_action('elementor/editor/after_enqueue_styles', function() {
	wp_enqueue_style('lcweb-elementor-icon', AG_URL .'/builders_integration/elementor_elements/lcweb_icon.css');	
});




////
} // end elementor's existence check