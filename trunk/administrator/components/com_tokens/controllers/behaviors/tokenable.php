<?php
/**
 * Tokens Controller Tokenable Command
.*
 * @author      Oli Griffiths <oli@organic-development.com>
 * @category    Nooku
 * @package     Nooku_Server
 * @subpackage  Tokens
 */
class ComTokensControllerBehaviorTokenable extends KControllerBehaviorExecutable
{ 
	protected $_priority = KCommand::PRIORITY_HIGHEST;
	
 	/**
     * Command handler
     * 
     * @param   string      The command name
     * @param   object      The command context
     * @return  boolean     Can return both true or false.  
     */
    public function execute( $name, KCommandContext $context) 
    { 
        $parts = explode('.', $name);

        if(isset($parts[1]) && in_array($parts[1],array('browse','read','edit','add','delete'))){
        	$auth = $this->getService('com://admin/tokens.helper.tokens')->authorize($this->action);
        	return $auth === null ? true : $auth;
        }
        
        return parent::execute($name, $context); 
    }
}