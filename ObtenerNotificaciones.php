<?php 
  header('Access-Control-Allow-Origin: *'); 
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  
  require_once("./config/database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

  $conexion = Database::db(); // CREA LA CONEXION

  $servicio = $_GET['id'];
  class Result {}
  $response = new Result();

  // REALIZA LA QUERY A LA DB
  $query = "select * from notificaciones where servicio = ? order by id asc";
  $sql = $conexion->prepare($query);
  $sql->execute([$servicio]);
  $response->data = $sql->fetchAll(PDO::FETCH_ASSOC);
  
  $json = json_encode($response); // GENERA EL JSON CON LOS DATOS OBTENIDOS

  header('Content-Type: application/json');

  echo ($json); // MUESTRA EL JSON GENERADO

?>
