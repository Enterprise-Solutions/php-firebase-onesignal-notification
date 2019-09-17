
<?PHP

require_once("config/database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB

class Canal {

    private $id;
    private $nombre;
    private $db;
    
    public function __construct() {

        $this->db = Database::db();

    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of nombre
     */ 
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set the value of nombre
     *
     * @return  self
     */ 
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getCanal($id) {
      
        $query = "SELECT nombre FROM CANALES WHERE ID = ?";
        $sql = $this->db->prepare($query);
        $sql->execute([$id]);
        $resultado = $sql->fetchColumn();
        return $resultado;
    }
}

?>

