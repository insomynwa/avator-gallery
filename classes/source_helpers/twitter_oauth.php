<?php
/**
 *  Fast twitter OAuth - by Mike Rogers
 * "classified" by Luca Montanari aka LCweb
 *
 *  Usage:
 *  Send the url you want to access url encoded in the url paramater, for example (This is with JS): 
 *  /twitter-proxy.php?url='+encodeURIComponent('statuses/user_timeline.json?screen_name=MikeRogers0&count=2')
*/



class ag_twitter_oauth {


	// The tokens, keys and secrets from the app you created at https://dev.twitter.com/apps
	public $config = array(
		'oauth_access_token' 		=> '482910828-q5IPdwomG68qoNOTzH5WnJxFMInBtaKxYaAQSN3k',
		'oauth_access_token_secret' => 'EGn4S9kndcguKTgkSAU0r3yS9hP9atJh9lSzyybo9Sy6K',
		'consumer_key' 				=> 'StiMETKBFES8pSm0Ssdc0LgkB',
		'consumer_secret' 			=> 'eB9KxEWIFzTMi3pBYWlWMNDUflgKzkP1xjAnnabTTIyvgNTCGN',
		'use_whitelist' 			=> false, // If you want to only allow some requests to use this script.
		'base_url' 					=> 'https://api.twitter.com/1.1/'
	);
	
	
	// Only allow certain requests to twitter. Stop randoms using your server as a proxy.
	public $whitelist = array();
	
	
	// API cURL call result - array
	public $data = array();  
		
		
	
	/* setup data and return call result */
	public function __construct($url) {
		$config = $this->config;
		$whitelist = $this->whitelist;
		
		if($config['use_whitelist'] && !isset($whitelist[$url])){
			die('URL is not authorised');
		}
	
		// Figure out the URL parmaters
		$url_parts = parse_url($url);
		parse_str($url_parts['query'], $url_arguments);
		
		$full_url = $config['base_url'].$url; // Url with the query on it.
		$base_url = $config['base_url'].$url_parts['path']; // Url without the query.
		

		/* PERFORM CALL */

		// Set up the oauth Authorization array
		$oauth = array(
			'oauth_consumer_key' => $config['consumer_key'],
			'oauth_nonce' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_token' => $config['oauth_access_token'],
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0'
		);
			
		$base_info = $this->buildBaseString($base_url, 'GET', array_merge($oauth, $url_arguments));
		$composite_key = rawurlencode($config['consumer_secret']) . '&' . rawurlencode($config['oauth_access_token_secret']);
		$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
		$oauth['oauth_signature'] = $oauth_signature;
		
		// Make Requests
		$header = array(
			$this->buildAuthorizationHeader($oauth), 
			'Expect:'
		);
		$options = array(
			CURLOPT_HTTPHEADER => $header,
			//CURLOPT_POSTFIELDS => $postfields,
			CURLOPT_HEADER => false,
			CURLOPT_URL => $full_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		);
		
		$feed = curl_init();
		curl_setopt_array($feed, $options);
		$result = curl_exec($feed);
		$info = curl_getinfo($feed);
		curl_close($feed);
		
		$this->data = json_decode($result);
		return true;
	}
	
	
	
	/**
	* Code below from http://stackoverflow.com/questions/12916539/simplest-php-example-retrieving-user-timeline-with-twitter-api-version-1-1 by Rivers 
	* with a few modfications by Mike Rogers to support variables in the URL nicely
	*/
	
	private function buildBaseString($baseURI, $method, $params) {
		$r = array();
		ksort($params);
		foreach($params as $key=>$value) {
			$r[] = "$key=" . rawurlencode($value);
		}
		
		return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
	}
	
	
	private function buildAuthorizationHeader($oauth) {
		$r = 'Authorization: OAuth ';
		$values = array();
		foreach($oauth as $key=>$value) {
			$values[] = "$key=\"" . rawurlencode($value) . "\"";
		}
		$r .= implode(', ', $values);
		return $r;
	}
	
}
