<?php
class Database {

	public static function db() {

		try{
			$conn = new PDO('pgsql:host=localhost;dbname=notificacion;','postgres','root');
            $conn->exec("set names UTF8");
            return $conn;

		}catch(PDOException $e) {
			echo "ERROR: " . $e->getMessage();
		}
	}
}
?>