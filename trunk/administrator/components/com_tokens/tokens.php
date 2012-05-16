<?php 
echo KService::get('com://admin/tokens.dispatcher', array('request' => array('view' => KRequest::get('request.view','cmd','tokens'))))->dispatch();