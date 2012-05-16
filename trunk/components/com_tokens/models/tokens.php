<?php 

class ComTokensModelTokens extends ComDefaultModelDefault
{
	public function __construct(KConfig $config){
		$config->table = 'com://admin/tokens.database.table.tokens';
				
		parent::__construct($config);
	}
}