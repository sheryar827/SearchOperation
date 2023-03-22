<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require '../includes/DbOperations.php';
require '../includes/ChCrytpo.php';

$app = AppFactory::create();


$app->post('/SearchOperation/Public/Createuser', function (Request $request, Response $response) {
    if(!haveEmptyParameters(array('UserName', 
	'MobileId',
	'UserCNIC',
	'UserMSISDN',
	'Password', 
	'EncKey',
	'FCM'), $response)){


		$db = new DbOperations;
		$chcrytpo = new ChCrytpo;

	$request_data = $request->getParsedBody();

	$EncKey = $request_data['EncKey'];

	// Original EncKey hidden inside Id
	$SecKey = $chcrytpo->decrypt($request_data['Id']
	, strrev($EncKey));



	$UserName = $chcrytpo->decrypt($request_data['UserName']
	, $EncKey);
	$MobileId = $chcrytpo->decrypt($request_data['MobileId']
	, $EncKey);
	$UserCNIC = $chcrytpo->decrypt($request_data['UserCNIC']
	, $EncKey);
	$UserMSISDN = $chcrytpo->decrypt($request_data['UserMSISDN']
	, $EncKey);
	$Password = $chcrytpo->decrypt($request_data['Password']
	, $EncKey);

	$FCM = $chcrytpo->decrypt($request_data['FCM']
	, $EncKey);
	

	$db = new DbOperations;

	$result = $db->createUser($UserName,
	$MobileId,
	$UserMSISDN,
	$UserCNIC,
	$Password,
	$SecKey,
	$FCM);

	if($result == USER_CREATED ){
		
		$message = array();
		$message['error'] = false;
		$message['message'] = $chcrytpo->encrypt('User created successfully'
		,$EncKey); 
		

		$response->getBody()->write(json_encode($message));
		return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);
	}
	else if($result == USER_FAILURE){
		$message = array();
		$message['error'] = true;
		$message['message'] = $chcrytpo->encrypt('Some error occured'
		,$EncKey); 

		$response->getBody()->write(json_encode($message));
		return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);
	}
	else if ($result == USER_EXIST) {
		$message = array();
		$message['error'] = true;
		$message['message'] =  $chcrytpo->encrypt('Some error occured'
		,$EncKey); 

		$response->getBody()->write(json_encode($message));
		return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);
	}
}
return $response
->withHeader('Content-type', 'application/json')
->withStatus(422);
});

$app->post('/SearchOperation/Public/Loginuser', function (Request $request, Response $response) {
	
	$db = new DbOperations;
	$chcrytpo = new ChCrytpo;

	if(!haveEmptyParameters(array('UserName', 
	'MobileId', 
	'Password', 
	'EncKey'), $response)){

		$request_data = $request->getParsedBody();

		

		$EncKey = $request_data['EncKey'];


		$MobileId =$chcrytpo->decrypt($request_data['MobileId']
		, $EncKey); 

		$user = $db->getUserByMobileId($MobileId);

		$UserName = $chcrytpo->decrypt($request_data['UserName']
		, $user['EncKey']); 
		
		$Password = $chcrytpo->decrypt($request_data['Password']
		, $user['EncKey']); 
		

		// $UserName = $request_data['UserName'];
		// $MobileId = $request_data['MobileId'];
		// $Password = $chcrytpo->decrypt($request_data['Password']
		// , $EncKey); 


		$result = $db->userLogin($UserName, $MobileId, $Password);


		if($result == USER_AUTHENTICATED){

			$response_data = array();
			//$user = $db->getUserByMobileId($MobileId);
			$response_data['error'] = false;
			//$response_data['message'] = 'Login Successful';
			$response_data['message'] = $chcrytpo->encrypt('Login Successful'
			, $user['EncKey']);
			$response_data['role'] = $chcrytpo->encrypt($user['RoleId']
			, $user['EncKey']);

			$response->getBody()->write(json_encode($response_data));
			return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);

		}else if($result == USER_NOT_FOUND){
			
			$response_data = array();
			
			$response_data['error'] = true;
			$response_data['message'] = 'User Not Exist';
		

			

			$response->getBody()->write(json_encode($response_data));
			return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);

		}else if($result == USER_NOT_AUTHENTICATED){
			$response_data = array();
			
			$response_data['error'] = true;
			$response_data['message'] = 'Invalid Credential';

			$response->getBody()->write(json_encode($response_data));
			return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);
		}
	}

	return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(422);
});

$app->post('/SearchOperation/Public/GetUserNamePassword', function (Request $request, Response $response){
	

	$db = new DbOperations;
		$chcrytpo = new ChCrytpo;

	$request_data = $request->getParsedBody();

	$EncKey = $request_data['EncKey'];

	// Original EncKey hidden inside Id
	$MobileId = $chcrytpo->decrypt($request_data['MobileId']
	, $EncKey);

	$user = $db->getUserByMobileId($MobileId);

	// User Found
	if(sizeof($user) > 0){
		$UserName = $chcrytpo->encrypt($user['UserName']
		, $EncKey); 
		
		// Get Password From Description
		$pass = substr($user['Description'], strpos($user['Description']
		, 'Password: ') + strlen('Password: '));

		$Password = $chcrytpo->encrypt($pass
		, $EncKey);
		
		$SecEnckKey = $chcrytpo->encrypt($user['EncKey']
		, strrev($EncKey));

		$response_data = array();
		$response_data['UserName'] =  $UserName;
		$response_data['Password'] = $Password;
		$response_data['SecEncKey'] = $SecEnckKey;
		$response->getBody()->write(json_encode(array('error'=>false, 'message'=>
		$chcrytpo->encrypt(json_encode($response_data)
	, strrev($EncKey)))));
			return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);
		

	}else{

		$response->getBody()->write(json_encode(array('error'=>true, 'message'=>
		'No Record Found!')));
			return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);

	}

	return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(422);
});


$app->post('/SearchOperation/Public/UploadSearchOps', function (Request $request, Response $response) {


		$db = new DbOperations;

	$request_data = $request->getParsedBody();

	$ps = $request_data['ps'];
	$lat = $request_data['lat'];
	$lng = $request_data['lng'];
	$name = $request_data['name'];
	$cnic = $request_data['cnic'];
	$msisdn = $request_data['msisdn'];
	$image = $request_data['image'];	
	$status = $request_data['status'];
	$mobid = $request_data['mobId'];
	

	$db = new DbOperations;

	$result = $db->uploadData($ps,
	$lat,
	$lng,
	$name,
	$cnic,
	$msisdn,
	$image,
	$status,
	$mobid);

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
	return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(422);
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

$app->get('/SearchOperation/Public/GetRoles', function(Request $request, Response $response){
	$request_data = $request->getParsedBody();
	$chcrytpo = new ChCrytpo;
	//$cnic = $request_data['cnic'];
	$db = new DbOperations;

	$roles = $db->getRoles();

	$EncKey = $db->generateRandomString();

	$response_data['roles'] = $roles;

	$response->getBody()->write(json_encode(array('error'=>false
	, 'message'=>
		$chcrytpo->encrypt(json_encode($response_data)
	, $EncKey)
	, 'enckey'=> $EncKey)));
			return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);
});


$app->post('/SearchOperation/Public/ActivateUser', function (Request $request, Response $response) {
    $db = new DbOperations;
	$chcrytpo = new ChCrytpo;

	$request_data = $request->getParsedBody();

	$EncKey = $request_data['EncKey'];

	$MobileId = $chcrytpo->decrypt($request_data['MobileId']
		, $EncKey); 

	$user = $db->getUserByMobileId($MobileId);	

	$aMobId = $chcrytpo->decrypt($request_data['AMobileId']
		, $user['EncKey']);	

	

	$role = $chcrytpo->decrypt($request_data['Role']
		, $EncKey); 	

	//$auser = $db->getUserByMobileId($aMobId);

	$result = $db->activateUser($aMobId, $role);


	if($result == USER_ACTIVATED ){
		$message = array();
		$message['error'] = false;
		$message['message'] = $chcrytpo->encrypt('User Activated successfully'
		,$user['EncKey']); 
	
		$response->getBody()->write(json_encode($message));
		return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);
	}

	if($result == USER_NOT_ACTIVATED ){
		$message = array();
		$message['error'] = true;
		$message['message'] = $chcrytpo->encrypt('Unable To Activate User'
		,$user['EncKey']); 
	
		$response->getBody()->write(json_encode($message));
		return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);
	}
	return $response
	->withHeader('Content-type', 'application/json')
	->withStatus(422);

});


$app->post('/SearchOperation/Public/InActiveUsers', function (Request $request, Response $response) {
    $db = new DbOperations;
	$chcrytpo = new ChCrytpo;

	$request_data = $request->getParsedBody();

	$EncKey = $request_data['EncKey'];

	$MobileId = $chcrytpo->decrypt($request_data['MobileId']
		, $EncKey); 

	$user = $db->getUserByMobileId($MobileId);

	$users = $db->inActiveUsersList();

	$response_data = array();

	$response_data['userresult'] = $users;

	$response->getBody()->write(json_encode(array('error'=>false, 'message'=>
		$chcrytpo->encrypt(json_encode($response_data)
	, $user['EncKey']))));
			return $response
					->withHeader('Content-type', 'application/json')
					->withStatus(200);

});


function haveEmptyParameters($required_paras, $response){
	$error = false;
	$error_params = '';

	$request_params = $_REQUEST;

	foreach($required_paras as $param){
		if(!isset($request_params[$param]) || strlen($request_params[$param]) <= 0){
			$error = true;
			$error_params .= $param . ', ';
		}
	}

	if($error){
		$error_detail = array();
		$error_detail['error'] = true;
		$error_detail['message'] = 'Required parameters ' 
		. substr($error_params, 0, -2) 
		. ' are missing or empty';
	
		$response->getBody()->write(json_encode($error_detail));
		

	}

	return $error;
}


$app->run();

?>