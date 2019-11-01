<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if(!defined('ABSPATH')) exit;



class ag_gallery_on_elementor extends Widget_Base {
	
	 public function get_icon() {
      return 'emtr_lcweb_icon';
   }
	
	public function get_name() {
		return 'g-gallery';
	}

	public function get_categories() {
		return array('global-gallery');
	}

   public function get_title() {
      return 'AG - '. __('Gallery', 'ag_ml');
   }



   protected function _register_controls() {

		$this->start_controls_section(
			'main',
			array(
				'label' => 'Avator Gallery - '. __('Gallery details', 'ag_ml'),
			)
		);
  
  
		$this->add_control(
		   'gid',
		   array(
			  'label' 	=> __('Choose gallery', 'ag_ml'),
			  'type' 	=> Controls_Manager::SELECT,
			  'default' => current(array_keys($GLOBALS['ag_emtr_galls'])),
			  'options' => $GLOBALS['ag_emtr_galls']
		   )
		);
		
		$this->add_control(
		   'random',
		   array(
			  'label' 		=> __('Random images?', 'ag_ml'),
			  'description'	=> __('Displays images randomly', 'ag_ml'),
			  'type' 		=> Controls_Manager::SWITCHER,
			  'default' 	=> '',
			  'label_on' 	=> __('Yes'),
			  'label_off' 	=> __('No'),
			  'return_value' => '1',
		   )
		);
		
		$this->add_control(
		   'watermark',
		   array(
			  'label' 		=> __('Use Watermark?', 'ag_ml'),
			  'description'	=> __('Applies watermark to images (where available)', 'ag_ml'),
			  'type' 		=> Controls_Manager::SWITCHER,
			  'default' 	=> '',
			  'label_on' 	=> __('Yes'),
			  'label_off' 	=> __('No'),
			  'return_value' => '1',
		   )
		);
		
		$this->add_control(
		   'filters',
		   array(
			  'label' 		=> __('Use tags filter?', 'ag_ml'),
			  'description'	=> '',
			  'type' 		=> Controls_Manager::SWITCHER,
			  'default' 	=> '',
			  'label_on' 	=> __('Yes'),
			  'label_off' 	=> __('No'),
			  'return_value' => '1',
		   )
		);
		
		$this->add_control(
		   'pagination',
		   array(
			  'label' 	=> __('Pagination system', 'ag_ml'),
			  'type' 	=> Controls_Manager::SELECT,
			  'default' => '',
			  'options' => array(
			  	'' 			=> __('Auto - follow global settings', 'ag_ml'),
				'standard' 	=> __('Standard', 'ag_ml'),
				'inf_scroll'=> __('Infinite scroll', 'ag_ml')
			  )
		   )
		);
		
		if(isset($GLOBALS['ag_emtr_overlays'])) {
			$this->add_control(
			   'overlay',
			   array(
				  'label' 	=> __('Custom Overlay', 'ag_ml'),
				  'type' 	=> Controls_Manager::SELECT,
				  'default' => 'default',
				  'options' => $GLOBALS['ag_emtr_overlays']
			   )
			);	
		}
			
		$this->end_controls_section();
   }


	
	////////////////////////


	protected function render() {
     	$vals = $this->get_settings();
		//var_dump($vals);

		$parts = array('gid', 'random', 'watermark', 'filters', 'pagination', 'overlay');
		$params = '';
		
		foreach($parts as $part) {
			$params .= $part.'="';
			
			if(!isset($vals[$part])) {$vals[$part] = '';}
			$params .= $vals[$part].'" ';	
		}
		
		echo do_shortcode('[g-gallery '. $params .']');
	}


	protected function _content_template() {}
}
