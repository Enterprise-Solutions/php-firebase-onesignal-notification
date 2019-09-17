<?php
	header('Access-Control-Allow-Origin: *'); 
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	
	require_once("../config/database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB
	$conexion = Database::db(); // CREA LA CONEXION

	$json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR
	
	require_once('notify.php');
	// var_dump($data) or die();
	require_once("../notification.php"); 
  	require_once("../canal.php"); 

	date_default_timezone_set('America/Asuncion');


	if (json_decode($json)) {

		class Result {}

		$data = json_decode($json); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

		$notification = new Notificacion();

		$canal = new Canal;

		$titulo = !empty($data->mensajetitulo) ? $data->mensajetitulo : '';
        $mensaje = !empty($data->mensaje) ? $data->mensaje : '';
        $segmento = !empty($data->segment[0]) ? $data->segment[0] : '';
        $image = !empty($data->imagen) ? $data->imagen : '';
        $action = isset($data->accion) ? $data->accion : '';
		
		$actionDestination = isset($data->destino) ? $data->destino : '';

		if($actionDestination == ''){
			$action = '';
        }

        if ($segmento == 'all') {
            $notification->setSegmento('Todos');
        } elseif ($segmento == 'id') {
            $notification->setSegmento('User');
        } else {
            $notification->setSegmento($canal->getCanal($segmento));
		}
		
		$notification->setTitulo($titulo);
        $notification->setMensaje($mensaje);
        $notification->setServicio('F');
        $notification->setImage($image);
        $notification->setAccion($action);
        $notification->setAccionDestino($actionDestination);

		
		$requestData = $notification->getNotification();
		
		if($data->segment[0] =='all'){

			$response = sendMessageToAll($requestData, $data);

			header('Content-Type: application/json');

            $response = json_encode($response);

            echo($response); // MUESTRA EL JSON GENERADO
			
			
		} elseif ($data->segment[0] == 'id') {

			$response = sendNotificationByUserId($requestData, $data);

            header('Content-Type: application/json');

            $response = json_encode($response);

            echo($response); // MUESTRA EL JSON GENERADO

			// $fields = array(
			// 	'to' => '1:776667055888:android:1c1e02c05c529a0f',
			// 	'data' => $requestData,
			// );

		} else {

			$response = sendNotificationBySegment($requestData, $data);

            header('Content-Type: application/json');

            $response = json_encode($response);

            echo($response); // MUESTRA EL JSON GENERADO

		}

		// // Set data variables
		// $url = 'https://fcm.googleapis.com/fcm/send';

		// $headers = array(
		// 	'Authorization: key=' . $firebase_api,
		// 	'Content-Type: application/json'
		// );
		
		// // Open connection
		// $ch = curl_init();

		// // Set the url, number of POST vars, POST data
		// curl_setopt($ch, CURLOPT_URL, $url);

		// curl_setopt($ch, CURLOPT_POST, true);
		// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// // Disabling SSL Certificate support temporarily
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// // Execute data
		// $result = curl_exec($ch);
		// if($result === FALSE){
		// 	die('Curl failed: ' . curl_error($ch));
		// }

		// // Close connection
		// curl_close($ch);
		

		// //echo '<h2>Result</h2><hr/><h3>Request </h3><p><pre>';
		// //echo '</pre></p><h3>Response </h3><p><pre>';
		// echo 'Este es el campo resultado: ', $result;
		
		// //echo '</pre></p>';
	}

	function sendMessageToAll($message, $data) {

		$contador = 0;

		// Si se eligió enviar uno por uno a todos
        if ($data->tipoDeEnvio) {

			// Obtener ID del Usuario y su token
            $query = "SELECT DISTINCT UC.USUARIO_ID, U.TOKEN FROM USUARIOS_X_CANALES UC JOIN USUARIOS U ON 
                UC.USUARIO_ID = U.ID WHERE U.ORIGEN = 'F' ORDER BY UC.USUARIO_ID";

			$sql = $GLOBALS['conexion']->prepare($query);
			
            if ($sql->execute()) {
                
                $respuestas = array();
                $users = $sql->fetchAll(PDO::FETCH_ASSOC);
				
                if (count($users) > 0) {

                    foreach ($users as $key => $value) {

						// Guardar los datos en el array y el destino(token)
                        $fields = array(
							'to' => $value['token'],
							'data' => $message,
						);

						$fields = json_encode($fields);

						$ch = $GLOBALS['notification']->enviarNotificacionFireBase($fields);

						$curlExec = curl_exec($ch);
						
						$errores = array();

						if ($curlExec === false) {

							$errorCurl = curl_error($ch);

							array_push($errores, $errorCurl);

						}

						$curlExec = json_decode($curlExec);
                        
						$recipient = 0;
						
						$id = 0;
						// var_dump($curlExec) or die();

                        if (isset($curlExec->results[0]->error)) {

							array_push($errores, $curlExec->results[0]->error);
							
                        };

                        if (isset($curlExec->success) && !empty($curlExec->success)) {

                            $recipient = 1;

                            $id = $curlExec->multicast_id;
                            
						};

						if (empty($errores)) {

                            $contador++;

                        }

						// Respuesta enviada al frontend
                        $response = array(

                            'sent' => empty($errores) ? true : false,

                            'idUsuario' => $value['usuario_id'],

                            'content' => array(

                                'recipient' => $recipient,

								'id' => $id
                            ),

                            'errors' => $errores

						);
						
                        array_push($respuestas, $response);

					}
					
                    $responseObj = new Result();

                    $responseObj->data = $respuestas;
                    
                } else {

					// Respuesta enviada al frontend
                    $response = array(

                        'sent' => false,
        
                        'idUsuario' => 0,
        
                        'content' => array(
        
                            'recipient' => 0,
        
							'id' => 0
                        ),
        
                        'errors' => ['Sin usuarios registrados']
        
                    );

                    $responseObj = new Result();
        
                    $responseObj->data = $response;
                    
                }

            } else {

                $respuestas = array();

				$errors = $sql->errorInfo();
				
				// Respuesta enviada al frontend
                $response = array(

                    'sent' => false,

                    'idUsuario' => 0,

                    'content' => array(

                        'recipient' => 0,

						'id' => 0
                    ),

                    'errors' => $errors

                );

                $responseObj = new Result();

                $responseObj->data = $response;

			}
			
		// Si se eligió enviar a todos no de a uno
		} else {

			$fields = array(
				'to' => '/topics/' . 'global',
				'data' => $message,
			);

            $fields = json_encode($fields);

			$ch = $GLOBALS['notification']->enviarNotificacionFireBase($fields);

			$curlExec = curl_exec($ch);

			$errores = array();

			if ($curlExec === false) {

				$errorCurl = curl_error($ch);

				array_push($errores, $errorCurl);

			}

			$curlExec = json_decode($curlExec);

			$recipient = 0;

			$message_id = 0;

			if (isset($curlExec->error)) {

				array_push($errores, $curlExec->error);

			};

			if (isset($curlExec->message_id) && !empty($curlExec->message_id)) {

				$message_id = $curlExec->message_id;

				$recipient = 1;
				
			};

			if (empty($errores)) {

                $contador++;
                
            }

			// Respuesta enviada al frontend
			$response = array(

				'sent' => empty($errores) ? true : false,

				'idUsuario' => 0,

				'content' => array(

					'recipient' => $recipient,

					'id' => $message_id

				),

				'errors' => $errores

			);

            $responseObj = new Result();

            $responseObj->data = $response;

        }

		curl_close($ch);

		// var_dump('Hola estas en sendall', $contador) or die();
		
		if ($contador > 0) {

            $GLOBALS['notification']->guardarNotificacion(true);

        } else {

            $GLOBALS['notification']->guardarNotificacion(false);

        }

        return $responseObj;

	}
	
	function sendNotificationByUserId($message, $data){

		// Guardar los datos en el array y el destino(token)
		$fields = array(
			'to' => $data->token,
			'data' => $message,
		);

		$fields = json_encode($fields);
				  
		$ch = $GLOBALS['notification']->enviarNotificacionFireBase($fields);

		$curlExec = curl_exec($ch);
		
		$errores = array();

		if ($curlExec === false) {

			$errorCurl = curl_error($ch);

			array_push($errores, $errorCurl);

		}
		
		$curlExec = json_decode($curlExec);

		$recipient = 0;
		
		$id = 0;
	
		if (isset($curlExec->results[0]->error)) {

			array_push($errores, $curlExec->results[0]->error);
			
		};

		if (isset($curlExec->success) && !empty($curlExec->success)) {

			$recipient = 1;

			$id = $curlExec->multicast_id;
			
		};
	
		// Respuesta enviada al frontend
		$response = array(
	
			'sent' => empty($errores) ? true : false,
	
			'idUsuario' => $data->segment[1],
	
			'content' => array(
	
				'recipient' => $recipient,
	
				'id' => $id
			),
	
			'errors' => $errores
	
		);
				
		$responseObj = new Result();
	
		$responseObj->data = $response;
		
		curl_close($ch);

		if (count($errores) == 0) {

            $GLOBALS['notification']->guardarNotificacion(true);

        } else {

            $GLOBALS['notification']->guardarNotificacion(false);

        }
	
		return $responseObj;

	}
	
	function sendNotificationBySegment($message, $data) {

		$segment = $data->segment[0];

		$contador = 0;

		// Si se eligió enviar uno por uno a todos
        if ($data->tipoDeEnvio) {

			// Obtener ID del Usuario y su token
            $query = "SELECT DISTINCT UC.USUARIO_ID, U.TOKEN FROM USUARIOS_X_CANALES UC JOIN USUARIOS U 
			ON UC.USUARIO_ID = U.ID WHERE U.ORIGEN = 'F' AND UC.CANAL_ID = ? ORDER BY UC.USUARIO_ID";
			
			$sql = $GLOBALS['conexion']->prepare($query);
			
			// var_dump('Hola estas en sendall') or die();

            if ($sql->execute([$segment])) {
                
                $respuestas = array();
                $users = $sql->fetchAll(PDO::FETCH_ASSOC);
				$ch = curl_init();
				
				// var_dump('Hola estas en sendAll ', $users) or die();

                if (count($users) > 0) {

                    foreach ($users as $key => $value) {

						// Guardar los datos en el array y el destino(token)
                        $fields = array(
							'to' => $value['token'],
							'data' => $message,
						);
                        // var_dump($fields) or die();
            
						$fields = json_encode($fields);

						$ch = $GLOBALS['notification']->enviarNotificacionFireBase($fields);

						$curlExec = curl_exec($ch);

						$errores = array();

						if ($curlExec === false) {

							$errorCurl = curl_error($ch);

							array_push($errores, $errorCurl);

						}
                        
                        $curlExec = json_decode($curlExec);

						$recipient = 0;
						
						$id = 0;
						// var_dump($curlExec) or die();

                        if (isset($curlExec->results[0]->error)) {

							array_push($errores, $curlExec->results[0]->error);
							
                        };

                        if (isset($curlExec->success) && !empty($curlExec->success)) {

                            $recipient = 1;

                            $id = $curlExec->multicast_id;
                            
						};
						
						if (empty($errores)) {

                            $contador++;

                        }

						// Respuesta enviada al frontend
                        $response = array(

                            'sent' => empty($errores) ? true : false,

                            'idUsuario' => $value['usuario_id'],

                            'content' => array(

                                'recipient' => $recipient,

								'id' => $id
                            ),

                            'errors' => $errores

						);
						
                        array_push($respuestas, $response);

					}
					
                    $responseObj = new Result();

                    $responseObj->data = $respuestas;
                    
                } else {

					// Respuesta enviada al frontend
                    $response = array(

                        'sent' => false,
        
                        'idUsuario' => 0,
        
                        'content' => array(
        
                            'recipient' => 0,
        
							'id' => 0
                        ),
        
                        'errors' => ['Sin usuarios registrados']
        
                    );

                    $responseObj = new Result();
        
                    $responseObj->data = $response;
                    
                }

            } else {

                $respuestas = array();

				$errors = $sql->errorInfo();
				
				// Respuesta enviada al frontend
                $response = array(

                    'sent' => false,

                    'idUsuario' => 0,

                    'content' => array(

                        'recipient' => 0,

						'id' => 0
                    ),

                    'errors' => $errors

                );

                $responseObj = new Result();

                $responseObj->data = $response;

			}
			
		// Si se eligió enviar a todos no de a uno
        } else {

			$query = "SELECT * FROM CANALES WHERE ID=?";

            $sql = $GLOBALS['conexion']->prepare($query);

			$sql->execute([$data->segment[0]]);
			
			$segment = $sql->fetchObject();

			// Guardar los datos en el array y el destino(segmento)
			$fields = array(
				'to' => '/topics/' . $segment->nombre,
				'data' => $message,
			);

            $fields = json_encode($fields);

			$ch = $GLOBALS['notification']->enviarNotificacionFireBase($fields);

			$curlExec = curl_exec($ch);

			$errores = array();

			if ($curlExec === false) {

				$errorCurl = curl_error($ch);

				array_push($errores, $errorCurl);

			}

			$curlExec = json_decode($curlExec);

			$recipient = 0;
			$message_id = 0;

			if (isset($curlExec->error)) {

				array_push($errores, $curlExec->error);

			};

			if (isset($curlExec->message_id) && !empty($curlExec->message_id)) {

				$message_id = $curlExec->message_id;

				$recipient = 1;
				
			};

			if (empty($errores)) {

                $contador++;
                
            }

			// Respuesta enviada al frontend
			$response = array(

				'sent' => empty($errores) ? true : false,

				'idUsuario' => 0,

				'content' => array(

					'recipient' => $recipient,

					'id' => $message_id
				),

				'errors' => $errores

			);

            $responseObj = new Result();

            $responseObj->data = $response;

        }

		curl_close($ch);
		
		if ($contador > 0) {

            $GLOBALS['notification']->guardarNotificacion(true);

        } else {

            $GLOBALS['notification']->guardarNotificacion(false);

        }

        return $responseObj;
    }

?>	

