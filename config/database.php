<?php
class Database {

	public static function db() {

		try{
			$conn = new PDO('pgsql:host=raja.db.elephantsql.com;dbname=mgtweizk;','mgtweizk','HwmS4LrUqU1UDFzf0fxWXQXXwCINMuLs');
            $conn->exec("set names UTF8");
            return $conn;

		}catch(PDOException $e) {
			echo "ERROR: " . $e->getMessage();
		}
	}
}
?>