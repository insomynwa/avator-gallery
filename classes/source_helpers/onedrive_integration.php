<?php
/**
 * Interactions with Microsoft OneDrive REST API
 * 
 * @author Luca Montanari
 */
 
 
class ag_onedrive_integration {
	
	private $secret			= 'xnerSEN45$|}avzHUOE538_';
	private $client_id 		= 'ba38fe35-4885-495f-bffd-0b1a30b8c617';
	
	private $redirect_uri 	= 'https://lcweb.it/';
	private $scope 			= 'files.read offline_access';	


	// tokens stored into ag_onedrive_base_tokens_db option
	public $access_token; 	// coe to be used to access graph (expires, then must be refreshed)
	public $refresh_token;	// token needed to refresh the acess one
	
	public $username; 		// db reference to store tokens 
	public $main_folder_id;
		
	
	/* get data from connection ID and eventually loads tokens */
	public function __construct($connect_id = false, $username = false) {

		if(empty($connect_id)) {
			$this->username	= $username;
		}
		else {
			include_once(AG_DIR .'/functions.php');
			
			$conn_data 		= ag_get_conn_hub_data(false, $connect_id);
			$this->username	= ag_get_arr_key($conn_data, 'onedrive_user');
			
			$tokens = $this->base_tokens_db('get');
			if($tokens) {
				$this->access_token  = $tokens['access'];
				$this->refresh_token = $tokens['refresh'];	
			}
		}
		
		return true;	
	}
	
	
	
	
	/* first check - let user accept the app and get the token */
	public function accept_app() {
		 $params = array(
			'response_type' => 'code',
			'client_id' 	=> $this->client_id,
			'redirect_uri' 	=> $this->redirect_uri,
			'scope' 		=> $this->scope,
		 );
 
		return 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?' . http_build_query($params);
	}
	
	
	
	
	/*
	 * Performs the very first access token retrieval through auth token passed by the user - stores values into ag_onedrive_base_tokens_db 
	 * @return (bool|string) the access token - false if fails
	 */
	public function setup_first_access_token($auth_token) {
		$params = array(
			'client_id' 	=> $this->client_id,
			'client_secret' => $this->secret,
			'redirect_uri' 	=> $this->redirect_uri,
			'grant_type' 	=> 'authorization_code',
            'code' 			=> $auth_token,
		);
		$result = $this->curl_call('https://login.microsoftonline.com/common/oauth2/v2.0/token', $params);
		
		if(is_array($result) && isset($result['access_token'])) {
			
			$this->base_tokens_db('set', array(
				'access' 	=> $result['access_token'], 
				'refresh' 	=> $result['refresh_token'], 
			));
			
			return $result['access_token'];	
		} else {
			return false;	
		}	
	}
	
	
	
	/*
	 * gets refreshed access token and prepare it - stores values into ag_onedrive_base_tokens_db 
	 * @return (bool|string) the access token - false if fails
	 */
	public function prepare_token() {
		$params = array(
			'client_id' 	=> $this->client_id,
			'client_secret' => $this->secret,
			'redirect_uri' 	=> $this->redirect_uri,
			'grant_type' 	=> 'refresh_token',
            'refresh_token'	=> $this->refresh_token,
		);
		$result = $this->curl_call('https://login.microsoftonline.com/common/oauth2/v2.0/token', $params);
		
		if(is_array($result) && isset($result['access_token'])) {
			
			$this->base_tokens_db('set', array(
				'access' 	=> $result['access_token'], 
				'refresh' 	=> $result['refresh_token'], 
			));
			
			$this->access_token  = $result['access_token'];
			$this->refresh_token = $result['refresh_token'];	
			
			return $result['access_token'];	
		} else {
			return false;	
		}			
	}
	
	
	
	/* manage base tokens database to avoid expired tokens
	 * 
	 *  @param string $action - action to perform - set/get
	 *  @param array $tokens - associative array of base token + refresh token
	 */
	public function base_tokens_db($action, $tokens = array('access' => '', 'refresh' => '')) {
		$db = (array)get_option('ag_onedrive_base_tokens_db', array());
		$username = $this->username;
		
		// set
		if($action == 'set') {
			$db[$username] = $tokens;
			update_option('ag_onedrive_base_tokens_db', $db);
		}
		
		// get
		else {
			return (isset($db[$username])) ? $db[$username] : false; 	
		}
	}
	
	
	
	/* perform CURL call */
	private function curl_call($url, $params = array(), $use_token = false) {
		if(!function_exists('curl_version')) {return false;}
		
		/*$headers = array("Content-type" => "application/x-www-form-urlencoded"); 
		if($use_token) {
			$headers['Authorization'] = 'Bearer test'; //. $this->access_token;	
		}*/
		

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL 			=> $url,
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_ENCODING 		=> "",
			CURLOPT_MAXREDIRS 		=> 5,
			CURLOPT_TIMEOUT 		=> 15,
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
			CURLOPT_VERBOSE			=> true,
			CURLINFO_HEADER_OUT		=> true,
		));
		
		if(!empty($params)) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);	
		}
		
		if($use_token) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, 
				array(
					'Content-Type: application/json', 'Authorization: Bearer '. $this->access_token
				)
			);
		}
		
		$response = json_decode( curl_exec($ch), true);
		return $response;
	}

	
	
	
	///////////////////////////////////////////////////////////////////////////////////////////////////
	



	/* GET WRAPPER "avatorgallery" FOLDER ID */
	public function set_wrap_folder_id() {
		if(!$this->prepare_token()) {
			return false;	
		}
		
		$endpoint = 'https://graph.microsoft.com/v1.0/me/drive/root/children?select=name,id';
		$response = $this->curl_call($endpoint, false, true);
		
		if(!is_array($response) || !isset($response['value'])) {
			return false;	
		}
		
		
		// check for agallery folder
		foreach($response['value'] as $folder) {
			
			if(isset($folder['name']) && $folder['name'] == 'agallery') {
				$this->main_folder_id = $folder['id'];
				break;	
			}
			
			/*
			if(isset($folder['specialFolder']) && $folder['specialFolder']['name'] == 'public') {}
			*/
		}
		
		return ($this->main_folder_id) ? $this->main_folder_id : false;
	}
	
	
	
	/* LIST FOLDERS containing galleries
	 * @return (mixed) 
	 	false if connection failed
		albums array (could be empty)
	 */
	public function list_albums() {
		if(!$this->prepare_token()) {
			return false;	
		}

		if(!$this->set_wrap_folder_id()) {
			return __('Main folder missing', 'ag_ml');	
		}
		
		$endpoint = 'https://graph.microsoft.com/v1.0/me/drive/items/'. $this->main_folder_id .'/children'; // ?select=name,id';
		$response = $this->curl_call($endpoint, false, true);
		
		if(!is_array($response) || !isset($response['value'])) {return __('Error querying albums', 'ag_ml');}
		if(!count($response['value'])) {return __('No albums found', 'ag_ml');}
		
		$folders = array();
		foreach($response['value'] as $folder) {
			$folders[ $folder['id'] ] = $folder['name'];
		}
		
		return $folders;
	}
	
	
	
	/* GET IMAGES 
	 * @return (mixed) 
	 	false if connection failed
		albums array (could be empty)
	 */
	public function get_images($folder_id, $search = false) {
		if(!$this->prepare_token()) {
			return __('Connection error', 'ag_ml');	
		}

		if($search) {
			$endpoint = "https://graph.microsoft.com/v1.0/me/drive/items/". $folder_id ."/search(q='". str_replace("'", "\'", $search) ."')";	
		} else {
			$endpoint = 'https://graph.microsoft.com/v1.0/me/drive/items/'. $folder_id .'/children';	
		}

		$response = $this->curl_call($endpoint, false, true);

		if(!is_array($response) || !isset($response['value'])) {
			return __('Error querying the album', 'ag_ml');
		}
		
		$images = array();
		foreach($response['value'] as $file) {
			
			if(!isset($file['image'])) {
				continue;	
			}
			
			$images[] = array(
				'url' 	=> $file['@microsoft.graph.downloadUrl'], 
				'author'=> $file['lastModifiedBy']['user']['displayName'],
				'title'	=> str_replace(array('.jpg', '.jpeg', '.png', '.gif',  '.JPG', '.JPEG', '.PNG', '.GIF'), '', $file['name']),
				'descr'	=> (isset($file['description'])) ? $file['description'] : ''
			);
		}
		
		return $images;
	}


}


