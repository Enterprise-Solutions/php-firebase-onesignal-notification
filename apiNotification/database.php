<?php
require_once('params.php');
class Database { 

	public static function db() {

		try{
			$conn = new PDO('pgsql:host=localhost;dbname=notificacion;','postgres','root');
			$conn = new PDO('pgsql:host='. hostName . ';dbname=' .dbName . ';', userName,pass);

            $conn->exec("set names UTF8");
            return $conn;

		}catch(PDOException $e) {
			echo "ERROR: " . $e->getMessage();
		}
	}
}
?>