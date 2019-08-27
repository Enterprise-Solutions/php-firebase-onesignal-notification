<?php 
  header('Access-Control-Allow-Origin: *'); 
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  
  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR

  //$array2 = ['usuario_id'=> '6','canal_id'=> '42'];
  //var_dump($array2) or die();
  
 
  $params = json_decode($json, true); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE
  //$json = json_encode($array2);
  //var_dump($json) or die();

  
  require_once("database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

  $conexion = Database::db(); // CREA LA CONEXION
  
  // REALIZA LA QUERY A LA DB
  $query = "INSERT INTO usuarios_x_canales(usuario_id, canal_id) 
    VALUES (?, ?)";
    $sql = $conexion->prepare($query);
    $sql->execute([$params['usuario_id'], $params['canal_id']]);  
    
   
  
  class Result {}

  // GENERA LOS DATOS DE RESPUESTA
  $response = new Result();
  $response->resultado = 'OK';

  header('Content-Type: application/json');

  echo json_encode($response); // MUESTRA EL JSON GENERADO
?>