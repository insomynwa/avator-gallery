<?php
/**
 * Class to watermark any image
 * Written to work with WordPress functions. Be sure your server supports mkdir() and file_put_contents()
 * 
 * @version:	1.0
 * @author:		Luca Montanari aka LCweb
 * @copyright:	2017 Luca Montanari - https://lcweb.it
 *
 * Licensed under the MIT license
 */
 
 
if(!class_exists('lc_wp_watermark')) {
	
	
// cURL followlocation switch
$followloc = (!ini_get('open_basedir') && !ini_get('safe_mode')) ? true : false; 
define('LCWW_FOLLOWLOCATION', $followloc);	
	
	
class lc_wp_watermark {
	
	private $debug_mode = true;
	
	public $cache_folder_dir; 	// (string) cache folder's path where images will be saved - by default cache foler is placed in WP uploads dir and named "lc_watermarked"
	public $cache_folder_url; 	// (string) cache folder's url where images will be saved
	public $quality = 95; 		// (int) resulting image quality
	
	public $proportional = true;		// (bool) where to use fixed watermark image sizes or set it proportionally basing on image sizes
	public $prop_sizes = array(15, 15);	// (array) specifies proportional watermark sizes (width-height) in PERCENTAGE values related to image sizes	
	
	public $wm_img_path; 			// (string) watermark image's path
	public $wm_pos = 'mm'; 			// (string) watermark position - use lt/mt/rt lm/mm/rm lb/mb/rb
	public $wm_opacity = 100;		// (int) watermark opacity (0 to 100)
	public $wm_margin = 10;			// (int) watermark margin (in pixels) from image edges
	public $wm_margin_type = '%';	// (string) watermark margin type: px or %
		
	private $wm_mime; 			// (string) watermark mime type
	private $img_mime;			// (string) image mime type
	private $rm_img_data;		// contents received from WP_remote_get/cURL when it comes to manage an external image 

	
	/*
	 * Initialize class specifying watermark image and eventually an associative array containing class properties to override
	 * By default cache foler is placed in WP uploads dir and named "lc_watermarked"
	 
	 * @param (int/string) $wm_img - WP attachment ID or direct path (if URL is passed, class will try to retrieve the path)
	 */
	public function __construct($wm_img, $props = array()) {

		// check watermark image existence
		if(empty($wm_img)) {
			$this->throw_notice('No watermark specified', __LINE__);	
			return false;	
		}
		
		
		// retrieve watermark image's path
		if(is_numeric($wm_img)) { // attach id
			$this->wm_img_path = get_attached_file($wm_img);	
		}
		elseif($this->is_url($wm_img)) { // image URL
			global $wpdb;
			$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM ". $wpdb->posts ." WHERE guid = '%s';", $wm_img)); 
			$this->wm_img_path = (!is_array($attachment) || !count($attachment)) ? '' : get_attached_file($attachment[0]); 
		}
		else {
			$this->wm_img_path = (file_exists($wm_img)) ? $wm_img : ''; 	
		}
			
		// has path	been setup?
		if(empty($this->wm_img_path)) {
			$this->throw_notice('Watermark path not found', __LINE__);	
			return false;		
		}
		
		
		// retrieve watermark mime type
		$this->wm_mime = $this->path_to_mime($this->wm_img_path);
		if(!in_array($this->wm_mime, array('image/jpeg', 'image/png', 'image/gif') )) {
			$this->throw_notice('Watermark image must be JPG, PNG or GIF', __LINE__);	
			return false;		
		}
		
			
		// setup properties
		foreach($props as $key => $val) {
			$this->{$key} = $val;	
		}	
			
			
			
		// if no custom cache folder is specified - setup the default one 
		if(!$this->cache_folder_dir || !$this->cache_folder_url) {
				
			$wp_dirs = wp_upload_dir();
			$folder_name = 'lc_watermarked'; 
			
			$this->cache_folder_dir = trailingslashit($wp_dirs['basedir']) . $folder_name;
			$this->cache_folder_url = trailingslashit($wp_dirs['baseurl']) . $folder_name;	
		}
			
		// check cache folder existence
		if(!$this->setup_cache_dir()) {
			return false;		
		}
	}
	
	
	
	/*
	 * Create cache folder and check its existence + its readability
	 * @return (bool) 
	 */
	private function setup_cache_dir() {
		if(!wp_mkdir_p( $this->cache_folder_dir )) {
			$this->throw_notice('Cache path ('. $this->cache_folder_dir .') does not exist or folder has wrong permissions', __LINE__);	
			return false;		
		}
		
		return true;
	}
	
	
	
	///////////////////////////////////////////////////////////////////
	


	/* 
	 * CREATE WATERMARKED IMAGE 
	 * @param (int/string) $img - could be a WP attachment ID or image path or image URL (even external URL)
	 * @return (array/bool) array(path, url) containing watermarked image or false if there are problems
	 */
	public function mark_it($img_src) {
		
		// if is numeric - retrieve image's path
		if(is_numeric($img_src)) {
			$img_src = get_attached_file($img_src);	
		}
		if(empty($img_src)) {
			$this->throw_notice('Image not found', __LINE__);	
			return false;	
		}
		

		/////////
		// a watermarked verson already exists in cache folder? just returns the final array
		$extless_path = $this->cache_folder_dir .'/'. $this->cache_filename($img_src, true);
		
		if(@file_exists($extless_path . '.jpg')) {
			$this->img_mime = 'image/jpeg';
			$exists = true;	
		}
		elseif(@file_exists($extless_path . '.png')) {
			$this->img_mime = 'image/png';
			$exists = true;		
		}
		elseif(@file_exists($extless_path . '.gif')) {
			$this->img_mime = 'image/gif';
			$exists = true;		
		}
		
		if(isset($exists)) {
			return $this->wm_paths_arr($img_src);		
		}
		/////////
		
		
		
		// setup mime type and also retrieve contents for remote images
		if($this->is_url($img_src)) {
			$this->get_remote_contents($img_src);
		}
		else {
			$this->img_mime = $this->path_to_mime($img_src);	
		}
		
		// check mime
		if(!in_array($this->img_mime, array('image/jpeg', 'image/png', 'image/gif')) ) {
			$this->throw_notice('Remote image ('. $img_src .') not found or is unsupported type', __LINE__);	
			return false;			
		}

		
		// create wm image
		(_wp_image_editor_choose() == 'WP_Image_Editor_Imagick') ? $this->imagick_wm($img_src) : $this->gd_wm($img_src);
		
		
		// if file exist returns paths - otherwise false
		if(@file_exists($this->cache_folder_dir .'/'. $this->cache_filename($img_src))) {
			return $this->wm_paths_arr($img_src);
		} else {
			return false;	
		}
		
		$this->rm_img_data = null; // free up memory
	}
	
	
	///////////
	
	
	/**
	 * watermarking process through imagick
	 * $img_src could be a path or an URL 
	 */
	private function imagick_wm($img_src) {
		$options = array();
		
		// create image resource
		if($this->is_url($img_src)) {
			$image = new Imagick();
			$image->readImageBlob( $this->rm_img_data );	
		}
		else {
			$image = new Imagick($img_src);
		}

		
		// create watermark resource
		$watermark = new Imagick( $this->wm_img_path );

		// set wm opacity
		$watermark->evaluateImage(Imagick::EVALUATE_MULTIPLY, ($this->wm_opacity / 100), Imagick::CHANNEL_ALPHA);


		// set compression quality
		if( $this->img_mime === 'image/jpeg' ) {
			$image->setImageCompressionQuality( $this->quality );
			$image->setImageCompression( imagick::COMPRESSION_JPEG );
		} 
		else {
			$image->setImageCompressionQuality( $this->quality );
		}

		// set image output to progressive
		$image->setImageInterlaceScheme( Imagick::INTERLACE_PLANE );


		// get image dimensions
		$image_dim = $image->getImageGeometry();

		// get watermark dimensions
		$watermark_dim = $watermark->getImageGeometry();

		// calculate watermark new dimensions
		list( $width, $height ) = $this->wm_dimensions( $image_dim['width'], $image_dim['height'], $watermark_dim['width'], $watermark_dim['height']);

		// resize watermark
		$watermark->resizeImage( $width, $height, imagick::FILTER_POINT, 1 );

		// calculate image coordinates
		list( $dest_x, $dest_y ) = $this->wm_coordinates( $image_dim['width'], $image_dim['height'], $width, $height);

		// combine two images together
		//$watermark->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
		$image->compositeImage( $watermark, Imagick::COMPOSITE_OVER, $dest_x, $dest_y);

		// save watermarked image
		$destination = $this->cache_folder_dir .'/'. $this->cache_filename($img_src);
		$image->writeImage($destination);

		// clear image memory
		$image->clear();
		$image->destroy();
		$image = null;

		// clear watermark memory
		$watermark->clear();
		$watermark->destroy();
		$watermark = null;	
	}
	
	
	///////////
	
	
	/**
	 * watermarking process through GD library
	 * $img_src could be a path or an URL 
	 */
	private function gd_wm($img_src) {
		
		// create image resource
		if($this->is_url($img_src)) {
			$image = imagecreatefromstring($this->rm_img_data);
		}
		else {
			switch($this->img_mime) {
				case 'image/jpeg':
					$image = imagecreatefromjpeg($img_src);
					break;
	
				case 'image/png':
					$image = imagecreatefrompng($img_src);
					imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), imagecolorallocatealpha( $image, 255, 255, 255, 127));
					break;
	
				case 'image/gif':
					$image = imagecreatefromgif($img_src);
					break;

				default:
					$image = false;
			}
		}
		
		
		// image resource check
		if(is_resource($image)) {
			imagealphablending($image, false);
			imagesavealpha($image, true);
		}
		else {
			$this->throw_notice('Invalid GD resource ('. $img_src .')', __LINE__);	
			return false;	
		}		
		
		
		// add watermark image to image
		$image = $this->gd_add_watermark_image($image);

		
		// if it's ok - save
		if($image !== false) {
			$destination = $this->cache_folder_dir .'/'. $this->cache_filename($img_src);
			
			switch($this->img_mime) {
				case 'image/jpeg':
				case 'image/pjpeg':
					imagejpeg($image, $destination, $this->quality);
					break;
	
				case 'image/png':
					imagepng($image, $destination, (int)round( 9 - (9 * $this->quality / 100), 0) );
					break;
					
				case 'image/gif':
					imagegif($image, $destination);
					break;
			}

			// clear watermark memory
			imagedestroy($image);
			$image = null;
		}
	}
	
	
	/**
	 * Add watermark image to a GD resource previously created
	 * @param resource $image Image resource
	 * @return resource	Watermarked image
	 */
	private function gd_add_watermark_image($image) {
		switch($this->wm_mime) {
			case 'image/jpeg':
				$watermark = imagecreatefromjpeg($this->wm_img_path);
				break;

			case 'image/gif':
				$watermark = imagecreatefromgif($this->wm_img_path);
				break;

			case 'image/png':
				$watermark = imagecreatefrompng($this->wm_img_path);
				break;

			default:
				return false;
		}

		// get image dimensions
		$image_width = imagesx($image);
		$image_height = imagesy($image);

		// calculate watermark new dimensions
		list($w, $h) = $this->wm_dimensions($image_width, $image_height, imagesx($watermark), imagesy($watermark));

		// wm image has to be resized?
		if($w != imagesx($watermark) || imagesy($watermark)) {
			$watermark = $this->gd_resize($watermark, $w, $h);	
		}

		// calculate image coordinates
		list($dest_x, $dest_y) = $this->wm_coordinates($image_width, $image_height, $w, $h);

		// combine two images together
		$this->gd_imagecopymerge_alpha($image, $watermark, $dest_x, $dest_y, 0, 0, $w, $h);

		// set image output to progressive
		imageinterlace( $image, true);

		return $image;
	}
	
	
	private function gd_imagecopymerge_alpha($image, $watermark, $dst_x, $dst_y, $src_x, $src_y, $wm_w, $wm_h) {
		// create a cut resource
		$cut = imagecreatetruecolor( $wm_w, $wm_h );

		// copy relevant section from background to the cut resource
		imagecopy( $cut, $image, 0, 0, $dst_x, $dst_y, $wm_w, $wm_h );

		// copy relevant section from watermark to the cut resource
		imagecopy( $cut, $watermark, 0, 0, $src_x, $src_y, $wm_w, $wm_h);

		// insert cut resource to destination image
		imagecopymerge( $image, $cut, $dst_x, $dst_y, 0, 0, $wm_w, $wm_h, $this->wm_opacity);
	}
	
	
	
	/**
	 * Resize image.
	 *
	 * @param resource $image Image resource
	 * @param int $width Image width
	 * @param int $height Image height
	 * @return resource	Resized image
	 */
	private function gd_resize($image, $width, $height) {
		$new_image = imagecreatetruecolor($width, $height);

		// check if this image is PNG/GIF, then set if transparent
		if($this->wm_mime != 'image/jpeg') {
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
			imagefilledrectangle($new_image, 0, 0, $width, $height, imagecolorallocatealpha( $new_image, 255, 255, 255, 127 ));
		}

		imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image) );

		return $new_image;
	}
	
	

	//////////////////////////////////////////////////////////////
	
	
	
	/**
	 * Calculate watermark dimensions
	 *
	 * @param $image_width Image width
	 * @param $image_height Image height
	 * @param $watermark_width Watermark width
	 * @param $watermark_height	Watermark height
	 * @param $options Options
	 *
	 * @return array Watermark new dimensions
	 */
	private function wm_dimensions($image_width, $image_height, $watermark_width, $watermark_height) {
		
		// calculate margins
		if($this->wm_margin_type == 'px') {
			$vert_margin = $horiz_margin = (int)$this->wm_margin * 2;
		} else {
			$horiz_margin = floor( ((float)$this->wm_margin / 100) * $image_width) * 2; 
			$vert_margin = floor( ((float)$this->wm_margin / 100) * $image_height) * 2; 
		}
		
		
		// proportional size
		if($this->proportional) {
			$width = floor($image_width * ($this->prop_sizes[0] / 100));
			if($width + $horiz_margin > $image_width) {$width = $image_width - $horiz_margin;}

			$height = floor( ($watermark_height * $width) / $watermark_width );
				
			$max_h = floor($image_height * ($this->prop_sizes[1] / 100));
			if($max_h + $vert_margin > $image_height) {$max_h = $image_height - $vert_margin;}	
				
			if($height + $vert_margin > $max_h) {
				$height = $max_h;
				$width = floor( ($watermark_width * $height) / $watermark_height );	
			}
		} 

		// use original WM size and if bigger than image > downscale
		else {

			// if is bigger than image - scale down
			if($watermark_width + $horiz_margin > $image_width) {
				$width = $image_width - $horiz_margin;
				$height = floor( ($watermark_height * $width) / $watermark_width );
					
				if($height + $vert_margin > $image_height) {
					$height = $image_height - $vert_margin;
					$width = floor( ($watermark_width * $height) / $watermark_height );	
				}
			}
			
			elseif($watermark_height + $vert_margin > $image_height) {
				$height = $image_height - $vert_margin;
				$width = floor( ($watermark_width * $height) / $watermark_height );	

				if($width + $horiz_margin > $image_width) {
					$width = $image_width - $horiz_margin;
					$height = floor( ($watermark_height * $width) / $watermark_width );
				}
			}

			else {
				$width = $watermark_width;
				$height = $watermark_height;
			}
		}
		
		return array($width, $height);
	}
	


	/**
	 * Calculate watermark coordinates
	 *
	 * @param $image_width Image width
	 * @param $image_height	Image height
	 * @param $watermark_width Watermark width
	 * @param $watermark_height	Watermark height
	 * @return array Image coordinates
	 */
	private function wm_coordinates($image_width, $image_height, $watermark_width, $watermark_height) {
		
		// calculate margins
		if($this->wm_margin_type == 'px') {
			$vert_margin = $horiz_margin = (int)$this->wm_margin;
		} else {
			$horiz_margin = floor( ((float)$this->wm_margin / 100) * $image_width); 
			$vert_margin = floor( ((float)$this->wm_margin / 100) * $image_height); 
		}


		switch ($this->wm_pos) {
			case 'lt':
				$dest_x = $horiz_margin;
				$dest_y = $vert_margin;
				break;

			case 'mt':
				$dest_x = ( $image_width / 2 ) - ( $watermark_width / 2 );
				$dest_y = $vert_margin;
				break;

			case 'rt':
				$dest_x = $image_width - $watermark_width - $horiz_margin; 
				$dest_y = $vert_margin;
				break;

			case 'lm':
				$dest_x = $horiz_margin;
				$dest_y = ( $image_height / 2 ) - ( $watermark_height / 2 );
				break;

			case 'rm':
				$dest_x = $image_width - $watermark_width - $horiz_margin;
				$dest_y = ( $image_height / 2 ) - ( $watermark_height / 2 );
				break;

			case 'lb':
				$dest_x = $horiz_margin;
				$dest_y = $image_height - $watermark_height - $vert_margin;
				break;

			case 'mb':
				$dest_x = ( $image_width / 2 ) - ( $watermark_width / 2 );
				$dest_y = $image_height - $watermark_height - $vert_margin;
				break;

			case 'rb':
				$dest_x = $image_width - $watermark_width - $horiz_margin;
				$dest_y = $image_height - $watermark_height - $vert_margin;
				break;

			case 'mm':
			default:
				$dest_x = ( $image_width / 2 ) - ( $watermark_width / 2 );
				$dest_y = ( $image_height / 2 ) - ( $watermark_height / 2 );
		}

		return array( $dest_x, $dest_y );
	}
	

	
	/*
	 * Retrieves external images and setups $img_mime and $rm_img_data
	 */
	private function get_remote_contents($url) {
		$data = wp_remote_get($url, array('timeout' => 8, 'redirection' => 3));

		// nothing got - use cURL 
		if(is_wp_error($data) || 200 != wp_remote_retrieve_response_code($data) || empty($data['body'])) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_USERAGENT, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, LCWW_FOLLOWLOCATION);
			
			$mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
			$contents = curl_exec($ch);

			curl_close($ch);	
		}
		else {
			$mime = $data['headers']['content-type'];
			$contents = $data['body'];	
		}	

		// ok - setup
		$this->img_mime = $mime;
		$this->rm_img_data = $contents;
		
		if(empty($contents)) {
			$this->throw_notice('cURL call returned empty value ('. $url .')', __LINE__);
			return false;	
		} else {
			return true;
		}
	}
	
	
	
	/* create cached filename */
	protected function cache_filename($img_src, $extensionless = false) {
		
		if($extensionless) {
			$ext = '';	
		}
		else {
			switch($this->img_mime) {
				case 'image/jpeg' : $ext = '.jpg'; break;
				case 'image/png'  : $ext = '.png'; break;
				case 'image/gif'  : $ext = '.gif'; break;
			}
		}
		
		$parts = array(md5($img_src), $this->quality, $this->wm_pos, $this->wm_opacity, $this->wm_margin.str_replace('%', 'perc', $this->wm_margin_type), (int)$this->proportional);
		if($this->proportional) {
			$parts[] = implode('-', $this->prop_sizes);
		}
		return implode('_', $parts) . $ext;
	}
	
	
	
	/* prepare the array containing path and url */
	private function wm_paths_arr($img_src) {
		return array(
			'path'	=> $this->cache_folder_dir .'/'. $this->cache_filename($img_src), 
			'url'	=> $this->cache_folder_url .'/'. $this->cache_filename($img_src),
		);	
	}
	
	
		
	///////////////////////////////////////////////////////////////////
	
	
	
	/* know whether a string is an URL or not */
	private function is_url($string) {
		return (strpos( str_replace('https://', 'http://', strtolower($string)), 'http://') !== false || filter_var($string, FILTER_VALIDATE_URL)) ? true : false;	
	}


	/* path to mime-type (only necessary ones - otherwise returns false) */	
	private function path_to_mime($path) {
		
		$arr = explode('.', $path);
		$ext = strtolower(end( $arr ));
		
		switch($ext) {
			case 'jpg' :
			case 'jpeg' :
				$mime = 'image/jpeg';
				break;
				
			case 'png' :
				$mime = 'image/png';
				break;
				
			case 'gif' :
				$mime = 'image/gif';
				break;
					
			default :  
				$mime = false;		
		}
		
		return $mime;
	}	


	private function throw_notice($text, $file_line) {
		if($this->debug_mode) {
			trigger_error($text .' [line '.$file_line.'] &nbsp; ');	
		}
	}
	
}
} // end class existence check
