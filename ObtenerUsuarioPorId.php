<?php 
  header('Access-Control-Allow-Origin: *'); 
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  
    require("./config/database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

    $conexion = Database::db(); // CREA LA CONEXION
    class Result {}
    $response = new Result();
    $param = isset($_GET['id']) ? $_GET['id'] : '';
       
    $query = "SELECT * FROM USUARIOS WHERE ID = ?";
    $sql = $conexion->prepare($query);
    if ($sql->execute([$param])) {
        $response->data = $sql->fetchObject();
    }else{
        $response = $sql->errorInfo();
    }
    
  
    // GENERA LOS DATOS DE RESPUESTA
  
    header('Content-Type: application/json');
    echo json_encode($response); // MUESTRA EL JSON GENERADO

?>
