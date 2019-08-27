<?php 
  header('Access-Control-Allow-Origin: *'); 
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  
  require_once("database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

  $conexion = Database::db(); // CREA LA CONEXION

  // REALIZA LA QUERY A LA DB
  $query = "select * from canales order by id asc";
			$sql = $conexion->prepare($query);
			$sql->execute();
      $resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
    

  $json = json_encode($resultado); // GENERA EL JSON CON LOS DATOS OBTENIDOS

  
  echo $json; // MUESTRA EL JSON GENERADO
  
  header('Content-Type: application/json');
?>