
<?PHP

  header('Access-Control-Allow-Origin: *'); 
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

//   $appId = '21c076da-ecc9-4d16-9f25-53d68097b32d';
//   $restApi = 'YWUwZmU0MTYtYjgyYi00ZDIzLWIzOTMtOWExMzMzOGI4MmI4';
  $appId = 'ece757e4-9696-4b91-8c9a-31776a9adfd3';
  $restApi = 'YWFkMjllMzItOTU5Yi00NzNmLWJlNzEtZTJjODllMzZiZTAy';
  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR
    if (json_decode($json)) {
        $data = json_decode($json);

        #ENVIAR NOTIFICACION A TODOS
    
        if($data->to == 'all'){

            $response = sendMessageToAll($data);

            $return["allresponses"] = $response;

            $return = json_encode($return);

            $data = json_decode($response, true);

            print_r($data);

            $id = $data['id'];

            print_r($id);

            print("\n\nJSON received:\n");

            print($return);

            print("\n");  

            header('Content-Type: application/json');
            echo json_encode($return); // MUESTRA EL JSON GENERADO

        
        #ENVIAR NOTIFICACION POR ID
        } elseif ($data->to == 'id') {

            $app_id = $_POST['app_id'];

            $user_id = $_POST['user_id'];

            $heading = $_POST['heading'];

            $message = $_POST['message'];

            $response = sendNotificationByUserId($app_id, $user_id, $heading, $message);

            $return["allresponses"] = $response;

            $return = json_encode( $return);
            

            print("\n\nJSON received:\n");

            print($return);

            print("\n");


        #ENVIAR NOTIFICACION POR SEGMENTO

        } else {
        // ($data->to == 'segment') {

            $response = sendNotificationBySegment($data);

            $return["allresponses"] = $response;

            $return = json_encode( $return);
            

            print("\n\nJSON received:\n");

            print($return);

            print("\n");

        }
    }

    #ENVIAR NOTIFICACION A TODOS

    function sendMessageToAll($message) {

        $content      = array(

            "en" => $message->mensaje

        );

        $headings = array(

            'en' => $message->mensajetitulo

        );
        
        // var_dump($GLOBALS['appId'], $message->imagen) or die();

        $fields = array(

            'app_id' => $GLOBALS['appId'],

            'included_segments' => array(

                'All'

            ),

            'data' => array(

                "foo" => "bar"

            ),

            'contents' => $content,

            'headings' => $headings,
            'large_icon' => $message->imagen,
            'big_picture' => $message->imagen,
            'chrome_web_icon' => $message->imagen,
            'chrome_web_image' => $message->imagen


        );
        

        $fields = json_encode($fields);

        print("\nJSON sent:\n");

        print($fields);
        

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

            'Content-Type: application/json; charset=utf-8',

            'Authorization: Basic ' . $GLOBALS['restApi'] . ''

        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        

        $response = curl_exec($ch);

        curl_close($ch);
        

        return $response;

    }

    #ENVIAR NOTIFICACION POR USUARIO

    function sendNotificationByUserId($user_id, $heading, $message){

        $content = array(

            "en" => $message

            );

        $headings = array(

            'en' => $heading

        );
        

        $fields = array(

            'app_id' => $GLOBALS['appId'],

            'include_player_ids' => array($user_id),

            'data' => array("foo" => "bar"),

            'contents' => $content,

            'headings' => $headings

        );
        

        $fields = json_encode($fields);

        print("\nJSON sent:\n");

        print($fields);
        

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);

        curl_close($ch);
        

        return $response;

    }

    function sendNotificationBySegment($message) {

        $content = array(

			"en" => $message->mensaje
		);
        $headings = array(

            'en' => $message->mensajetitulo

        );
		$fields = array(
			'app_id' => $GLOBALS['appId'],
			'included_segments' => $message->segment,
			'data' => array("foo" => "bar"),
            'contents' => $content,
            'headings' => $headings
		);
		
		$fields = json_encode($fields);
    	print("\nJSON sent:\n");
    	print($fields);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
												   'Authorization: Basic ' . $GLOBALS['restApi'] . ''));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
		
		return $response;
    }
        


?>

