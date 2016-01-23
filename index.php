<?php
ini_set('display_errors','on');
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
date_default_timezone_set('UTC');
require 'Slim/Middleware/jsonP.php';
require 'Slim/Middleware/bitConvert.php';
require 'Slim/Extras/Middleware/HttpBasicAuthRoute.php';
include 'includes/connect.php';
$user_id='';
$company_id='';

session_cache_limiter(false);
session_start();
//cek jika data sudah ada
function dataExists($qry){
    $sql =$qry;
    $db = getConnection();
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->rowCount();

}
function validateApiKey($key) {
    $sql = "select * FROM il_user where api_key='".$key."'";
    $db = getConnection();
    $sth = $db->prepare($sql);
    $sth->execute();
    return $sth->rowCount();
}
function response($message,$typeMessage,$err){
	if (!$err||$err==''){
		echo json_encode(array(
			's' => 0,
			'type' => $typeMessage,
			'message' => $message
		));
	}else{
		echo json_encode(array(
		's' => 1,
		'type' => $typeMessage,
		'data' => $message
	));
	}
}
function requiredFields($required_fields) {
	 $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
    if ($error) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        response('Required field(s) : ' . substr($error_fields, 0, -2) . ' is missing or empty','Missing Fields',false);
        $app->stop();
    }
}

$authKey = function (\Slim\Route $route) {
	$headers = apache_request_headers();
    $app = \Slim\Slim::getInstance();
    if (isset($headers['Authorization'])) {
    	$key = $headers['Authorization'];
	    if (validateApiKey($key)==0) {
	      response('Api Key Error ','Authorization',false);
	      $app->stop();
	    }
		
    }else{
    	response('Api Key is missing ','Authorization',false);
        $app->stop();
    }
};

//Require File
require_once 'includes/require_params.php';
foreach (glob("routes/*.php") as $filename){require_once $filename;}
// end 

$app->add(new \Slim\Middleware\JSONPMiddleware());
$app->add(new \Slim\Middleware\BitConvertMiddleware());
$app->add(new \Slim\Middleware\SessionCookie(array(
    'expires' => '20 minutes',
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'httponly' => false,
    'name' => 'ilog_session',
    'secret' => 'ilog_screet',
    'cipher' => MCRYPT_RIJNDAEL_256,
    'cipher_mode' => MCRYPT_MODE_CBC
)));
$app->contentType('application/json');
$app->run();