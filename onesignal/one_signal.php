
<?PHP

  header('Access-Control-Allow-Origin: *'); 
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

  require_once("../config/database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

  require_once("../notification.php"); 
  require_once("../canal.php"); 

  $conexion = Database::db(); // CREA LA CONEXION

  $appId = '21c076da-ecc9-4d16-9f25-53d68097b32d';
  $restApi = 'YWUwZmU0MTYtYjgyYi00ZDIzLWIzOTMtOWExMzMzOGI4MmI4';
//   $appId = 'ece757e4-9696-4b91-8c9a-31776a9adfd3';
//   $restApi = 'YWFkMjllMzItOTU5Yi00NzNmLWJlNzEtZTJjODllMzZiZTAy';

  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR


    if (json_decode($json)) {

        $data = json_decode($json);

        $notification = new Notificacion;
        $canal = new Canal;

        class Result {}

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
        $notification->setServicio('O');
        $notification->setImage($image);
        $notification->setAccion($action);
        $notification->setAccionDestino($actionDestination);

        // Setear contenido a enviar al usuario
        // Referencia: https://documentation.onesignal.com/reference
        $content = array(

            "en" => $notification->getMensaje()
        
        );
        
        $headings = array(
    
            'en' => $notification->getTitulo()
    
        );
    
        $hashes_array = array();
        array_push($hashes_array, array(
            "id" => "like-button",
            "text" => "Like",
            "icon" => "http://i.imgur.com/N8SN8ZS.png",
            "url" => "https://yoursite.com"
        ));
        array_push($hashes_array, array(
            "id" => "like-button2",
            "text" => "Like",
            "icon" => "http://i.imgur.com/N8SN8ZS.png",
            "url" => "https://yoursite.com"
        ));

        #ENVIAR NOTIFICACION A TODOS

        if($data->segment[0] == 'all'){

            $response = sendMessageToAll($data);

            header('Content-Type: application/json');
            
            $response = json_encode($response);

            echo($response); // MUESTRA EL JSON GENERADO

            // var_dump($response) or die();
        
        #ENVIAR NOTIFICACION POR ID
        } elseif ($data->segment[0] == 'id') {

            $response = sendNotificationByUserId($data);

            header('Content-Type: application/json');

            $response = json_encode($response);

            echo($response); // MUESTRA EL JSON GENERADO

        #ENVIAR NOTIFICACION POR SEGMENTO

        } else {

            $response = sendNotificationBySegment($data);

            header('Content-Type: application/json');

            $response = json_encode($response);

            echo($response); // MUESTRA EL JSON GENERADO

        }
    }


    #ENVIAR NOTIFICACION A TODOS

    function sendMessageToAll($message) {

        $contador = 0;

        // Si se eligió enviar uno por uno a todos(según BBDD)
        if ($message->tipoDeEnvio) {

            // Obtener ID del Usuario y su token
            $query = "SELECT DISTINCT UC.USUARIO_ID, U.TOKEN FROM USUARIOS_X_CANALES UC JOIN USUARIOS U ON 
                UC.USUARIO_ID = U.ID WHERE U.ORIGEN = 'O' ORDER BY UC.USUARIO_ID";

            $sql = $GLOBALS['conexion']->prepare($query);

            if ($sql->execute()) {
                
                $respuestas = array();
                $users = $sql->fetchAll(PDO::FETCH_ASSOC);

                if (count($users) > 0) {

                    foreach ($users as $key => $value) {

                        // Setear formato a enviar a OneSignal
                        // Referencia: https://documentation.onesignal.com/reference
                        $fields = array(
            
                            'app_id' => $GLOBALS['appId'],
                
                            'include_player_ids' => array($value['token']),
                
                            'data' => array("foo" => "bar"),
                
                            'buttons' => $GLOBALS['hashes_array'],
                
                            'contents' => $GLOBALS['content'],
                
                            'headings' => $GLOBALS['headings'],
                
                            // 'large_icon' => $GLOBALS['notification']->getImage(),
                            'big_picture' => $GLOBALS['notification']->getImage(),
                            // 'chrome_web_icon' => $GLOBALS['notification']->getImage(),
                            'chrome_web_image' => $GLOBALS['notification']->getImage(),
                            'ios_attachments' => $GLOBALS['notification']->getImage()
                
                        );
            
                        $fields = json_encode($fields);
            
                        $ch = $GLOBALS['notification']->enviarNotificacionOneSignal($fields);

                        $curlExec = curl_exec($ch);
            
                        $errores = array();

                        if ($curlExec === false) {

                            $errorCurl = curl_error($ch);
            
                            array_push($errores, $errorCurl);
            
                        }

                        $curlExec = json_decode($curlExec);

                        if (isset($curlExec->errors)) {

                            array_push($errores, $curlExec->errors);
                            
                        };

                        $recipient = 0;

                        $id = '';

                        if (isset($curlExec->recipients)) {

                            $recipient = $curlExec->recipients;

                            $id = $curlExec->id;
                            
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

                                'id' => $id,

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
        
                            'id' => '',

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

                        'id' => ''
                    ),

                    'errors' => $errors

                );

                $responseObj = new Result();

                $responseObj->data = $response;

            }

        // Si se eligió enviar a todos no uno por uno
        } else {

            // Setear formato a enviar a OneSignal
            // Referencia: https://documentation.onesignal.com/reference
            $fields = array(

                'app_id' => $GLOBALS['appId'],
    
                'included_segments' => array(
    
                    'All'
    
                ),
    
                'data' => array(
    
                    "foo" => "bar"
    
                ),
    
                'contents' => $GLOBALS['content'],

                'buttons' => $GLOBALS['hashes_array'],
    
                'headings' => $GLOBALS['headings'],
                'large_icon' => $GLOBALS['notification']->getImage(),
                'big_picture' => $GLOBALS['notification']->getImage(),
                'chrome_web_icon' => $GLOBALS['notification']->getImage(),
                'chrome_web_image' => $GLOBALS['notification']->getImage()
    
            );

            $fields = json_encode($fields);

            $ch = $GLOBALS['notification']->enviarNotificacionOneSignal($fields);

            $curlExec = curl_exec($ch);
  
            $recipient = 0;

            $id = '';

            $errores = array();

            if ($curlExec === false) {

                $errorCurl = curl_error($ch);

                array_push($errores, $errorCurl);

            }

            $curlExec = json_decode($curlExec);

            if (isset($curlExec->errors)) {

                array_push($errores, $curlExec->errors);
                
            };

            if (isset($curlExec->recipients)) {

                $recipient = $curlExec->recipients;

                $id = $curlExec->id;
                
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

                    'id' => $id
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

    #ENVIAR NOTIFICACION POR USUARIO

    function sendNotificationByUserId($message){
                        
        $respuestas = array();
            
        // Setear formato a enviar a OneSignal
        // Referencia: https://documentation.onesignal.com/reference
        $fields = array(
    
        'app_id' => $GLOBALS['appId'],
    
        'include_player_ids' => array($message->token),
    
        'data' => array("foo" => "bar"),
    
        'buttons' => $GLOBALS["hashes_array"],
    
        'contents' => $GLOBALS['content'],
    
        'headings' => $GLOBALS['headings'],
    
        // 'large_icon' => $GLOBALS['notification']->getImage(),
        'big_picture' => $GLOBALS['notification']->getImage(),
        // 'chrome_web_icon' => $GLOBALS['notification']->getImage(),
        'chrome_web_image' => $GLOBALS['notification']->getImage(),
        'ios_attachments' => $GLOBALS['notification']->getImage()
    
        );
        // var_dump($fields) or die();
    
        $fields = json_encode($fields);
    
        $ch = $GLOBALS['notification']->enviarNotificacionOneSignal($fields);

        $curlExec = curl_exec($ch);
    
        // var_dump(curl_exec($ch)) or die();
        
        $recipient = 0;

        $id = '';
    
        $errores = array();

        if ($curlExec === false) {

            $errorCurl = curl_error($ch);

            array_push($errores, $errorCurl);

        }

        $curlExec = json_decode($curlExec);

        if (isset($curlExec->errors)) {

            array_push($errores, $curlExec->errors);
            
        };
      
        if (isset($curlExec->recipients)) {
    
        $recipient = $curlExec->recipients;
    
        $id = $curlExec->id;
            
        };
    
        // Respuesta enviada al frontend
        $response = array(
    
            'sent' => empty($errores) ? true : false,
    
            'idUsuario' => $message->segment[1],
    
            'content' => array(
    
                'recipient' => $recipient,
    
                'id' => $id
            ),
    
            'errors' => $errores
    
        );
      
        array_push($respuestas, $response);
            
        $responseObj = new Result();
    
        $responseObj->data = $respuestas;
        
        curl_close($ch);

        if (count($errores) == 0) {

            $GLOBALS['notification']->guardarNotificacion(true);

        } else {

            $GLOBALS['notification']->guardarNotificacion(false);

        }
    
        return $responseObj;

    }

    function sendNotificationBySegment($message) {

        $contador = 0;
        
        // Si se eligió enviar uno por uno a todos(según BBDD)
        if ($message->tipoDeEnvio) {

            // Obtener ID del Usuario y su token
            $query = "SELECT DISTINCT UC.USUARIO_ID, U.TOKEN FROM USUARIOS_X_CANALES UC JOIN USUARIOS U 
            ON UC.USUARIO_ID = U.ID WHERE U.ORIGEN = 'O' AND UC.CANAL_ID = ? ORDER BY UC.USUARIO_ID";

            $sql = $GLOBALS['conexion']->prepare($query);

            if ($sql->execute([$message->segment])) {
                
                $respuestas = array();

                $users = $sql->fetchAll(PDO::FETCH_ASSOC);

                if (count($users) > 0) {

                    foreach ($users as $key => $value) {

                        // Setear formato a enviar a OneSignal
                        // Referencia: https://documentation.onesignal.com/reference
                        $fields = array(
            
                            'app_id' => $GLOBALS['appId'],
                
                            'include_player_ids' => array($value['token']),
                
                            'data' => array("foo" => "bar"),
                
                            'buttons' => $GLOBALS['hashes_array'],
                
                            'contents' => $GLOBALS['content'],
                
                            'headings' => $GLOBALS['headings'],
                
                            // 'large_icon' => $GLOBALS['notification']->getImage(),
                            'big_picture' => $GLOBALS['notification']->getImage(),
                            // 'chrome_web_icon' => $GLOBALS['notification']->getImage(),
                            'chrome_web_image' => $GLOBALS['notification']->getImage(),
                            'ios_attachments' => $GLOBALS['notification']->getImage()
                
                        );
                        // var_dump($fields) or die();
            
                        $fields = json_encode($fields);
                    
                        // var_dump(curl_exec($ch)) or die();

                        $ch = $GLOBALS['notification']->enviarNotificacionOneSignal($fields);

                        $curlExec = curl_exec($ch);
                    
                        $recipient = 0;

                        $id = '';
        
                        $errores = array();

                        if ($curlExec === false) {

                            $errorCurl = curl_error($ch);
            
                            array_push($errores, $errorCurl);
            
                        }

                        $curlExec = json_decode($curlExec);

                        if (isset($curlExec->errors)) {

                            array_push($errores, $curlExec->errors);
                            
                        };
        
                        if (isset($curlExec->recipients)) {
        
                            $recipient = $curlExec->recipients;
        
                            $id = $curlExec->id;
                            
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
        
                                'id' => $id,

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
        
                            'id' => '',

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

                        'id' => ''

                    ),

                    'errors' => $errors

                );

                $responseObj = new Result();

                $responseObj->data = $response;

            }

        // Si se eligió enviar a todos no uno por uno
        } else {

            $query = "SELECT * FROM CANALES WHERE ID=?";

            $sql = $GLOBALS['conexion']->prepare($query);

            $sql->execute([$message->segment[0]]);

            $segment = $sql->fetchObject();

            // Setear formato a enviar a OneSignal
            // Referencia: https://documentation.onesignal.com/reference
            $fields = array(

                'app_id' => $GLOBALS['appId'],
    
                'included_segments' => array(
    
                    $segment->nombre
    
                ),
    
                'data' => array(
    
                    "foo" => "bar"
    
                ),
    
                'contents' => $GLOBALS['content'],

                'buttons' => $GLOBALS['hashes_array'],
    
                'headings' => $GLOBALS['headings'],
                'large_icon' => $GLOBALS['notification']->getImage(),
                'big_picture' => $GLOBALS['notification']->getImage(),
                'chrome_web_icon' => $GLOBALS['notification']->getImage(),
                'chrome_web_image' => $GLOBALS['notification']->getImage()
    
            );

            $fields = json_encode($fields);
            
            $ch = $GLOBALS['notification']->enviarNotificacionOneSignal($fields);

            $curlExec = curl_exec($ch);
            // var_dump(curl_exec($ch)) or die();

            $recipient = 0;

            $id = '';

            $errores = array();

            if ($curlExec === false) {

                $errorCurl = curl_error($ch);

                array_push($errores, $errorCurl);

            }

            $curlExec = json_decode($curlExec);

            if (isset($curlExec->errors)) {

                array_push($errores, $curlExec->errors);
                
            };

            if (isset($curlExec->recipients)) {

                $recipient = $curlExec->recipients;

                $id = $curlExec->id;
                
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

                    'id' => $id
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

