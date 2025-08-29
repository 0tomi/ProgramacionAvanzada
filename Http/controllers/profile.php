<?php
// Http/controllers/profile.php

use Core\Database;

session_start();

$userId = $_SESSION['user']['id'] ?? null;
if (!$userId) {
    header('Location: /login');
    exit;
}

$config = require base_path('public/assets/config.php');
$db = new Database($config);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'] ?? '';
    $imageName = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image']['tmp_name'];
        $originalName = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $imageName = uniqid('profile_') . '.' . $ext;
            $dest = base_path('public/assets/profile/' . $imageName);
            move_uploaded_file($tmpName, $dest);
        }
    }

    if ($imageName) {
        $db->Query('UPDATE profile SET description = :description, image = :image WHERE user_id = :user_id', [
            'description' => $description,
            'image' => $imageName,
            'user_id' => $userId
        ]);
    } else {
        $db->Query('UPDATE profile SET description = :description WHERE user_id = :user_id', [
            'description' => $description,
            'user_id' => $userId
        ]);
    }
    redirect('/profile');
    exit;
}

$profile = $db->Query('SELECT * FROM profile WHERE user_id = :user_id', [
    'user_id' => $userId
])->find();

view('profile.view.php', [
    'profile' => $profile
]);