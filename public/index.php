<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require '../includes/DbOperations.php';

$app = AppFactory::create();


$app->post('/SearchOperation/Public/UploadSearchOps', function (Request $request, Response $response) {


		$db = new DbOperations;

	$request_data = $request->getParsedBody();

	$ps = $request_data['ps'];
	$lat = $request_data['lat'];
	$lng = $request_data['lng'];
	$cnic = $request_data['cnic'];
	$msisdn = $request_data['msisdn'];
	$image = $request_data['image'];	
	$status = $request_data['status'];
	

	$db = new DbOperations;

	$result = $db->uploadData($ps,
	$lat,
	$lng,
	$cnic,
	$msisdn,
	$image,
	$status);

	if($result == true){
		$message = array();
		$message['error'] = false;
		$message['message'] = 'Data Uploaded Successfully';
		$message['blacklist'] = $db->getBlackListData($cnic);

		$response->getBody()->write(json_encode($message));
		return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);
	}
	else if($result == false){
		$message = array();
		$message['error'] = true;
		$message['message'] = 'Some error occured';
		$message['blacklist'] = $db->getBlackListData($cnic);
		$response->getBody()->write(json_encode($message));
		return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);
	}
}
);


$app->get('/SearchOperation/Public/GetPoliceStations', function (Request $request, Response $response, array $args) {
    

	$db = new DbOperations;

	$policeStations = $db->getPoliceStations();

	$response_data['error'] = false;
	$response_data['policestations'] = $policeStations;

	$response->getBody()->write(json_encode($response_data));
			return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);


    return $response;
});


$app->get('/SearchOperation/Public/GetSearchData', function (Request $request, Response $response, array $args) {
    

	$db = new DbOperations;

	$searchData = $db->getSearchData();

	$response_data['error'] = false;
	$response_data['searchdata'] = $searchData;

	$response->getBody()->write(json_encode($response_data));
			return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);


    return $response;
});

$app->run();

?>