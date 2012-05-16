<?php 

class ComTokensHelperTokens extends KObject implements KServiceInstantiatable
{
	protected $headers = array();
	
	public function __construct(KConfig $config){
		parent::__construct($config);
		
		//Get the model
		$this->model = $this->getService('com://site/tokens.model.tokens');
		
		//Get the token from the headers first
		$header = KRequest::get('server.HTTP_KOOWA_TOKEN','string');
		if($header){
			foreach(explode(';', $header) AS $header){
				$tmp = explode('=',$header);
				if($tmp[0] == 'token' && isset($tmp[1])) $tmp[1] = rawurldecode($tmp[1]); 
				$this->headers[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : null;
			}
		}
		
		//Set the key and token from request array
		$this->model->getState()
			->insert('key','string', $this->getVar('key','string'), true)
			->insert('token','string', $this->getVar('token'));		
	}
	
	
	/**
	 * Ensure only 1 instance of the model is ever set
	 * @param KConfigInterface $config
	 * @param KServiceInterface $container
	 */
	public static function getInstance(KConfigInterface $config, KServiceInterface $container){
		static $instance;
		if(!$instance){
			$instance = new ComTokensHelperTokens($config);
		}
		
		return $instance;
	}
	
	
	/**
	 * Returns the param from either the headers array if present or the request array
	 * @param string $name
	 * @param string $filter
	 */
	public function getVar($name, $filter = 'raw'){
		if(isset($this->headers[$name])){
			return KService::get('koowa:filter.factory')->instantiate($filter)->sanitize($this->headers[$name]);
		}else{
			return KRequest::get('request.api_'.$name,$filter);
		}
	}
	

	/**
	 * Main authenticate method.
	 * Check there is an AIP record for the supplied key
	 * Then verifies the supplied data is correct:
	 * This is done by constructing a string using the request method, uri and parameters
	 * See http://oauth.net/core/1.0/#sig_base_example for example url structure (note: this is not oauth, just the same algorithm)
	 */
	public function authenticate(){
		static $authenticated = null;
		
		//Only run once
		if($authenticated !== null && $this->model->key) return $authenticated;
		
		//Check we have a key
		if(!$this->model->key){
			$authenticated = false;
        	throw new ComTokensException('No API key supplied', KHttpResponse::FORBIDDEN);
			return $authenticated;
		}
	
		//Check we have a token
        if(!$this->model->token){
        	$authenticated = false;
        	throw new ComTokensException('No API token supplied', KHttpResponse::FORBIDDEN);
        	return false;
        }
        		        
		//Get the item
        $this->item = $this->model->set('id', null)->set('enabled', true)->getItem();
        
        //Check we have an access record
        if(!$this->item->get('id')){
        	$authenticated = false;
        	throw new ComTokensException('No API account found for the supplied API key', KHttpResponse::FORBIDDEN);
        	return false;
        }
        
        if(!$this->item->enabled){
        	$authenticated = false;
        	throw new ComTokensException('API account disabled', KHttpResponse::FORBIDDEN);
        	return false;
        }   

        //Store the last request time
        $last_request = date('Y-m-d H', strtotime($this->item->last_request));
        
        //Increment request counter and last request date
        $this->item->requests_in_last_hour++;
        $this->item->requests_total++;
        $this->item->last_request = time();
        $this->item->save();
        
        //Check request count for rate limiting
        if($this->item->last_request){
        	
        	//Check if the last request time is in the last hour
        	if($last_request == date('Y-m-d H')){
        		
        		//Check if we're rate limiting
        		if($this->item->requests_max && $this->item->requests_in_last_hour > $this->item->requests_max){
        			$authenticated = false;
        			throw new ComTokensException('You have exceed the maximum ('.$this->item->requests_max.') requests permitted within 1 hour. Please wait until the next hour.', KHttpResponse::FORBIDDEN);
        			return false;
        		}	
        	}else{
        		$this->item->requests_in_last_hour = 0;
        		$this->item->save();
        	}
        }
        
        
        //Get the users IP
        $ip = KRequest::get('server.REMOTE_ADDR','string');
        
        //Check if IP has been blacklisted
        if($ip_blacklist = $this->item->get('ip_blacklist')){
        	$ip_blacklist = explode("\n", $ip_blacklist);
        	if($this->isIpMatch($ip_blacklist, $ip)){
        		throw new ComTokensException('Your IP has been blacklisted', KHttpResponse::FORBIDDEN);
        	}
        }
        
        //Check if IP is in whitelist
        if($ip_whitelist = $this->item->get('ip_whitelist')){
        	$ip_whitelist = explode("\n", $ip_whitelist);
        	if(!$this->isIpMatch($ip_whitelist, $ip)){
        		throw new ComTokensException('Your IP is not whitelisted', KHttpResponse::FORBIDDEN);
        	}
        } 
        
        
        /**
         * Below we attempt to re-create the api token
         */        
        //Get the request url
        $uri = KRequest::url();
        $url = $uri->get(KHTTPUrl::BASE);
        
        //We get the questrgin from the server var so we know it hasnt altered elsewhere
        parse_str(KRequest::get('server.QUERY_STRING','string'), $querstring);
        
        //Get the request
        $params = array_merge($querstring,KRequest::get('post','raw'));
		
        //Api token MUST be excluded
        unset($params['api_token']);
        
		//Check timestamp presence
		$timestamp = $this->getVar('timestamp');
		if(!$timestamp){
			throw new ComTokensException('No API timestamp given', KHttpResponse::FORBIDDEN);
		}

		//Check timestamp validity
		if($timestamp < time() - 60){
			throw new ComTokensException('API timestamp out of date. Ensure you are using UTC time', KHttpResponse::FORBIDDEN);
		}
		
		//Set timestamp in query
		$params['api_timestamp'] = $timestamp;
		
		//Sort and encode the params
		$params = $this->prepareParams($params);
		
        //Generate the token string
        $token_string = KRequest::method().'&'.rawurlencode($url).'&'.rawurlencode(http_build_query($params, '', '&'));
        
        //Token is encoded using SHA1, then base64 encoded, then urlencoded
        $token = rawurlencode(base64_encode(hash_hmac('sha1', $token_string, $this->item->get('secret'), true)));
        
        //Check if the generated token matches the supplied token
        $authenticated = $token == rawurlencode($this->model->get('token'));
        
        //If authenticated, Force the token
        if($authenticated){
        	KRequest::set('request._token', JUtility::getToken());
        }else{
        	throw new ComTokensException('API token check failed. Check consumer secret.', KHttpResponse::FORBIDDEN);
        }

        return $authenticated;
	}
	
	
	/**
	 * Check if an IP is an IP, if not, try resolving hostname
	 */
	protected function isIpMatch(array $ips, $ip){
		
		foreach($ips AS $ip_addr){
			
			$ip_addr = trim($ip_addr);
			if(!$ip_addr) continue;
			
        	//Check if ip is actually a hostname
        	if(!preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $ip_addr)){
        		$ip_addr = @gethostbyname($ip_addr);
        	}
        	
        	if($ip == $ip_addr) return true;
        }
        return false;
	}
	
	/**
	 * Authorize the request action
	 */
	public function authorize($action){
		
		//If no key supplied, return true
		if(!$this->model->get('key')) return null;
		
		//Check if we're authenticated
		if(!$this->authenticate()) return false;
		
		//Check if the action is 1
		return $this->item->get($action);
	}
	
	/**
	 * Sort the parameters and urlencode the keys and values
	 */
	protected function prepareParams($array){
		
		$return = array();
		foreach($array AS $key => $value){
			$key = rawurlencode($key);
			if(is_array($value)) $return[$key] = $this->prepareParams($value);
			else $return[$key] = rawurlencode($value);			
		}	

		ksort($return);
		return $return;
	}
}