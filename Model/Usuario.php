<?php
class User {
    private $idUsuario, $flash;
    private $nombre, $descripcion, $profilePhoto;

    public function __construct($id, $name, $desc, $pp){
        $this->idUsuario = $id;
        $this->nombre = $name;
        $this->descripcion = $desc;
        $this->profilePhoto = $pp;
        $this->flash = ['type' => 'success', 'msg' => 'Bienvenido a Ritual, '.$name];
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