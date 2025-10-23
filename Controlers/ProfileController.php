<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/Profile.php';
require_once __DIR__ . '/../Model/lectorEnv.php';

/**
 * Controlador que maneja las interacciones entre el modelo Profile y la vista perfil.php
 */
class ProfileController {
    private ?Profile $profile = null;
    private array $errors = [];
    private int $viewerId;
    private int $profileOwnerId;

    public function __construct(?int $profileOwnerId = null, ?string $profileUsername = null) {
        if (!isset($_SESSION['user'])) {
            throw new RuntimeException('Usuario no autenticado');
        }

        $this->viewerId = (int)$_SESSION['user']->getIdUsuario();
        $this->profileOwnerId = $this->resolveProfileOwnerId($profileOwnerId, $profileUsername);

        if ($this->profileOwnerId <= 0) {
            $this->errors[] = 'Perfil no encontrado.';
            $this->profile = null;
            return;
        }

        try {
            $this->profile = new Profile($this->profileOwnerId);
        } catch (RuntimeException $e) {
            $this->errors[] = $e->getMessage();
            $this->profile = null;
        }
    }

    public function getProfileOwnerId(): int {
        return $this->profileOwnerId;
    }

    public function getViewerId(): int {
        return $this->viewerId;
    }

    public function isOwner(): bool {
        return $this->viewerId === $this->profileOwnerId;
    }

    private function resolveProfileOwnerId(?int $profileOwnerId, ?string $profileUsername): int {
        if ($profileUsername !== null) {
            $username = trim($profileUsername);
            if ($username !== '') {
                $resolved = $this->findUserIdByUsername($username);
                if ($resolved !== null) {
                    return $resolved;
                }
                $this->errors[] = sprintf('No encontramos el perfil para "%s".', $username);
            }
        }

        if ($profileOwnerId !== null && $profileOwnerId > 0) {
            return $profileOwnerId;
        }

        return $this->viewerId;
    }

    private function findUserIdByUsername(string $username): ?int {
        $username = trim($username);
        if ($username === '') {
            return null;
        }

        $db = null;
        $stmt = null;

        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $db = new mysqli(
                $_ENV['DB_HOST'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $_ENV['DB_NAME'],
                (int)$_ENV['DB_PORT']
            );
            $db->set_charset('utf8mb4');

            $stmt = $db->prepare(
                "SELECT idUser
                 FROM User
                 WHERE username = ? OR userTag = ?
                 LIMIT 1"
            );
            $stmt->bind_param('ss', $username, $username);
            $stmt->execute();
            $stmt->bind_result($id);
            $foundId = null;
            if ($stmt->fetch()) {
                $foundId = (int)$id;
            }
            return $foundId;
        } catch (mysqli_sql_exception $e) {
            error_log('ProfileController::findUserIdByUsername -> ' . $e->getMessage());
            return null;
        } finally {
            if ($stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if ($db instanceof mysqli) {
                $db->close();
            }
        }
    }

    /**
     * Obtiene los datos del perfil para mostrar en la vista
     */
    public function getProfileData(): array {
        if ($this->profile === null) {
            return [
                'id' => $this->profileOwnerId,
                'displayName' => '',
                'userTag' => '',
                'description' => '',
                'profilePhoto' => 'Resources/profilePictures/defaultProfilePicture.png',
                'errors' => $this->errors,
                'success' => false,
                'isOwner' => $this->isOwner(),
            ];
        }

        $data = $this->profile->toArray();
        $data['errors'] = $this->errors;
        $data['success'] = empty($this->errors);
        $data['isOwner'] = $this->isOwner();

        return $data;
    }

    /**
     * Procesa la actualización de datos del perfil
     */
    public function processProfileUpdate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!$this->isOwner() || $this->profile === null) {
            $this->errors[] = 'No tenés permiso para editar este perfil.';
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'No tenés permiso para editar este perfil.'];
            return;
        }

        $action = $_POST['action'] ?? '';

        $performed = false;
        try {
            switch ($action) {
                case 'updateDescripcion':
                    if (isset($_POST['description'])) {
                        $description = trim((string)$_POST['description']);
                        $this->profile->updateDescripcion($description);
                        $performed = true;
                    }
                    break;

                case 'updateUserTag':
                    if (isset($_POST['userTag'])) {
                        $userTag = trim((string)$_POST['userTag']);
                        $this->profile->updateUserTag($userTag);
                        $performed = true;
                    }
                    break;

                case 'updatePhoto':
                    if (isset($_FILES['imagen']) && is_array($_FILES['imagen'])) {
                        $fileName = basename((string)($_FILES['imagen']['name'] ?? ''));
                        if ($fileName !== '') {
                            $this->profile->updatePhoto($fileName);
                            $performed = true;
                        }
                    }
                    break;

                default:
                    break;
            }

            if ($performed && empty($this->errors)) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Perfil actualizado correctamente.'];
            }
        } catch (RuntimeException $e) {
            $this->errors[] = $e->getMessage();
            $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getMessage()];
        }
    }
}
