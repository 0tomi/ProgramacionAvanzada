<?php

class Profile {
    private $userID;
    private $Descripcion;
    private $userTag;
    private $database;

    public function __construct($userID_) {

        if(!$userID_) {
            throw new RuntimeException("Se necesita un userID para cargar un perfil.");
        }

        try {
            $this->database = new mysqli(
                $_ENV['DB_HOST'],
                $_ENV['DB_USER'], 
                $_ENV['DB_PASS'], 
                $_ENV['DB_NAME'], 
                $_ENV['DB_PORT']
            );

            $this->userID = $userID_;
            $this->loadProfileData();

        } catch (mysqli_sql_exception $e) {
            throw new RuntimeException("Error: " . $e->getMessage());
        }

        
    }

    private function loadProfileData() {

        $stmt = $this->database->prepare(
            "SELECT Profile.Descripcion, User.userTag 
             FROM Profile 
             JOIN User ON Profile.idUser = User.idUser 
             WHERE Profile.idUser = ?"
        );

        $stmt->bind_param('si', $this->userID);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $this->Descripcion = $row['Descripcion'];
        } else {
            $this->Descripcion = '';
        }

        $stmt->close();
    }

    public function getDescripcion() {
        $stmt = $this->database->prepare(
            "SELECT Descripcion 
             FROM Profile 
             WHERE idUser = ?"
        );

        $stmt->bind_param('i', $this->userID);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $Descripcion = $row['Descripcion'];
        } else {
            $Descripcion = '';
        }

        return $Descripcion;
    }

    public function getUserTag() {
        if (!$this->userID) {
            throw new RuntimeException("Usuario no inicializado");
        }

        $stmt = $this->database->prepare(
            "SELECT userTag 
             FROM User 
             WHERE idUser = ?"
        );

        $stmt->bind_param('i', $this->userID);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $userTag = $row['userTag'];
        } else {
            $userTag = '';
        }
    }

    public function updateDescripcion($newDescripcion) {
        $stmt = $this->database->prepare(
            "UPDATE Profile 
             SET Descripcion = ? 
             WHERE idUser = ?"
        );

        $stmt->bind_param('si', $newDescripcion, $this->userID);

        $stmt->execute();

        $stmt->close();

        $this->Descripcion = $newDescripcion;
    }

    public function updateUserTag($newUserTag) {
        $stmt = $this->database->prepare(
            "UPDATE User 
             SET userTag = ? 
             WHERE idUser = ?"
        );

        $stmt->bind_param('si', $newUserTag, $this->userID);

        $stmt->execute();

        $stmt->close();
    }
}

?>