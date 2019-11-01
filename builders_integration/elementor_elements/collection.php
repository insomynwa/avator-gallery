<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if(!defined('ABSPATH')) exit;



class ag_collection_on_elementor extends Widget_Base {
	
	 public function get_icon() {
      return 'emtr_lcweb_icon';
   }
	
	public function get_name() {
		return 'g-collection';
	}

	public function get_categories() {
		return array('global-gallery');
	}

   public function get_title() {
      return 'AG - '. __('Collection', 'ag_ml');
   }



   protected function _register_controls() {
		
		// collections array
		$collections = get_terms('ag_collections', 'hide_empty=0');
		
		$colls_arr = array(); 
		foreach($collections as $collection) {
			$colls_arr[ $collection->term_id ] = $collection->name;
		}		
		////////////////////////////////
		
		
		$this->start_controls_section(
			'main',
			array(
				'label' => 'Avator Gallery - '. __('Collection details', 'ag_ml'),
			)
		);
  
  
		$this->add_control(
		   'cid',
		   array(
			  'label' 	=> __('Choose collection', 'ag_ml'),
			  'type' 	=> Controls_Manager::SELECT,
			  'default' => current(array_keys($colls_arr)),
			  'options' => $colls_arr
		   )
		);
		
		$this->add_control(
		   'filter',
		   array(
			  'label' 		=> __('Allow Filters?', 'ag_ml'),
			  'description'	=> __('Allows galleries filtering by category', 'ag_ml'),
			  'type' 		=> Controls_Manager::SWITCHER,
			  'default' 	=> '',
			  'label_on' 	=> __('Yes'),
			  'label_off' 	=> __('No'),
			  'return_value' => '1',
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

		$parts = array('cid', 'filter', 'random', 'overlay');
		$params = '';
		
		foreach($parts as $part) {
			$params .= $part.'="';
			
			if(!isset($vals[$part])) {$vals[$part] = '';}
			$params .= $vals[$part].'" ';	
		}
		
		echo do_shortcode('[g-collection '. $params .']');
	}


	protected function _content_template() {}
}
