<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/Profile.php';

/**
 * Controlador que maneja las interacciones entre el modelo Profile y la vista perfil.php
 */
class ProfileController {
    private Profile $profile;
    private array $errors = [];

    public function __construct() {
        if (!isset($_SESSION['user'])) {
            throw new RuntimeException('Usuario no autenticado');
        }
        
        $userId = $_SESSION['user']->getIdUsuario();
        $this->profile = new Profile($userId);
    }

    /**
     * Obtiene los datos del perfil para mostrar en la vista
     */
    public function getProfileData(): array {
        try {
            return [
                'userTag' => $this->profile->getUserTag(),
                'description' => $this->profile->getDescripcion(),
                'profilePhoto' => $this->profile->getProfileImage(),
                'errors' => $this->errors,
                'success' => empty($this->errors)
            ];
        } catch (RuntimeException $e) {
            $this->errors[] = $e->getMessage();
            return [
                'userTag' => '',
                'description' => '',
                'profilePhoto' => '',
                'errors' => $this->errors,
                'success' => false
            ];
        }
    }

    /**
     * Procesa la actualización de datos del perfil
     */
    public function processProfileUpdate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Validar qué acción se está solicitando
        $action = $_POST['action'] ?? '';
        
        try {
            switch ($action) {

                case 'updateDescripcion':
                    if (isset($_POST['description'])) {
                        $this->profile->updateDescripcion($_POST['description']);
                    }
                    break;

                case 'updateUserTag':
                    if (isset($_POST['userTag'])) {
                        $this->profile->updateUserTag($_POST['userTag']);
                    }
                    break;

                case 'updatePhoto':
                    if (isset($_FILES['imagen'])) {
                        $this->profile->updatePhoto($_FILES['imagen']['name']);
                    }
                    break;

                default:
                    // Si no hay acción específica, solo mostramos el perfil
                    break;
            }

        } catch (RuntimeException $e) {
            $this->errors[] = $e->getMessage();
            $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getMessage()];
        }
    }

}