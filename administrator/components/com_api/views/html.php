<?php 

class ComApiViewHtml extends ComDefaultViewHtml{
	
	public function __construct(KConfig $config)
	{
		parent::__construct($config);
		
		JHTML::_('behavior.mootools');
	}
}