<?php
require_once 'NookuRequest.php';

$api_key = 'ENTER YOUR API KEY';
$api_secret = 'ENTER YOUR API SECRET';
$host = 'http://yourdomain.com/index.php';

//Create new nooku request object
$api = new NookuRequest($host, $api_key, $api_secret, 'admin','jackass');

//Run a get request
$response = $api->get(array('option' => 'com_users', 'view' => 'users'));

//Check response code
if($response->getStatus() != 200){
	echo $body->message;
}else{
	
	//Retrieve the body from the response
	$body = $response->getBody();
	
	var_dump($body->items);	
}