<?php

class ComTokensDatabaseTableTokens extends ComGroupsDatabaseTableNodes
{
	protected function _initialize(KConfig $config)
	{
		$config->append(array(
            'name'  => 'tokens',
            'base'  => 'tokens'
        ));

        parent::_initialize($config);
	}
}