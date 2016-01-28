<?php
$app->get('/host/:comp',$authKey, function ($comp) use ($app)  {  
    $sql = "select * FROM il_host where il_company_acc='".$comp."'";
    getData($sql,'HOST CODE');
});
$app->post('/host/:comp',$authKey,function($comp) use ($app){
	try{
		$request_params=array();
		$request_params = $_REQUEST;
	    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
	        $app = \Slim\Slim::getInstance();
	        parse_str($app->request()->getBody(), $request_params);
	    }
	    $requiredfields=reqParams::reqUomInsert();
    	requiredFields($requiredfields);
    	if (dataExists("select * FROM il_host where il_company_acc='".$comp."' and il_host_id='".$request_params['host_id']."'")==0){
		$sql="INSERT INTO il_host (
                        il_host_id,
                        il_company_acc,
                        il_host_name) VALUES (:country_id, :country_company, :country_name)";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("country_id", $request_params['country_id']);
        $stmt->bindParam("country_company", $comp);
        $stmt->bindParam("country_name",$request_params['country_name']);
        $stmt->execute();
        $data= $db->lastInsertId();
        $db = null;
        response($data,'Insert COUNTRY Success',true);
    	}else{
    		$app->response()->header('Content-Type', 'application/json');
        	response('This Id Already Exists' ,'INSERT COUNTRY',false);
    	}
	} catch(PDOException $e){
		$app->response()->status(400);
	    $app->response()->header('X-Status-Reason', $e->getMessage());	
	}
});


