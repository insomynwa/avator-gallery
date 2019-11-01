<?php

/**
 * Element Definition
 */

class lcweb_ag_gallery {

	public function ui() {
		return array(
		  'title'       => 'AG - '. __('Gallery', 'ag_ml'),
		  'autofocus' => array(
				'heading' => 'h4.lcweb_ag_gallery-heading',
				'content' => '.lcweb_ag_gallery'
			),
			'icon_group' => 'lcweb_ag_gallery'
		);
	}


	public function update_build_shortcode_atts( $atts ) {
		
		/*
		// This allows us to manipulate attributes that will be assigned to the shortcode
		// Here we will inject a background-color into the style attribute which is
		// already present for inline user styles
		if ( !isset( $atts['style'] ) ) {
			$atts['style'] = '';
		}


		if ( isset( $atts['background_color'] ) ) {
			$atts['style'] .= ' background-color: ' . $atts['background_color'] . ';';
			unset( $atts['background_color'] );
		}
		
		*/
		
		return $atts;
	}

}