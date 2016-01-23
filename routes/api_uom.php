<?php 


$app->get('/uom/:comp',$authKey, function ($comp) use ($app)  {  
    $sql = "select * FROM il_uom where il_company_acc='".$comp."'";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        $app->response()->header('Content-Type', 'application/json');
        response($data ,'Get UOM',true);
    } catch(PDOException $e) {
    	response($e->getMessage() ,'Error Get UOM',false);        
    }
});

$app->get('/uom/:comp/:id',$authKey, function ($comp,$id) use ($app)  { 

    $sql = "select * FROM il_uom where il_company_acc='".$comp."' and il_uom_id='".$id."'";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        $app->response()->header('Content-Type', 'application/json');
        response($data ,'Get UOM',true);
    } catch(PDOException $e) {
    	response($e->getMessage() ,'Error Get UOM',false);
    }
});
$app->post('/uom/:comp', $authKey, function ($comp) use ($app)  {    
  try {
    // ambil parameter request 

    $request_params = array();
    $request_params = $_REQUEST;
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    $requiredfields=reqParams::reqUomInsert();
    requiredFields($requiredfields);
    // cek data exists filter by company 
    if (dataExists("select * FROM il_uom where il_company_acc='".$comp."' and il_uom_id='".$request_params['uom_id']."'")==0){
        $sql="INSERT INTO il_uom (
                        il_uom_id,
                        il_company_acc,
                        il_uom_desc) VALUES (:uom_id, :uom_company, :uom_desc)";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("uom_id", $request_params['uom_id']);
        $stmt->bindParam("uom_company", $comp);
        $stmt->bindParam("uom_desc",$request_params['uom_desc']);
        $stmt->execute();
        $data= $db->lastInsertId();
        $db = null;
        response($data,'Insert UOM Success',true);
     }
     else{
        $app->response()->header('Content-Type', 'application/json');
        response('This Id Already Exists' ,'INSERT UOM',false);
    }
   } catch(PDOException $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
});

$app->put('/uom/:comp/:id', $authKey, function ($comp,$id) use ($app)  {
try {
    $request_params = array();
    $request_params = $_REQUEST;
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    $requiredfields=reqParams::reqUomInsert();
    requiredFields($requiredfields);

        $sql = "UPDATE il_uom set il_uom_id=:uom_id, il_uom_desc=:uom_desc where idil_uom=:uom_iid and il_company_acc='".$comp."'";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("uom_iid", $id);
        $stmt->bindParam("uom_id", $request_params['uom_id']);
        $stmt->bindParam("uom_desc",$request_params['uom_desc']);

        $stmt->execute();
        $data= $db->lastInsertId();
        $db = null;
        response($data,'Update UOM Success',true);
} catch(PDOException $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
});


$app->delete('/uom/:comp/:id', $authKey, function ($comp,$id) use ($app)  {
try {
        $sql = "DELETE FROM il_uom where idil_uom=:uom_iid and il_company_acc='".$comp."'";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("uom_iid", $id);
    
        $stmt->execute();
        $data= $db->lastInsertId();
        $db = null;
        response($data,'Delete UOM Success',true);
} catch(PDOException $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
});




