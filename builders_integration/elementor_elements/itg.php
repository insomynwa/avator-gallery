<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if(!defined('ABSPATH')) exit;



class ag_itg_on_elementor extends Widget_Base {
	
	 public function get_icon() {
      return 'emtr_lcweb_icon';
   }
	
	public function get_name() {
		return 'g-itg';
	}

	public function get_categories() {
		return array('global-gallery');
	}

   public function get_title() {
      return 'AG - '. __('Image-to-Gallery', 'ag_ml');
   }



   protected function _register_controls() {
		include_once(AG_DIR .'/settings/field_options.php');
		
		
		$this->start_controls_section(
			'main',
			array(
				'label' => 'Avator Gallery - '. __('Image-to-Gallery details', 'ag_ml'),
			)
		);
  
  
		$this->add_control(
		   'gid',
		   array(
			  'label' 	=> __('Images source', 'ag_ml'),
			  'type' 	=> Controls_Manager::SELECT,
			  'default' => current(array_keys($GLOBALS['ag_emtr_galls'])),
			  'options' => $GLOBALS['ag_emtr_galls']
		   )
		);
		
		$this->add_control(
		   'width',
		   array(
			  	'label' => __("Box width", 'ag_ml'),
			  	'type' => Controls_Manager::SLIDER,
				'size_units' => array('%', 'px'),
				'default' => array(
					'unit' => '%',
					'size' => 100,
				),
				'range' => array(
					'%' => array(
						'max' => 100,
						'min' => 5,
						'step' => 1,
					),
					'px' => array(
						'max' => 1500,
						'min' => 30,
						'step' => 10,
					),
				),
		   )
		);
		
		$this->add_control(
		   'img_h',
		   array(
			  	'label' => __("Images  height", 'ag_ml'),
				'description' => __('Using % value, height is proportional to width', 'ag_ml'),
			  	'type' => Controls_Manager::SLIDER,
				'size_units' => array('%', 'px'),
				'default' => array(
					'unit' => '%',
					'size' => 75,
				),
				'range' => array(
					'%' => array(
						'max' => 200,
						'min' => 20,
						'step' => 1,
					),
					'px' => array(
						'max' => 1500,
						'min' => 30,
						'step' => 10,
					),
				),
		   )
		);
		
		$this->add_control(
		   'layout',
		   array(
			  'label' 		=> __('Layout', 'ag_ml'),
			  'type' 		=> Controls_Manager::SELECT,
			  'default' 	=> '',
			  'options' 	=> array('' => __('(as default)', 'ag_ml')) + ag_itg_layouts(),
		   )
		);
		
		$this->add_control(
		   'img_num',
		   array(
			  'label' 		=> __('Images to display?', 'ag_ml'),
			  'description' => __('This will be ignored if chosen layout is "main image + two on sides"', 'ag_ml'),
			  'type' 		=> Controls_Manager::SELECT,
			  'default' 	=> 1,
			  'options' 	=> array(
				1 => 1,
				2 => 2,
				4 => 4,
			  ),
		   )
		);
		
		$this->add_control(
		   'font_size',
		   array(
			  	'label' => __("Custom font size", 'ag_ml') .' <small>(REM)</small>',
				'description' => __('Leave empty to use default one', 'ag_ml'),
			  	'type' => Controls_Manager::SLIDER,
				'size_units' => array('rem'),
				'default' => array(
					'unit' => 'rem',
					'size' => '',
				),
				'range' => array(
					'rem' => array(
						'max' => 5,
						'min' => 0.1,
						'step' => 0.1,
					),
				),
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
		   'custom_contents',
		   array(
			  'label' 		=> __('Custom overlay text', 'ag_ml'),
			  'description'	=> __('Remember you can use placeholders and FontAwesome icons as explained in settings', 'ag_ml'),
			  'type' 		=> Controls_Manager::TEXTAREA,
			  'default' 	=> '',
		   )
		);
		
		$this->end_controls_section();
   }


	
	////////////////////////


	protected function render() {
     	$vals = $this->get_settings();
		//var_dump($vals);

		// create strings for height and width
		$vals['width'] = $vals['width']['size'].$vals['width']['unit'];
		$vals['img_h'] = $vals['img_h']['size'].$vals['img_h']['unit'];
		
		$vals['font_size'] = (empty($vals['font_size']['size'])) ? '' : $vals['font_size']['size'];

		$parts = array('gid', 'width', 'img_h', 'layout', 'img_num', 'font_size', 'random', 'watermark');
		$params = '';
		
		foreach($parts as $part) {
			$params .= $part.'="';

			if(!isset($vals[$part])) {$vals[$part] = '';}
			$params .= $vals[$part].'" ';	
		}
		
		echo do_shortcode('[g-itg '. $params .']'. trim($vals['custom_contents']) .'[/g-itg]');
	}


	protected function _content_template() {}
}
