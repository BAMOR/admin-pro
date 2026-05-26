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
    <title>Términos y Condiciones - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <h1>Términos y Condiciones</h1>
        <div>
            <a href="index.php">Tienda</a>
            <a href="faq.php">FAQ</a>
            <a href="chat.php">Chat</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="container">
        <h2>Aceptación de términos</h2>
        <p>Al usar este sitio, aceptas estos términos y condiciones en su totalidad.</p>

        <h2>Uso del sitio</h2>
        <p>El uso del sitio es responsabilidad del usuario. No se permite el uso para fines ilegales.</p>

        <h2>Productos y precios</h2>
        <p>Los precios y descripciones de los productos están sujetos a cambios sin previo aviso.</p>

        <h2>Devoluciones y reembolsos</h2>
        <p>Aceptamos devoluciones dentro de los 30 días posteriores a la compra.</p>

        <h2>Limitación de responsabilidad</h2>
        <p>No nos hacemos responsables por daños indirectos o consecuentes.</p>
    </div>
</body>
</html>