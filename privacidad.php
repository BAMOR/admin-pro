<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Políticas de Privacidad - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <h1>Políticas de Privacidad</h1>
        <div>
            <a href="index.php">Tienda</a>
            <a href="faq.php">FAQ</a>
            <a href="chat.php">Chat</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="container">
        <h2>Información que recopilamos</h2>
        <p>Recopilamos información personal cuando te registras en nuestro sitio o realizas una compra. La información incluye nombre, dirección de correo electrónico y datos de pago.</p>

        <h2>Uso de la información</h2>
        <p>Usamos la información recopilada para procesar compras, enviar notificaciones y mejorar tu experiencia en el sitio.</p>

        <h2>Compartir información</h2>
        <p>No compartimos tu información personal con terceros, excepto cuando sea necesario para cumplir con la ley.</p>

        <h2>Seguridad</h2>
        <p>Implementamos medidas de seguridad para proteger tu información personal.</p>

        <h2>Cookies</h2>
        <p>Utilizamos cookies para mejorar la funcionalidad del sitio y personalizar la experiencia del usuario.</p>
    </div>
</body>
</html>