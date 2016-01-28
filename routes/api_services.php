<?php 

$app->get('/uom/:comp',$authKey, function ($comp) use ($app)  {  
    $sql = "select * FROM il_uom where il_company_acc='".$comp."'";
    $xData=getData($sql,'UOM');
    $response = $app->response();
    $response->write(json_encode($xData));
});

