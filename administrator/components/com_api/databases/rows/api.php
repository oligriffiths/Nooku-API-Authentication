<?php 

class ComApiDatabaseRowApi extends KDatabaseRowDefault{
	
	/**
	 * Overridden save function to generate the key and secret
	 */
	public function save(){
		
		if(strlen($this->get('key')) != 32){
			$this->set('key', $this->generateKey(true));
		}

		if(strlen($this->get('secret')) != 32 || $this->get('generate_secret')){
			$this->set('secret', $this->generateKey());
		}
		
		return parent::save();
	}
	
	/**
	 * Generate a unique key
	 * 
	 * @param boolean unique	force the key to be unique
	 * @return string
	 */
	public function generateKey ( $unique = false )
	{
		$key = md5(uniqid(rand(), true));
		if ($unique)
		{
			list($usec,$sec) = explode(' ',microtime());
			$key .= dechex($usec).dechex($sec);
			$key = md5($key);
		}
		return $key;
	}
	
}

