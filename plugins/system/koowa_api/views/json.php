<?php
/**
 * Default JSON View
.*
 * @author      Oli Griffiths <organic-development.com>
 * @category    NookuApi
 * @package     Views
 * @subpackage  JSON
 */
class ComDefaultViewJson extends KViewJson
{
	public function display()
	{
		$api = $this->getService('com://admin/api.model.api');
		if(!$api->authenticate()){
			throw new ComApiException('Access to the JSON API is restricted to authenticated users', KHttpResponse::FORBIDDEN);
		}
		return parent::display();
	}
}