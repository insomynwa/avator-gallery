<?php
/**
 * Interactions with Google API - PHP SDK
 * NOTE: Designed to be used with PHP v5.4 and up
 *
 * Updated on January 2019 using the new photoslibrary API
 * 
 * @author Luca Montanari
 */
 
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  throw new Exception('Google SDK requires PHP version 5.4 or higher');
} 
 
 
class ag_gplus_integration {
	
	private $client; // client object set up by google class
	
	private $client_id = '30140518177-feqfop7mqku6tla4f0mhde175pv5epig.apps.googleusercontent.com';
	private $client_secret = 'oJiuW8STONYXlftpX5SQYec6';
	private $redirect_uri = 'https://lcweb.it';
	private $scope = "https://www.googleapis.com/auth/photoslibrary.readonly";

	private $real_token = '';
	private $curl_cust_req = false;
	public $g_username = false;
	
	
	/* get google username from connection ID - or set it manually */
	public function __construct($connect_id, $username = false) {
		if(!function_exists('google_api_php_client_autoload')) {
			include_once(AG_DIR .'/classes/google-api-php-client-1.1.7/src/Google/autoload.php');
		}
		
		$client = new Google_Client(); 
		
		$client->setClientId( $this->client_id );
		$client->setClientSecret( $this->client_secret );
		$client->setRedirectUri( $this->redirect_uri );
		
		$client->setAccessType('offline');
		$client->setApprovalPrompt('force');
		
		$client->addScope("openid email");
		$client->addScope($this->scope);
		$this->client = $client;


		if(empty($connect_id)) {
			$this->g_username = $username;	
		} 
		else {
			include_once(AG_DIR .'/functions.php');
			$conn_data = ag_get_conn_hub_data(false, $connect_id);
			$this->g_username = ag_get_arr_key($conn_data, 'gplus_user');
		}
		
		return true;	
	}
	
	
	
	/* first check - let user accept the app and get refresh token */
	public function accept_app() {
		 $params = array(
			'response_type' 	=> 'code',
			'client_id' 		=> $this->client_id,
			'redirect_uri' 		=> $this->redirect_uri,
			'access_type' 		=> 'offline',
			'scope' 			=> $this->scope,
			'approval_prompt' 	=> 'force'
		 );
 
		return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
	}


	
	/* perform CURL call */
	private function curl_call($url, $params = false, $with_header = true) {
		if(!function_exists('curl_version')) {return false;}
		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        
        curl_setopt($ch, CURLOPT_HEADER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
       
	    if($with_header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, 
				array('Content-Type: application/json','Authorization: Bearer '. $this->real_token, 'X-JavaScript-User-Agent: Avator Gallery', 'GData-Version: 2')
			);
		}
		if(!empty($params)) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		
		if($this->curl_cust_req) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->curl_cust_req);	
		}
		
		return curl_exec($ch);
	}


    /* get access and refresh token - to perform only in connection setup 
	 * @param (string) $base_token - initial token submitted by user
	 */
    public function get_access_token($base_token) {
		try {
			$this->client->authenticate( urldecode($base_token) );
			$data = json_decode($this->client->getAccessToken()); 
		} 
		catch (Exception $e) {
			return false;	
		}
		
		if(isset($data->refresh_token)) {
			// store 
			$this->base_tokens_db('set', array('base' => $base_token, 'refresh' => $data->refresh_token)); 
			return $data->refresh_token;
		} 
		else {
			//var_dump($data); // debug
			return false;	
		}
	}
	
	
	
	/* manage base tokens database to avoid expired tokens
	 * 
	 *  @param string $action - action to perform - set/get
	 *  @param array $tokens - associative array of base token + refresh token
	 */
	public function base_tokens_db($action, $tokens = array('base' => '', 'refresh' => '')) {
		$db = (array)get_option('ag_gplus_base_tokens_db', array());
		$username = $this->g_username;
		
		// set
		if($action == 'set') {
			$db[$username] = $tokens;
			update_option('ag_gplus_base_tokens_db', $db);
		}
		
		// get
		else {
			return (isset($db[$username])) ? $db[$username] : false; 	
		}
	}
	
	
	
	/* get a refreshed token */
	public function get_refreshed_token() {
		// get refresh toke from database
		$stored_token = $this->base_tokens_db('get'); 
		if(!$stored_token) {return false;}

		$tokenURL = 'https://accounts.google.com/o/oauth2/token';
        $postData = array(
        	'refresh_token' => $stored_token['refresh'],
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type'    => 'refresh_token',
        );

		$data = json_decode( $this->curl_call($tokenURL, $postData, false) );
		if(isset($data->access_token)) {
			return $data->access_token;
		} else {
			//var_dump($data); // debug
			return false;	
		}
    }


	
	/* 
	 * check if is possible to perform a request - all data are ok 
	 * get temporary access token
	 */
	private function is_ok() {
		$this->real_token = $this->get_refreshed_token();
		return ($this->real_token) ? true : false;
	}
	
	
	
	
	
	
	//////////////////////////////////////////////////////////////////////////////////////////////////
	




	
	/* GET ALBUMS 
	 * @return (mixed) 
	 	false if connection failed
		albums array (could be empty)
	 */
	public function get_albums() {
		$url = 'https://photoslibrary.googleapis.com/v1/albums?pageSize=50';
		
		if($this->is_ok()) {
			$data = json_decode( $this->curl_call($url), true);
			
			if(!isset( $data['albums'] )) {
				
				if(isset($_REQUEST['ag_php_debug'])) {
					var_dump($data);	
				}
				return false;	
			}
			
			$albums = array();
			foreach($data['albums'] as $album) {
				$albums[ $album['id'] ] = $album['title'];		
			}
			
			return $albums;
		}
		else {
			return false;
		}
	}
	
	
	
	
	/* GET IMAGES 
	 * @return (mixed) 
	 	false if connection failed
		albums array (could be empty)
	 */
	public function get_images($album_id, $recursive_pag_token = false) {

		$url = 'https://photoslibrary.googleapis.com/v1/mediaItems:search';
		$params = json_encode(array(
			'pageSize' => 100,
			'albumId' => $album_id
		));
		
		
		if($this->is_ok()) {
			$data = json_decode( $this->curl_call($url, $params), true);
				
			if(!isset( $data['mediaItems'] )) {
				
				if(isset($_REQUEST['ag_php_debug'])) {
					var_dump($data);	
				}
				return false;	
			}

			// fetch next images
			if(isset($data['nextPageToken']) && !empty($data['nextPageToken'])) {
				
				return array_merge($data['mediaItems'], $this->get_images($album_id, $data['nextPageToken']));				
			}
			else {
				return $data['mediaItems'];
			}
		}
		else {
			return false;	
		}
	}

}
