<?php
ini_set('display_errors','on');

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
date_default_timezone_set('UTC');
require 'Slim/Middleware/jsonP.php';
require 'Slim/Middleware/bitConvert.php';
require 'Slim/Extras/Middleware/HttpBasicAuthRoute.php';
// require 'CorsSlim.php';
include 'includes/connect.php';
$user_id='';
$company_id='';

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

}


function getData($qry,$message){
    try {
        $db = getConnection();
        $stmt = $db->query($qry);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        $x=response($data ,'Get '.$message,true);
        // $app->response()->header('Content-Type', 'application/json');
        return  $x;
        // response($data ,'Get '.$message,true);
    } catch(PDOException $e) {
        
         echo json_encode(array(
     's' => 0,
     'type' => 'Error get',
     'data' => $e->getMessage()
    ));
        // response($e->getMessage() ,'Error Get '.$message,false);        
    }
}

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
       return array(
            's' => 0,
            'type' => $typeMessage,
            'message' => $message
        );
    }else{
       return array(
        's' => 1,
        'type' => $typeMessage,
        'data' => $message
    );
    }
}

// function response($message,$typeMessage,$err){
// 	if (!$err||$err==''){
// 		echo json_encode(array(
// 			's' => 0,
// 			'type' => $typeMessage,
// 			'message' => $message
// 		));
// 	}else{
// 		echo json_encode(array(
// 		's' => 1,
// 		'type' => $typeMessage,
// 		'data' => $message
// 	));
// 	}
// }
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
        
        $app = \Slim\Slim::getInstance();
        $response = $app->response();    
        $response->write(json_encode(response('Required field(s) : ' . substr($error_fields, 0, -2) . ' is missing or empty','Missing Fields',false)));
        $app->stop();
    }
}

$authKey = function (\Slim\Route $route) {
	$headers = apache_request_headers();
    $app = \Slim\Slim::getInstance();
    if (isset($headers['Authorization'])) {
    	$key = $headers['Authorization'];
	    if (validateApiKey($key)==0) {
          $response = $app->response();
          $response->write(json_encode(response('Api Key Error ','Authorization',false)));
	      $app->stop();
	    }
		
    }else{
            $response = $app->response();
          $response->write(json_encode(response('Api Key is missing ','Authorization',false)));
          
    	
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
$app->map('/:x+', function($x) {
    http_response_code(200);
})->via('OPTIONS');
$response = $app->response();    
$app->contentType('application/json');
$app->run();