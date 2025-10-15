<?php
$host = 'localhost';
$dbname = 'RitualBD';
$dbUser = 'admin';
$dbPassword = 'admin123';

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header('Location: ../register.php?error=Datos+incompletos');
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $dbUser,
        $dbPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM Usuario WHERE nombre = :nombre');
    $stmt->execute([':nombre' => $username]);

    if ($stmt->fetchColumn() > 0) {
        header('Location: ../register.php?error=Usuario+ya+existe');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertStmt = $pdo->prepare(
        'INSERT INTO Usuario (nombre, passw) VALUES (:nombre, :passw)'
    );

    $insertStmt->execute([
        ':nombre' => $username,
        ':passw' => $hashedPassword,
    ]);

    header('Location: ../login.php?success=Registro+exitoso');
    exit;
} catch (PDOException $e) {
    header('Location: ../register.php?error=Error+al+registrar');
    exit;
}
