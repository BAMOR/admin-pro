<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) === 0) {
    header("Location: carrito.php");
    exit();
}

// Calcular total
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Procesar compra (UN SOLO BLOQUE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar'])) {
    $metodo_pago = $_POST['pago'];
    $metodo_envio = $_POST['envio'];

    // Registrar venta
    $stmt = $pdo->prepare("INSERT INTO ventas (usuario_id, total, metodo_pago, metodo_envio, estado) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total, $metodo_pago, $metodo_envio, 'Confirmado']);

    // Actualizar stock de productos
    foreach ($_SESSION['carrito'] as $item) {
        $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['cantidad'], $item['id']]);
    }

    // Vaciar carrito
    unset($_SESSION['carrito']);
    
    // Guardar mensaje en sesión
    $_SESSION['mensaje_compra'] = "Compra confirmada exitosamente. ¡Gracias por tu compra!";
    
    // Redirigir para evitar reenvío de formulario
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <h1>Checkout</h1>
        <a href="carrito.php">Volver al carrito</a>
        <a href="logout.php">Cerrar sesión</a>
    </header>

    <div class="container">
        <h2>Resumen de tu compra</h2>

        <table border="1" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['carrito'] as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                        <td>$<?php echo $item['precio']; ?></td>
                        <td><?php echo $item['cantidad']; ?></td>
                        <td>$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Total: $<?php echo number_format($total, 2); ?></h3>

        <form method="POST" style="margin-top: 20px;">
            <h2>Método de envío</h2>
            <select name="envio" required>
                <option value="gratis">Envío gratis (5-7 días)</option>
                <option value="express">Envío express ($5 extra, 1-2 días)</option>
            </select>

            <h2>Método de pago</h2>
            <select name="pago" required>
                <option value="tarjeta">Tarjeta de crédito</option>
                <option value="paypal">PayPal</option>
                <option value="transferencia">Transferencia bancaria</option>
            </select>

            <button type="submit" name="comprar" onclick="descargarFactura()">Confirmar Compra</button>
        </form>
    </div>

    <script>
        function descargarFactura() {
            // Abrir nueva ventana para descargar la factura
            window.open('generar_factura.php', '_blank');
        }
    </script>
</body>
</html>