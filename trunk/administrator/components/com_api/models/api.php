<?php 

class ComApiModelApi extends ComDefaultModelDefault
{
	public function __construct(KConfig $config){
		parent::__construct($config);
		
		$this->getState()->remove('id')->insert('token','string')->insert('enabled','int',1, true);
		
		//Hack for now, for some reason nooku isnt keeping the state of this object
		$this->set('key', KRequest::get('get.api_key','string'))->set('token',KRequest::get('get.api_token','raw'));
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
		if($authenticated !== null && $this->get('key')) return $authenticated;
		
		//Check we have a key
		if(!$this->get('key')){
			$authenticated = false;
        	throw new ComApiException('No API key supplied', KHttpResponse::FORBIDDEN);
			return $authenticated;
		}
	
		//Check we have a token
        if(!$this->get('token')){
        	$authenticated = false;
        	throw new ComApiException('No API token supplied', KHttpResponse::FORBIDDEN);
        	return false;
        }
        		        
		//Get the item
        $item = $this->getItem();
        
        //Check we have an access record
        if(!$item->get('id')){
        	$authenticated = false;
        	throw new ComApiException('No API account found for the supplied API key', KHttpResponse::FORBIDDEN);
        	return false;
        }
        
        /**
         * Below we attempt to re-create the api token
         */        
        //Get the request url
        $uri = KRequest::url();
        $url = $uri->get(KHTTPUrl::BASE);
        
        //Get the request
        $params = array_merge(KRequest::get('get','raw'), KRequest::get('post','raw'));
		
        //Api token MUST be excluded
        unset($params['api_token']);
        
        //Sort and encode the params
        $params = $this->prepareParams($params);
		
		//Check timestamp presence
		$timestamp = isset($params['api_timestamp']) ? $params['api_timestamp'] : null;
		if(!$timestamp){
			throw new ComApiException('No API timestamp given', KHttpResponse::FORBIDDEN);
		}

		//Check timestamp validity
		if($timestamp < time() - 300){
			throw new ComApiException('API timestamp out of date', KHttpResponse::FORBIDDEN);
		}
        
        //Generate the token string
        $string = KRequest::method().'&'.rawurlencode($url).'&'.rawurlencode(http_build_query($params, '', '&'));
		
        //Token is encoded using SHA1, then base64 encoded, then urlencoded
        $token = rawurlencode(base64_encode(hash_hmac('sha1', $string, $item->get('secret'), true)));
        
        //Check if the generated token matches the supplied token
        $authenticated = $token == rawurlencode($this->get('token'));

        //If authenticated, Force the token
        if($authenticated){
        	KRequest::set('request._token', JUtility::getToken());
        }else{
        	throw new ComApiException('API token is invalid', KHttpResponse::FORBIDDEN);
        }

        return $authenticated;
	}
	
	public function authorize($action){
		
		//If no key supplied, return true
		if(!$this->get('key')) return true;
		
		//Check if we're authenticated
		if(!$this->authenticate()) return false;
		
		//Get the api item
		$item = $this->getItem();		
		
		//Check if the action is 1
		return $item->get($action);
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