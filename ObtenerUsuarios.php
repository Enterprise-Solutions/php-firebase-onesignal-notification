<?php 
  header('Access-Control-Allow-Origin: *'); 
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  
    require("./config/database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

    $conexion = Database::db(); // CREA LA CONEXION
    class Result {}
    $response = new Result();
    $param = isset($_GET['param']) ? $_GET['param'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
       
    if ($search == 'false') {
        if ($param == 'all') {
            $query = "SELECT * FROM USUARIOS ORDER BY USUARIO";
            $sql = $conexion->prepare($query);
            if ($sql->execute()) {
                $response->data = $sql->fetchAll(PDO::FETCH_ASSOC);
            }else{
                $response = $sql->errorInfo();
            }
        } else {
            $query = "SELECT * FROM USUARIOS WHERE ORIGEN = ? ORDER BY USUARIO";
            $sql = $conexion->prepare($query);
            if ($sql->execute([$param])) {
                $response->data = $sql->fetchAll(PDO::FETCH_ASSOC);
            }else{
                $response = $sql->errorInfo();
            }

        }
    } else {
        $query = "SELECT * FROM USUARIOS WHERE USUARIO ILIKE ? ORDER BY USUARIO";
        $sql = $conexion->prepare($query);
        if ($sql->execute(["%$param%"])) {
            $response->data = $sql->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $response = $sql->errorInfo();
        }
    }
  
    // GENERA LOS DATOS DE RESPUESTA
  
    header('Content-Type: application/json');
    echo json_encode($response); // MUESTRA EL JSON GENERADO

?>
