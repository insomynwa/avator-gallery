<?php
/**
 * Interactions with Facebook API v5 - PHP SDK
 * NOTE: Designed to be used with PHP v5.4 and up
 * 
 * @author Luca Montanari
 */
 
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  throw new Exception('Facebook SDK requires PHP version 5.4 or higher');
} 
 
 
class ag_facebook_integration {
	private $client; // client object
	private $app_token; // default app token
	
	private $app_id = '328245323937836';
	private $app_secret = 'fc667c61baec6c55e2354a21006aef94';
	private $redirect_uri = 'http://www.lcweb.it';
	private $scope;	

	public $connect_data = ''; // array containing connection data
	
	
	/* get data from connection ID - or set it manually */
	public function __construct($connect_id, $connect_data = array()) {
		include_once(AG_DIR .'/classes/facebook_sdk/Facebook/autoload.php');
		
		$this->client = new Facebook\Facebook([
			'app_id'     => $this->app_id,
			'app_secret' => $this->app_secret,
			'default_graph_version' => 'v3.1',
		]);
		
		$this->app_token = $this->client->getApp()->getAccessToken();


		if(empty($connect_id)) {
			$this->connect_data = $connect_data;	
		} 
		else {
			include_once(AG_DIR .'/functions.php');
			$this->connect_data = ag_get_conn_hub_data(false, $connect_id);
		}
		
		return true;	
	}
	
	
	
	/* first check - let user accept the app and get token */
	public function accept_app() {
		$permissions = array(); //['email', 'user_posts']; // optional
		$callback = $this->redirect_uri;
		$helper = new Facebook\FacebookRedirectLoginHelper($callback);
		$loginUrl = $helper->getLoginUrl($permissions);
		
		return $helper->getLoginUrl($permissions);
	}

	
	///////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	
	/* GET FB PAGE ID */
	public function page_url_to_id($url) {
		if(strpos($url, 'facebook.com/') === false) {
			return false;	
		}
		
		// manage URL to get last part
		$pos = strpos($url, '?'); 
		if(strpos($url, '?')) {$url = substr($url, 0, $pos);}
		$url_arr = explode('/', untrailingslashit($url));
		
		
		
		// old FB structure reporting a 
		if(strpos($url, 'pages/')) { return end($url_arr); }
		
		
		else {
			$page_username = end($url_arr);
			
			try {
				$page_data = $this->client->get('/'.$page_username, $this->app_token);
			} catch(Exception $e) {
				return false;
			}
			
			$graphObject = $page_data->getGraphObject();
			return $graphObject->getProperty('id');
		}
	}
	


	/* GET ALBUMS */
	public function get_albums() {
		if(isset($this->connect_data['fb_src_switch']) && $this->connect_data['fb_src_switch'] == 'page') {
			$instruction = '/'. $this->connect_data['fb_page_id'] .'/albums?limit=9999';
		}
		else {return false;}

		try {
			$response 	= $this->client->get($instruction, $this->app_token);
			$edge 		= $response->getGraphEdge();
			$graphArray = $edge->asArray();	
			
			if(!is_array($graphArray)) {return false;}
			$fetched_albums = $graphArray;
			
			
			// get next albums (for pages having a ton of albums) */	
			for($a=0; $a < 20; $a++) {
				$edge = $this->client->next($edge); 	
				if(is_null($edge)) {break;}
				
				$fetched_albums = array_merge($fetched_albums, $edge->asArray());
			}

			$albums = array();
			foreach($fetched_albums as $album) {
				$albums[] = array(
					'id'	=> $album['id'],
					'name' 	=> $album['name']
				);	
			}
			
			return $albums;
		} 
		catch(Exception $e) {
			return false;
		}
	}
	
	
	
	/* GET ALBUM IMAGES COUNT */
	public function album_images_count($album_id) {
		try {
			$response = $this->client->get('/'.$album_id.'?fields=count', $this->app_token);
			$data = $response->getGraphObject()->asArray();
			return (int)$data['count'];
		} 
		catch(Exception $e) {
			return false;
		}	
	}
	
	
	
	/* GET ALBUM IMAGES */
	public function album_images($album_id, $limit = 15, $offset = 0) {

		$true_limit = ($limit > 100) ? 100 : $limit;  
		
		if($this->connect_data['fb_src_switch'] == 'page') {
			$instruction = '/'.$album_id.'/photos?fields=name,images,from&limit='. $true_limit .'&offset='. $offset;
		}
		else {return false;}
		


		try {
			$response 	= $this->client->get($instruction, $this->app_token);
			$edge 		= $response->getGraphEdge();
			$graphArray = $edge->asArray();	
			
			if(!is_array($graphArray)) {return false;}
			$fetched_images = $graphArray;

			
			// max single-page limit is 100 - if needs more than 100, cycle
			if($limit > 100) {

				// ----get next images (until a limit of 2000)
				
				// OCT 2018 - to avoid FB limits, lower to 300 images	
				for($a=0; $a < 3; $a++) {
					$edge = $this->client->next($edge); 	
					if(is_null($edge)) {break;}
					
					$fetched_images = array_merge($fetched_images, $edge->asArray());
					if(count($fetched_images) >= $limit) {
						break;	
					}
				}
			}
			
			
			return $fetched_images;
		} 
		catch(Exception $e) {
			return false;
		}


	}
}


