<?php
/**
 * Tokens Controller Respondable Command
.*
 * @author      Oli Griffiths <oli@organic-development.com>
 * @category    Nooku
 * @package     Nooku_Server
 * @subpackage  Tokens
 */
class ComTokensControllerBehaviorRespondable extends KControllerBehaviorAbstract
{ 
	protected $_response;
	
	/**
	 * Constructor.
	 *
	 * @param 	object 	An optional KConfig object with configuration options
	 */
	public function __construct( KConfig $config = null)
	{
		parent::__construct($config);
		
		$this->registerCallback('after.dispatch' , array($this, 'store'));
		$this->registerCallback('before.render' , array($this, 'response'));
		
	}
	
	/**
	 *
	 * @return mixed
	 */
	public function store(KCommandContext $context)
	{
		$method = KRequest::method();
		
		//Set a response for HTTP calls that used POST/PUT
		if (KRequest::type() == 'HTTP' && ($method == 'POST' || $method == 'PUT'))
		{
			$this->_response = $this->getController()->execute('display', $context);
		}

		return $context->result;
	}
	
	/**
	 * 
	 * @param KCommandContext $context
	 */
	public function response(KCommandContext $context)
	{
		//Reset the response
		if($this->_response) $context->result = $this->_response;
		
		return $context->result;
	}
}