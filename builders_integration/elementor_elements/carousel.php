<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if(!defined('ABSPATH')) exit;



class ag_carousel_on_elementor extends Widget_Base {
	
	 public function get_icon() {
      return 'emtr_lcweb_icon';
   }
	
	public function get_name() {
		return 'g-carousel';
	}

	public function get_categories() {
		return array('global-gallery');
	}

   public function get_title() {
      return 'AG - '. __('Carousel', 'ag_ml');
   }



   protected function _register_controls() {

		$this->start_controls_section(
			'main',
			array(
				'label' => 'Avator Gallery - '. __('Carousel details', 'ag_ml'),
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
		   'img_max_w',
		   array(
			  'label' 		=> __('Images maximum width', 'ag_ml') . ' (px)',
			  'type' => Controls_Manager::SLIDER,
			  'size_units' => array('px'),
				'default' => array(
					'unit' => 'px',
					'size' => 180,
				),
				'range' => array(
					'px' => array(
						'max' => 2000,
						'min' => 40,
						'step' => 10,
					),
				),
		   )
		);
		
		$this->add_control(
		   'height',
		   array(
			  'label' 		=> __('Images Height', 'ag_ml'),
			  'type' => Controls_Manager::SLIDER,
			  'size_units' => array('px', '%'),
				'default' => array(
					'unit' => 'px',
					'size' => 180,
				),
				'range' => array(
					'px' => array(
						'max' => 1000,
						'min' => 30,
						'step' => 10,
					),
					'%' => array(
						'max' => 200,
						'min' => 10,
						'step' => 1,
					),
				),
		   )
		);
		
		$this->add_control(
		   'rows',
		   array(
			  'label' 	=> __('Rows', 'ag_ml'),
			  'description'	=> __('Choose how many image rows to use', 'ag_ml'),
			  'type' 	=> Controls_Manager::NUMBER,
			  'default' => 1,
			  'max' => 10,
			  'min' => 1,
			  'step' => 1,
		   )
		);
		
		$this->add_control(
		   'multiscroll',
		   array(
			  'label' 		=> __('Multiple Scroll?', 'ag_ml'),
			  'description'	=> __('Slides multiple images per time', 'ag_ml'),
			  'type' 		=> Controls_Manager::SWITCHER,
			  'default' 	=> '',
			  'label_on' 	=> __('Yes'),
			  'label_off' 	=> __('No'),
			  'return_value' => '1',
		   )
		);
		
		$this->add_control(
		   'center',
		   array(
			  'label' 		=> __('Center mode?', 'ag_ml'),
			  'description'	=> __('Enables center display mode', 'ag_ml'),
			  'type' 		=> Controls_Manager::SWITCHER,
			  'default' 	=> '',
			  'label_on' 	=> __('Yes'),
			  'label_off' 	=> __('No'),
			  'return_value' => '1',
		   )
		);
		
		$this->add_control(
		   'nocrop',
		   array(
			  'label' 		=> __('Avoid images crop?', 'ag_ml'),
			  'description'	=> __('Just downscales images', 'ag_ml'),
			  'type' 		=> Controls_Manager::SWITCHER,
			  'default' 	=> '',
			  'label_on' 	=> __('Yes'),
			  'label_off' 	=> __('No'),
			  'return_value' => '1',
		   )
		);
		$this->add_control(
		   'static',
		   array(
			  'label' 		=> __('Static mode?', 'ag_ml'),
			  'description'	=> __('Disables overlay and lightbox', 'ag_ml'),
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
		   'autoplay',
		   array(
			  'label' 	=> __('Autoplay slider?', 'ag_ml'),
			  'type' 	=> Controls_Manager::SELECT,
			  'default' => 'auto',
			  'options' => array(
			  	'auto' => __('(as default)', 'ag_ml'),
				1 => __('Yes', 'ag_ml'),
				0 => __('No', 'ag_ml')
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

		// manage num/unit fields
		$vals['img_max_w'] = $vals['img_max_w']['size'];
		$vals['h_type'] = $vals['height']['unit'];
		$vals['height'] = $vals['height']['size'];
		
		$parts = array('gid', 'img_max_w', 'height', 'h_type', 'rows', 'multiscroll', 'center', 'nocrop', 'static', 'random', 'watermark', 'autoplay', 'overlay');
		$params = '';
		
		foreach($parts as $part) {
			$params .= $part.'="';
			
			if(!isset($vals[$part])) {$vals[$part] = '';}
			$params .= $vals[$part].'" ';	
		}
		
		echo do_shortcode('[g-carousel '. $params .']');
	}


	protected function _content_template() {}
}
