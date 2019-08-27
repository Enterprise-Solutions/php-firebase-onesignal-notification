<?php 
  header('Access-Control-Allow-Origin: *'); 
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  
  $json = file_get_contents('php://input'); // RECIBE EL JSON DE ANGULAR
  
  //$array = ['nombre'=> 'Juegos'];
  
 
  $params = json_decode($json, true); // DECODIFICA EL JSON Y LO GUARADA EN LA VARIABLE
  $json = json_encode($array);
  //var_dump($json) or die();

  
  require_once("database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

  $conexion = Database::db(); // CREA LA CONEXION
  
  // REALIZA LA QUERY A LA DB
  $query = "INSERT INTO canales(nombre) 
			VALUES (?)";
			$sql = $conexion->prepare($query);
      $sql->execute([$array['nombre']]);

  $query2 = "SELECT MAX(id) FROM canales";
    $sql = $conexion->prepare($query2);
    $sql->execute();
    $id = $sql->fetchColumn();  
  
  class Result {}

  // GENERA LOS DATOS DE RESPUESTA
  $response = new Result();
  $response->resultado = 'El Canal fue creado exitosamente!';
  $response->id = $id;

  header('Content-Type: application/json');

  echo json_encode($response); // MUESTRA EL JSON GENERADO
?>