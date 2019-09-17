<?php 
  header('Access-Control-Allow-Origin: *'); 
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  
  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

  if (json_decode($json)) {
    $params = json_decode($json, true); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE
    require_once("./config/database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB
  
    $conexion = Database::db(); // CREA LA CONEXION
    
    #Validación de campos
    $errors = array();
   
    $titulo = isset($params['mensajetitulo']) ? $params['mensajetitulo'] : array_push($errors, '');
    $descripcion = isset($params['mensaje']) ? $params['mensaje'] : array_push($errors, '');
    $segmento = isset($params['segment']) ? $params['segment'] : array_push($errors, '');
    $servicio = isset($_GET['id']) ? $_GET['id'] : array_push($errors, '');
    $image = isset($params['imagen']) ? $params['imagen'] : array_push($errors, '');
    $date = isset($params['fecha']) ? $params['fecha'] : array_push($errors, '');

    if (empty($errors)) {
      $query = "INSERT INTO notificaciones(titulo, descripcion, canal, servicio, imagen, fecha)" .
      "VALUES (?, ?, ?, ?, ?, ?)";
      $sql = $conexion->prepare($query);
      class Result {}
      // var_dump($titulo, $descripcion, $segmento, $servicio, $image) or die();

      foreach ($segmento as $key => $value) {
        $sql->bindValue(1, $titulo, PDO::PARAM_STR);
        $sql->bindValue(2, $descripcion, PDO::PARAM_STR);
        $sql->bindValue(3, $value, PDO::PARAM_STR);
        $sql->bindValue(4, $servicio, PDO::PARAM_STR);
        $sql->bindValue(5, $image, PDO::PARAM_STR);
        $sql->bindValue(6, $date, PDO::PARAM_STR);

        if ($sql->execute()) {
          $response = new Result();
        } else {
          $response = new Result();
          array_push($errors, $sql->errorInfo());
          $response->errors = $errors;
        }
      }
      echo json_encode($response); // MUESTRA EL JSON GENERADO
    } else {
      class Result {}
      $response = new Result();
      $response->id = null;
      $response->errors = $errors;
      echo json_encode($response); // MUESTRA EL JSON GENERADO
    }
    
  
    // GENERA LOS DATOS DE RESPUESTA
  
    header('Content-Type: application/json');
  
  } else {
    class Result {}
    $response = new Result();
    $response->id = null;
    $response->resultado = Array("Formato Json inválido"); 
    header('Content-Type: application/json');

    echo json_encode($response); // MUESTRA EL JSON GENERADO 
  }
  

?>
