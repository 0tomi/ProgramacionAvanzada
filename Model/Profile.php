<?php

class Profile {
    private $userID;
    private $Descripcion;
    private $userTag;
    private $database;
    private $PhotoPath;

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

        $stmt->bind_param('i', $this->userID);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $this->Descripcion = $row['Descripcion'];
        } else {
            $this->Descripcion = '';
        }

        $stmt->close();
    }

    // Getters

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

        if (!$this->userTag) {
            $this->userTag = 'defaultTag';
        }

        $stmt = $this->database->prepare(
            "SELECT userTag 
             FROM User
             JOIN Profile ON User.idUser = Profile.idUser
             WHERE Profile.idUser = ?"
        );

        $stmt->bind_param('i', $this->userID);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $userTag = $row['userTag'];
        } else {
            $userTag = '';
        }

        return $userTag;
    }

    public function getProfileImage() {
        if (!$this->userID) {
            throw new RuntimeException("Usuario no inicializado");
        }

        $stmt = $this->database->prepare(
            "SELECT profileImageRoute
             FROM User
             WHERE idUser = ?"
        );

        $stmt->bind_param('i', $this->userID);
        $stmt->execute();

        $result = $stmt->get_result();
        $photoPath = null;

        if ($row = $result->fetch_assoc()) {
            $photoPath = $row['profileImageRoute'] ?? null;
        }

        $stmt->close();

        if (empty($photoPath)) {
            $photoPath = 'Resources/profilePictures/defaultProfilePicture.png';
        }

        return $this->normalizePublicPath($photoPath);
    }

    private function normalizePublicPath(string $path): string
    {
        if ($path === '') {
            return '../Resources/profilePictures/defaultProfilePicture.png';
        }

        if (preg_match('#^(https?://|data:)#i', $path)) {
            return $path;
        }

        if (strpos($path, '../') === 0 || $path[0] === '/') {
            return $path;
        }

        $trimmed = ltrim($path, '/');

        return '../' . $trimmed;
    }

    // updates (Modificacion)

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

        $this->userTag = $newUserTag;
    }

    public function updatePhoto($originalFileName) {
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException("No se recibió un archivo de imagen válido.");
        }

        if (!is_uploaded_file($_FILES['imagen']['tmp_name'])) {
            throw new RuntimeException("No se recibió un archivo de imagen válido.");
        }

        $extension = strtolower((string)pathinfo($originalFileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new RuntimeException("Formato de imagen no soportado.");
        }

        try {
            $uniqueName = sprintf(
                '%d_%s.%s',
                $this->userID,
                bin2hex(random_bytes(8)),
                $extension
            );
        } catch (Exception $e) {
            throw new RuntimeException("No se pudo generar un nombre para la imagen.");
        }

        $photoPathFull = 'Resources/profilePictures/' . $uniqueName;
        $targetPath = __DIR__ . '/../' . $photoPathFull;

        $targetDirectory = dirname($targetPath);
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException("No se pudo crear el directorio de destino para la imagen.");
        }

        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $targetPath)) {
            throw new RuntimeException("Error al mover el archivo subido.");
        }

        $stmt = $this->database->prepare(
            "UPDATE User
             SET profileImageRoute = ?
             WHERE idUser = ?"
        );

        $stmt->bind_param('si', $photoPathFull, $this->userID);
        $stmt->execute();
        $stmt->close();

        $this->PhotoPath = $photoPathFull;

        if (isset($_SESSION['user']) && method_exists($_SESSION['user'], 'setProfilePhoto')) {
            $_SESSION['user']->setProfilePhoto($photoPathFull);
        }
    }
}

?>