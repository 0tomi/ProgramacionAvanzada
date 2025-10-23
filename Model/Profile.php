<?php

class Profile {
    private mysqli $database;
    private int $userID;
    private string $Descripcion = '';
    private string $userTag = '';
    private string $PhotoPath = 'Resources/profilePictures/defaultProfilePicture.png';
    private string $displayName = '';

    public function __construct(int $userID_) {
        if (!$userID_) {
            throw new RuntimeException("Se necesita un userID para cargar un perfil.");
        }

        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $this->database = new mysqli(
                $_ENV['DB_HOST'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $_ENV['DB_NAME'],
                (int)$_ENV['DB_PORT']
            );
            $this->database->set_charset('utf8mb4');

            $this->userID = $userID_;
            $this->loadProfileData();
        } catch (mysqli_sql_exception $e) {
            throw new RuntimeException("Error al cargar el perfil: " . $e->getMessage(), 0, $e);
        }
    }

    private function loadProfileData(): void {
        $stmt = $this->database->prepare(
            "SELECT username, userTag, profileImageRoute
             FROM User
             WHERE idUser = ?"
        );
        $stmt->bind_param('i', $this->userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $userRow = $result->fetch_assoc();
        $stmt->close();

        if (!$userRow) {
            throw new RuntimeException("Perfil no encontrado.");
        }

        $this->displayName = (string)($userRow['username'] ?? '');
        $this->userTag = (string)($userRow['userTag'] ?? '');

        $photo = trim((string)($userRow['profileImageRoute'] ?? ''));
        if ($photo !== '') {
            $this->PhotoPath = $photo;
        }

        $stmt = $this->database->prepare(
            "SELECT Descripcion
             FROM Profile
             WHERE idUser = ?"
        );
        $stmt->bind_param('i', $this->userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $profileRow = $result->fetch_assoc();
        $stmt->close();

        $this->Descripcion = (string)($profileRow['Descripcion'] ?? '');
    }

    public function getDescripcion(): string {
        return $this->Descripcion;
    }

    public function getUserTag(): string {
        return $this->userTag;
    }

    public function getProfileImage(): string {
        return $this->PhotoPath;
    }

    public function getDisplayName(): string {
        return $this->displayName;
    }

    public function getUserId(): int {
        return $this->userID;
    }

    public function toArray(): array {
        return [
            'id' => $this->getUserId(),
            'displayName' => $this->getDisplayName(),
            'userTag' => $this->getUserTag(),
            'description' => $this->getDescripcion(),
            'profilePhoto' => $this->getProfileImage(),
        ];
    }

    // updates (Modificacion)

    public function updateDescripcion(string $newDescripcion): void {
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

    public function updateUserTag(string $newUserTag): void {
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

    public function updatePhoto(string $newPhotoPath): void {
        $photoPathFull = 'Resources/profilePictures/' . $newPhotoPath;
        $targetPath = __DIR__ . '/../' . $photoPathFull;

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
    }
}

?>
