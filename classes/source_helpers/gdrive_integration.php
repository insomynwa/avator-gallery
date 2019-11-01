<?php
/**
 * Interactions with Google API - PHP SDK
 * NOTE: Designed to be used with PHP v5.4 and up
 * 
 * @author Luca Montanari
 */
 
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  throw new Exception('Google SDK requires PHP version 5.4 or higher');
} 
 
 
class ag_gdrive_integration {
	
	private $client; // client object set up by google class
	
	private $client_id = '30140518177-feqfop7mqku6tla4f0mhde175pv5epig.apps.googleusercontent.com';
	private $client_secret = 'oJiuW8STONYXlftpX5SQYec6';
	private $redirect_uri = 'https://lcweb.it';
	private $scope;	

	private $refresh_obj; // refresh token object
	private $real_token = '';
	private $access_token = '';
	private $curl_cust_req = false;
	private $service;
	
	public $main_folder_id; // contains "avatorgallery" wrapper folder in googledrive account
	public $g_username = false;
	
	
	/* get google username from connection ID - or set it manually */
	public function __construct($connect_id, $username = false) {
		if(!function_exists('google_api_php_client_autoload')) {
			include_once(AG_DIR .'/classes/google-api-php-client-1.1.7/src/Google/autoload.php');
		}
		
		$this->scope = implode(' ', array(Google_Service_Drive::DRIVE_METADATA_READONLY));

		
		$client = new Google_Client(); 
		
		$client->setClientId( $this->client_id );
		$client->setClientSecret( $this->client_secret );
		$client->setRedirectUri( $this->redirect_uri );
		
		$client->setAccessType('offline');
		$client->setApprovalPrompt('force');
		
		$client->addScope($this->scope);
		$this->client = $client;


		if(empty($connect_id)) {
			$this->g_username = $username;	
		} 
		else {
			include_once(AG_DIR .'/functions.php');
			$conn_data = ag_get_conn_hub_data(false, $connect_id);
			$this->g_username = ag_get_arr_key($conn_data, 'gdrive_user');
		}
		
		return true;	
	}
	
	
	
	/* first check - let user accept the app and get refresh token */
	public function accept_app() {
		 $params = array(
			'response_type' => 'code',
			'client_id' => $this->client_id,
			'redirect_uri' => $this->redirect_uri,
			'access_type' => 'offline',
			'scope' => $this->scope,
			'approval_prompt' => 'force'
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
				array('Content-Type: application/json','Authorization: Bearer '. $this->access_token, 'X-JavaScript-User-Agent: Avator Gallery', 'GData-Version: 2')
			);
		}
		if($params) {
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
		catch(Exception $e) {
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
		$db = (array)get_option('ag_gdrive_base_tokens_db', array());
		$username = $this->g_username;
		
		// set
		if($action == 'set') {
			$db[$username] = $tokens;
			update_option('ag_gdrive_base_tokens_db', $db);
		}
		
		// get
		else {
			return (isset($db[$username])) ? $db[$username] : false; 	
		}
	}
	

	
	/* 
	 * check if is possible to perform a request and setup service property
	 * get temporary access token
	 */
	private function is_ok() {
		if(empty($this->real_token)) {
			$stored_token = $this->base_tokens_db('get');
			$this->real_token = (empty($stored_token)) ? false : $stored_token['refresh'];
			
			if(!$this->real_token) {return false;}
		}
		
		
		// setup service
		$this->client->refreshToken( $this->real_token );
		
		$access_token = json_decode($this->client->getAccessToken());
		$this->access_token = $access_token->access_token;
		
		$this->service = new Google_Service_Drive( $this->client );
		return true;
	}
	
	
	
	/* test connection */
	public function test_conn() {
		if($this->is_ok()) {		
			$optParams = array(
			  'maxResults' => 1000,
			   'q' => "mimeType = 'application/vnd.google-apps.folder'"
			);
			return $this->service->files->listFiles($optParams);
		
		
		}
		else {
			return false;	
		}
	}
	
	
	
	
	//////////////////////////////////////////////////////////////////////////////////////////////////
	

	/* GET WRAPPER "avatorgallery" FOLDER ID */
	public function set_wrap_folder_id() {
		if($this->is_ok()) {
			$optParams = array(
				'maxResults' => 1000,
				'q' => "mimeType = 'application/vnd.google-apps.folder'"
			);
			
			$results = $this->service->files->listFiles($optParams);
			$items = $results->getItems();
			if(!is_array($items)) {
				$this->main_folder_id = false;
				return false;
			}
			
			
			foreach($items as $file) {
				if($file->getTitle() == 'avatorgallery') {
					$this->main_folder_id = $file->getId();
					break;	
				}
			}
			
			return $this->main_folder_id;
		}
		else {
			return false;	
		}
	}
		


	
	/* LIST FOLDERS containing galleries
	 * @return (mixed) 
	 	false if connection failed
		albums array (could be empty)
	 */
	public function list_albums() {
		if($this->is_ok()) {
			if(!$this->set_wrap_folder_id()) {
				return __('Main folder missing', 'ag_ml');	
			}
			
			$optParams = array(
				'maxResults' => 1000,
				'q' => "mimeType = 'application/vnd.google-apps.folder' and '". $this->main_folder_id ."' in parents"
			);
			$results = $this->service->files->listFiles($optParams);
			$items = $results->getItems();
			
			if(!is_array($items)) {return __('Error querying albums', 'ag_ml');}
			if(!count($items)) {return __('No albums found', 'ag_ml');}
			
			$folders = array();
			foreach($results->getItems() as $file) {
				$folders[ $file->getId() ] = $file->getTitle();
			}
			
			return $folders;
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
	public function get_images($folder_id, $search = false) {
		if($this->is_ok()) {
			if(!$this->set_wrap_folder_id()) {
				return __('Main folder missing', 'ag_ml');	
			}
			
			$search_part = (empty($search)) ? '' : "and fullText contains '". str_replace("'", "\'", $search) ."'";
			
			$optParams = array(
				'maxResults'=> 999,
				'orderBy'	=> (empty($search)) ? 'title' : '', // Google doesn't allow sorting on search
				'q'			=> "'". $folder_id ."' in parents and trashed = false and (mimeType = 'image/jpeg' or mimeType = 'image/png' or mimeType = 'image/gif') ". $search_part, 
				'fields'	=> 'items(description,owners/displayName,title,webContentLink)'
			);
			$results = $this->service->files->listFiles($optParams);
			
			$images = array();
			foreach($results->getItems() as $file) {
				$images[] = array(
					'url' 	=> str_replace('&export=download', '', $file->webContentLink), 
					'author'=> $file->owners[0]->displayName,
					'title'	=> $file->title,
					'descr'	=> $file->description
				);
			}
			
			return $images;
		}
		else {
			return false;	
		}
	}

}
