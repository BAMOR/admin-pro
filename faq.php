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
    <title>Preguntas Frecuentes - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <h1>Preguntas Frecuentes</h1>
        <div>
            <a href="index.php">Tienda</a>
            <a href="chat.php">Chat</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="container">
        <h2>¿Cómo hago una compra?</h2>
        <p>Selecciona los productos que deseas, agrégalos al carrito y procede al checkout.</p>

        <h2>¿Cuánto tiempo tarda el envío?</h2>
        <p>El envío normal tarda entre 5 y 7 días hábiles. El envío express tarda 1 a 2 días.</p>

        <h2>¿Puedo cancelar mi pedido?</h2>
        <p>Sí, puedes cancelar tu pedido antes de que sea enviado. Contacta con soporte.</p>

        <h2>¿Qué métodos de pago aceptan?</h2>
        <p>Aceptamos tarjetas de crédito, PayPal y transferencias bancarias.</p>

        <h2>¿Tienen garantía los productos?</h2>
        <p>Sí, todos nuestros productos tienen garantía de 30 días.</p>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>