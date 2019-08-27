<?php
	header('Access-Control-Allow-Origin: *'); 
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	
	$json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR
	
	$params = json_decode($json, true); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE
	//var_dump($params) or die();

	$serverKey = 'AAAAtNT2xxA:APA91bHaEjh4FZlZCs0rw2uxhnQ8S2Fb-V7dY97PDbayT3EAzhXpKGy4gpyF2ZT9M4TaZ5Evb81xREYKw2MrWuJ1VCZHhyk5AYK6Eo5vVufh6MMHipHfn7-WNegJOs6LlsLuLr4W0GEd';

	if(isset($params['title'])){

		require_once __DIR__ . '/notify.php';
		$notification = new Notification();

		$title = isset($params['title'])?$params['title']:'';
		$message = isset($params['message'])?$params['message']:'';
		$imageUrl = isset($params['image'])?$params['image']:'';
		//$action = isset($params['action'])?$params['action']:'';

		
		
		//$actionDestination = isset($params['action_destination'])?$params['action_destination']:'';

		/*if($actionDestination ==''){
			$action = '';
		}*/
		$notification->setTitle($title);
		$notification->setMessage($message);
		$notification->setImage($imageUrl);
		//$notification->setAction($action);
		//$notification->setActionDestination($actionDestination);
		
		$firebase_token = isset($params['firebase_token'])?$params['firebase_token']:'';
		$firebase_api = $serverKey;
		
		
		$topic = 'global';
		
		$requestData = $notification->getNotification();
		
		if($params['to']=='topic'){
			$fields = array(
				'to' => '/topics/' . $topic,
				'data' => $requestData,
			);
			
			
		}else{
			$fields = array(
				'to' => $firebase_token,
				'data' => $requestData,
			);
		}
		


		// Set params variables
		$url = 'https://fcm.googleapis.com/fcm/send';

		$headers = array(
			'Authorization: key=' . $firebase_api,
			'Content-Type: application/json'
		);
		
		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Disabling SSL Certificate support temporarily
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute params
		$result = curl_exec($ch);
		if($result === FALSE){
			die('Curl failed: ' . curl_error($ch));
		}

		// Close connection
		curl_close($ch);
		

		//echo '<h2>Result</h2><hr/><h3>Request </h3><p><pre>';
		echo json_encode($fields,JSON_PRETTY_PRINT);
		//echo '</pre></p><h3>Response </h3><p><pre>';
		echo $result;
		
		//echo '</pre></p>';
	}
?>	