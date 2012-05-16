<?php 
/**
 * Nooku request signing class. Requests are signed using a consumer key and secret
 * @author Oli Griffiths
 */
class NookuRequest
{
	public $host;
	public $querystring = array();
	public $headers = array();
	public $key;
	public $secret;
	public $username;
	public $password;
	public $format = 'json';
	public $curl_options = array(
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_TIMEOUT => 10,
			
			/**
			 * To use a cookie jar set these variables. 
			 * Be aware that the cookies will be shared for all connections that use this script if the cookiejar name is the same
			 */
			//CURLOPT_COOKIEJAR => './cookies.txt'
	);
	

	public function __construct($uri, $api_key, $api_secret, $username = '', $password = '', $format = 'json')
	{
		$uri = explode('?',$uri);
		$this->uri = $uri[0];
		if(isset($uri[1]) && $uri[1]) parse_str($uri[1], $this->querystring);
		$this->key = $api_key;
		$this->secret = $api_secret;
		$this->username = $username;
		$this->password = $password;
		$this->format = $format;
	}
	

	/**
	 * GET method, accepts URL and curl options
	 */
	public function get($query = array(), $options = array())
	{
		return $this->request('GET', $query, array(), $options);
	}


	/**
	 * POST method, accepts URL, data and curl options
	 */
	public function post($query = array(), $data = array(), $options = array())
	{
		return $this->request('POST', $query, $data, $options);
	}


	/**
	 * PUT method, accepts URL, data and curl options
	 */
	public function put($query = array(), $data = array(), $options = array())
	{
		return $this->request('PUT', $query, $data, $options);
	}


	/**
	 * DELETE method, accepts URL and curl options
	 */
	public function delete($query = array(), $options = array())
	{
		return $this->request('DELETE', $query, array(), $options);
	}


	/**
	 * Generic request function called by helper methods above
	 * Gets the tokenized request
	 */
	protected function request($method, $query = array(), $data = array(), $options = array())
	{
		//Merge in the supplied querystring
		$query = array_merge($this->querystring, $query);
		
		//Set the format
		$query['format'] = $this->format;

		//Tokenize the url
		$url = $this->tokenize($method, $query, $data);

		//Run the request
		$response = $this->curl_request($url, $method, $data, $options);
		
		return $response;
	}


	/**
	 * Tokenizing funciton creates a token from the URL and REQUEST params
	 */
	protected function tokenize($method, $query = array(), $data= array())
	{
		//Prepare the url for encoding etc
		$uri = $this->prepareURI($this->uri, $query);

		//Prepare the data for encoding
		$data = $this->encodeParams($data);

		//Generate the request token
		$token = $this->generateToken($method, $uri['uri'], $uri['query'], $data);
		
		//Set the token in the header
		$this->headers['token'] = $token;

		return $uri['uri'].'?'.http_build_query($uri['query']);//.'&api_token='.$token;
	}


	/**
	 * Prepares the URL for tokenizing
	 * Converts a querystringed url into URI and QUERY, with the query sorted by key and values urlencoded
	 */
	protected function prepareURI($url, $query = array())
	{
		//Add the key
		$this->headers['key'] = $this->key;

		//Set the timestamp
		$this->headers['timestamp'] = time();
		
		ksort($query);

		return array('uri'=> $url, 'query'=> $query);
	}


	/**
	 * Tonken generation function according to the following formula
	 * urlencode( base64_encode( hash_hmac( 'sha1', 'METHOD&urlencode(URI)&urlencode(ksort(REQUEST_PARAMS))', SECRET )
	 */
	protected function generateToken($method, $url, $query = array(), $data = array())
	{
		//Merge request data,
		$params = array_merge($query, $data);

		//Encode the params
		$params = $this->encodeParams($params);
		
		//Set the timestamp in the token
		$params['api_timestamp'] = $this->headers['timestamp'];

		//Sort params
		ksort($params);

		//Create the token string
		$token_string = $method.'&'.rawurlencode($url).'&'.rawurlencode(http_build_query($params));

		//Encode the token string
		$token = rawurlencode(base64_encode(hash_hmac('sha1', $token_string, $this->secret, true)));

		return $token;
	}


	/**
	 * Urlencode the keys and values
	 */
	protected function encodeParams($array){

		$return = array();
		foreach($array AS $key => $value){
			$key = rawurlencode($key);
			if(is_array($value)) $return[$key] = $this->encodeParams($value);
			else $return[$key] = rawurlencode($value);
		}
		return $return;
	}


	/**
	 * Runs a CURL request
	 */
	protected function curl_request($url, $type = "GET", array $post = array(), array $options = array())
	{
		$defaults = $this->curl_options;
		$defaults[CURLOPT_CUSTOMREQUEST] = $type;
		$defaults[CURLOPT_URL] = $url;
		
		//Set the headers
		$headers = array();
		foreach($this->headers AS $k => $v) $headers[] = $k.'='.$v;
		$defaults[CURLOPT_HTTPHEADER][] = 'KOOWA_TOKEN:'.implode(';', $headers);
		
		//Set the username/pw if set
		if($this->username) $defaults[CURLOPT_HTTPHEADER][] = 'Authorization:Basic '.base64_encode($this->username.':'.$this->password);
		

		//POST and PUT operations have postfields
		if($type == 'POST' || $type == 'PUT'){
			$defaults[CURLOPT_POST] = true;
			$defaults[CURLOPT_POSTFIELDS] = http_build_query($post);
		}

		//Combine options
		$options = $options + $defaults;
		
		
		//Set cookie file if cookiejar is set and cookiefile is not
		if(isset($options[CURLOPT_COOKIEJAR]) && !isset($options[CURLOPT_COOKIEFILE])) $options[CURLOPT_COOKIEFILE] = $options[CURLOPT_COOKIEJAR];
		
		
		//Get cookies & set them in the request if we're not using a cookie jar
		if((!isset($options[CURLOPT_COOKIEJAR]) || !is_file($options[CURLOPT_COOKIEJAR])) && !isset($options[CURLOPT_COOKIE])){
			$cookies = array();
			foreach($_COOKIE AS $cookie => $value){
				if(preg_match('/^K_/', $cookie)) $cookies[] = preg_replace('/^K_/','',$cookie).'='.$value;
			}
			//Set cookie header
			if(count($cookies)) $options[CURLOPT_COOKIE] = implode('; ',$cookies);
		}
		

		//Always request header
		$options[CURLOPT_HEADER] = 1;

		$curl = curl_init();
		curl_setopt_array($curl, $options);
		if( ! $result = curl_exec($curl))
		{
			trigger_error(curl_error($curl));
		}

		
		//Create response
		$response = new NookuApiResponse($curl, $result);
		
		
		//If no cookie jar set, try to set the cookies locally
		$cookies = $response->getHeader('Set-Cookie');
		if((!isset($options[CURLOPT_COOKIEJAR]) || !is_file($options[CURLOPT_COOKIEJAR])) && $cookies){
			foreach((array) $cookies AS $cookie){
				$parts = explode(';', $cookie);
		
				$settings = array();
				foreach($parts AS $part){
					$tmp = explode('=', $part);
					$settings = array_merge($settings, array(trim($tmp[0]) => $tmp[1]));
				}
		
				reset($settings);
				$settings = array_merge($settings, array('expires' => null, 'path' => null, 'domain' => null, 'secure' => null, 'httponly' => null));
				if($settings['expires']) $settings['expires'] = strtotime($settings['expires']);
				setcookie('K_'.key($settings), current($settings), $settings['expires'], $settings['path'],$settings['domain'],$settings['secure'],$settings['httponly']);
			}
		}

		curl_close($curl);
		return $response;
	}
}



/**
 * Nooku response class.
 * This class holds the response from a nooku api call
 * @author Oli Griffiths
 */
class NookuApiResponse
{
	protected $headers = array();
	protected $body;
	protected $headers_raw;
	protected $body_raw;

	public function __construct(&$curl, $response)
	{
		//Split headers and body
		$result = explode("\r\n\r\n", $response, 2);

		//Add headers
		$this->headers_raw = $result[0];
		$headers = explode("\r\n", $this->headers_raw);

		foreach($headers AS $header){
			if(strstr($header, 'HTTP/')) $header = explode(' ', $header, 2);
			else $header = explode(':', $header, 2);
			
			if($header[0] == 'Set-Cookie'){
				if(!isset($this->headers['Set-Cookie'])) $this->headers['Set-Cookie'] = array();
				$this->headers['Set-Cookie'][] = trim($header[1]);
			}else{
				$this->headers[$header[0]] = trim($header[1]);
			}			
		}		

		//Set some required headers
		$this->headers['status'] = curl_getinfo(&$curl, CURLINFO_HTTP_CODE);
		$this->headers['headers']['Content-Type'] = curl_getinfo(&$curl, CURLINFO_CONTENT_TYPE);
	

		//Set the body
		$this->body = isset($result[1]) ? $result[1] : $result[0];
		$this->body_raw = $this->body;

		//If json response, decode body
		if(strstr($this->getHeader('Content-Type'), 'application/json')){
			$this->body = json_decode($this->body);
		}
	}

	/**
	 * Retrieves the response body
	 */
	public function getBody($raw = false)
	{
		return $raw ? $this->body_raw : $this->body;
	}

	/**
	 * Retrieves requests headers
	 */
	public function getHeaders($raw = false)
	{
		return $raw ? $this->headers_raw : $this->headers;
	}

	/**
	 * Returns an individual header from the request
	 */
	public function getHeader($header)
	{
		return isset($this->headers[$header]) ? $this->headers[$header] : null;
	}

	/**
	 * Gets the request status
	 */
	public function getStatus()
	{
		return $this->getHeader('status');
	}

	public function __toString()
	{
		return $this->headers_raw."\r\n\r\n".$this->body_raw;
	}
}
