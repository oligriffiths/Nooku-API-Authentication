<?php
/**
 * Koowa Tokens System plugin
.*
 * @author      Oli Griffiths <organic-development.com>
 * @category    Nooku
 * @package     Nooku_Plugins
 * @subpackage  System
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

class plgSystemKoowa_Tokens extends JPlugin{
	
	public function __construct($subject, $config = array()){
		parent::__construct($subject, $config);

		//Change the Joomla error handler to our own local handler and call it
		JError::setErrorHandling( E_ERROR, 'callback', array($this,'errorHandler'));
		
		//Set the exception handler
		set_exception_handler(array($this, 'exceptionHandler'));		
	}
	

	/**
	 * On after intitialse event handler
	 * 
	 * This functions implements API authentication support
	 * 
	 * @return void
	 */
	public function onAfterInitialise()
	{ 
		require_once dirname(__FILE__).'/koowa_tokens/behaviors/executable.php';
		require_once dirname(__FILE__).'/koowa_tokens/exceptions/exception.php';

		//Get the token helper
		$tokens = KService::get('com://site/tokens.helper.tokens');
		
		//If no key was supplied return here
		if(!$tokens->key) return;
		
		$tokens->authenticate();
	}
	
	/**
	 * Catch all exception handler
	 *
	 * Calls the Joomla error handler to process the exception
	 *
	 * @param object an Exception object
	 * @return void
	 */
	public function exceptionHandler($exception)
	{
		$this->_exception = $exception; //store the exception for later use
		
		//Change the Joomla error handler to our own local handler and call it
		JError::setErrorHandling( E_ERROR, 'callback', array($this,'errorHandler'));
		
		//Make sure we have a valid status code
		JError::raiseError(KHttpResponse::isError($exception->getCode()) ? $exception->getCode() : 500, $exception->getMessage());
	}
	
	
	/**
	 * Custom JError callback
	 *
	 * Push the exception call stack in the JException returned through the call back
	 * adn then rener the custom error page
	 *
	 * @param object A JException object
	 * @return void
	 */
	public function errorHandler($error)
	{	/*
		$error->setProperties(array(
			'backtrace'	=> $this->_exception->getTrace(),
			'file'		=> $this->_exception->getFile(),
			'line'		=> $this->_exception->getLine()
		));
		
		
	    if(JFactory::getConfig()->getValue('config.debug') || 1) {
			$error->set('message', (string) $this->_exception);
		} else {
			$error->set('message', KHttpResponse::getMessage($error->get('code')));
		}*/
		
	    if($error->getCode() == KHttpResponse::UNAUTHORIZED) {
		   header('WWW-Authenticate: Basic Realm="'.KRequest::base().'"');
		}
		
		//Make sure the buffers are cleared
		while(@ob_get_clean());
				
		//Throw json formatted error
		$content = KRequest::content('type');
		$accept = KRequest::accept('format');
		$json = isset($accept['application/json']) && $accept['application/json'];
		
		if(	KRequest::get('request.format', 'cmd') == 'json' || $content == 'application/json' || $json){
			$app =& JFactory::getApplication();		

			//Get the public properties
			$properties = $error->getProperties();
			unset($properties['backtrace']);
			
			//If not debugging, do not expose file locations
			if(!KDEBUG){
				unset($properties['file']);
				unset($properties['line']);
				unset($properties['function']);
				unset($properties['class']);
				unset($properties['args']);
			}			
			ksort($properties);
			
			//Encode data
			$data = json_encode($properties);			
			JResponse::setHeader('status',$error->getCode());
			JResponse::setHeader('Content-Type','application/json');
			JResponse::setBody($data);
			echo JResponse::toString();
			$app->close(0);
		}else{			
			JError::customErrorPage($error);
		}
	}
}