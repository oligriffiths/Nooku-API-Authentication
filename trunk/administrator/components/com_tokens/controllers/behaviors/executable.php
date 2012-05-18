<?php
/**
 * Tokens Controller Executable Command
.*
 * @author      Oli Griffiths <oli@organic-development.com>
 * @category    Nooku
 * @package     Nooku_Server
 * @subpackage  Tokens
 */
class ComDefaultControllerBehaviorExecutable extends KControllerBehaviorExecutable
{ 
	protected $action;
	
 	/**
     * Command handler
     * 
     * @param   string      The command name
     * @param   object      The command context
     * @return  boolean     Can return both true or false.  
     * @throws  KControllerException
     */
    public function execute( $name, KCommandContext $context) 
    { 
        $parts = explode('.', $name); 

        if($parts[0] == 'before') 
        { 
        	$this->action = $parts[1];
            if(!$this->_checkToken($context)) 
            {    
                $context->setError(new KControllerException(
                	'Invalid token or session time-out', KHttpResponse::FORBIDDEN
                ));
                
                return false;
            }
        }
        
        return parent::execute($name, $context); 
    }
    
	/**
     * Generic authorize handler for controller browse actions
     * 
     * @return  boolean     Can return both true or false.  
     */
    public function canBrowse()
    {
        $auth = $this->getService('com://admin/tokens.helper.tokens')->authorize($this->action);
		return $auth === null ? true : $auth;
    }
    
	/**
     * Generic authorize handler for controller read actions
     * 
     * @return  boolean     Can return both true or false.  
     */
    public function canRead()
    {
        $auth = $this->getService('com://admin/tokens.helper.tokens')->authorize($this->action);
		return $auth === null ? true : $auth;
    }
    
    /**
     * Generic authorize handler for controller add actions
     * 
     * @return  boolean     Can return both true or false.  
     */
    public function canAdd()
    {
        $result = false;
        
        if(parent::canAdd())
        {
        	$isAdmin = JFactory::getApplication()->isAdmin();        	        	
            if(version_compare(JVERSION,'1.6.0','ge')) {
                $result = JFactory::getUser()->authorise('core.create') === true;
            } else {
                $result = JFactory::getUser()->get('gid') > ($isAdmin ? 22 : 18);
            }
			
			if($this->getService('com://admin/tokens.helper.tokens')->authorize($this->action) === false){
        		$result = false;
        	}
        }
		
        return $result;
    }
    
    /**
     * Generic authorize handler for controller edit actions
     * 
     * @return  boolean     Can return both true or false.  
     */
    public function canEdit()
    {
        $result = false;
        
        if(parent::canEdit())
        {
        	$isAdmin = JFactory::getApplication()->isAdmin();        	
            if(version_compare(JVERSION,'1.6.0','ge')) {
                $result = JFactory::getUser()->authorise('core.edit') === true;
            } else {
                $result = JFactory::getUser()->get('gid') > ($isAdmin ? 22 : 19);
            }
			
			if($this->getService('com://admin/tokens.helper.tokens')->authorize($this->action) === false){
        		$result = false;
        	}
        }
              
        return $result;
    }
    
    /**
     * Generic authorize handler for controller delete actions
     * 
     * @return  boolean     Can return both true or false.  
     */
    public function canDelete()
    {
        $result = false;
        
        if(parent::canDelete())
        {
        	$isAdmin = JFactory::getApplication()->isAdmin();
            if(version_compare(JVERSION,'1.6.0','ge')) {
                $result = JFactory::getUser()->authorise('core.delete') === true;
            } else {
                $result = JFactory::getUser()->get('gid') > ($isAdmin ? 22 : 20);
            }
			
			if($this->getService('com://admin/tokens.helper.tokens')->authorize($this->action) === false){
        		$result = false;
        	}
        }
            
        return $result;
    }
    
	/**
	 * Check the token to prevent CSRF exploits
	 *
	 * @param   object  The command context
	 * @return  boolean Returns FALSE if the check failed. Otherwise TRUE.
	 */
    protected function _checkToken(KCommandContext $context)
    {
        //Check the token
        if($context->caller->isDispatched())
        {  
            $method = KRequest::method();
            
            //Only check the token for PUT, DELETE and POST requests
            if(($method != KHttpRequest::GET) && ($method != KHttpRequest::OPTIONS)) 
            {     
                if( KRequest::token() !== JUtility::getToken()) {     
                    return false;
                }
            }
        }
        
        return true;
    }
}