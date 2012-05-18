<?php
/**
 * API Exception Class
 */
class ComTokensControllerException extends KControllerException
{
    /**
     * Format the exception for display
     *
     * @return string
     */
    public function __toString()
    {
    	//Start the OB so errors don't get thrown in debug plugin
    	if(count(ob_list_handlers())){
    		ob_start();
        	ob_start();
    	}
        return "Exception thrown by API with message: ".$this->getMessage();
    }
}