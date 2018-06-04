<?php

require_once '../include/DbHandler.php';
// require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;

function verifyRequiredParams($required_fields) {
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
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}

$app->get('/getAllProductByStore/:store_id', function($store_id) {
    $response = array();
    $db = new DbHandler();


    $result = $db->getAllProductByStore($store_id);
    $response["error"] = false;
    $response["products"] = array();

    while ($product = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["product_id"] = $product["id"];
        $tmp["product_name"] = $product["product_name"];
        $tmp["product_barcode"] = $product["product_barcode"];
        $tmp["product_price"] = $product["product_price"];
        $tmp["product_image"] = $product["product_image_url"];
        $tmp["quantity"] = 0;
        array_push($response["products"], $tmp);
    }
    

    echoResponse(200, $response);
});

$app->post('/createPurchase',function() use ($app) {

    verifyRequiredParams(array('store_id','item_list'));

    $response = array();
    $store_id = $app->request->post('store_id');
    $item_list = $app->request->post('item_list');

    $db = new DbHandler();
    
    $trans_id = $db->createPurchase($store_id, $item_list);

    if ($trans_id != NULL) {
        $response["error"] = false;
        $response["message"] = "Purchase created successfully";
        $response["create_at"] = $trans_id;
        // $response["feedback_text"] = $feedback_text;
    } else {
        $response["error"] = true;
        $response["message"] = "Failed to create purchase. Please try again";
    }
    echoResponse(201, $response);
});


function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>