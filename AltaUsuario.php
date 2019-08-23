<?php 
  header('Access-Control-Allow-Origin: *'); 
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  
  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

  if (json_decode($json, true)) {
    $params = json_decode($json, true); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE
    require("database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB
  
    $conexion = Database::db(); // CREA LA CONEXION
    
    #Validación de campos
    $errors = array();
   
    $usuario = empty($params['usuario']) ? array_push($errors, 'Usuario no válido') : $params['usuario'];
    $nombre = empty($params['nombre']) ? array_push($errors, 'Nombre no válido') : $params['nombre'];
    $apellido = empty($params['apellido']) ? array_push($errors, 'Apellido no válido') : $params['apellido'];
    $token = empty($params['token']) ? array_push($errors, 'Token no válido') : $params['token'];
    $origen = empty($params['origen']) ? array_push($errors, 'Orígen no válido') : strtoupper($params['origen']);
    
    if (empty($errors)) {
      $query = "INSERT INTO usuarios(USUARIO, NOMBRE, APELLIDO, TOKEN, ORIGEN)" .
      "VALUES (?, ?, ?, ?, ?)";
      $sql = $conexion->prepare($query);
  
      $sql->bindValue(1, $usuario, PDO::PARAM_STR);
      $sql->bindValue(2, $nombre, PDO::PARAM_STR);
      $sql->bindValue(3, $apellido, PDO::PARAM_STR);
      $sql->bindValue(4, $token, PDO::PARAM_STR);
      $sql->bindValue(5, $origen, PDO::PARAM_STR);
  
      class Result {}
        if ($sql->execute()) {
          $query = "SELECT MAX(ID) FROM USUARIOS";
          $sql = $conexion->prepare($query);
          $sql->execute();
          $id = $sql->fetchColumn();
          $response = new Result();
          $response->id = $id;
          $response->errors = $errors;
        }else{
          $response = new Result();
          $response->id = null;
          array_push($errors, $sql->errorInfo());
          $response->errors = $errors;
        }
    } else {
      class Result {}
      $response = new Result();
      $response->id = null;
      $response->errors = $errors;
    }
    
  
    // GENERA LOS DATOS DE RESPUESTA
  
    header('Content-Type: application/json');
  
    echo json_encode($response); // MUESTRA EL JSON GENERADO
  } else {
    class Result {}
    $response = new Result();
    $response->id = null;
    $response->resultado = Array("Formato Json inválido"); 
    header('Content-Type: application/json');

    echo json_encode($response); // MUESTRA EL JSON GENERADO 
  }
  

?>
