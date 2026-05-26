<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Función para obtener productos con stock bajo
function getProductosBajoStock($pdo, $umbral = 5) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE stock < ? AND stock > 0 ORDER BY stock ASC");
    $stmt->execute([$umbral]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$productos_bajos = getProductosBajoStock($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <h1>Panel de Administrador</h1>
        <a href="logout.php">Cerrar sesión</a>
    </header>

    <div class="container">
        <!-- Notificación de stock bajo -->
        <?php if (count($productos_bajos) > 0): ?>
            <div style="background-color: #ffdddd; border: 1px solid #ff0000; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <h3>⚠️ Alerta de Stock Bajo</h3>
                <ul>
                    <?php foreach ($productos_bajos as $p): ?>
                        <li><?php echo htmlspecialchars($p['nombre']); ?> - Stock: <?php echo $p['stock']; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <nav>
    <a href="inventario.php">Inventario</a>
    <a href="ventas.php">Ventas</a>
    <a href="usuarios.php">Usuarios</a>
    <a href="admin_chat.php">Chat</a>
    <a href="reportes.php">Reportes</a>
    
</nav>
        <h2>Panel Principal</h2>
        <p>Bienvenido, <?php echo $_SESSION['nombre']; ?>. Aquí puedes gestionar tu tienda.</p>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>