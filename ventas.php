<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Cambiar estado de venta
if (isset($_POST['cambiar_estado'])) {
    $venta_id = $_POST['venta_id'];
    $nuevo_estado = $_POST['estado'];

    $stmt = $pdo->prepare("UPDATE ventas SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $venta_id]);
}

$stmt = $pdo->query("SELECT v.*, u.nombre AS cliente_nombre FROM ventas v JOIN usuarios u ON v.usuario_id = u.id ORDER BY v.fecha DESC");
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Panel Admin</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <h1>Panel de Ventas</h1>
        <a href="admin.php">Volver al panel</a>
        <a href="logout.php">Cerrar sesión</a>
    </header>

    <div class="container">
        <h2>Historial de Ventas</h2>

        <table border="1" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Método de Pago</th>
                    <th>Método de Envío</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $venta): ?>
                    <tr>
                        <td><?php echo $venta['id']; ?></td>
                        <td><?php echo htmlspecialchars($venta['cliente_nombre']); ?></td>
                        <td>$<?php echo $venta['total']; ?></td>
                        <td><?php echo htmlspecialchars($venta['metodo_pago']); ?></td>
                        <td><?php echo htmlspecialchars($venta['metodo_envio']); ?></td>
                        <td><?php echo $venta['fecha']; ?></td>
                        <td><?php echo htmlspecialchars($venta['estado']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="venta_id" value="<?php echo $venta['id']; ?>">
                                <select name="estado">
                                    <option value="Confirmado" <?php echo $venta['estado'] === 'Confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                                    <option value="En empaque" <?php echo $venta['estado'] === 'En empaque' ? 'selected' : ''; ?>>En empaque</option>
                                    <option value="En transporte" <?php echo $venta['estado'] === 'En transporte' ? 'selected' : ''; ?>>En transporte</option>
                                    <option value="En camino" <?php echo $venta['estado'] === 'En camino' ? 'selected' : ''; ?>>En camino</option>
                                    <option value="Entregado" <?php echo $venta['estado'] === 'Entregado' ? 'selected' : ''; ?>>Entregado</option>
                                </select>
                                <input type="hidden" name="cambiar_estado">
                                <button type="submit">Actualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>