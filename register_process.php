<?php
session_start(); // ✅ Asegúrate de tener esto
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $email, $password]);

        $_SESSION['mensaje'] = "Usuario registrado exitosamente."; // ✅ Guarda mensaje
        header("Location: login.php"); // ✅ Redirige
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error al registrar usuario.";
        header("Location: register.php");
        exit();
    }
}
?>