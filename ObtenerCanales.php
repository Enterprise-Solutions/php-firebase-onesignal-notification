<?php
  header('Access-Control-Allow-Origin: *');
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

  require_once("config/database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

  $conexion = Database::db(); // CREA LA CONEXION

    class Result {}
    $response = new Result();

  // REALIZA LA QUERY A LA DB
  $query = "select * from canales order by id asc";
			$sql = $conexion->prepare($query);
			$sql->execute();
      $response->data = $sql->fetchAll(PDO::FETCH_ASSOC);


  $data = json_encode($response); // GENERA EL JSON CON LOS DATOS OBTENIDOS


  echo $data; // MUESTRA EL JSON GENERADO

  header('Content-Type: application/json');
?>