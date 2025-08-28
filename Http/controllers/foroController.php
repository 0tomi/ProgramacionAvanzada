<?php
// ForoController.php
// Controlador para gestionar la sección de foro y mostrar los formularios de notas

namespace Http\controllers;

use Core\Database;
use Core\Response;

class ForoController {
    public function index() {
        // Obtener todas las notas con los nuevos campos
        $config = require base_path('Core/config.php');
        $db = new Database($config);
        $notas = $db->query('SELECT * FROM notas');
        view('forum.view.php', ['notas' => $notas]);
    }

    public function showForm() {
        // Mostrar el formulario para crear una nueva nota
        view('notes/create.view.php');
    }
}
