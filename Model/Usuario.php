<?php
use Dba\Connection;
//require_once '../includes/lectorEnv.php';
class User {
    private $idUsuario, $flash;
    private $nombre, $descripcion, $profilePhoto;
    private $bd;

    public function __construct($id){
        
        //$bd = new mysqli($_ENV['DB_HOST'],$_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME'], $_ENV['DB_PORT']);
        $bd = new mysqli(
            '127.0.0.1',
            'admin',
            'admin123',
            'Ritual',
            '3306'
        );
        if ($bd->connect_errno) 
            echo("Error en la bd: $bd->connect_error");
        
        
        $query = 
        "SELECT u.username as Username, u.profileImageRoute as PIR, p.Descripcion as Descr FROM `User` as u
         inner join Profile as p on u.idUser = p.idUser
         where u.idUser = $id";
        
        //echo("hasta aca llegamos");
        $result = $bd->query($query);
        if (!$result) {
            error_log('Query error: ' . $bd->error);
            throw new RuntimeException('No pude ejecutar la consulta');
        }
        $rows = $result->fetch_assoc();
        if (!$rows) {
            throw new RuntimeException("No hay fila para id $id");
        }
        
        $this->idUsuario = $id;
        $this->nombre = $rows['Username'];
        $this->descripcion = $rows['Descr'];
        $this->profilePhoto = $rows['PIR'];
        $this->flash = ['type' => 'success', 'msg' => 'Bienvenido a Ritual, '.$this->nombre];
    }

    /**
     * Get the value of idUsuario
     */ 
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    /**
     * Set the value of idUsuario
     *
     * @return  self
     */ 
    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;

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

    /**
     * Get the value of descripcion
     */ 
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set the value of descripcion
     *lofi
     * @return  self
     */ 
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get the value of profilePhoto
     */ 
    public function getProfilePhoto()
    {
        return $this->profilePhoto;
    }

    /**
     * Set the value of profilePhoto
     *
     * @return  self
     */ 
    public function setProfilePhoto($profilePhoto)
    {
        $this->profilePhoto = $profilePhoto;

        return $this;
    }

    /**
     * Get the value of flash
     */ 
    public function getFlash()
    {
        return $this->flash;
    }

    /**
     * Set the value of flash
     *
     * @return  self
     */ 
    public function setFlash($flash)
    {
        $this->flash = $flash;

        return $this;
    }
}