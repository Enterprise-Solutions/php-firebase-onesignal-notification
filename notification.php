
<?PHP

  require_once("config/database.php"); // IMPORTA EL ARCHIVO CON LA CONEXION A LA DB


  class Notificacion {

    private $titulo;
    private $mensaje;
    private $segmento;
    private $servicio;
    private $image;
    private $date;
    private $accion;
    private $accionDestino;
    private $db;
    
    public function __construct() {

      $this->db = Database::db();

    }


    public function getDB() {

      return $this->db;

    }

    /**
     * Get the value of titulo
     */ 
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set the value of titulo
     *
     * @return  self
     */ 
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get the value of segmento
     */ 
    public function getSegmento()
    {
        return $this->segmento;
    }

    /**
     * Set the value of segmento
     *
     * @return  self
     */ 
    public function setSegmento($segmento)
    {
        $this->segmento = $segmento;

        return $this;
    }

    /**
     * Get the value of servicio
     */ 
    public function getServicio()
    {
        return $this->servicio;
    }

    /**
     * Set the value of servicio
     *
     * @return  self
     */ 
    public function setServicio($servicio)
    {
        $this->servicio = $servicio;

        return $this;
    }

    /**
     * Get the value of image
     */ 
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @return  self
     */ 
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get the value of date
     */ 
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get the value of mensaje
     */ 
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * Set the value of mensaje
     *
     * @return  self
     */ 
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;

        return $this;
    }

    /**
     * Get the value of accion
     */ 
    public function getAccion()
    {
        return $this->accion;
    }

    /**
     * Set the value of accion
     *
     * @return  self
     */ 
    public function setAccion($accion)
    {
        $this->accion = $accion;

        return $this;
    }

    /**
     * Get the value of accionDestino
     */ 
    public function getAccionDestino()
    {
        return $this->accionDestino;
    }

    /**
     * Set the value of accionDestino
     *
     * @return  self
     */ 
    public function setAccionDestino($accionDestino)
    {
        $this->accionDestino = $accionDestino;

        return $this;
    }


    public function guardarNotificacion($estado) {

      date_default_timezone_set('America/Asuncion');

      $date = date('d-m-y H:i:s');

      $execute = array($this->getTitulo(), $this->getMensaje(), $this->getSegmento(), $this->getServicio(), 
      $this->getImage(), $date, $estado);
      
      $query = "INSERT INTO notificaciones(titulo, descripcion, canal, servicio, image, fecha, estado) 
        values(?, ?, ?, ?, ?, ?, ?)";
        
      $sql = $this->db->prepare($query);

      $sql->bindValue(1, $this->getTitulo(), PDO::PARAM_STR);
      $sql->bindValue(2, $this->getMensaje(), PDO::PARAM_STR);
      $sql->bindValue(3, $this->getSegmento(), PDO::PARAM_STR);
      $sql->bindValue(4, $this->getServicio(), PDO::PARAM_STR);
      $sql->bindValue(5, $this->getImage(), PDO::PARAM_STR);
      $sql->bindValue(6, $date, PDO::PARAM_STR);
      $sql->bindValue(7, $estado, PDO::PARAM_BOOL);

      $resultado = $sql->execute();

    }

    public function getNotification(){

        $notification = array();
        
        $notification['title'] = $this->getTitulo();
        
        $notification['message'] = $this->getMensaje();
        
        $notification['image'] = $this->getImage();
        
        $notification['action'] = $this->getAccion();
        
        $notification['action_destination'] = $this->getAccionDestino();
        
        return $notification;
        
	}

    public function enviarNotificacionOneSignal($fields) {

        // Referencia: https://documentation.onesignal.com/reference

        //   $restApi = 'YWUwZmU0MTYtYjgyYi00ZDIzLWIzOTMtOWExMzMzOGI4MmI4';
        $restApi = 'YWFkMjllMzItOTU5Yi00NzNmLWJlNzEtZTJjODllMzZiZTAy';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
            
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

            'Content-Type: application/json; charset=utf-8',

            'Authorization: Basic ' . $restApi . ''

        ));          
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        return $ch;

    }

    public function enviarNotificacionFireBase($fields) {

        $firebase_api = 'AAAAtNT2xxA:APA91bHaEjh4FZlZCs0rw2uxhnQ8S2Fb-V7dY97PDbayT3EAzhXpKGy4gpyF2ZT9M4TaZ5Evb81xREYKw2MrWuJ1VCZHhyk5AYK6Eo5vVufh6MMHipHfn7-WNegJOs6LlsLuLr4W0GEd';
      
        // Set data variables
        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = array(
            'Authorization: key=' . $firebase_api,
            'Content-Type: application/json'
        );

        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarily
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        // Execute data
        return $ch;
                        
    }

  }


?>

