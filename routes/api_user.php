<?php 

/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
 $app->post('/login', function () use ($app)  {    
  try {
    $request_params = array();
    $request_params = $_REQUEST;
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    $requiredfields=reqParams::reqLogin();
    requiredFields($requiredfields);
  	require_once 'includes/pass.php';
	$sql = "select * from il_user where email='".$request_params['email']."'";
	$db = getConnection();
    $stmt = $db->query($sql);
    $data = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_OBJ)),true);
    $db = null;
    if (passHash::check_password($data[0]['password_hash'], $request_params['password'])) {
        $_SESSION["company"] = $data[0]['il_company_acc'];
    	response($data,'Login-success',true);
    }else{
    	response('Invalid User Name / Password','Login-Failed',false);
    }
  } catch(PDOException $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
});
