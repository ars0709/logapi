<?php
$app->get('/country/:comp',$authKey, function ($comp) use ($app)  {  
    $sql = "select * FROM il_country where il_company_acc='".$comp."'";
    getData($sql,'COUNTRY');
});

$app->post('/country/:comp',$authKey,function($comp) use ($app){
	try{
		$request_params=array();
		$request_params = $_REQUEST;
	    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
	        $app = \Slim\Slim::getInstance();
	        parse_str($app->request()->getBody(), $request_params);
	    }
	    $requiredfields=reqParams::reqUomInsert();
    	requiredFields($requiredfields);

    	if (dataExists("select * FROM il_country where il_company_acc='".$comp."' and il_country_id='".$request_params['country_id']."'")==0){
		$sql="INSERT INTO il_country (
                        il_country_id,
                        il_company_acc,
                        il_country_name) VALUES (:country_id, :country_company, :country_name)";
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


