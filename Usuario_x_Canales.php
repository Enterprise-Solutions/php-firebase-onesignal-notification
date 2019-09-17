<?php

  header('Access-Control-Allow-Origin: *'); 

  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

  require_once("./config/database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

  $conexion = Database::db(); // CREA LA CONEXION
  
  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

  $params = json_decode($json, true); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE

  $errors = array();

  if (isset($params['usuario_id']) && !empty($params['usuario_id'])) {

    $usuarioId = $params['usuario_id'];

  } else {

    array_push($errors, 'ID_Usuario Invalid');

  }

  if (isset($params['canal_id']) && !empty($params['canal_id'])) {

    $canalId = $params['canal_id'];

  } else {

    array_push($errors, 'ID_Canal Invalid');

  }

  if (empty($errors)) {

    class Result {}

    $query = "INSERT INTO usuarios_x_canales(usuario_id, canal_id) VALUES (?, ?)";

    $sql = $conexion->prepare($query);

    if ($sql->execute([$usuarioId, $canalId])) {

      $response = new Result();

      $response->success = 'true';

      $response->errors = $errors;

    } else {

      $response = new Result();

      $response->success = 'false';

      $response->errors = $sql->errorInfo();
    }
      
  } else {

    class Result {}

    $response = new Result();

    $response->success = 'false';

    $response->errors = $errors;

  }

  header('Content-Type: application/json');

  echo json_encode($response); // MUESTRA EL JSON GENERADO
?>